<?php namespace ProcessWire;

/**
 * ProcessWire Grapick Fieldtype
 *
 * Copyright (C) 2023 by Jacob Gorny, Solon Media Group LLC 
 * Licensed under MPL 2.0
 * 
 */

 class FieldtypeGrapick extends Fieldtype implements Module {

	public static function getModuleInfo() {
		return array(
			'title' => 'Grapick',
			'version' => '1.0.0',
			'author' => 'Jacob Gorny',
			'href' => 'https://github.com/solonmedia/FieldtypeGrapick',
			'summary' => 'Field that incorporates the Grapick javascript gradient designer and stores an array of 32-bit rgba colors, gradient positions and optionally gradient styles and a plaintext style rule for CSS.',
			'installs' => 'InputfieldGrapick',
			'icon' => 'paint-brush',
			'autoload' => true,
            'singular' => true,
			'requires' => array(
                'ProcessWire>=3.0.16',
                'PHP>=8.0',
            ),
		);
	}

	/**
	 * Include our CssGradient class, which serves as the value for fields of type FieldtypeGrapick
	 *
	 */
	public function __construct() {	
		parent::__construct();
		require_once(dirname(__FILE__) . '/CssGradient.php'); 
	}


	public function init() {
	    $this->addHookAfter('ProcessPageEdit::buildFormContent', $this, 'setJsConfig');
	}

	public function setJsConfig($e) {
		$form = $e->return;
		$js_array = [];
		foreach($form->children as $fc) {
			$f_class = $fc->className;
			switch($f_class) {
				case 'InputfieldGrapick' :
					$ctrl = $fc->name;
					$js_array[$ctrl]['loaded'] = false;
				case 'InputfieldRepeater' :
				case 'InputfieldRepeaterMatrix' :
						foreach($fc->value as $enum => $it) {
							$fg = $it->template->fieldgroup;
							foreach($fg as $r_field) {
								if($r_field->type == 'FieldtypeGrapick') {
									$ctrl = $r_field->name.'_repeater'.$it->id;
									$js_array[$ctrl]['loaded'] = false;
								}
							}
						}
			}
		}		
		wire()->config->jsConfig('grapicks', $js_array);

		//bd($inputfields);
	}

	/**
	 * Return the Inputfield required by this Fieldtype
	 * 
	 * @param Page $page
	 * @param Field $field
	 * @return InputfieldCssGradient
	 *
	 */
	public function getInputfield(Page $page, Field $field) {
		/** @var InputfieldCssGradient $inputfield */

		$inputfield = $this->wire('modules')->get('InputfieldGrapick');

		if($page->template->pageClass == 'RepeaterMatrixPage') {
			if($page->getField($field->name)) {
				$field_in_context = $page->fieldgroup->getFieldContext($field, "matrix$page->repeater_matrix_type");
				if($field_in_context) {
					$field = $field_in_context;
				}
			}
		$inputfield->setField($field);
		$inputfield->setPage($page);
		}

		return $inputfield;
	}

	/**
	 * Return all compatible Fieldtypes 
	 * 
	 * @param Field $field
	 * @return null
	 *
	 */
	public function ___getCompatibleFieldtypes(Field $field) {
		// there are no other fieldtypes compatible with this one
		return null;
	}

	/**
	 * Sanitize value for runtime
	 * 
	 * @param Page $page
	 * @param Field $field
	 * @param CssGradient $value
	 * @return CssGradient
	 *
	 */
	public function sanitizeValue(Page $page, Field $field, $value) {

		// if it's not a CssGradient, then just return a blank CssGradient
		if(!$value instanceof CssGradient) $value = $this->getBlankValue($page, $field); 

		return $value; 
	}

	/**
	 * Get a blank value used by this fieldtype
	 * 
	 * @param Page $page
	 * @param Field $field
	 * @return CssGradient
	 *
	 */
	public function getBlankValue(Page $page, Field $field) {
		
		$context = ($page && $page->id) ? $field->getContext($page->template) : $field;

		if($page->template->pageClass == 'RepeaterMatrixPage') {
    		if($page->getField($field->name)) {
    			$context = $page->fieldgroup->getFieldContext($field, "matrix$page->repeater_matrix_type");
			}
    	}
		$gradient = new CssGradient(); //Context for this object isn't important because there is no config.

		return $gradient;

	}

	/**
	 * Given a raw value (value as stored in DB), return the value as it would appear in a Page object
	 *
	 * @param Page $page
	 * @param Field $field
	 * @param string|int|array $value
	 * @return CssGradient
	 *
	 */
	public function ___wakeupValue(Page $page, Field $field, $value) {

		// get a blank CssGradient instance
		$gradient = $this->getBlankValue($page, $field); 

		// populate the gradient
		$gradient->style = $value['style'];
		$gradient->origin = $value['origin'];
		$gradient->angle = ($value['angle'] == '') ? 0 : $value['angle'];
		$gradient->stops = $value['data'];
		$gradient->rule = $value['rule'];
		$gradient->size = ($value['size'] == '') ? 1 : $value['size'];
		$gradient->setTrackChanges(true); 

		return $gradient; 
	}

	/**
	 * Given an 'awake' value, as set by wakeupValue, convert the value back to a basic type for storage in DB. 
	 *              
	 * @param Page $page
	 * @param Field $field
	 * @param string|int|array|object $value
	 * @return array
	 * @throws WireException
	 *
	 */
	public function ___sleepValue(Page $page, Field $field, $value) {

		$gradient = $value;

		if(!$gradient instanceof CssGradient) {
			throw new WireException("Expecting an instance of CssGradient");
		}

		$sleepValue = [
			'data' => $gradient->stops,
			'style' => $gradient->style, 
			'angle' => wire()->sanitizer->digits($gradient->angle), 
			'origin' => $gradient->origin, 
			'size' => $gradient->size,
			'rule' => $gradient->build_rule(),
		];
		return $sleepValue; 
	}

	public function getDatabaseSchema(Field $field) {

		$schema = parent::getDatabaseSchema($field);

		$schema['data'] = 'varchar(512) NOT NULL';  // stops storage
		$schema['style'] = 'text NOT NULL'; 
		$schema['angle'] = 'text NOT NULL';
		$schema['origin'] = 'text NOT NULL';
		$schema['rule'] = 'text NOT NULL';
		$schema['size'] = 'text NOT NULL';

		return $schema;
	}

}

<?php namespace ProcessWire;

/**
 * ProcessWire Grapick Inputfield
 *
 * Provides the admin control panel inputs for FieldtypeGrapick
 * 
 * ProcessWire 3.x 
 * Copyright (C) 2024 by Jacob Gorny, Solon Media Group LLC 
 * Licensed under MPL 2.0
 * 
 * https://processwire.com
 * 
 */

class InputfieldGrapick extends Inputfield {

	public $js_array;

	/**
	 * Module information
	 */
	public static function getModuleInfo() {
		return array(
			'title' => "Grapick",
			'summary' => 'Field that implements Grapick javascript gradient designer.',
			'version' => '1.0.1',
			'author' => 'Jacob Gorny',
			'href' => 'https://github.com/solonmedia/FieldtypeGrapick',
			'icon' => 'paint-brush',
			'requires' => array(
				'FieldtypeGrapick',
                'ProcessWire>=3.0.16',
                'PHP>=8.0',
            ),
		);
	}

	/**
	 * Construct
	 * 
	 * @throws WireException
	 * 
	 */
	public function __construct() {
		require_once(dirname(__FILE__) . '/CssGradient.php');
		parent::__construct();
	}

	/**
	 * Set an attribute to this Inputfield
	 *
	 * In this case, we just capture the 'value' attribute and make sure it's something valid
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return $this
	 * @throws WireException
 	 *
	 */
	public function setAttribute($key, $value) {

		if($key == 'value' && !$value instanceof CssGradient && !is_null($value)) {
			throw new WireException("This input only accepts a CssGradient for its value"); 
		}

		return parent::setAttribute($key, $value); 
	}


    /**
     * Set the current Field
     *
     * @param Field $field
     *
     */
    public function setField(Field $field) {
        $this->field = $field; 
    }

	/**
	 * Page object that houses this field.
	 */
	protected $page;
	public function setPage(Page $page)
	{
		$this->page = $page;
	}

	/**
	 * @return FieldtypeGrapick
	 * 
	 */
	public function fieldtype() {
		/** @var FieldtypeGrapick $fieldtype */
		$fieldtype = $this->wire()->modules->get('FieldtypeGrapick');
		return $fieldtype;
	}

	//Load JS and CSS assets

	public function renderReady(Inputfield $parent = null, $renderValueMode = false) {

		// Add JS and CSS dependencies
		$config = $this->config;
		$info = $this->getModuleInfo();
		$version = $info['version'];
		$mod_url = $config->urls->{$this};
		$mod_scripts = [
			$mod_url . "vendor/grapick_0_1_10/dist/grapick.min.js?v=$version",
			$mod_url . "vendor/spectrum_1_8_1/spectrum.js?v=$version",
			$mod_url . "{$this}.js?v=$version",
		];
		$mod_styles = [
			$mod_url . "vendor/grapick_0_1_10/dist/grapick.min.css?v=$version",
			$mod_url . "vendor/spectrum_1_8_1/spectrum.css?v=$version",
			$mod_url . "{$this}.css?v=$version",
		];
		foreach($mod_scripts as $ms) {
			$config->scripts->add($ms);
		}
		foreach($mod_styles as $ms) {
			$config->styles->add($ms);
		}

		$this->config->js('InputfieldGrapick', [
				'settings' => [
					'test' => 'item',
				],
			],
		);

		return parent::renderReady($parent, $renderValueMode);
	}

	/**
	 * Render the markup needed to draw the Inputfield
	 * 
	 * @return string
	 *
	 */
	public function ___render() {
	
		$sanitizer = $this->wire()->sanitizer;
		$adminTheme = $this->wire()->adminTheme;

		$name = $this->attr('name');
		$page = $this->page;
		$id = $this->attr('id'); 

		$def_gradient = new CssGradient();

		if($this->attr('value')) $gradient = $this->attr('value');
		else {
			$gradient = $def_gradient;
		} 

		$stops = (empty($gradient->stops)) ? $def_gradient->stops : $gradient->stops;
		$style = $gradient->style;
		$origin = $gradient->origin;
		$angle = $gradient->angle;
		$size = $gradient->size;
		$rule = $gradient->rule;

		$classes = array('input' => '', 'select' => '', 'radio' => '');
	
		$labels = array(
			'style' => $this->_('Gradient Style'), 
			'origin' => $this->_('Origin'),
			'angle' => $this->_('Angle'),
		);

		if($adminTheme && method_exists($adminTheme, 'getClass')) {
			foreach(array_keys($classes) as $key) {
				$classes[$key] = $adminTheme->getClass($key);
			}
		}

		foreach($labels as $key => $label) {
			$labels[$key] = $sanitizer->entities1($label);
		}

		$style_radios = [
			'linear' => 'Linear',
			'conical' => 'Conical',
			'radial-circle' => 'Radial Circle',
			'radial-ellipse' => 'Radial Ellipse',
			'repeating-linear' => 'Repeating Linear',
			'repeating-conical' => 'Repeating Conical',
			'repeating-radial-circle' => 'Repeating Circle',
			'repeating-radial-ellipse' => 'Repeating Ellipse',
		];

		$origin_options = [
			'-100%_-100%' => 'Far Top Left',
			'50%_-100%' => 'Far Top Center',
			'200%_-100%' => 'Far Top Right',
			'-50%_-50%' => 'Near Top Left',
			'50%_-50%' => 'Near Top Center',
			'150%_-50%' => 'Near Top Right',
			'top_left' => 'Top Left',
			'top_center' => 'Top Center',
			'top_right' => 'Top Right',
			'-100%_50%' => 'Far Middle Left',
			'-50%_50%' => 'Near Middle Left',
			'center_left' => 'Middle Left',
			'center_center' => 'Center',
			'center_right' => 'Middle Right',
			'150%_50%' => 'Near Middle Right',
			'200%_50%' => 'Far Middle Right',
			'bottom_left' => 'Bottom Left',
			'bottom_center' => 'Bottom Center',
			'bottom_right' => 'Bottom Right',
			'-50%_150%' => 'Near Bottom Left',
			'50%_150%' => 'Near Bottom Center',
			'150%_150%' => 'Near Bottom Right',
			'-100%_200%' => 'Far Bottom Left',
			'50%_200%' => 'Far Bottom Center',
			'200%_200%' => 'Far Bottom Right',
		];

		$inputfields = new InputfieldFieldset();

		$inputfields->label = 'Grapick Gradient';

		$f = $this->modules->get('InputfieldMarkup');
		$f->id = $name.'-gradient';
		$f->label = "Grapick Gradient";
		$f->name = $name.'_gradient';
		$f->columnWidth = 50;
		$f->value =  "<div class='uk-height-small grapick' id='{$name}_grapick_control'></div><br><div class='uk-dark uk-border-rounded uk-padding-remove'><span class='uk-text-meta' id='{$name}-rule'>{$rule}</span></div>";
		$inputfields->add($f);

		$f = $this->modules->get('InputfieldMarkup');
		$f->id = $name.'-sample';
		$f->label = "Gradient Sample";
		$f->name = $name.'_sample';
		$f->columnWidth = 50;
		$f->value = "<div id='{$name}-sample_target' class='uk-height-small uk-position-relative uk-padding-small' style='background:{$rule}'></div>";
		$inputfields->add($f);

		$f = $this->modules->get('InputfieldSelect');
		$f->id = $name.'-style';
		$f->label = 'Gradient Style';
		$f->name = $name.'_style';
		$f->columnWidth = 25;
		$f->options = $style_radios;
		$f->value = $style;
		$inputfields->add($f);

		$f = $this->modules->get('InputfieldSelect');
		$f->id = $name.'-origin';
		$f->label = 'Origin';
		$f->name = $name.'_origin';
		$f->options = $origin_options;
		$f->columnWidth = 25;
		$f->value = $origin;
		$inputfields->add($f);

		$f = $this->modules->get('InputfieldInteger');
		$f->id = $name.'-angle';
		$f->label = 'Angle';
		$f->name = $name.'_angle';
		$f->size = 4;
		$f->zeroNotEmpty = 1;
		$f->min = -360;
		$f->max = 360;
		$f->inputType = 'number';
		$f->columnWidth = 25;
		$f->value = $angle;
		$inputfields->add($f);

		$f = $this->modules->get('InputfieldInteger');
		$f->id = $name.'-size';
		$f->label = 'Size';
		$f->name = $name.'_size';
		$f->size = 3;
		$f->zeroNotEmpty = 1;
		$f->inputType = 'number';
		$f->min = 1;
		$f->notes = '';
		$f->columnWidth = 25;
		$f->value = $size;
		$inputfields->add($f);

		$f = $this->modules->get('InputfieldText');
		$f->id = $name.'-stops';
		$f->label = 'Stops';
		$f->name = $name.'_stops';
		$f->placeholder = 'This is where Grapick will place stops as they are generated in text code.';
		$f->notes = 'If you alter these values, you must save to see the results.';
		$f->columnWidth = 100;
		$f->collapsed = 1;
		$f->value = $stops;
		$f->defaultValue = $def_gradient->stops;
		$inputfields->add($f);

		return $inputfields->render(); 
	}

	/**
	 * Process the input after a form submission
	 * 
	 * @param WireInputData $input
	 * @return $this
	 *
	 */
	public function ___processInput(WireInputData $input) {

		$name = $this->attr('name');
		$gradient = $this->attr('value');

		$input_names = array(
			'style' => "{$name}_style",
			'origin' => "{$name}_origin",
			'angle' => "{$name}_angle",
			'stops' => "{$name}_stops",
			'size' => "{$name}_size"
		);


        foreach($input_names as $key => $p_name) {

            if(!empty($input->$p_name) && isset($input->$p_name) && $gradient->$key != $input->$p_name) {

                $gradient->set($key, $input->$p_name);
				$this->trackChange('gradient');

            }
        }

		return $this;
	}



}

<?php namespace ProcessWire;

class CssGradient extends WireData {

    public function __construct($options = []) {

        //style refers to the dropdown default value
        //origin refers to the string used to calculate the starting point for the gradient
        //stops refers to the array of color stops

		parent::__construct();
		$this->set('style', 'linear');
		$this->set('origin', '');
        $this->set('angle', '180');
		$this->set('stops', 'FFFFFFFF^0|FF000000^100');
        $this->set('rule', '');
        $this->set('size', '');

        foreach($options as $key => $opt) {
            $this->set($key, $opt);
            $this->set('rule', $this->build_rule());
        }

    }

	public function set($key, $value) {

        $sanitizer = wire()->sanitizer;
        //sanitize
        switch($key) {
            case 'style':
            case 'origin':
                $value = $sanitizer->chars($value, '[alpha][digit]_-%');
                break;
            case 'stops':
                $value = $sanitizer->chars($value,'[digit]abcdefABCDEF|^');
                break;
            case 'size':
                $value = $sanitizer->int($value, ['min' => 0, 'blankValue' => 100]);
                break;
            case 'angle':
                $value = $sanitizer->int($value, ['min' => -360, 'max' => 360, 'blankValue' => 0]);
                break;
            case 'rule':
                $value = $this->getRule();
                break;
        }

		return parent::set($key, $value);

	}

	public function get($key) {

        return parent::get($key);
        
	}

    public function build_rule() {

        $css_fn = '';
        $shape = '';
        $origin = false;
        $angle = false;
        $size = false;
        $repeat = false;

        switch($this->style) {
            case 'radial-circle' :
                $css_fn = 'radial-gradient';
                $shape = 'circle ';
                $origin = str_replace('_', ' ', $this->origin);
                break;
            case 'radial-ellipse' :
                $css_fn = 'radial-gradient';
                $shape = 'ellipse ';
                $shape .= ($this->size == '') ? 'farthest-corner' : $this->size.'% '.$this->size.'%'; 
                $origin = str_replace('_', ' ', $this->origin);
                break;
            case 'repeating-linear' :
                $css_fn = 'repeating-linear-gradient';
                $angle = $this->angle . 'deg ';
                $repeat = true;
                break;
            case 'repeating-radial-circle' :
                $css_fn = 'repeating-radial-gradient';
                $shape = 'circle ';
                $origin = str_replace('_', ' ', $this->origin);
                $repeat = true;
                $size = $this->size;
                break;
            case 'repeating-radial-ellipse' :
                $css_fn = 'repeating-radial-gradient';
                $shape = 'ellipse ';
                $shape .= ($this->size == '') ? 'farthest-corner' : $this->size.'% '.$this->size.'%'; 
                $origin = str_replace('_', ' ', $this->origin);
                $repeat = true;
                break;
            case 'conical' :
                $css_fn = 'conic-gradient';
                $angle = 'from ' . $this->angle . 'deg ';
                $origin = str_replace('_', ' ', $this->origin);
                break;
            case 'repeating-conical' :
                $css_fn = 'repeating-conic-gradient';
                $angle = 'from ' . $this->angle . 'deg ';
                $origin = str_replace('_', ' ', $this->origin);
                $repeat = true;
                break;
            case 'linear' :
            default : 
                $css_fn = 'linear-gradient';
                $angle = $this->angle . 'deg ';
                break;

        }

        $out = "$css_fn(";

        $out .= ($shape !== '') ? $shape .' ' : '';
        $out .= ($angle) ? $angle . '' : '';
        $out .= ($origin) ?  ' at ' . $origin . ' ' : '';
        
        $out = rtrim($out);

        $rule = ', ';

        if(!empty($this->stops)) {
            $stops_ary = explode('|',$this->stops);
            foreach($stops_ary as $stop) {
                $details = explode('^',$stop);
                if ($repeat) {
                    $gr_size = ($details[1]!=0) ? (int) $details[1] : 100 ;
		        	$px_size = ($details[1]/100) * $gr_size;
                    if (str_contains($this->style,'conical')) {
                        $rule .= $this->toRgba($details[0]) . ' ' . round($px_size) .'%, ';
                    } else {
                        $rule .= $this->toRgba($details[0]) . ' ' . round($px_size) .'px, ';
                    }
                } else {
                    $rule .= $this->toRgba($details[0]) . ' ' . $details[1] .'%, ';
                }
            }
        }

        
        $out .= rtrim($rule, ', ' );

        $out .= ");";

        return $out;

    }

    public function getRule(string $delim=null) {

        $out = $this->build_rule();
        if($delim === '') {
            $out = rtrim($out, ';');
        } elseif ($delim) {
            $delim = substr($delim,0,1);
            $out = str_replace(';',$delim,$out);
        }

        return $out;
    }

    public function __toString() {
        
        $out = $this->build_rule();

        return $out;

    }

    private function toRgba($color) {

        $default = 'transparent';

        //Return default if no color provided
        if(empty($color) || $color == 'transparent')
            return $default; 

        //Sanitize $color if "#" is provided 
            if ($color[0] == '#' ) {
                $color = substr( $color, 1 );
            }

            $hex = array(
                $color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5],
                $color[6] . $color[7],
            );

            $rgba =  array_map('hexdec', $hex);

            $output = 'rgba('.$rgba[1].', '.$rgba[2].', '.$rgba[3].', '.round((float)($rgba[0]/255),1).')';

            return $output;

    }


}
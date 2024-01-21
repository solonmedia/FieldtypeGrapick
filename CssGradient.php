<?php namespace ProcessWire;

class CssGradient extends WireData {

    public function __construct() {

        parent::__construct();

    }

	public function set($key, $value) {

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
                $shape = 'circle at';
                $origin = str_replace('_', ' ', $this->origin);
                break;
            case 'radial-ellipse' :
                $css_fn = 'radial-gradient';
                $shape = 'ellipse 125% 125% at';
                $origin = str_replace('_', ' ', $this->origin);
                break;
            case 'repeating-linear' :
                $css_fn = 'repeating-linear-gradient';
                $angle = str_replace('-','',$this->angle);
                $repeat = true;
                break;
            case 'repeating-radial-circle' :
                $css_fn = 'repeating-radial-gradient';
                $shape = 'circle  at';
                $origin = str_replace('_', ' ', $this->origin);
                $repeat = true;
                break;
            case 'repeating-radial-ellipse' :
                $css_fn = 'repeating-radial-gradient';
                $shape = 'ellipse 125% 125% at';
                $origin = str_replace('_', ' ', $this->origin);
                $repeat = true;
                break;
            case 'linear' :
            default : 
                $css_fn = 'linear-gradient';
                $angle = str_replace('-','',$this->angle);
                break;

        }

        $out = "$css_fn(";

        $out .= ($shape !== '') ? $shape .' ' : '';

        $out .= ($origin) ?  $origin . ', ' : '';
        $out .= ($angle) ? $angle . ', ' : '';

        $rule = '';

        if(!empty($this->stops)) {
            $stops_ary = explode('|',$this->stops);
            foreach($stops_ary as $stop) {
                $details = explode('^',$stop);
                if ($repeat) {
                    $gr_size = ($details[1]!=0) ? (int) $details[1] : 100 ;
		        	$px_size = ($details[1]/100) * $gr_size;

                    $rule .= $this->toRgba($details[0]) . ' ' . round($px_size) .'px, ';
                } else {
                    $rule .= $this->toRgba($details[0]) . ' ' . $details[1] .'%, ';
                }
            }
        }

        
        $out .= rtrim($rule, ', ' );

        $out .= ");";

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
<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

Class CSSCompression_Combine
{
	/**
	 * Combine Patterns
	 *
	 * @class Control: Compression Controller
	 * @param (array) options: Reference to options
	 * @param (array) methods: List of options with their corresponding handler
	 */
	private $Control;
	private $options;
	private $methods = array(
		'csw-combine' => 'combineCSWproperties',
		'auralcp-combine' => 'combineAuralCuePause',
		'mp-combine' => 'combineMPproperties',
		'border-combine' => 'combineBorderDefinitions',
		'font-combine' => 'combineFontDefinitions',
		'background-combine' => 'combineBackgroundDefinitions',
		'list-combine' => 'combineListProperties',
	);

	/**
	 * Stash a reference to the controller on each instantiation
	 *
	 * @param (class) control: CSSCompression Controller
	 */
	public function __construct( CSSCompression_Control $control ) {
		$this->Control = $control;
		$this->options = &$control->Option->options;
	}

	/**
	 * Reads through each detailed package and checks for cross defn combinations
	 *
	 * @param (array) selectors: Array of selectors
	 * @param (array) details: Array of details
	 */
	public function combine( $selectors = array(), $details = array() ) {
		foreach ( $details as &$value ) {
			foreach ( $this->methods as $option => $fn ) {
				if ( $this->options[ $option ] ) {
					$value = $this->$fn( $value );
				}
			}
		}

		return array( $selectors, $details );
	}

	/**
	 * Combines color/style/width of border/outline properties
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	private function combineCSWproperties( $val ) {
		$storage = array();
		$pattern = "/(border|outline)-(color|style|width):(.*?);/is";
		preg_match_all( $pattern, $val, $matches );

		for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
			$a = strtolower( $matches[ 1 ][ $i ] );
			$b = strtolower( $matches[ 2 ][ $i ] );

			if ( ! isset( $storage[ $a ] ) ) {
				$storage[ $a ] = array();
			}

			$storage[ $a ][ $b ] = $matches[ 3 ][ $i ];
		}

		// Go through each tag for possible combination
		foreach ( $storage as $tag => $arr ) {
			// Make sure all 3 are defined and they aren't directionals
			if ( count( $arr ) == 3 && ! $this->checkUncombinables( $arr ) ) {
				// String to replace each instance with
				$replace = "$tag:" . $arr['width'] . ' ' . $arr['style'] . ' ' . $arr['color'];
				// Replace every instance, as multiple declarations removal will correct it
				foreach ( $arr as $x => $y ) {
					$val = str_ireplace( "$tag-$x:$y", $replace, $val );
				}
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines Aural properties (currently being depreciated in W3C Standards)
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	private function combineAuralCuePause( $val ) {
		$storage = array();
		$pattern = "/(cue|pause)-(before|after):(.*?);/i";
		preg_match_all( $pattern, $val, $matches );

		for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
			$a = strtolower( $matches[ 1 ][ $i ] );
			$b = strtolower( $matches[ 2 ][ $i ] );

			if ( ! isset( $storage[ $a ] ) ) {
				$storage[ $a ] = array();
			}

			$storage[ $a ][ $b ] = $matches[ 3 ][ $i ];
		}

		// Go through each tag for possible combination
		foreach ( $storage as $tag => $arr ) {
			if ( count( $arr ) == 2 && ! $this->checkUncombinables( $arr ) ) {
				// String to replace each instance with
				$replace = "$tag:" . $arr['before'] . ' ' . $arr['after'];
				// Replace every instance, as multiple declarations removal will correct it
				foreach ( $arr as $x => $y ) {
					$val = str_ireplace( "$tag-$x:$y", $replace, $val );
				}
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines multiple directional properties of 
	 * margin/padding into single definition.
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	private function combineMPproperties( $val ) {
		$storage = array();
		$pattern = "/(margin|padding)-(top|right|bottom|left):(.*?);/i";
		preg_match_all( $pattern, $val, $matches );

		for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++){
			if ( ! isset( $storage[ $matches[1][$i] ] ) ) {
				$storage[ $matches[1][$i] ] = array( $matches[2][$i] => $matches[3][$i] );
			}

			// Override double written properties
			$storage[$matches[1][$i]][$matches[2][$i]] = $matches[3][$i];
		}

		// Go through each tag for possible combination
		foreach ( $storage as $tag => $arr ) {
			// Drop capitols
			$tag = strtolower( $tag );

			// Only combine if all 4 definitions are found
			if ( count( $arr ) == 4 && ! $this->checkUncombinables( $arr ) ) {
				// If all definitions are the same, combine into single definition
				if ( $arr['top'] == $arr['bottom'] && $arr['left'] == $arr['right'] && $arr['top'] == $arr['left'] ) {
					// String to replace each instance with
					$replace = "$tag:" . $arr['top'];

					// Replace every instance, as multiple declarations removal will correct it
					foreach ( $arr as $a => $b ) {
						$val = str_ireplace( "$tag-$a:$b", $replace, $val );
					}
				}
				// If opposites are the same, combine into single definition
				else if ( $arr['top'] == $arr['bottom'] && $arr['left'] == $arr['right'] ) {
					// String to replace each instance with
					$replace = "$tag:" . $arr['top'] . ' ' . $arr['left'];
					// Replace every instance, as multiple declarations removal will correct it
					foreach ( $arr as $a => $b ) {
						$val = str_ireplace( "$tag-$a:$b", $replace, $val );
					}
				}
				else{
					// String to replace each instance with
					$replace = "$tag:" . $arr['top'] . ' ' . $arr['right'] . ' ' . $arr['bottom'] . ' ' . $arr['left'];
					// Replace every instance, as multiple declarations removal will correct it
					foreach ( $arr as $a => $b ) {
						$val = str_ireplace( "$tag-$a:$b", $replace, $val );
					}
				}
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines multiple border properties into single definition
	 *
	 * @param (string) val: CSS Selector Properties
	 */
	private function combineBorderDefinitions( $val ) {
		$storage = array();
		$pattern = "/(border)-(top|right|bottom|left):(.*?);/i";
		preg_match_all( $pattern, $val, $matches );

		for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
			$a = $matches[1][$i];
			$b = $matches[2][$i];

			if ( ! isset( $storage[ $a ] ) ) {
				$storage[ $a ] = array(  $b => $matches[ 3 ][ $i ] );
			}
			else {
				$storage[ $a ][ $b ] = $matches[ 3 ][ $i ];
			}
		}

		foreach ( $storage as $tag => $arr ) {
			if ( count( $arr ) == 4 && $arr['top'] == $arr['bottom'] && $arr['left'] == $arr['right'] && $arr['top'] == $arr['right'] ) {
				// String to replace each instance with
				$replace = "$tag:" . $arr['top'];

				// Replace every instance, as multiple declarations removal will correct it
				foreach ( $arr as $a => $b ) {
					$val = str_ireplace( "$tag-$a:$b", $replace, $val );
				}
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines multiple font-definitions into single definition
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	private function combineFontDefinitions( $val ) {
		$storage = array();
		$pattern = "/(font|line)-(style|variant|weight|size|height|family):(.*?);/i";
		preg_match_all( $pattern, $val, $matches );

		for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
			// Store each property in it's full state
			$storage[ $matches[1][$i] . '-' . $matches[2][$i] ] = $matches[3][$i];
		}

		// Combine font-size & line-height if possible
		if ( isset( $storage['font-size'] ) && isset( $storage['line-height'] ) ) {
			$storage['size/height'] = $storage['font-size'] . '/' . $storage['line-height'];
			unset( $storage['font-size'], $storage['line-height'] );
		}

		// Setup property groupings
		$fonts = array(
			array( 'font-style', 'font-variant', 'font-weight', 'size/height', 'font-family' ),
			array( 'font-style', 'font-variant', 'font-weight', 'font-size', 'font-family' ),
			array( 'font-style', 'font-variant', 'size/height', 'font-family' ),
			array( 'font-style', 'font-variant', 'font-size', 'font-family' ),
			array( 'font-style', 'font-weight', 'size/height', 'font-family' ),
			array( 'font-style', 'font-weight', 'font-size', 'font-family' ),
			array( 'font-variant', 'font-weight', 'size/height', 'font-family' ),
			array( 'font-variant', 'font-weight', 'font-size', 'font-family' ),
			array( 'font-weight', 'size/height', 'font-family' ),
			array( 'font-weight', 'font-size', 'font-family' ),
			array( 'font-variant', 'size/height', 'font-family' ),
			array( 'font-variant', 'font-size', 'font-family' ),
			array( 'font-style', 'size/height', 'font-family' ),
			array( 'font-style', 'font-size', 'font-family' ),
			array( 'size/height', 'font-family' ),
			array( 'font-size', 'font-family' ),
		);

		// Loop through each property check and see if they can be replaced
		foreach ( $fonts as $props ) {
			if ( $replace = $this->searchDefinitions( 'font', $storage, $props ) ) {
				break;
			}
		}

		// If replacement string found, run it on all options
		if ( $replace ) {
			for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
				if ( ! isset( $storage['line-height'] ) || 
					( isset( $storage['line-height'] ) && stripos( $matches[0][$i], 'line-height') !== 0 ) ) {
						$val = str_ireplace( $matches[0][$i], $replace, $val );
				}
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines multiple background props into single definition
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	private function combineBackgroundDefinitions( $val ) {
		$storage = array();
		$pattern = "/background-(color|image|repeat|attachment|position):(.*?);/i";
		preg_match_all( $pattern, $val, $matches );

		for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
			// Store each property in it's full state
			$storage[ $matches[1][$i] ] = $matches[2][$i];
		}

		// List of background props to check
		$backgrounds = array(
			// With color
			array( 'color', 'image', 'repeat', 'attachment', 'position' ),
			array( 'color', 'image', 'attachment', 'position' ),
			array( 'color', 'image', 'repeat', 'position' ),
			array( 'color', 'image', 'repeat', 'attachment' ),
			array( 'color', 'image', 'repeat' ),
			array( 'color', 'image', 'attachment' ),
			array( 'color', 'image', 'position' ),
			array( 'color', 'image' ),
			// Without Color
			array( 'image', 'attachment', 'position' ),
			array( 'image', 'repeat', 'position' ),
			array( 'image', 'repeat', 'attachment' ),
			array( 'image', 'repeat' ),
			array( 'image', 'attachment' ),
			array( 'image', 'position' ),
			array( 'image' ),
			// Just Color
			array( 'color' ),
		);

		// Run background checks and get replacement str
		foreach ( $backgrounds as $props ) {
			if ( $replace = $this->searchDefinitions( 'background', $storage, $props ) ) {
				break;
			}
		}

		// If replacement string found, run it on all options
		if ( $replace ) {
			for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
				$val = str_ireplace( $matches[0][$i], $replace, $val );
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines multiple list style props into single definition
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	private function combineListProperties( $val ) {
		$storage = array();
		$pattern = "/list-style-(type|position|image):(.*?);/i";
		preg_match_all( $pattern, $val, $matches );

		// Store secondhand prop
		for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
			$storage[ $matches[1][$i] ] = $matches[2][$i];
		}

		// List os list-style props to check against
		$lists = array(
			array( 'type', 'position', 'image' ),
			array( 'type', 'position' ),
			array( 'type', 'image' ),
			array( 'position', 'image' ),
			array( 'type' ),
			array( 'position' ),
			array( 'image' ),
		);

		// Run background checks and get replacement str
		foreach ( $lists as $props ) {
			if ( $replace = $this->searchDefinitions( 'list-style', $storage, $props ) ) {
				break;
			}
		}

		// If replacement string found, run it on all options
		if ( $replace ) {
			for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
				$val = str_ireplace( $matches[0][$i], $replace, $val );
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Helper function to ensure flagged words don't get
	 * overridden
	 *
	 * @param (array|string) obj: Array/String of definitions to be checked
	 */ 
	private function checkUncombinables( $obj ) {
		if ( is_array( $obj ) ) {
			foreach ( $obj as $item ) {
				if ( preg_match( "/inherit|\!important|\s/i", $item ) ) {
					return true;
				}
			}
			return false;
		}
		else {
			return preg_match( "/inherit|\!important|\s/i", $obj );
		}
	}

	/**
	 * Helper function to ensure all values of search array
	 * exist within the storage array
	 *
	 * @param (string) prop: CSS Property
	 * @param (array) storage: Array of definitions found
	 * @param (array) search: Array of definitions requred
	 */ 
	private function searchDefinitions( $prop, $storage, $search ) {
		// Return if storage & search don't match
		if ( count( $storage ) != count( $search ) ) {
			return false;
		}

		$str = "$prop:";
		foreach ( $search as $value ) {
			if ( ! isset( $storage[ $value ] ) || $this->checkUncombinables( $storage[ $value ] ) ) {
				return false;
			}
			$str .= $storage[ $value ] . ' ';
		}
		return trim( $str ) . ';';
	}


	/**
	 * Access to private methods for testing
	 *
	 * @param (string) method: Method to be called
	 * @param (array) args: Array of paramters to be passed in
	 */
	public function access( $method, $args ) {
		if ( method_exists( $this, $method ) ) {
			return call_user_func_array( array( $this, $method ), $args );
		}
		else {
			throw new Exception( "Unknown method in Color Class - " . $method );
		}
	}
};

?>

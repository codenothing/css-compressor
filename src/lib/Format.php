<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

Class CSSCompression_Format
{
	/**
	 * Format Patterns
	 *
	 * @class Control: Compression Controller
	 * @param (array) options: Reference to options
	 * @param (regex) rsemicolon: Checks for semicolon without an escape '\' character before it
	 * @param (regex) rcolon: Checks for colon without an escape '\' character before it
	 * @param (array) readability: Mapping to readability functions
	 */
	private $Control;
	private $options = array();
	private $rsemicolon = "/(?<!\\\);/";
	private $rcolon = "/(?<!\\\):/";
	private $readability = array(
		CSSCompression::READ_MAX => 'maximum',
		CSSCompression::READ_MED => 'medium',
		CSSCompression::READ_MIN => 'minimum',
		CSSCompression::READ_NONE => 'none',
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
	 * Reformats compressed CSS into specified format
	 *
	 * @param (int) readability: Readability level of compressed output
	 * @param (string) import: CSS Import property removed at beginning
	 * @param (array) selectors: Array of selectors
	 * @param (array) details: Array of details
	 */ 
	public function readability( $readability = CSSCompression::READ_NONE, $import = '', $selectors = array(), $details = array() ) {
		if ( isset( $this->readability[ $readability ] ) ) {
			$fn = $this->readability[ $readability ];
			return trim( $this->$fn( $import, $selectors, $details ) );
		}
		else {
			return 'Invalid Readability Value';
		}
	}

	/**
	 * Returns maxium readability, breaking on every selector, brace, and property
	 *
	 * @param (string) import: CSS Import property removed at beginning
	 * @param (array) selectors: Array of selectors
	 * @param (array) details: Array of details
	 */ 
	private function maximum( $import, $selectors, $details ) {
		$css = str_replace( ';', ";\n", $import );
		if ( $import ) {
			$css .= "\n";
		}

		foreach ( $selectors as $k => $v ) {
			if ( ! $details[ $k ] || trim( $details[ $k ] ) == '' ) {
				continue;
			}

			$v = str_replace( '>', ' > ', $v );
			$v = str_replace( '+', ' + ', $v );
			$v = str_replace( ',', ', ', $v );
			$css .= "$v {\n";
			$arr = preg_split( $this->rsemicolon, $details[ $k ] );

			foreach ( $arr as $item ) {
				if ( ! $item ) {
					continue;
				}

				list( $prop, $val ) = preg_split( $this->rcolon, $item, 2 );
				$css .= "\t$prop: $val;\n";
			}

			// Kill that last semicolon at users request
			if ( $this->options['unnecessary-semicolons'] ) {
				$css = preg_replace( "/;\n$/", "\n", $css );
			}

			$css .= "}\n\n";
		}

		return $css;
	}

	/**
	 * Returns medium readability, putting selectors and details on new lines
	 *
	 * @param (string) import: CSS Import property removed at beginning
	 * @param (array) selectors: Array of selectors
	 * @param (array) details: Array of details
	 */ 
	private function medium( $import, $selectors, $details ) {
		$css = str_replace( ';', ";\n", $import );
		foreach ( $selectors as $k => $v ) {
			if ( $details[ $k ] && $details[ $k ] != '' ) {
				$css .= "$v {\n\t" . $details[ $k ] . "\n}\n";
			}
		}

		return $css;
	}

	/**
	 * Returns minimum readability, breaking after every selector and it's details
	 *
	 * @param (string) import: CSS Import property removed at beginning
	 * @param (array) selectors: Array of selectors
	 * @param (array) details: Array of details
	 */ 
	private function minimum( $import, $selectors, $details ) {
		$css = str_replace( ';', ";\n", $import );
		foreach ( $selectors as $k => $v ) {
			if ( $details[ $k ] && $details[ $k ] != '' ) {
				$css .= "$v{" . $details[ $k ] . "}\n";
			}
		}

		return $css;
	}
	
	/**
	 * Returns an unreadable, but fully compressed script
	 *
	 * @param (string) import: CSS Import property removed at beginning
	 * @param (array) selectors: Array of selectors
	 * @param (array) details: Array of details
	 */ 
	private function none( $import, $selectors, $details ) {
		$css = $import;
		foreach ( $selectors as $k => $v ) {
			if ( isset( $details[ $k ] ) && $details[ $k ] != '' ) {
				$css .= trim( "$v{" . $details[ $k ] . "}" );
			}
		}

		return $css;
	}

	/**
	 * Byte format return of file sizes
	 *
	 * @param (int) size: File size in Bytes
	 */ 
	public function size( $size = 0 ) {
		$ext = array( 'B', 'K', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );
		for( $c = 0; $size > 1024; $c++ ) {
			$size /= 1024;
		}
		return round( $size, 2 ) . $ext[ $c ];
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

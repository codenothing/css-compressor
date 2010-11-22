<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

Class CSSCompression_Individuals
{
	/**
	 * Individual patterns
	 *
	 * @class Control: Compression Controller
	 * @class Numeric: Numeric handler
	 * @class Color: Color Handler
	 * @param (array) options: Reference to options
	 * @param (regex) rdirectional: Properties that may have multiple directions
	 * @param (regex) rnoneprop: Properties that can have none as their value(will be converted to 0)
	 * @param (regex) rnone: Looks for a none value in shorthand notations
	 * @param (regex) rfilter: Special alpha filter for msie
	 * @param (regex) rspace: Checks for space without an escape '\' character before it
	 * @param (array) weights: Array of font-weight name conversions to their numeric counterpart
	 */
	private $Control;
	private $Numeric;
	private $Color;
	private $options = array();
	private $rdirectional = "/^(margin|padding)$/";
	private $rnoneprop = "/^(border|background)/";
	private $rnone = "/\snone\s/";
	private $rfilter = "/[\"']?PROGID:DXImageTransform.Microsoft.Alpha\(Opacity=(\d+)\)[\"']?/i";
	private $rspace = "/(?<!\\\)\s/";
	private $weights = array(
		"lighter" => 100,
		"normal" => 400,
		"bold" => 700,
		"bolder" => 900,
	);

	/**
	 * Stash a reference to the controller on each instantiation
	 *
	 * @param (class) control: CSSCompression Controller
	 */
	public function __construct( CSSCompression_Control $control ) {
		$this->Control = $control;
		$this->Numeric = $control->Numeric;
		$this->Color = $control->Color;
		$this->options = &$control->Option->options;
	}

	/**
	 * Runs special unit/directional compressions
	 *
	 * @param (string) prop: CSS Property
	 * @param (string) val: Value of CSS Property
	 */ 
	public function individuals( $prop, $val ) {
		// Properties should always be lowercase
		$prop = strtolower( $prop );

		// Split up each definiton for color and numeric compressions
		$parts = preg_split( $this->rspace, $val );
		foreach ( $parts as &$v ) {
			if ( ! $v || $v == '' ) {
				continue;
			}

			// Remove uneeded decimals/units
			if ( $this->options['format-units'] ) {
				$v = $this->Numeric->numeric( $v );
			}

			// Color compression
			$v = $this->Color->color( $v );
		}
		$val = trim( implode( ' ', $parts ) );

		// Remove uneeded side definitions if possible
		if ( $this->options['directional-compress'] && count( $parts ) > 1 && preg_match( $this->rdirectional, $prop ) ) {
			$val = $this->directionals( strtolower( $val ) );
		}

		// Font-weight converter
		if ( $this->options['fontweight2num'] && $prop === 'font-weight' ) {
			$val = $this->fontweight( strtolower( $val ) );
		}

		// None to 0 converter
		$val = $this->none( $prop, $val );

		// MSIE Filters
		$val = $this->filter( $prop, $val );

		// Return for list retrival
		return array( $prop, $val );
	}

	/**
	 * Finds directional compression on methods like margin/padding
	 *
	 * @param (string) val: Value of CSS Property
	 */ 
	private function directionals( $val ) {
		// Split up each definiton
		$direction = preg_split( $this->rspace, $val );

		// 4 Direction reduction
		$count = count( $direction );
		if ( $count == 4 ) {
			// All 4 sides are the same, combine into 1 definition
			if ( $direction[0] == $direction[1] && $direction[2] == $direction[3] && $direction[0] == $direction[3] ) {
				$direction = array( $direction[ 0 ] );
			}
			// top-bottom/left-right are the same, reduce definition
			else if ( $direction[0] == $direction[2] && $direction[1] == $direction[3] ) {
				$direction = array( $direction[ 0 ], $direction[ 1 ] );
			}
		}
		// 3 Direction reduction
		else if ( $count == 3 ) {
			// All directions are the same
			if ( $direction[0] == $direction[1] && $direction[1] == $direction[2] ) {
				$direction = array( $direction[ 0 ] );
			}
			// Only top(first) and bottom(last) are the same
			else if ( $direction[0] == $direction[2] ) {
				$direction = array( $direction[ 0 ], $direction[ 1 ] );
			}
		}
		// 2 Direction reduction
		// Both directions are the same, combine into single definition
		else if ( $count == 2 && $direction[0] == $direction[1] ) {
			$direction = array( $direction[ 0 ] );
		}

		// Return the combined version of the directions
		// Single entries will just return
		return implode( ' ', $direction );
	}

	/**
	 * Converts font-weight names to numbers
	 *
	 * @param (string) val: font-weight prop value
	 */ 
	private function fontweight( $val ) {
		return isset( $this->weights[ $val ] ) ? $this->weights[ $val ] : $val;
	}

	/**
	 * Convert none vals to 0
	 *
	 * @param (string) prop: Current Property
	 * @param (string) val: property value
	 */ 
	private function none( $prop, $val ) {
		if ( preg_match( $this->rnoneprop, $prop ) ) {
			if ( $val == 'none' ) {
				$val = '0';
			}
			// Wrap spaces in case none is the last value
			else if ( preg_match( $this->rnone, " " . $val . " " ) ) {
				$val = trim( preg_replace( $this->rnone, ' 0 ', " " . $val . " " ) );
			}
		}

		return $val;
	}

	/**
	 * MSIE Filter Conversion
	 *
	 * @param (string) prop: Current Property
	 * @param (string) val: property value
	 */ 
	private function filter( $prop, $val ) {
		if ( preg_match( "/filter/", $prop ) ) {
			$val = preg_replace( $this->rfilter, "alpha(opacity=$1)", $val );
		}

		return $val;
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

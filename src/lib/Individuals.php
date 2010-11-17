<?php

Class CSSCompression_Individuals extends CSSCompression_Format
{
	/**
	 * Individual patterns
	 *
	 * @param (regex) rdirectional: Properties that may have multiple directions
	 * @param (regex) rnone: Properties that can have none as their value(will be converted to 0)
	 * @param (regex) rfilter: Special alpha filter for msie
	 */
	private $rdirectional = "/^(margin|padding)$/";
	private $rnone = "/^(border|background)$/";
	private $rfilter = "/PROGID:DXImageTransform.Microsoft.Alpha\(Opacity=(\d+)\)/i";

	/**
	 * Array of font-weight name conversions to their
	 * numeric counterpart
	 */
	private $weights = array(
		"lighter" => 100,
		"normal" => 400,
		"bold" => 700,
		"bolder" => 900,
	);

	/**
	 * Just passes along the initializer
	 */
	protected function __construct( $css = NULL, $options = NULL ) {
		parent::__construct( $css, $options );
	}

	/**
	 * Runs special unit/directional compressions
	 *
	 * @param (string) prop: CSS Property
	 * @param (string) val: Value of CSS Property
	 */ 
	protected function individuals( $prop, $val ) {
		// Properties should always be lowercase
		$prop = strtolower( $prop );

		// Split up each definiton for color and numeric compressions
		$parts = preg_split( $this->r_space, $val );
		foreach ( $parts as &$v ) {
			if ( ! $v || $v == '' ) {
				continue;
			}

			// Remove uneeded decimals/units
			if ( $this->options['format-units'] ) {
				$v = $this->numeric( $v );
			}

			// Color compression
			$v = $this->color( $v );
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

		// Convert none vals to 0
		if ( preg_match( $this->rnone, $prop ) && $val == 'none' ) {
			$val = '0';
		}

		// Thank you ms for this nasty conversion
		if ( preg_match( "/filter/", $prop ) ) {
			$val = preg_replace( $this->rfilter, "alpha(opacity=$1)", $val );
		}

		// Return for list retrival
		return array( $prop, $val );
	}

	/**
	 * Finds directional compression on methods like margin/padding
	 *
	 * @param (string) val: Value of CSS Property
	 */ 
	private function directionals( $val ) {
		// Check if side definitions already reduced down to a single definition
		if ( strpos( $val, ' ' ) === false ) {
			// Redundent, but just in case
			if ( $this->options['format-units'] ) {
				$val = $this->numeric( $val );
			}
			return $val;
		}

		// Split up each definiton
		$direction = preg_split( $this->r_space, $val );

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
	protected function fontweight( $val ) {
		return isset( $this->weights[ $val ] ) ? $this->weights[ $val ] : $val;
	}
};

?>

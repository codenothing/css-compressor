<?php

Class CSSCompression_Numeric extends CSSCompression_Individuals
{
	/**
	 * Numerical regexs for trimming down units
	 *
	 * @param (regex) rdecimal: Checks for zero decimal
	 * @param (regex) runit: Checks for suffix on 0 unit
	 * @param (regex) rzero: Checks for preceding 0 to decimal unit
	 */
	private $rdecimal = "/^(\d+\.0*)(\%|[a-z]{2})$/i";
	private $runit = "/^0(\%|[a-z]{2})$/i";
	private $rzero = "/^0(\.\d+)(\%|[a-z]{2})?$/i";


	/**
	 * Just passes along the initializer
	 */
	protected function __construct( $css = NULL, $options = NULL ) {
		parent::__construct( $css, $options );
	}


	/**
	 * Runs all numeric operations
	 *
	 * @param (string) str: Unit string
	 */
	protected function numeric( $str ) {
		$str = $this->decimal( $str );
		$str = $this->units( $str );
		$str = $this->zeroes( $str );
		return $str;
	}

	/**
	 * Remove's unecessary decimal, ie 13.0px => 13px
	 *
	 * @param (string) str: Unit string
	 */ 
	private function decimal( $str ) {
		if ( preg_match( $this->rdecimal, $str, $match ) ) {
			$str = intval( $match[ 1 ] ) . $match[ 2 ];
		}

		return $str;
	}

	/**
	 * Removes suffix from 0 unit, ie 0px; => 0;
	 *
	 * @param (string) str: Unit string
	 */ 
	private function units( $str ) {
		if ( preg_match( $this->runit, $str, $match ) ) {
			$str = '0';
		}

		return $str;
	}


	/**
	 * Removes leading zero in decimal, ie 0.33px => .33px
	 *
	 * @param (string) str: Unit string
	 */
	private function zeroes( $str ) {
		if ( preg_match( $this->rzero, $str, $match ) ) {
			$str = $match[ 1 ] . $match[ 2 ];
		}

		return $str;
	}
};

?>

<?php

Class Color
{
	// Prevent instance creation
	// Static only class
	private function __construct(){}
	private function __clone(){}

	// List of colors
	private static $colors = array(
		'red' => 31,
		'green' => 32,
		'yellow' => 33,
		'blue' => 34
	);

	// Main wrapping function
	public static function wrap( $color = 'red', $bold = false, $msg = 'Invalid Message' ) {
		return "\x1B[" . ( $bold ? '1' : '0' ) . ";" . self::$colors[ $color ] . "m" . $msg . "\x1B[0m";
	}

	// Main utility functions ( red, gree, yellow, blue )
	public static function red( $msg ) {
		return self::wrap( 'red', false, $msg );
	}

	public static function green( $msg ) {
		return self::wrap( 'green', false, $msg );
	}

	public static function yellow( $msg ) {
		return self::wrap( 'yellow', false, $msg );
	}

	public static function blue( $msg ) {
		return self::wrap( 'blue', false, $msg );
	}


	// Bold versions of utitlity functions
	public static function boldred( $msg ) {
		return self::wrap( 'red', true, $msg );
	}

	public static function boldgreen( $msg ) {
		return self::wrap( 'green', true, $msg );
	}

	public static function boldyellow( $msg ) {
		return self::wrap( 'yellow', true, $msg );
	}

	public static function boldblue( $msg ) {
		return self::wrap( 'blue', true, $msg );
	}
};

?>

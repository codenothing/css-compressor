<?php
/**
 * CSS Compressor [VERSION] - Test Suite
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */
error_reporting( E_ALL );
require( dirname( __FILE__ ) . '/src/Core.php' );


/**
 * Start the unit tests by passing in list of temp blocks
 *
 * @param (array) temp: Files that are only temporarily blocked until fix is found
 * @param (array) only: For testing, focus only on this set of test files
 */
new CSScompression_Test(array(
	'temp' => array(
	),
	'only' => array(
	),
));

?>

<?php
/**
 * CSS Compressor [VERSION] - Test Suite
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */
error_reporting( E_ALL );
require( dirname( __FILE__ ) . '/src/Focus.php' );



/**
 * Configuration for the focused test (Manipulate these!)
 *
 * @param (string) class: Subclass to focus on
 * @param (string) method: Class method to focus on
 * @param (string) mode: What mode you want the compressor in
 * @param (array) options: Any extra set of options needed
 * @param (mixed) params: Parameters to pass into the method
 * @param (mixed) expect: Expected result
 */
new FocusedTest(array(
	'class' => 'Combine.BorderRadius',
	'method' => 'combine',
	'mode' => 'small',
	'options' => array(
	),
	'params' => array(
		"border-radius:10px 10px 10px 10px",
	),
	'expect' => "border-radius:10px;",
));

?>

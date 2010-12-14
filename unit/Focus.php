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
	'class' => 'Combine.Border',
	'method' => 'replace',
	'mode' => 'small',
	'options' => array(
	),
	'params' => array(
		"border-top:1px solid red;color:blue;border-left:1px solid red;border-right:1px solid red;border-bottom:1px solid red;",
	),
	'expect' => "border:1px solid red;",
));

?>

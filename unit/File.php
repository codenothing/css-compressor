<?php
/**
 * CSS Compressor [VERSION] - Test Suite
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */
error_reporting( E_ALL );
require( dirname( __FILE__ ) . '/src/File.php' );



/**
 * Configuration for the file test (Manipulate these!)
 *
 * @param (string) file: Filename to test (resides in unit/sheets)
 * @param (string) mode: What mode you want the compressor in
 * @param (array) options: Any extra set of options needed
 */
new FileTest(array(
	'file' => 'id.css',
	'mode' => 'sane',
	'options' => array(
	),
));

?>

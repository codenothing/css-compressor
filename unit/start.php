<?php
/**
 * CSS Compressor [VERSION] - Test Suite
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */
error_reporting( E_ALL );

// Include compressor and unit tests
$root = dirname(__FILE__);
include( $root . '/../src/css-compression.php' );
include( $root . '/color.php' );
include( $root . '/unit.php' );

// Unit Testing is on autorun
new CSScompressionTestUnit();

?>

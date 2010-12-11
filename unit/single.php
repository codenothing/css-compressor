<?php
/**
 * CSS Compressor [VERSION] - Test Suite
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */
error_reporting( E_ALL );

// Access Setup
$config = array(
	'options' => 'small',
	'class' => 'Combine',
	'method' => 'combineBorderRadius',
	'params' => array(
		"-webkit-border-radius:10px 9px 8px 7px/5px 4px 3px 2px;",
	),
	'expect' => "blah",
);


// Include compressor and unit tests
$root = dirname( __FILE__ );
include( $root . '/../src/CSSCompression.inc' );
include( $root . '/color.php' );


// Create instance based on requirments
$CSSC = new CSSCompression( $config['options'] );
$result = $CSSC->access( $config['class'], $config['method'], $config['params'] );


// Just have to eyeball array based tests
if ( is_array( $config['expect'] ) ) {
	print_r( $result );
	print_r( $config['expect'] );
}
// Strict comparrison
else if ( $result === $config['expect'] ) {
	echo "Expecting:\n" . $config['expect'] . "\n====\nResult:\n" . $result . "\n====\n" . Color::boldgreen('Singular Test Passed') . "\n\n";
	exit( 0 );
}
// Failed
else {
	echo "Expecting:\n" . $config['expect'] . "\n====\nResult:\n" . $result . "\n====\n" . Color::boldRed('Singular Test Failed') . "\n\n";
	exit( 1 );
}

?>

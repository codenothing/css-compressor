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
	'class' => 'Trim',
	'method' => 'comments',
	'params' => array(
		"t\"/*e\"/*ab\"c\\*/def*/st",
	),
	'expect' => "t\"/*e\"st"
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
	echo $config['expect'] . "\n====\n" . $result . "\n====\n" . Color::boldgreen('Singular Test Passed') . "\n\n";
	exit( 0 );
}
// Failed
else {
	echo $config['expect'] . "\n====\n" . $result . "\n====\n" . Color::boldRed('Singular Test Failed') . "\n\n";
	exit( 1 );
}

?>

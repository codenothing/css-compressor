<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 
require("../src/CSSCompression.inc");

// Make sure all options have a setting
foreach ( CSSCompression::$defaults as $key => $value ) {
	if ( $key == 'readability' ) {
		continue;
	}
	$_POST[ $key ] = isset( $_POST[ $key ] ) && $_POST[ $key ] == 'on' ? true : false;
}

// Setup the instance and run the compression
$options = $_POST['mode'] == 'custom' ? $_POST : $_POST['mode'];
$CSSC = new CSSCompression( '', $options );
$CSSC->option( 'readability', $_POST['readability'] );
$CSSC->compress( $_POST['css'] );


// Size display
function size( $size ) {
	$original = "(${size}B)";
	$ext = array( 'B', 'K', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );
	for ( $c = 0; $size > 1024; $c++ ) {
		$size /= 1024;
	}
	return round( $size, 2 ) . $ext[ $c ] . $original;
}

// Stats
$stats = $CSSC->stats;
$before = $stats['before'];
$after = $stats['after'];

// Custom size handling
$size = array(
	'before' => size( $before['size'] ),
	'after' => size( $after['size'] ),
	'final' => size( $before['size'] - $after['size'] ) . ' ' 
		. number_format( $after['size'] / ( $before['size'] < 1 ? 1 : $before['size'] ) * 100, 2 ) 
		. '%'
);

?>
<!DOCTYPE html>
<html>
<head>
	<title>CSS Compressor [VERSION]</title>
	<link rel='stylesheet' type='text/css' href='result.css' />
</head>
<body>


<div id='results'>
<table cellspacing='1'>
	<tr>
		<th>&nbsp;</th>
		<th>Before</th>
		<th>After</th>
		<th>Compresssion</th>
	</tr>
	<tr>
		<th>Time</th>
		<td>-</td>
		<td>-</td>
		<td><?php echo round( $after['time'] - $before['time'], 3 ); ?>ms</td>
	</tr>
	<tr>
		<th>Selectors</th>
		<td><?php echo $before['selectors']; ?></td>
		<td><?php echo $after['selectors']; ?></td>
		<td><?php echo ( $before['selectors'] - $after['selectors'] ); ?></td>
	</tr>
	<tr>
		<th>Properties</th>
		<td><?php echo $before['props']; ?></td>
		<td><?php echo $after['props']; ?></td>
		<td><?php echo ( $before['props'] - $after['props'] ); ?></td>
	</tr>
	<tr>
		<th>Size</th>
		<td><?php echo $size['before']; ?></td>
		<td><?php echo $size['after']; ?></td>
		<td><?php echo $size['final']; ?></td>
	</tr>
</table>
<textarea onclick='this.select()'><?php echo $CSSC->css; ?></textarea><br><br>
</div>

</body>
</html>

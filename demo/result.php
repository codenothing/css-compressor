<!DOCTYPE html>
<html>
<head>
	<title>CSS Compressor [VERSION]</title>
	<link rel='stylesheet' type='text/css' href='result.css' />
</head>
<body>

<!--
CSS Compressor [VERSION]
[DATE]
Corey Hart @ http://www.codenothing.com
-->


<?php
// Run compression on passed script
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

// Add results above the form
echo "<div id='results'>";
// $CSSC->displayStats();
echo "<textarea onclick='this.select()'>".$CSSC->css."</textarea><br><br>";
echo '</div>';
?>

</body>
</html>

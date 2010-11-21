<?
require('CSSCompression.inc');
$obj = new CSSCompression();
echo $obj->access('Color', 'hex2short', array( '#FF0000' ) );
//echo $obj->compress( "BODY { margin: 10px 10.0px 10.0px 10.0px; color: #FF0000; } ", "small" );
echo "\n\n";
?>

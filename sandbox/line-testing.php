<?php
/**
 * CSS Compressor - Test Suite
 * Sepetember 5, 2009
 * Corey Hart @ http://www.codenothing.com
 *
 * Holds array of all unit tests needed to be done
 * on all methods possible.
 */

$testarr = array(
	// runSpecialCompressions method
	'runSpecialCompressions' => array(
		// Directionals/Unit compression
		'margin:10.0px 10px 10px 10px' => 'margin:10px',
		'padding:10px 15px 10.0px 15.0px' => 'padding:10px 15px',
		'margin:10cm 9cm 8cm 7cm' => 'margin:10cm 9cm 8cm 7cm',
		'margin:10in 10in' => 'margin:10in',
		'padding:8in 7in' => 'padding:8in 7in',
		'padding:10cm' => 'padding:10cm',
		// Font-Weight compressions
		'font-weight:lighter' => 'font-weight:100',
		'font-weight:normal' => 'font-weight:400',
		'font-weight:bold' => 'font-weight:700',
		'font-weight:bolder' => 'font-weight:900',
		'font-weight:heavy' => 'font-weight:heavy',
		// RGB Conversions (Spaces are removed by inital trim)
		'color:rgb(145)' => 'color:#919191',
		'color:rgb(145,123,16)' => 'color:#917B10',
		'color:rgb(50%,50%,50%)' => 'color:#7F7F7F',
		// Long name to hex conversions
		'color:aliceblue' => 'color:#f0f8ff',
		'color:darkgoldenrod' => 'color:#b8860b',
		'color:lightgoldenrodyellow' => 'color:#fafad2',
		// Long hex to short name conversions
		'color:#f0ffff' => 'color:azure',
		'color:#cd853f' => 'color:peru',
		// Long hex to short hex conversions
		'color:#aa6600' => 'color:#a60',
		'color:#773366' => 'color:#736',
		'color:#772213' => 'color:#772213',
	),

	// lowercaseSelectors method
	'lowercaseSelectors' => array(
		'INPUT' => 'input',
		'FONT' => 'font',
		'INPUT.testclass' => 'input.testclass',
		'A:active,B:first-child' => 'a:active,b:first-child',
		'BODY>DIV:first-child+A:active>P:first-child' => 'body>div:first-child+a:active>p:first-child',
		'BODY>DIV:first-child * A:active>P:first-child' => 'body>div:first-child * a:active>p:first-child',
	),

	// combineCSWproperties method
	'combineCSWproperties' => array(
		'border-color:red;border-style:solid;border-width:2px;' => 'border:2px solid red;',
		'outline-color:blue;outline-style:thin;outline-width:1px;' => 'outline:1px thin blue;',
	),

	// combineAuralCuePause method
	'combineAuralCuePause' => array(
		'cue-before:url(sound.wav);cue-after:url(after.wav);' => 'cue:url(sound.wav) url(after.wav);',
	),

	// combineMPproperties method
	'combineMPproperties' => array(
		'margin-top:10px;margin-left:10px;margin-right:10px;margin-bottom:10px;' => 'margin:10px;',
		'padding-top:12px;padding-left:10px;padding-right:10px;padding-bottom:12px;' => 'padding:12px 10px;',
	),

	// combineBorderDefinitions method
	'combineBorderDefinitions' => array(
		'border-top:1px solid red;border-left:1px solid red;border-right:1px solid red;border-bottom:1px solid red;' => 'border:1px solid red;',
	),

	// combineFontDefinitions method
	'combineFontDefinitions' => array(
		'font-style:italic;font-variant:normal;font-weight:bold;font-size:12pt;line-height:20px;font-family:arial;' => 'font:italic normal bold 12pt/20px arial;',
		'font-style:italic;font-variant:normal;font-weight:bold;font-size:12pt;font-family:arial;' => 'font:italic normal bold 12pt arial;',
		'font-style:italic;font-variant:normal;font-size:12pt;line-height:20px;font-family:arial;' => 'font:italic normal 12pt/20px arial;',
		'font-style:italic;font-variant:normal;font-size:12pt;font-family:arial;' => 'font:italic normal 12pt arial;',
		'font-style:italic;font-weight:bold;font-size:12pt;line-height:20px;font-family:arial;' => 'font:italic bold 12pt/20px arial;',
		'font-style:italic;font-weight:bold;font-size:12pt;font-family:arial;' => 'font:italic bold 12pt arial;',
		'font-variant:normal;font-weight:bold;font-size:12pt;line-height:20px;font-family:arial;' => 'font:normal bold 12pt/20px arial;',
		'font-variant:normal;font-weight:bold;font-size:12pt;font-family:arial;' => 'font:normal bold 12pt arial;',
		'font-weight:bold;font-size:12pt;line-height:20px;font-family:arial;' => 'font:bold 12pt/20px arial;',
		'font-weight:bold;font-size:12pt;font-family:arial;' => 'font:bold 12pt arial;',
		'font-variant:normal;font-size:12pt;line-height:20px;font-family:arial;' => 'font:normal 12pt/20px arial;',
		'font-variant:normal;font-size:12pt;font-family:arial;' => 'font:normal 12pt arial;',
		'font-style:italic;font-size:12pt;line-height:20px;font-family:arial;' => 'font:italic 12pt/20px arial;',
		'font-style:italic;font-size:12pt;font-family:arial;' => 'font:italic 12pt arial;',
		'font-size:12pt;line-height:20px;font-family:arial;' => 'font:12pt/20px arial;',
		'font-size:12pt;font-family:arial;' => 'font:12pt arial;',
	),

	// combineBackgroundDefinitions method
	'combineBackgroundDefinitions' => array(
		'background-color:green;background-image:url(images/img.gif);background-repeat:no-repeat;background-attachment:scroll;background-position:center;' => 'background:green url(images/img.gif) no-repeat scroll center;',
		'background-color:green;background-image:url(images/img.gif);background-attachment:scroll;background-position:center;' => 'background:green url(images/img.gif) scroll center;',
		'background-color:green;background-image:url(images/img.gif);background-repeat:no-repeat;background-position:center;' => 'background:green url(images/img.gif) no-repeat center;',
		'background-color:green;background-image:url(images/img.gif);background-repeat:no-repeat;background-attachment:scroll;' => 'background:green url(images/img.gif) no-repeat scroll;',
		'background-color:green;background-image:url(images/img.gif);background-repeat:no-repeat;' => 'background:green url(images/img.gif) no-repeat;',
		'background-color:green;background-image:url(images/img.gif);background-attachment:scroll;' => 'background:green url(images/img.gif) scroll;',
		'background-color:green;background-image:url(images/img.gif);background-position:center;' => 'background:green url(images/img.gif) center;',
		'background-color:green;background-image:url(images/img.gif);' => 'background:green url(images/img.gif);',
		'background-image:url(images/img.gif);background-attachment:scroll;background-position:center;' => 'background:url(images/img.gif) scroll center;',
		'background-image:url(images/img.gif);background-repeat:no-repeat;background-position:center;' => 'background:url(images/img.gif) no-repeat center;',
		'background-image:url(images/img.gif);background-repeat:no-repeat;background-attachment:scroll;' => 'background:url(images/img.gif) no-repeat scroll;',
		'background-image:url(images/img.gif);background-repeat:no-repeat;' => 'background:url(images/img.gif) no-repeat;',
		'background-image:url(images/img.gif);background-attachment:scroll;' => 'background:url(images/img.gif) scroll;',
		'background-image:url(images/img.gif);' => 'background:url(images/img.gif);',
		'background-color:green;' => 'background:green;',
	),

	// combineListProperties method
	'combineListProperties' => array(
		'list-style-type:none;list-style-position:inline;list-style-image:url(images/img.gif);' => 'list-style:none inline url(images/img.gif);',
		'list-style-type:none;list-style-position:inline;' => 'list-style:none inline;',
		'list-style-type:none;list-style-image:url(images/img.gif);' => 'list-style:none url(images/img.gif);',
		'list-style-position:inline;list-style-image:url(images/img.gif);' => 'list-style:inline url(images/img.gif);',
		'list-style-type:none;' => 'list-style:none;',
		'list-style-position:inline;' => 'list-style:inline;',
		'list-style-image:url(images/img.gif);' => 'list-style:url(images/img.gif);',
	),
);
?>

<?php
/**
 * CSS Compressor [VERSION] - Test Suite
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 *
 * Holds array of all unit tests needed to be done
 * on all methods possible.
 */

$testarr = array(
	// runSpecialCompressions method
	// This test runs simple tests, as there are more extensive tests below, this
	// is just to make sure the central function works as expected
	'runSpecialCompressions' => array(
		// Directionals/Unit compression
		'margin:10.0px 10px 10px 10px' => 'margin:10px',
		'padding:10px 15px 10.0px 15.0px' => 'padding:10px 15px',
		'padding:10px 15px 10.0px' => 'padding:10px 15px',
		// Font-Weight compressions
		'font-weight:lighter' => 'font-weight:100',
		'font-weight:bolder' => 'font-weight:900',
		'font-weight:heavy' => 'font-weight:heavy',
		// RGB Conversions (Spaces are removed by inital trim)
		'color:rgb(145)' => 'color:#919191',
		'color:rgb(145,123,16)' => 'color:#917B10',
		'color:rgb(50%,50%,50%)' => 'color:#7F7F7F',
		// Long name to hex conversions
		'color:aliceblue' => 'color:#f0f8ff',
		// Long hex to short name conversions
		'color:#cd853f' => 'color:peru',
		// Long hex to short hex conversions
		'color:#aa6600' => 'color:#a60',
	),

	// sidesDirectional method
	'sidesDirectional' => array (
		'10px 10px 10px 10px' => '10px',
		'10px 15px 10px 15px' => '10px 15px',
		'10px 15px 10px' => '10px 15px',
		'10px 15px 12px' => '10px 15px 12px',
		'10cm 9cm 8cm 7cm' => '10cm 9cm 8cm 7cm',
		'10in 10in' => '10in',
		'8in 7in' => '8in 7in',
		'10cm' => '10cm',
	),

	// fontweightConversion method
	'fontweightConversion' => array(
		'lighter' => '100',
		'normal' => '400',
		'bold' => '700',
		'bolder' => '900',
		'heavy' => 'heavy',
		'blah' => 'blah',
	),

	// removeDecimal method
	'removeDecimal' => array(
		'1.0em' => '1em',
		'1.059em' => '1.059em',
	),

	// removeUnits method
	'removeUnits' => array(
		'0px' => '0',
		'1pt' => '1pt',
	),

	// runColorChanges method
	'runColorChanges' => array(
		// RGB Conversions (Spaces are removed by inital trim)
		'rgb(145)' => '#919191',
		'rgb(145,123,16)' => '#917B10',
		'rgb(50%,50%,50%)' => '#7F7F7F',
		// Long name to hex conversions
		'aliceblue' => '#f0f8ff',
		'darkgoldenrod' => '#b8860b',
		'lightgoldenrodyellow' => '#fafad2',
		// Long hex to short name conversions
		'#f0ffff' => 'azure',
		'#cd853f' => 'peru',
		// Long hex to short hex conversions
		'#aa6600' => '#a60',
		'#773366' => '#736',
		'#772213' => '#772213',
	),

	// lowercaseSelectors method
	'lowercaseSelectors' => array(
		'INPUT' => 'input',
		'FONT' => 'font',
		'INPUT.testclass' => 'input.testclass',
		'A:active,B:first-child' => 'a:active,b:first-child',
		'BODY>DIV:first-child+A:active * P:first-child' => 'body>div:first-child+a:active * p:first-child',
		'BODY#BODY>DIV.CLASS * A#ID>P.CLASS' => 'body#BODY>div.CLASS * a#ID>p.CLASS',
	),

	// combineCSWproperties method
	'combineCSWproperties' => array(
		'border-color:red;border-style:solid;border-width:2px;' => 'border:2px solid red;border:2px solid red;border:2px solid red;',
		'outline-color:blue;outline-style:thin;outline-width:1px;' => 'outline:1px thin blue;outline:1px thin blue;outline:1px thin blue;',
	),

	// combineAuralCuePause method
	'combineAuralCuePause' => array(
		'cue-before:url(sound.wav);cue-after:url(after.wav);' => 'cue:url(sound.wav) url(after.wav);cue:url(sound.wav) url(after.wav);',
	),

	// combineMPproperties method
	'combineMPproperties' => array(
		'margin-top:10px;margin-left:10px;' => 'margin-top:10px;margin-left:10px;',
		'margin-top:10px;margin-bottom:10px;' => 'margin-top:10px;margin-bottom:10px;',
		'margin-top:10px;margin-left:10px;margin-right:10px;margin-bottom:10px;' => 'margin:10px;margin:10px;margin:10px;margin:10px;',
		'padding-top:12px;padding-left:10px;padding-right:10px;padding-bottom:12px;' => 'padding:12px 10px;padding:12px 10px;padding:12px 10px;padding:12px 10px;',
	),

	// combineBorderDefinitions method
	'combineBorderDefinitions' => array(
		'border-top:1px solid red;border-left:1px solid red;border-right:1px solid red;border-bottom:1px solid red;' => 'border:1px solid red;border:1px solid red;border:1px solid red;border:1px solid red;',
	),

	// combineFontDefinitions method
	'combineFontDefinitions' => array(
		'font-style:italic;font-variant:normal;font-weight:bold;font-size:12pt;line-height:20px;font-family:arial;' => 'font:italic normal bold 12pt/20px arial;font:italic normal bold 12pt/20px arial;font:italic normal bold 12pt/20px arial;font:italic normal bold 12pt/20px arial;font:italic normal bold 12pt/20px arial;font:italic normal bold 12pt/20px arial;',
		'font-style:italic;font-variant:normal;font-weight:bold;font-size:12pt;font-family:arial;' => 'font:italic normal bold 12pt arial;font:italic normal bold 12pt arial;font:italic normal bold 12pt arial;font:italic normal bold 12pt arial;font:italic normal bold 12pt arial;',
		'font-style:italic;font-variant:normal;font-size:12pt;line-height:20px;font-family:arial;' => 'font:italic normal 12pt/20px arial;font:italic normal 12pt/20px arial;font:italic normal 12pt/20px arial;font:italic normal 12pt/20px arial;font:italic normal 12pt/20px arial;',
		'font-style:italic;font-variant:normal;font-size:12pt;font-family:arial;' => 'font:italic normal 12pt arial;font:italic normal 12pt arial;font:italic normal 12pt arial;font:italic normal 12pt arial;',
		'font-style:italic;font-weight:bold;font-size:12pt;line-height:20px;font-family:arial;' => 'font:italic bold 12pt/20px arial;font:italic bold 12pt/20px arial;font:italic bold 12pt/20px arial;font:italic bold 12pt/20px arial;font:italic bold 12pt/20px arial;',
		'font-style:italic;font-weight:bold;font-size:12pt;font-family:arial;' => 'font:italic bold 12pt arial;font:italic bold 12pt arial;font:italic bold 12pt arial;font:italic bold 12pt arial;',
		'font-variant:normal;font-weight:bold;font-size:12pt;line-height:20px;font-family:arial;' => 'font:normal bold 12pt/20px arial;font:normal bold 12pt/20px arial;font:normal bold 12pt/20px arial;font:normal bold 12pt/20px arial;font:normal bold 12pt/20px arial;',
		'font-variant:normal;font-weight:bold;font-size:12pt;font-family:arial;' => 'font:normal bold 12pt arial;font:normal bold 12pt arial;font:normal bold 12pt arial;font:normal bold 12pt arial;',
		'font-weight:bold;font-size:12pt;line-height:20px;font-family:arial;' => 'font:bold 12pt/20px arial;font:bold 12pt/20px arial;font:bold 12pt/20px arial;font:bold 12pt/20px arial;',
		'font-weight:bold;font-size:12pt;font-family:arial;' => 'font:bold 12pt arial;font:bold 12pt arial;font:bold 12pt arial;',
		'font-variant:normal;font-size:12pt;line-height:20px;font-family:arial;' => 'font:normal 12pt/20px arial;font:normal 12pt/20px arial;font:normal 12pt/20px arial;font:normal 12pt/20px arial;',
		'font-variant:normal;font-size:12pt;font-family:arial;' => 'font:normal 12pt arial;font:normal 12pt arial;font:normal 12pt arial;',
		'font-style:italic;font-size:12pt;line-height:20px;font-family:arial;' => 'font:italic 12pt/20px arial;font:italic 12pt/20px arial;font:italic 12pt/20px arial;font:italic 12pt/20px arial;',
		'font-style:italic;font-size:12pt;font-family:arial;' => 'font:italic 12pt arial;font:italic 12pt arial;font:italic 12pt arial;',
		'font-size:12pt;line-height:20px;font-family:arial;' => 'font:12pt/20px arial;font:12pt/20px arial;font:12pt/20px arial;',
		'font-size:12pt;font-family:arial;' => 'font:12pt arial;font:12pt arial;',
	),

	// combineBackgroundDefinitions method
	'combineBackgroundDefinitions' => array(
		'background-color:green;background-image:url(images/img.gif);background-repeat:no-repeat;background-attachment:scroll;background-position:center;' => 'background:green url(images/img.gif) no-repeat scroll center;background:green url(images/img.gif) no-repeat scroll center;background:green url(images/img.gif) no-repeat scroll center;background:green url(images/img.gif) no-repeat scroll center;background:green url(images/img.gif) no-repeat scroll center;',
		'background-color:green;background-image:url(images/img.gif);background-attachment:scroll;background-position:center;' => 'background:green url(images/img.gif) scroll center;background:green url(images/img.gif) scroll center;background:green url(images/img.gif) scroll center;background:green url(images/img.gif) scroll center;',
		'background-color:green;background-image:url(images/img.gif);background-repeat:no-repeat;background-position:center;' => 'background:green url(images/img.gif) no-repeat center;background:green url(images/img.gif) no-repeat center;background:green url(images/img.gif) no-repeat center;background:green url(images/img.gif) no-repeat center;',
		'background-color:green;background-image:url(images/img.gif);background-repeat:no-repeat;background-attachment:scroll;' => 'background:green url(images/img.gif) no-repeat scroll;background:green url(images/img.gif) no-repeat scroll;background:green url(images/img.gif) no-repeat scroll;background:green url(images/img.gif) no-repeat scroll;',
		'background-color:green;background-image:url(images/img.gif);background-repeat:no-repeat;' => 'background:green url(images/img.gif) no-repeat;background:green url(images/img.gif) no-repeat;background:green url(images/img.gif) no-repeat;',
		'background-color:green;background-image:url(images/img.gif);background-attachment:scroll;' => 'background:green url(images/img.gif) scroll;background:green url(images/img.gif) scroll;background:green url(images/img.gif) scroll;',
		'background-color:green;background-image:url(images/img.gif);background-position:center;' => 'background:green url(images/img.gif) center;background:green url(images/img.gif) center;background:green url(images/img.gif) center;',
		'background-color:green;background-image:url(images/img.gif);' => 'background:green url(images/img.gif);background:green url(images/img.gif);',
		'background-image:url(images/img.gif);background-attachment:scroll;background-position:center;' => 'background:url(images/img.gif) scroll center;background:url(images/img.gif) scroll center;background:url(images/img.gif) scroll center;',
		'background-image:url(images/img.gif);background-repeat:no-repeat;background-position:center;' => 'background:url(images/img.gif) no-repeat center;background:url(images/img.gif) no-repeat center;background:url(images/img.gif) no-repeat center;',
		'background-image:url(images/img.gif);background-repeat:no-repeat;background-attachment:scroll;' => 'background:url(images/img.gif) no-repeat scroll;background:url(images/img.gif) no-repeat scroll;background:url(images/img.gif) no-repeat scroll;',
		'background-image:url(images/img.gif);background-repeat:no-repeat;' => 'background:url(images/img.gif) no-repeat;background:url(images/img.gif) no-repeat;',
		'background-image:url(images/img.gif);background-attachment:scroll;' => 'background:url(images/img.gif) scroll;background:url(images/img.gif) scroll;',
		'background-image:url(images/img.gif);' => 'background:url(images/img.gif);',
		'background-color:green;' => 'background:green;',
	),

	// combineListProperties method
	'combineListProperties' => array(
		'list-style-type:none;list-style-position:inline;list-style-image:url(images/img.gif);' => 'list-style:none inline url(images/img.gif);list-style:none inline url(images/img.gif);list-style:none inline url(images/img.gif);',
		'list-style-type:none;list-style-position:inline;' => 'list-style:none inline;list-style:none inline;',
		'list-style-type:none;list-style-image:url(images/img.gif);' => 'list-style:none url(images/img.gif);list-style:none url(images/img.gif);',
		'list-style-position:inline;list-style-image:url(images/img.gif);' => 'list-style:inline url(images/img.gif);list-style:inline url(images/img.gif);',
		'list-style-type:none;' => 'list-style:none;',
		'list-style-position:inline;' => 'list-style:inline;',
		'list-style-image:url(images/img.gif);' => 'list-style:url(images/img.gif);',
	),

	// removeMultipleDefinitions method
	'removeMultipleDefinitions' => array(
		'margin-left:10px;color:blue;margin-left:10px;color:blue;' => 'margin-left:10px;color:blue;',
	),

	// removeEscapedURLs method
	'removeEscapedURLs' => array(
		"background:url(http\\://www.codenothing.com/random.php?foo=bar\\;&colon=te\\:st\\;)" => "background:url(http://www.codenothing.com/random.php?foo=bar;&colon=te:st;)",
		"color:blue\\;" => "color:blue\\;",
	),

	// removeUnnecessarySemicolon method
	'removeUnnecessarySemicolon' => array(
		'color:red;font-size:12pt;' => 'color:red;font-size:12pt',
		'background:white;' => 'background:white',
	),
);
?>

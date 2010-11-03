<?php
/**
 * CSS Compressor [VERSION] - Test Suite
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 *
 * Holds array of all unit tests needed to be done
 * on all methods possible.
 */

$sandbox = array(
	// runSpecialCompressions method
	// This test runs simple tests, as there are more extensive tests below, this
	// is just to make sure the central function works as expected
	'runSpecialCompressions' => array(
		// Directionals/Unit compression
		'Directionals: 4 to 1' => array(
			'margin:10.0px 10px 10px 10px',
			'margin:10px',
		),
		'Directionals: 4 to 2' => array(
			'padding:10px 15px 10.0px 15.0px',
			'padding:10px 15px',
		),
		'Directionals: 3 to 2' => array(
			'padding:10px 15px 10.0px',
			'padding:10px 15px',
		),

		// Font-Weight compressions
		'Weight: lighter' => array(
			'font-weight:lighter',
			'font-weight:100',
		),
		'Weight: bolder' => array(
			'font-weight:bolder',
			'font-weight:900',
		),
		'Weight: heavy' => array(
			'font-weight:heavy',
			'font-weight:heavy',
		),

		// RGB Conversions (Spaces are removed by inital trim)
		'RGB: single numeric' => array(
			'color:rgb(145)',
			'color:#919191',
		),
		'RGB: numeric' => array(
			'color:rgb(145,123,16)',
			'color:#917B10',
		),
		'RGB: percentage' => array(
			'color:rgb(50%,50%,50%)',
			'color:#7F7F7F',
		),

		// Long name to hex conversions
		'Color to hex' => array(
			'color:aliceblue',
			'color:#f0f8ff',
		),

		// Long hex to short name conversions
		'Hex to color' => array(
			'color:#cd853f',
			'color:peru',
		),

		// Long hex to short hex conversions
		'Longhex to Shorthex' => array(
			'color:#aa6600',
			'color:#a60',
		),
	),

	// sidesDirectional method
	'sidesDirectional' => array(
		'4 to 1' => array(
			'10px 10px 10px 10px',
			'10px',
		),
		'4 to 2' => array(
			'10px 15px 10px 15px',
			'10px 15px',
		),
		'3 to 2' => array(
			'10px 15px 10px',
			'10px 15px',
		),
		'3 to 3' => array(
			'10px 15px 12px',
			'10px 15px 12px',
		),
		'4 to 4' => array(
			'10cm 9cm 8cm 7cm',
			'10cm 9cm 8cm 7cm',
		),
		'2 to 1' => array(
			'10in 10in',
			'10in',
		),
		'2 to 2' => array(
			'8in 7in',
			'8in 7in',
		),
		'1 to 1' => array(
			'10cm' => '10cm',
		),
	),

	// fontweightConversion method
	'fontweightConversion' => array(
		'lighter' => array(
			'lighter',
			'100',
		),
		'normal' => array(
			'normal',
			'400',
		),
		'bold' => array(
			'bold',
			'700',
		),
		'bolder' => array(
			'bolder',
			'900',
		),
		'heavy' => array(
			'heavy',
			'heavy',
		),
		'blah' => array(
			'blah',
			'blah',
		),
	),

	// removeDecimal method
	'removeDecimal' => array(
		'remove' => array(
			'1.0em',
			'1em',
		),
		'keep' => array(
			'1.059em',
			'1.059em',
		),
	),

	// removeUnits method
	'removeUnits' => array(
		'remove' => array(
			'0px',
			'0',
		),
		'keep' => array(
			'1pt',
			'1pt',
		),
	),

	// runColorChanges method
	'runColorChanges' => array(
		// RGB Conversions (Spaces are removed by inital trim)
		'RGB: single numeric' => array(
			'rgb(145)',
			'#919191',
		),
		'RGB: numeric' => array(
			'rgb(145,123,16)',
			'#917B10',
		),
		'RGB: percentage' => array(
			'rgb(50%,50%,50%)',
			'#7F7F7F',
		),
		// Long name to hex conversions
		'Color2hex: aliceblue' => array(
			'aliceblue',
			'#f0f8ff',
		),
		'Color2hex: darkgoldenrod' => array(
			'darkgoldenrod',
			'#b8860b',
		),
		'Color2hex: lightgoldenrodyellow' => array(
			'lightgoldenrodyellow',
			'#fafad2',
		),
		// Long hex to short name conversions
		'Hex2color: azure' => array(
			'#f0ffff',
			'azure',
		),
		'Hex2color: peru' => array(
			'#cd853f',
			'peru',
		),
		// Long hex to short hex conversions
		'Long to short hex: #a60' => array(
			'#aa6600',
			'#a60',
		),
		'Long to short hex: #736' => array(
			'#773366',
			'#736',
		),
		'Long to short hex: #772213' => array(
			'#772213',
			'#772213',
		),
	),

	// lowercaseSelectors method
	'lowercaseSelectors' => array(
		'input' => array(
			'INPUT',
			'input',
		),
		'font' => array(
			'FONT',
			'font',
		),
		'class' => array(
			'INPUT.testclass',
			'input.testclass',
		),
		'pseudo' => array(
			'A:active,B:first-child',
			'a:active,b:first-child',
		),
		'complex' => array(
			'BODY>DIV:first-child+A:active * P:first-child',
			'body>div:first-child+a:active * p:first-child',
		),
		'id' => array(
			'BODY#BODY>DIV.CLASS * A#ID>P.CLASS',
			'body#BODY>div.CLASS * a#ID>p.CLASS',
		),
	),

	// combineCSWproperties method
	'combineCSWproperties' => array(
		'border' => array(
			'border-color:red;border-style:solid;border-width:2px;',
				'border:2px solid red;' .
				'border:2px solid red;' .
				'border:2px solid red;',
		),
		'outline' => array(
			'outline-color:blue;outline-style:thin;outline-width:1px;',
				'outline:1px thin blue;' .
				'outline:1px thin blue;' .
				'outline:1px thin blue;',
		),
	),

	// combineAuralCuePause method
	'combineAuralCuePause' => array(
		'cue' => array(
			'cue-before:url(sound.wav);cue-after:url(after.wav);',
				'cue:url(sound.wav) url(after.wav);' .
				'cue:url(sound.wav) url(after.wav);',
		),
	),

	// combineMPproperties method
	'combineMPproperties' => array(
		'top-left' => array(
			'margin-top:10px;margin-left:10px;',
			'margin-top:10px;margin-left:10px;',
		),
		'top-bottom' => array(
			'margin-top:10px;margin-bottom:10px;',
			'margin-top:10px;margin-bottom:10px;',
		),
		'4 to 1' => array(
			'margin-top:10px;margin-left:10px;margin-right:10px;margin-bottom:10px;',
				'margin:10px;' .
				'margin:10px;' .
				'margin:10px;' .
				'margin:10px;',
		),
		'4 to 2' => array(
			'padding-top:12px;padding-left:10px;padding-right:10px;padding-bottom:12px;',
				'padding:12px 10px;' .
				'padding:12px 10px;' .
				'padding:12px 10px;' .
				'padding:12px 10px;',
		),
	),

	// combineBorderDefinitions method
	'combineBorderDefinitions' => array(
		'border' => array(
			'border-top:1px solid red;border-left:1px solid red;border-right:1px solid red;border-bottom:1px solid red;',
				'border:1px solid red;' .
				'border:1px solid red;' .
				'border:1px solid red;' .
				'border:1px solid red;',
		),
	),

	// combineFontDefinitions method
	'combineFontDefinitions' => array(
		'1' => array(
			'font-style:italic;font-variant:normal;font-weight:bold;font-size:12pt;line-height:20px;font-family:arial;',
				'font:italic normal bold 12pt/20px arial;' .
				'font:italic normal bold 12pt/20px arial;' .
				'font:italic normal bold 12pt/20px arial;' .
				'font:italic normal bold 12pt/20px arial;' .
				'font:italic normal bold 12pt/20px arial;' .
				'font:italic normal bold 12pt/20px arial;',
		),
		'2' => array(
			'font-style:italic;font-variant:normal;font-weight:bold;font-size:12pt;font-family:arial;',
				'font:italic normal bold 12pt arial;' .
				'font:italic normal bold 12pt arial;' .
				'font:italic normal bold 12pt arial;' .
				'font:italic normal bold 12pt arial;' .
				'font:italic normal bold 12pt arial;',
		),
		'3' => array(
			'font-style:italic;font-variant:normal;font-size:12pt;line-height:20px;font-family:arial;',
				'font:italic normal 12pt/20px arial;' .
				'font:italic normal 12pt/20px arial;' .
				'font:italic normal 12pt/20px arial;' .
				'font:italic normal 12pt/20px arial;' .
				'font:italic normal 12pt/20px arial;',
		),
		'4' => array(
			'font-style:italic;font-variant:normal;font-size:12pt;font-family:arial;',
				'font:italic normal 12pt arial;' .
				'font:italic normal 12pt arial;' .
				'font:italic normal 12pt arial;' .
				'font:italic normal 12pt arial;',
		),
		'5' => array(
			'font-style:italic;font-weight:bold;font-size:12pt;line-height:20px;font-family:arial;',
				'font:italic bold 12pt/20px arial;' .
				'font:italic bold 12pt/20px arial;' .
				'font:italic bold 12pt/20px arial;' .
				'font:italic bold 12pt/20px arial;' .
				'font:italic bold 12pt/20px arial;',
		),
		'6' => array(
			'font-style:italic;font-weight:bold;font-size:12pt;font-family:arial;',
				'font:italic bold 12pt arial;' .
				'font:italic bold 12pt arial;' .
				'font:italic bold 12pt arial;' .
				'font:italic bold 12pt arial;',
		),
		'7' => array(
			'font-variant:normal;font-weight:bold;font-size:12pt;line-height:20px;font-family:arial;',
				'font:normal bold 12pt/20px arial;' .
				'font:normal bold 12pt/20px arial;' .
				'font:normal bold 12pt/20px arial;' .
				'font:normal bold 12pt/20px arial;' .
				'font:normal bold 12pt/20px arial;',
		),
		'8' => array(
			'font-variant:normal;font-weight:bold;font-size:12pt;font-family:arial;',
				'font:normal bold 12pt arial;' .
				'font:normal bold 12pt arial;' .
				'font:normal bold 12pt arial;' .
				'font:normal bold 12pt arial;',
		),
		'9' => array(
			'font-weight:bold;font-size:12pt;line-height:20px;font-family:arial;',
				'font:bold 12pt/20px arial;' .
				'font:bold 12pt/20px arial;' .
				'font:bold 12pt/20px arial;' .
				'font:bold 12pt/20px arial;',
		),
		'10' => array(
			'font-weight:bold;font-size:12pt;font-family:arial;',
				'font:bold 12pt arial;' .
				'font:bold 12pt arial;' .
				'font:bold 12pt arial;',
		),
		'11' => array(
			'font-variant:normal;font-size:12pt;line-height:20px;font-family:arial;',
				'font:normal 12pt/20px arial;' .
				'font:normal 12pt/20px arial;' .
				'font:normal 12pt/20px arial;' .
				'font:normal 12pt/20px arial;',
		),
		'12' => array(
			'font-variant:normal;font-size:12pt;font-family:arial;',
				'font:normal 12pt arial;' .
				'font:normal 12pt arial;' .
				'font:normal 12pt arial;',
		),
		'13' => array(
			'font-style:italic;font-size:12pt;line-height:20px;font-family:arial;',
				'font:italic 12pt/20px arial;' .
				'font:italic 12pt/20px arial;' .
				'font:italic 12pt/20px arial;' .
				'font:italic 12pt/20px arial;',
		),
		'14' => array(
			'font-style:italic;font-size:12pt;font-family:arial;',
				'font:italic 12pt arial;' .
				'font:italic 12pt arial;' .
				'font:italic 12pt arial;',
		),
		'15' => array(
			'font-size:12pt;line-height:20px;font-family:arial;',
				'font:12pt/20px arial;' .
				'font:12pt/20px arial;' .
				'font:12pt/20px arial;',
		),
		'16' => array(
			'font-size:12pt;font-family:arial;',
				'font:12pt arial;' .
				'font:12pt arial;',
		),
	),

	// combineBackgroundDefinitions method
	'combineBackgroundDefinitions' => array(
		'1' => array(
			'background-color:green;background-image:url(images/img.gif);background-repeat:no-repeat;' .
			'background-attachment:scroll;background-position:center;',
				'background:green url(images/img.gif) no-repeat scroll center;' .
				'background:green url(images/img.gif) no-repeat scroll center;' .
				'background:green url(images/img.gif) no-repeat scroll center;' .
				'background:green url(images/img.gif) no-repeat scroll center;' .
				'background:green url(images/img.gif) no-repeat scroll center;',
		),
		'2' => array(
			'background-color:green;background-image:url(images/img.gif);background-attachment:scroll;background-position:center;',
				'background:green url(images/img.gif) scroll center;' .
				'background:green url(images/img.gif) scroll center;' .
				'background:green url(images/img.gif) scroll center;' .
				'background:green url(images/img.gif) scroll center;',
		),
		'3' => array(
			'background-color:green;background-image:url(images/img.gif);background-repeat:no-repeat;background-position:center;',
				'background:green url(images/img.gif) no-repeat center;' .
				'background:green url(images/img.gif) no-repeat center;' .
				'background:green url(images/img.gif) no-repeat center;' .
				'background:green url(images/img.gif) no-repeat center;',
		),
		'4' => array(
			'background-color:green;background-image:url(images/img.gif);background-repeat:no-repeat;background-attachment:scroll;',
				'background:green url(images/img.gif) no-repeat scroll;' .
				'background:green url(images/img.gif) no-repeat scroll;' .
				'background:green url(images/img.gif) no-repeat scroll;' .
				'background:green url(images/img.gif) no-repeat scroll;',
		),
		'5' => array(
			'background-color:green;background-image:url(images/img.gif);background-repeat:no-repeat;',
				'background:green url(images/img.gif) no-repeat;' .
				'background:green url(images/img.gif) no-repeat;' .
				'background:green url(images/img.gif) no-repeat;',
		),
		'6' => array(
			'background-color:green;background-image:url(images/img.gif);background-attachment:scroll;',
				'background:green url(images/img.gif) scroll;' .
				'background:green url(images/img.gif) scroll;' .
				'background:green url(images/img.gif) scroll;',
		),
		'7' => array(
			'background-color:green;background-image:url(images/img.gif);background-position:center;',
				'background:green url(images/img.gif) center;' .
				'background:green url(images/img.gif) center;' .
				'background:green url(images/img.gif) center;',
		),
		'8' => array(
			'background-color:green;background-image:url(images/img.gif);',
				'background:green url(images/img.gif);' .
				'background:green url(images/img.gif);',
		),
		'9' => array(
			'background-image:url(images/img.gif);background-attachment:scroll;background-position:center;',
				'background:url(images/img.gif) scroll center;' .
				'background:url(images/img.gif) scroll center;' .
				'background:url(images/img.gif) scroll center;',
		),
		'10' => array(
			'background-image:url(images/img.gif);background-repeat:no-repeat;background-position:center;',
				'background:url(images/img.gif) no-repeat center;' .
				'background:url(images/img.gif) no-repeat center;' .
				'background:url(images/img.gif) no-repeat center;',
		),
		'11' => array(
			'background-image:url(images/img.gif);background-repeat:no-repeat;background-attachment:scroll;',
				'background:url(images/img.gif) no-repeat scroll;' .
				'background:url(images/img.gif) no-repeat scroll;' .
				'background:url(images/img.gif) no-repeat scroll;',
		),
		'12' => array(
			'background-image:url(images/img.gif);background-repeat:no-repeat;',
				'background:url(images/img.gif) no-repeat;' .
				'background:url(images/img.gif) no-repeat;',
		),
		'13' => array(
			'background-image:url(images/img.gif);background-attachment:scroll;',
				'background:url(images/img.gif) scroll;' .
				'background:url(images/img.gif) scroll;',
		),
		'14' => array(
			'background-image:url(images/img.gif);',
			'background:url(images/img.gif);',
		),
		'15' => array(
			'background-color:green;',
			'background:green;',
		),
	),

	// combineListProperties method
	'combineListProperties' => array(
		'1' => array(
			'list-style-type:none;list-style-position:inline;list-style-image:url(images/img.gif);',
				'list-style:none inline url(images/img.gif);' .
				'list-style:none inline url(images/img.gif);' .
				'list-style:none inline url(images/img.gif);',
		),
		'2' => array(
			'list-style-type:none;list-style-position:inline;',
				'list-style:none inline;' .
				'list-style:none inline;',
		),
		'3' => array(
			'list-style-type:none;list-style-image:url(images/img.gif);',
				'list-style:none url(images/img.gif);' .
				'list-style:none url(images/img.gif);',
		),
		'4' => array(
			'list-style-position:inline;list-style-image:url(images/img.gif);',
				'list-style:inline url(images/img.gif);' .
				'list-style:inline url(images/img.gif);',
		),
		'5' => array(
			'list-style-type:none;',
			'list-style:none;',
		),
		'6' => array(
			'list-style-position:inline;',
			'list-style:inline;',
		),
		'7' => array(
			'list-style-image:url(images/img.gif);',
			'list-style:url(images/img.gif);',
		),
	),

	// removeMultipleDefinitions method
	'removeMultipleDefinitions' => array(
		'1' => array(
			'margin-left:10px;color:blue;margin-left:10px;color:blue;',
			'margin-left:10px;color:blue;',
		),
	),

	// removeEscapedURLs method
	'removeEscapedURLs' => array(
		'1' => array(
			"background:url(http\\://www.codenothing.com/random.php?foo=bar\\;&colon=te\\:st\\;)",
			"background:url(http://www.codenothing.com/random.php?foo=bar;&colon=te:st;)",
		),
		'2' => array(
			"color:blue\\;",
			"color:blue\\;",
		),
	),

	// removeUnnecessarySemicolon method
	'removeUnnecessarySemicolon' => array(
		'1' => array(
			'color:red;font-size:12pt;',
			'color:red;font-size:12pt',
		),
		'2' => array(
			'background:white;',
			'background:white',
		),
	),
);
?>

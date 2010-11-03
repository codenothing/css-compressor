<!DOCTYPE html>
<html>
<head>
	<title>CSS Compressor [VERSION]</title>
	<link rel='stylesheet' type='text/css' href='styles.css' />
	<script type='text/javascript' src='js.js'></script>
</head>
<body>

<!--
CSS Compressor [VERSION]
[DATE]
Corey Hart @ http://www.codenothing.com
-->

<h2>CSS Compressor [VERSION]</h2>


<?php

if ( isset( $_GET['view'] ) && $_GET['view'] == "compress" ) {
	// Run compression on passed script
	require("../src/css-compression.php");
	$CSSC = new CSSCompression( $_POST['css'], $_POST );
	$height = ( $CSSC->media || $CSSC->readability > CSSCompression::READ_NONE ) ? "400px" : "12px";

	// Add results above the form
	echo "<div id='results'>";
	$CSSC->displayStats();
	echo "<textarea style='height:$height;' onclick='this.select()'>".$CSSC->css."</textarea><br><br>";
	echo '</div>';

	// Form Saving
	foreach ( $_POST as $k => $v ) {
		if ( $k != 'css' ) {
			$checked[ $k ] = $v ? "checked='checked'" : '';
		}
	}

	// Readability is a select, not a checkbox
	$checked['readability'] = $_POST['readability'];

}
else{
	// Set options by default
	$opts = array(
		'color-long2hex',
		'color-rgb2hex',
		'color-hex2shortcolor',
		'color-hex2shorthex',
		'fontweight2num',
		'format-units',
		'lowercase-selectors',
		'directional-compress',
		'multiple-selectors',
		'multiple-details',
		'csw-combine',
		'auralcp-combine',
		'mp-combine',
		'border-combine',
		'font-combine',
		'background-combine',
		'list-combine',
		'unnecessary-semicolons',
		'rm-multi-define',
	);

	foreach ( $opts as $key ) {
		$checked[ $key ] = "checked='checked'";
	}
	$checked['color-hex2shortcolor'] = '';
}

?>

<form action='index.php?view=compress' method='POST'>
<table>
<tr valign='top'>
	<td width='50%'><textarea name='css'><?php if ( isset( $_POST['css'] ) ) echo $_POST['css'];?></textarea></td>
	<td>
		<label class='order-important'>
			<input type='checkbox' name='order-important' onChange='forceOrder(this);' <?php if ( isset( $checked['order-important'] ) ) echo $checked['order-important']; ?> />
			Order Important Stylesheet (Won't mess with the order of selectors)
		</label>
		<div class='options'>
			<label>
				<input type='checkbox' name='color-long2hex' <?php if ( isset( $checked['color-long2hex'] ) ) echo $checked['color-long2hex']; ?> />
				Convert long color names to short hex names (aliceblue -&gt; #f0f8ff)
			</label>
			<label class='odd'>
				<input type='checkbox' name='color-rgb2hex' <?php  if ( isset( $checked['color-rgb2hex'] ) ) echo $checked['color-rgb2hex']; ?> />
				Convert rgb colors to hex (rgb(159,80,98) -&gt; #9F5062, rgb(100%) -&gt; #FFFFFF)
			</label>
			<label>
				<input type='checkbox' name='color-hex2shortcolor' <?php if ( isset( $checked['color-hex2shortcolor'] ) ) echo $checked['color-hex2shortcolor']; ?> />
				Convert long hex codes to short color names (#f5f5dc -&gt; beige)<br>
				<i>(Short colornames are only supported by newer browsers)</i>
			</label>
			<label class='odd'>
				<input type='checkbox' name='color-hex2shorthex' <?php if ( isset( $checked['color-hex2shorthex'] ) ) echo $checked['color-hex2shorthex']; ?> />
				Convert long hex codes to short hex codes (#44ff11 -&gt; #4f1)
			</label>
			<label>
				<input type='checkbox' name='fontweight2num' <?php if ( isset( $checked['fontweight2num'] ) ) echo $checked['fontweight2num']; ?> />
				Convert font-weight names to numbers (bold -&gt; 700)
			</label>
			<label class='odd'>
				<input type='checkbox' name='format-units' <?php if ( isset( $checked['format-units'] ) ) echo $checked['format-units']; ?> />
				Remove zero decimals and 0 units (15.0px -&gt; 15px || 0px -&gt; 0)
			</label>
			<label>
				<input type='checkbox' name='lowercase-selectors' <?php if ( isset( $checked['lowercase-selectors'] ) ) echo $checked['lowercase-selectors']; ?> />
				Lowercase html tags from list (BODY -&gt; body)
			</label>
			<label class='odd'>
				<input type='checkbox' name='directional-compress' <?php if ( isset( $checked['directional-compress'] ) ) echo $checked['directional-compress']; ?> />
				Compress single defined multi-directional properties (margin:15px 25px 15px 25px -&gt; margin:15px 25px)
			</label>
			<label>
				<input type='checkbox' id='orderimportant1' name='multiple-selectors' <?php echo ( isset( $checked['order-important'] ) ? "disabled='disabled'" : '')?> <?php if ( isset( $checked['multiple-selectors'] ) ) echo $checked['multiple-selectors']; ?> />
				Combine multiply defined selectors (p{color:blue;} p{font-size:12pt} -&gt; p{color:blue;font-size:12pt;})
			</label>
			<label class='odd'>
				<input type='checkbox' id='orderimportant2' name='multiple-details' <?php echo ( isset( $checked['order-important'] ) ? "disabled='disabled'" : '')?> <?php if ( isset( $checked['multiple-details'] ) ) echo $checked['multiple-details']; ?> />
				Combine selectors with same details (p{color:blue;} a{color:blue;} -&gt; p,a{color:blue;})
			</label>
			<label>
				<input type='checkbox' name='csw-combine' <?php if ( isset( $checked['csw-combine'] ) ) echo $checked['csw-combine']; ?> />
				Combine color/style/width properties (border-style:dashed;border-color:black;border-width:4px; -&gt; border:4px dashed black)
			</label>
			<label class='odd'>
				<input type='checkbox' name='auralcp-combine' <?php if ( isset( $checked['auralcp-combine'] ) ) echo $checked['auralcp-combine']; ?> />
				Combines cue/pause properties (cue-before: url(before.au); cue-after: url(after.au) -&gt; cue:url(before.au) url(after.au))
			</label>
			<label>
				<input type='checkbox' name='mp-combine' <?php if ( isset( $checked['mp-combine'] ) ) echo $checked['mp-combine']; ?> />
				Combine margin/padding directionals (margin-top:10px;margin-right:5px;margin-bottom:4px;margin-left:1px; -&gt; margin:10px 5px 4px 1px;)
			</label>
			<label class='odd'>
				<input type='checkbox' name='border-combine' <?php if ( isset( $checked['border-combine'] ) ) echo $checked['border-combine']; ?> />
				Combine border directionals (border-top|right|bottom|left:1px solid black -&gt; border:1px solid black)
			</label>
			<label>
				<input type='checkbox' name='font-combine' <?php if ( isset( $checked['font-combine'] ) ) echo $checked['font-combine']; ?> />
				Combine font properties (font-size:12pt; font-family: arial; -&gt; font:12pt arial)
			</label>
			<label class='odd'>
				<input type='checkbox' name='background-combine' <?php if ( isset( $checked['background-combine'] ) ) echo $checked['background-combine']; ?> />
				Combine background properties (background-color: black; background-image: url(bgimg.jpeg); -&gt; background:black url(bgimg.jpeg))
			</label>
			<label>
				<input type='checkbox' name='list-combine' <?php if ( isset( $checked['list-combine'] ) ) echo $checked['list-combine']; ?> />
				Combine list-style properties (list-style-type: round; list-style-position: outside -&gt; list-style:round outside
			</label>
			<label class='odd'>
				<input type='checkbox' name='unnecessary-semicolons' <?php if ( isset( $checked['unnecessary-semicolons'] ) ) echo $checked['unnecessary-semicolons']; ?> />
				Removes the last semicolon of a property set ({margin: 2px; color: blue;} -&gt; {margin: 2px; color: blue})
			</label>
			<label>
				<input type='checkbox' name='rm-multi-define' <?php if ( isset( $checked['rm-multi-define'] ) ) echo $checked['rm-multi-define']; ?> />
				Remove multiply defined properties, STRONGLY SUGGESTED TO KEEP THIS ONE TRUE
			</label>
			<label class='odd'>
				<select name='readability'>
					<option value='0' <?php echo isset( $checked['readability'] ) && $checked['readability'] == 0 ? "selected='selected'" : '';?> >None</option>
					<option value='1' <?php echo isset( $checked['readability'] ) && $checked['readability'] == 1 ? "selected='selected'" : '';?> >Minimal</option>
					<option value='2' <?php echo isset( $checked['readability'] ) && $checked['readability'] == 2 ? "selected='selected'" : '';?> >Average</option>
					<option value='3' <?php echo isset( $checked['readability'] ) && $checked['readability'] == 3 ? "selected='selected'" : '';?> >Maximum</option>
				</select>
				<b>Readability</b> after compression (None == single line)
			</label>
		</div>
	</td>
</tr>
<tr>
	<td align='center'><input type='submit' value=' Compress ' /></td>
	<td></td>
</tr>
</table>
</form>

<p style='margin-top:60px;font-size:9pt;'>
Have a question? Found a bug? Test it using the 
<a href='sandbox/'>sandbox</a> or 
<a href='mailto:corey@codenothing.com?Subject=CSSC Question/Bug'>mail me</a>.
</p>

<div style='margin-top:50px;'>
	<a href='http://www.codenothing.com/archives/php/css-compressor/'>Back to Original Article</a>
</div>

</body>
</html>

<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 
require( "../src/CSSCompression.inc" );
$modes = CSSCompression::modes();
$select = '';
foreach ( $modes as $mode => $config ) {
	$select .= "<option value='$mode'>" . ucfirst( $mode ) . "</option>";
}
$select .= "<option value='custom'>Custom</option>";
?>
<!DOCTYPE html>
<html>
<head>
	<title>CSS Compressor [VERSION]</title>
	<link rel='stylesheet' type='text/css' href='styles.css' />
	<script type='text/javascript' src='jquery-1.4.2.js'></script>
	<script type='text/javascript'>var CSSCompressionModes = <?php echo json_encode( $modes ); ?>;</script>
	<script type='text/javascript' src='js.js'></script>
</head>
<body>


<h2 title='testingspaces'>CSS Compressor [VERSION]</h2>


<iframe name='compression'></iframe>
<form action='result.php' method='POST' target='compression'>
<table>
<tr valign='top'>
	<td width='50%'>
		<textarea name='css'></textarea>
	</td>
	<td width='50%'>
		<div class='control'>
			<button class='compress' type='submit'>Compress</button>
			Mode:
			<select name='mode'><?php echo $select; ?></select>
			Readability:
			<select name='readability'>
				<option value='0'>None</option>
				<option value='1'>Minimal</option>
				<option value='2'>Average</option>
				<option value='3'>Maximum</option>
			</select>
		</div>
		<div class='options'>
			<label>
				<input type='checkbox' name='color-long2hex' checked='checked' />
				Convert long color names to short hex names
				<span class='example'>aliceblue -&gt; #f0f8ff</span>
			</label>
			<label>
				<input type='checkbox' name='color-rgb2hex' checked='checked' />
				Convert rgb colors to hex
				<span class='example'>rgb(159,80,98) -&gt; #9F5062, rgb(100%) -&gt; #FFFFFF</span>
			</label>
			<label>
				<input type='checkbox' name='color-hex2shortcolor' checked='checked' />
				Convert long hex codes to short color names
				<span class='example'>#f5f5dc -&gt; beige (Short colornames are only supported by newer browsers)</span>
			</label>
			<label>
				<input type='checkbox' name='color-hex2shorthex' checked='checked' />
				Convert long hex codes to short hex codes
				<span class='example'>#44ff11 -&gt; #4f1</span>
			</label>
			<label>
				<input type='checkbox' name='color-hex2safe' checked='checked' />
				Converts hex codes to safe CSS Level 1 color names
				<span class='example'>#F00 -&gt; red</span>
			</label>
			<label>
				<input type='checkbox' name='fontweight2num' checked='checked' />
				Convert font-weight names to numbers
				<span class='example'>bold -&gt; 700</span>
			</label>
			<label>
				<input type='checkbox' name='format-units' checked='checked' />
				Remove zero decimals and 0 units
				<span class='example'>15.0px -&gt; 15px || 0px -&gt; 0</span>
			</label>
			<label>
				<input type='checkbox' name='lowercase-selectors' checked='checked' />
				Lowercase html tags from list
				<span class='example'>BODY -&gt; body</span>
			</label>
			<label>
				<input type='checkbox' name='attr2selector' checked='checked' />
				Converts id and class attribute selectors, to their short selector counterpart
				<span class='example'>div[id=blah][class=moreblah] -&gt; div#blah.moreblah</span>
			</label>
			<label>
				<input type='checkbox' name='strict-id' checked='checked' />
				Promotes nested id's to the front of the selector
				<span class='example'>body &gt; div#elem p -&gt; $elem p</span>
			</label>
			<label>
				<input type='checkbox' name='pseudo-space' checked='checked' />
				Add space after pseudo selectors, for ie6
				<span class='example'>a:first-child{ -&gt; a:first-child {</span>
			</label>
			<label>
				<input type='checkbox' name='directional-compress' checked='checked' />
				Compress single defined multi-directional properties
				<span class='example'>margin:15px 25px 15px 25px -&gt; margin:15px 25px</span>
			</label>
			<label>
				<input type='checkbox' name='organize' checked='checked' />
				Combine multiply defined selectors and selectors with same details
				<span class='example'>
					p{color:blue;} p{font-size:12pt} -&gt; p{color:blue;font-size:12pt;}<br>
					p{color:blue;} a{color:blue;} -&gt; p,a{color:blue;}
				</span>
			</label>
			<label>
				<input type='checkbox' name='csw-combine' checked='checked' />
				Combine color/style/width properties
				<span class='example'>border-style:dashed;border-color:black;border-width:4px; -&gt; border:4px dashed black</span>
			</label>
			<label>
				<input type='checkbox' name='auralcp-combine' checked='checked' />
				Combines cue/pause properties
				<span class='example'>
					cue-before: url(before.au); cue-after: url(after.au) -&gt; cue:url(before.au) url(after.au)
				</span>
			</label>
			<label>
				<input type='checkbox' name='mp-combine' checked='checked' />
				Combine margin/padding directionals
				<span class='example'>
					margin-top:10px;margin-right:5px;margin-bottom:4px;margin-left:1px; -&gt; margin:10px 5px 4px 1px;
				</span>
			</label>
			<label>
				<input type='checkbox' name='border-combine' checked='checked' />
				Combine border directionals
				<span class='example'>border-top|right|bottom|left:1px solid black -&gt; border:1px solid black</span>
			</label>
			<label>
				<input type='checkbox' name='font-combine' checked='checked' />
				Combine font properties
				<span class='example'>font-size:12pt; font-family: arial; -&gt; font:12pt arial</span>
			</label>
			<label>
				<input type='checkbox' name='background-combine' checked='checked' />
				Combine background properties
				<span class='example'>
					background-color: black; background-image: url(bgimg.jpeg); -&gt; background:black url(bgimg.jpeg)
				</span>
			</label>
			<label>
				<input type='checkbox' name='list-combine' checked='checked' />
				Combine list-style properties
				<span class='example'>list-style-type: round; list-style-position: outside -&gt; list-style:round outside</span>
			</label>
			<label>
				<input type='checkbox' name='border-radius-combine' checked='checked' />
				Combine border-radius properties
				<span class='example'>
					{
					 border-top-left-radius: 10px;
					 border-top-right-radius: 10px;
					 border-bottom-right-radius: 10px;
					 border-bottom-left-radius: 10px;
					}
					-&gt; { border-radius: 10px; }
				</span>
			</label>
			<label>
				<input type='checkbox' name='unnecessary-semicolons' checked='checked' />
				Removes the last semicolon of a property set
				<span class='example'>margin: 2px; color: blue; -&gt; margin: 2px; color: blue</span>
			</label>
			<label>
				<input type='checkbox' name='rm-multi-define' checked='checked' />
				Removes multiple declarations within the same rule set
				<span class='example'>color:black;font-size:12pt;color:red; -&gt; color:red;font-size:12pt;</span>
			</label>
			<label>
				<input type='checkbox' name='add-unknown' checked='checked' />
				Add all unknown blocks to the top of the output in a comment strip.
				Purely for bug reporting, but also useful to know what isn't being handled
			</label>
		</div>
	</td>
</tr>
</table>
</form>

<p style='margin-top:40px;font-size:9pt;'>
Have a question? Found a bug? Test it using the 
<a href='sandbox/'>sandbox</a> or 
<a href='mailto:corey@codenothing.com?Subject=CSSC Question/Bug'>mail me</a>.
</p>

<div style='margin-top:50px;'>
	<a href='http://www.codenothing.com/archives/php/css-compressor/'>Back to Original Article</a>
</div>

</body>
</html>

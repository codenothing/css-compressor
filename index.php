<html>
<head>
	<title>CSS Compressor</title>
<style type='text/css'>
table{
	width: 100%;
	font-size: 9pt;
}
h2{
	margin: 2px;
}
input[type='checkbox'] {
	font-size: 8pt;
}
label{
	display: block;
}
textarea{
	width: 100%;
	height: 450px;
	font-size: 8pt;
}
</style>
</head>
<body>

<!--
CSS Compressor
r:6 - May 7, 2009
Corey Hart @ http://www.codenothing.com
-->


<?
if ($_GET['view'] == "compress"){
	include("css-compression.php");
	$css = $CSSC->compress($_POST['css']);
	$height = ($CSSC->media || intval($_POST['readability']) > 0) ? "400px" : "20px";
	$CSSC->displayStats();
	echo "<textarea style='height:$height;' onclick='this.select()'>$css</textarea><br><br><a href='index.php'>Back</a>";
}else{
?>

<h2>CSS Compressor</h2>
<form action='index.php?view=compress' method='POST'>
<table>
<tr valign='top'>
	<td width='50%'><textarea name='css'></textarea></td>
	<td rowspan='2'>
		<label>
			<input type='checkbox' name='color-long2hex' checked='checked' />
			Convert long color names to short hex names (aliceblue -&gt; #f0f8ff)
		</label>
		<label>
			<input type='checkbox' name='color-rgb2hex' checked='checked' />
			Convert rgb colors to hex (rgb(159,80,98) -&gt; #9F5062, only 0-255 ranges)
		</label>
		<label>
			<input type='checkbox' name='color-hex2shortcolor' />
			Convert long hex codes to short color names (#f5f5dc -&gt; beige)
		</label>
		<label>
			<input type='checkbox' name='color-hex2shorthex' checked='checked' />
			Convert long hex codes to short hex codes (#44ff11 -&gt; #4f1)
		</label>
		<label>
			<input type='checkbox' name='fontweight2num' checked='checked' />
			Convert font-weight names to numbers (bold -&gt; 700)
		</label>
		<label>
			<input type='checkbox' name='format-units' checked='checked' />
			Remove zero decimals and 0 units (15.0px -&gt; 15px || 0px -&gt; 0)
		</label>
		<label>
			<input type='checkbox' name='lowercase-selectors' checked='checked' />
			Lowercase html tags from list (BODY -&gt; body)
		</label>
		<label>
			<input type='checkbox' name='directional-compress' checked='checked' />
			Compress single defined multi-directional properties (margin:15px 25px 15px 25px -&gt; margin:15px 25px)
		</label>
		<label>
			<input type='checkbox' name='multiple-selectors' checked='checked' />
			Combine multiply defined selectors (p{color:blue;} p{font-size:12pt} -&gt; p{color:blue;font-size:12pt;})
		</label>
		<label>
			<input type='checkbox' name='multiple-details' checked='checked' />
			Combine selectors with same details (p{color:blue;} a{color:blue;} -&gt; p,a{color:blue;})
		</label>
		<label>
			<input type='checkbox' name='csw-combine' checked='checked' />
			Combine color/style/width properties (border-style:dashed;border-color:black;border-width:4px; -&gt; border:4px dashed black)
		</label>
		<label>
			<input type='checkbox' name='auralcp-combine' checked='checked' />
			Combines cue/pause properties (cue-before: url(before.au); cue-after: url(after.au) -&gt; cue:url(before.au) url(after.au))
		</label>
		<label>
			<input type='checkbox' name='mp-combine' checked='checked' />
			Combine margin/padding directionals (margin-top:10px;margin-right:5px;margin-bottom:4px;margin-left:1px; -&gt; margin:10px 5px 4px 1px;)
		</label>
		<label>
			<input type='checkbox' name='border-combine' checked='checked' />
			Combine border directionals (border-top|right|bottom|left:1px solid black -&gt; border:1px solid black)
		</label>
		<label>
			<input type='checkbox' name='font-combine' checked='checked' />
			Combine font properties (font-size:12pt; font-family: arial; -&gt; font:12pt arial)
		</label>
		<label>
			<input type='checkbox' name='background-combine' checked='checked' />
			Combine background properties (background-color: black; background-image: url(bgimg.jpeg); -&gt; background:black url(bgimg.jpeg))
		</label>
		<label>
			<input type='checkbox' name='list-combine' checked='checked' />
			Combine list-style properties (list-style-type: round; list-style-position: outside -&gt; list-style:round outside
		</label>
		<label>
			<input type='checkbox' name='rm-multi-define' checked='checked' />
			Remove multiply defined properties, STRONGLY SUGGESTED TO KEEP THIS ONE TRUE
		</label>
		<label>
			<select name='readability'>
				<option value='0' selected='selected'>None</option>
				<option value='1'>Minimal</option>
				<option value='2'>Average</option>
				<option value='3'>Maximum</option>
			</select>
			<b>Readability</b> after compression (None == single line)
		</label>
	</td>
</tr>
<tr>
	<td align='center'><input type='submit' value=' Compress ' /></td>
</tr>
</table>
</form>
<?}// END ELSE CLAUSE?>

</body>
</html>

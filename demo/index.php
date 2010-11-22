<!DOCTYPE html>
<html>
<head>
	<title>CSS Compressor [VERSION]</title>
	<link rel='stylesheet' type='text/css' href='styles.css' />
	<script type='text/javascript' src='jquery-1.4.2.js'></script>
	<script type='text/javascript' src='js.js'></script>
</head>
<body>

<!--
CSS Compressor [VERSION]
[DATE]
Corey Hart @ http://www.codenothing.com
-->

<h2>CSS Compressor [VERSION]</h2>



<table>
<tr valign='top'>
	<td width='50%'>
		<form action='result.php' method='POST' target='compression'>
			<div class='control'>
				<button class='compress'>Compress</button>
				Mode:
				<select name='mode'>
					<option value='safe'>Safe</option>
					<option value='medium'>Medium</option>
					<option value='small'>Small</option>
					<option value='custom'>Custom</option>
				</select>
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
					<div class='example'>aliceblue -&gt; #f0f8ff</div>
				</label>
				<label>
					<input type='checkbox' name='color-rgb2hex' checked='checked' />
					Convert rgb colors to hex
					<div class='example'>rgb(159,80,98) -&gt; #9F5062, rgb(100%) -&gt; #FFFFFF</div>
				</label>
				<label>
					<input type='checkbox' name='color-hex2shortcolor' checked='checked' />
					Convert long hex codes to short color names
					<div class='example'>#f5f5dc -&gt; beige (Short colornames are only supported by newer browsers)</div>
				</label>
				<label>
					<input type='checkbox' name='color-hex2shorthex' checked='checked' />
					Convert long hex codes to short hex codes
					<div class='example'>#44ff11 -&gt; #4f1</div>
				</label>
				<label>
					<input type='checkbox' name='fontweight2num' checked='checked' />
					Convert font-weight names to numbers
					<div class='example'>bold -&gt; 700</div>
				</label>
				<label class='odd'>
					<input type='checkbox' name='format-units' checked='checked' />
					Remove zero decimals and 0 units
					<div class='example'>15.0px -&gt; 15px || 0px -&gt; 0</div>
				</label>
				<label>
					<input type='checkbox' name='lowercase-selectors' checked='checked' />
					Lowercase html tags from list
					<div class='example'>BODY -&gt; body</div>
				</label>
				<label>
					<input type='checkbox' name='directional-compress' checked='checked' />
					Compress single defined multi-directional properties
					<div class='example'>margin:15px 25px 15px 25px -&gt; margin:15px 25px</div>
				</label>
				<label>
					<input type='checkbox' name='organize' checked='checked' />
					Combine multiply defined selectors and selectors with same details
					<div class='example'>
						p{color:blue;} p{font-size:12pt} -&gt; p{color:blue;font-size:12pt;}<br>
						p{color:blue;} a{color:blue;} -&gt; p,a{color:blue;}
					</div>
				</label>
				<label>
					<input type='checkbox' name='csw-combine' checked='checked' />
					Combine color/style/width properties
					<div class='example'>border-style:dashed;border-color:black;border-width:4px; -&gt; border:4px dashed black</div>
				</label>
				<label>
					<input type='checkbox' name='auralcp-combine' checked='checked' />
					Combines cue/pause properties
					<div class='example'>
						cue-before: url(before.au); cue-after: url(after.au) -&gt; cue:url(before.au) url(after.au)
					</div>
				</label>
				<label>
					<input type='checkbox' name='mp-combine' checked='checked' />
					Combine margin/padding directionals
					<div class='example'>
						margin-top:10px;margin-right:5px;margin-bottom:4px;margin-left:1px; -&gt; margin:10px 5px 4px 1px;
					</div>
				</label>
				<label>
					<input type='checkbox' name='border-combine' checked='checked' />
					Combine border directionals
					<div class='example'>border-top|right|bottom|left:1px solid black -&gt; border:1px solid black</div>
				</label>
				<label>
					<input type='checkbox' name='font-combine' checked='checked' />
					Combine font properties
					<div class='example'>font-size:12pt; font-family: arial; -&gt; font:12pt arial</div>
				</label>
				<label>
					<input type='checkbox' name='background-combine' checked='checked' />
					Combine background properties
					<div class='example'>
						background-color: black; background-image: url(bgimg.jpeg); -&gt; background:black url(bgimg.jpeg)
					</div>
				</label>
				<label>
					<input type='checkbox' name='list-combine' checked='checked' />
					Combine list-style properties
					<div class='example'>list-style-type: round; list-style-position: outside -&gt; list-style:round outside</div>
				</label>
				<label>
					<input type='checkbox' name='unnecessary-semicolons' checked='checked' />
					Removes the last semicolon of a property set
					<div class='example'>{margin: 2px; color: blue;} -&gt; {margin: 2px; color: blue}</div>
				</label>
			</div>
			<textarea name='css'></textarea>
		</form>
	</td>
	<td>
		<iframe name='compression'></iframe>
	</td>
</tr>
</table>

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

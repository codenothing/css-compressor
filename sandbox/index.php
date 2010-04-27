<?php
$root = dirname(__FILE__);
include( $root . '/../css-compression.php' );
include( $root . '/sandbox.php' );
include( $root . '/unit.php' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>CSS Compressor [VERSION] - Test Suite</title>
<style type='text/css'>
body {
	font-size: 10pt;
}
tr {
	background-color: #f9f9f9;
}
td {
	font-size: 9pt;
	text-align: center;
	padding: 2px 20px;
}
th {
	background-color: #d1d1d1;
}
pre {
	font-size: 8pt;
}
</style>
</head>
<body>

<!--
CSS Compressor [VERSION] - Test Suite
[DATE]
Corey Hart @ http://www.codenothing.com
-->

<h1>CSS Compressor [VERSION] - Test Suite</h1>

<table bgcolor='#989898' cellspacing='1' cellpadding='3' style='border:1px solid #989898;'>
<tr>
	<th>Method</th>
	<th>Entry</th>
	<th>Result</th>
</tr>
<?php new CSScompressionTestUnit( $sandbox ); ?>
</table>


<p>
Found a bug? Have a new test to add? <a href='mailto:corey@codenothing.com?Subject=CSSC Bug/Feature Request'>Let me know</a>
</p>

<p style='margin-top:30px;'>
<a href='../'>Back to CSS-Compressor</a>
</p>

</body>
</html>

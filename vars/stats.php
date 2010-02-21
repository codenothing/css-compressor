<!--

CSS Compressor [VERSION] - Stats Template
[DATE]
Corey Hart @ http://www.codenothing.com

-->

<table cellspacing='1' cellpadding='2' style='width:400px;margin-bottom:20px;'>
	<tr bgcolor='#d1d1d1' align='center'>
		<th bgcolor='#f1f1f1' style='color:#8B0000;'>Results &raquo;</th>
		<th width='100'>Before</th>
		<th width='100'>After</th>
		<th width='100'>Compresssion</th>
	</tr>
	<tr bgcolor='#f1f1f1' align='center'>
		<th bgcolor='#d1d1d1'>Time</th>
		<td>-</td>
		<td>-</td>
		<td><?php echo round($after['time']-$before['time'],2); ?> seconds</td>
	</tr>
	<tr bgcolor='#f1f1f1' align='center'>
		<th bgcolor='#d1d1d1'>Selectors</th>
		<td><?php echo $before['selectors']; ?></td>
		<td><?php echo $after['selectors']; ?></td>
		<td><?php echo ($before['selectors']-$after['selectors']); ?></td>
	</tr>
	<tr bgcolor='#f1f1f1' align='center'>
		<th bgcolor='#d1d1d1'>Properties</th>
		<td><?php echo $before['props']; ?></td>
		<td><?php echo $after['props']; ?></td>
		<td><?php echo ($before['props']-$after['props']); ?></td>
	</tr>
	<tr bgcolor='#f1f1f1' align='center'>
		<th bgcolor='#d1d1d1'>Size</th>
		<td><?php echo $size['before']; ?></td>
		<td><?php echo $size['after']; ?></td>
		<td><?php echo $size['final']; ?></td>
	</tr>
</table>

<html>

<head>
	<link rel="stylesheet" type="text/css" href="platformStyles.css">
</head>

<body>

<!-- Get User Name -->
<?php
	$username= "bhermsen";
	$ver = 7;
?>

<table>
	<form action="insertPlatform.php" method="post">
		<input type="hidden" name="username" value="<?php echo $username; ?>" >
		<tr>
			<th>Jurisdiction (Federal, Wisconsin, etc.)</th>
			<td><input type="text" name="jurisdiction" placeholder="Jurisdiction (Federal, Wisconsin, etc.)"></td>
		</tr>
		<tr>
			<th>Category (Law, Economy, etc.)</th>
			<td><input type="text" name="category" placeholder="Category (Law, Economy, etc.)"></td>
		</tr>
		<tr>
			<th>Issue or Topic</th>
			<td><textarea name="issue" rows="3" placeholder="Issue"></textarea></td>
		</tr>
		<tr>
			<th>Your Platform</th>
			<td><textarea name="platform" rows="5" placeholder="Your Platform"></textarea></td>
		</tr>
		<tr>
			<th>How Important is this issue?</th>
			<td><input type="number" name="importance" step="1" placeholder="Importance"></td> <!-- Can I make this a slider? -->
		</tr>
		<tr>
			<th>How informed do you feel about this issue?</th>
			<td><input type="number" name="informed" step="1" placeholder="Informed"></td> <!-- Can I make this a slider? -->
		</tr>
		<tr>
			<th>Other Comments</th>
			<td><textarea name="comment" rows="3" placeholder="Comments"></textarea></td>
		</tr>
		<tr>
			<th>References</th>
			<td><input type="text" name="ref" placeholder="References"></td>
		</tr>
		<tr>
			<th>Submit</th>
			<td><input type="submit" name="submit" value="Submit"></td>
		</tr>
	</form>
</table>

</body>

</html>
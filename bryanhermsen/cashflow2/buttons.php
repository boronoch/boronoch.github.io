<!DOCTYPE html>
<html>
<head>
<!-- CSS -->
<link rel="stylesheet" type="text/css" href="cash2styles.css">
</head>
<body>

<?php

	//include 'classes.php';
	//include 'databaseConnect.php';	
	//include 'cash2_functions.php';
	$versions->buttons = 3;

?>

<div class="center">
	<p>
		<form action="account.php" method="get">
			<input type='submit' value = "GE Checking" class="button" name="print_command">
			<input type='submit' value = "Chase" class="button" name="print_command">
			<input type='submit' value = "ChaseCheck" class="button" name="print_command">
			<input type='submit' value = "Cash" class="button" name="print_command">
			<input type='submit' value = "AmEx" class="button" name="print_command">
			<input type='submit' value = "Southwest" class="button" name="print_command">
			<input type='submit' value = "Widget" class="button" name="print_command">
		</form>
	</p>
</div>

<div class="center">
	<p>
		<a class="button" href="display_transactions.php">Display Transactions</a>
		<a class="button" href="addTransaction.php">Add Transaction</a>
		<a class="button" href="balance.php">Display Balances</a>
	</p>
</div>
<br>


</body>
</html>
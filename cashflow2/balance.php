<html>
<head>
<title>Balance</title>
<!-- Placeholder for CSS
<link rel="stylesheet" type="text/css" href="brotherStyles.css">
-->

<!-- 	balance.php
		<description>
-->
</head>

<body>

<?php

	// Declare classes and functions
	include 'classes.php';
	include 'cash2_functions.php';
	
	$versions->balance = 5;
	$versions->functions = functions_ver();
	
	// Connect to database
	include 'databaseConnect.php';
	
	// Initialize Accounts, Funds, Categories
	include 'names.php';
	
	print_r($versions); echo "<br>";
	
	// read most recent archive	
	list($Accounts, $Categories) = read_latest_cash_balances($conn, $Categories, $Accounts, $categories_list, $accounts_list);
			
		
	// read all unarchived transactions
	$newTransactions = read_new_transactions($conn);
	
		// DEBUG
		print_r($newTransactions);
		echo "<br>";
		
	echo "<p>Next: finish and debug process_transactions</p>";
	
	// process unarchived transactions
	list($Accounts, $Categories) = process_transactions($conn, $newTransactions, $Accounts, $Categories, true);
	
	
	// display account balances
	
	// display form for adding a transaction
	
	// display buttons (update, archive, delete, moved 2nd above 1st, move multiple, select all
	
	// display fileters for archived transactions
	
	// display archived transactions that pass filter criteria
	
	// Close connection
	$conn->close();
	
	
?>

<p>End of PHP in balance.php.</p>
</body>

</html>
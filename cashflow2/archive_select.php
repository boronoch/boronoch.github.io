<html>
<head>
<title>Archive Select</title>
<!-- Placeholder for CSS
<link rel="stylesheet" type="text/css" href="brotherStyles.css">
-->

<!-- 	archive_select.php
		<description>
-->
</head>

<body>

<?php

	// Declare classes and functions
	include 'classes.php';
	include 'cash2_functions.php';
	
	$versions->archive_select = 1;
	$versions->functions = functions_ver();
	
	// Connect to database
	include 'databaseConnect.php';
	
	// Initialize Accounts, Funds, Categories
	include 'names.php';
	
	// read most recent archive	
	list($Accounts, $Categories) = read_latest_cash_balances($conn, $Categories, $Accounts, $categories_list, $accounts_list);
			
	// read all unarchived transactions
	$newTransactions = read_new_transactions($conn);

	// Check to see if "Select All" was clicked.

	// Display transactions in a table with check boxes
		// process transactions
		foreach ($newTransactions as $thisTransaction)
		{
			list($Accounts, $Categories) = process_transactions($conn, $thisTransaction, $Accounts, $Categories, $Funds, true);
		}
	
	// Close connection
	$conn->close();

?>

<p>End of PHP in archive_select.php.</p>
</body>

</html>
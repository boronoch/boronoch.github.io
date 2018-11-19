<html>
<head>
<title>Account</title>
<!-- Placeholder for CSS
<link rel="stylesheet" type="text/css" href="brotherStyles.css">
-->

<!-- 	account.php
		<description>
-->
</head>

<body>

<?php
	
	// Declare classes and functions
	include 'classes.php';
	include 'cash2_functions.php';
	
	$versions->accout = 1;
	$versions->functions = functions_ver();
	
	// Connect to database
	include 'databaseConnect.php';
	
	// Initialize Accounts, Funds, Categories
	include 'names.php';
	
	print_r($versions); echo "<br>";
	
	// read most recent archive	
	list($Accounts, $Categories, $Goals) = read_latest_cash_balances($conn, $Categories, $Accounts, $Goals, $categories_list, $accounts_list, $goals_list);
	
	// read all unarchived transactions
	$newTransactions = read_new_transactions($conn);
	
	/* Next: call process_transactions, but modify it to only print transactions of a particular account or category. Would need to pass another argument for which account or category. maybe allow it to be multiple accounts or categories in case I want to use it that way on another page 
	
	Then: display a form with 5-10 entry rows to add multiple transactions at once. Include Sort Order as a field so the transactions can be placed in a particular spot
	
	*/
	
	
	
?>

<p>End of PHP in account.php</p>
</body>

</html>
	
	
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
	
	$versions->balance = 11;
	$versions->functions = functions_ver();
	
	// Connect to database
	include 'databaseConnect.php';
	
	// Initialize Accounts, Funds, Categories
	include 'names.php';
	
	print_r($versions); echo "<br>";
	
	/* DEBUG
	echo "<pre>Goals list in balance before read_latest_cash_balances: <br>";
	print_r($Goals['list']);
	echo "<br>";
	print_r($goals_list);
	echo "<br><br></pre>";
	*/
	
	// read most recent archive	
	list($Accounts, $Categories, $Goals) = read_latest_cash_balances($conn, $Categories, $Accounts, $Goals, $categories_list, $accounts_list, $goals_list);
			
	/* DEBUG
	echo "<pre>Goals list in balance after read_latest_cash_balances: <br>";
	print_r($Goals['list']);
	echo "<br>";
	print_r($goals_list);
	echo "<br><br></pre>";
	*/

	
	// read all unarchived transactions
	$newTransactions = read_new_transactions($conn);
	
		// DEBUG
		/*
			echo "New Transactions:<br>";
			var_dump_pre($newTransactions);
			echo "<br>"; */
		
	echo "<p>Next: finish and debug process_transactions</p>";
	
	/* DEBUG
	echo "<pre>Goals list in balance before process_transactions: <br>";
	print_r($Goals["list"]);
	echo "<br>";
	print_r($goals_list);
	echo "<br><br></pre>";
	*/
	
	// process unarchived transactions
	list($Accounts, $Categories, $Goals) = process_transactions($conn, $newTransactions, $Accounts, $Categories, $Funds, $Goals, true);
	
	
	// display account balances
	$total_M = $Categories["Marann"]->balance + 
			   $Categories["Card"]->balance +
			   $Categories["Mwed"]->balance;
			   
	$total_E = $Categories["Bank"]->balance + 
			   $Categories["Emergency"]->balance + 
			   $Categories["Goals"]->balance + 
			   $Categories["Wedding"]->balance +
			   $Categories["Roth"]->balance +
			   $total_M;
	?>
	<div>
		<p>Total Emergency = $<?php echo $total_E; ?><br>
		(Bank+Emergency+Goals+Wedding+Roth+Card)<br>
		Goal: $10,765 (six months)</p>
		
		<p>Total Card = $<?php echo $total_M; ?><br>
		(Marann+Card+Mwed)</p>
	</div>
	<?php
	
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
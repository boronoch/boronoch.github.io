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
	
	date_default_timezone_set('America/Chicago');
	
	// Version (must be after including cash2_functions.php
	$versions->account = 7;
	$versions->functions = functions_ver();
	
	// Inputs
	$print_command = $_GET["print_command"];
	
	if ($print_command == null)
	{
		$print_command = true;
	}
	
	
	
	
	// Connect to database
	include 'databaseConnect.php';
	
	// Initialize Accounts, Funds, Categories
	include 'names.php';
	
	// read most recent archive	
	list($Accounts, $Categories, $Goals) = read_latest_cash_balances($conn, $Categories, $Accounts, $Goals, $categories_list, $accounts_list, $goals_list);
	
	// read all unarchived transactions
	$newTransactions = read_new_transactions($conn);
	
	// process unarchived transactions
	list($Accounts, $Categories, $Goals) = process_transactions($conn, $newTransactions, $Accounts, $Categories, $Funds, $Goals, $print_command);
	
	// Display form for adding transactions
	?>
	<div>
		<form action="submit_transaction.php" method="post">
		<table>
			<tr>
				<th>Sort Order</th>
				<th>Date</th>
				<th>Description</th>
				<th>Amount</th>
				<th>Account (Transfer To)</th>
				<th>Category (Transfer From)</th>
				<th>Cleared</th>
				<th>Check Amount</th>				
			</tr>
			<tr>
				<td><input name="sortOrder"  type="number" value="<?php echo $sortOrder;?>"></td>
				<td><input name="transDate"  type="date" value="<?php echo date("Y-m-d");?>"></td>
				<td><input name="transDesc" type="text"></td>
				<td><input name="transAmnt" type="number" step=".01"></td>
				<td><select name="transAcct">	
					<option value="Chase">Chase</option>
					<option value="GE Checking">GE Checking</option>						
					<?
						foreach ($Accounts["list"] as $thisAcct)
						{
							?><option value="<?echo $thisAcct;?>">
							<?echo $thisAcct;
							?></option>
							<?
						}
						foreach ($Categories["list"] as $thisCat)
						{
							?><option value="<?echo $thisCat;?>">
							<?echo $thisCat;
							?></option>
							<?
						}
					?>
					<option value="Grocery">Grocery</option>
					<option value="Gasoline">Gasoline</option>
					<option value="Spending Other">Spending Other</option>
					<option value="Income">Income</option>
					<option value="Bills">Bills</option>
					<option value="Loans">Loans</option>
					<option value="Bank Other">Bank Other</option>
					<option value="Dividend">Dividend</option>
					<option value="Dining">Dining</option>
				</select></td>
				<td><select name="transCat">
					<option value="Grocery">Grocery</option>
					<option value="Gasoline">Gasoline</option>
					<option value="Spending Other">Spending Other</option>
					<option value="Income">Income</option>
					<?
						foreach ($Categories["list"] as $thisCat)
						{
							?><option value="<?echo $thisCat;?>">
							<?echo $thisCat;
							?></option>
							<?
						}
						foreach ($Accounts["list"] as $thisAcct)
						{
							?><option value="<?echo $thisAcct;?>">
							<?echo $thisAcct;
							?></option>
							<?
						}
					?>
					<option value="Bills">Bills</option>
					<option value="Loans">Loans</option>
					<option value="Bank Other">Bank Other</option>
					<option value="Dividend">Dividend</option>
					<option value="Dining">Dining</option>
				</select></td>
				<td><input type='checkbox' name="cleared" checked></td>
				<td><input name="check_amount" type='checkbox'></td>
			</tr>
		</table>
		<input type="submit" value="Submit">
		</form>
	</div>

<?php
	
	/* Next: Create page to delete transactions (refer to add.php)
	
	Then: Create page to Modify transactions (refer to cashflow 1 functions). If user changes sort order, modify all subsequent sort orders
	
	Then: add Delete and Modify buttons to each row in the table (fine if its in balance.php too; just add it to the print_ledger_row and balances_header files.) 
	
	Then: pre-populate sort order in account.php with one greater than the maximum sort order, looking at new transactions and archived transactions
	
	Then: display a form with 5 entry rows to add multiple transactions at once. Include Sort Order as a field so the transactions can be placed in a particular spot. Need to modify submit_transaction to receive 5 rows, or call it 5 times. What does the input look like where there are 5 rows? Does each need to have a unique name, or should I send each input as an array? An array would be easier to scale
	
	Then: Create an array of strings by selecting check boxes, and pass it back to account.php as $print_command. 
	
	*/
	
	// Print buttons at the bottom
	include 'buttons.php';
	
	echo "<br>Versions printed from account.php: <br>";
	print_r($versions); echo "<br>";
	
?>

<p>End of PHP in account.php</p>
</body>

</html>
	
	
<!DOCTYPE html>
<html>
<head>
<!-- CSS -->
<link rel="stylesheet" type="text/css" href="cash2styles.css">

<title>Submit</title>

</head>
<body>

<?php

	include 'classes.php';
	include 'databaseConnect.php';	
	include 'cash2_functions.php';
	$versions->submit_transaction = 7;
	
	// Read inputs from addTransaction.php
	$sortOrder = $_POST["sortOrder"];
	$transDate = $_POST["transDate"];
	$transDesc = $_POST["transDesc"];
	$transAmnt = $_POST["transAmnt"];
	$transAcct = $_POST["transAcct"];
	$transCat =  $_POST["transCat"];
	$cleared =   $_POST["cleared"];
	$checkAmnt = $_POST["check_amount"];
	
// ADD 1 TO SORT ORDER OF TRANSACTIONS GREATER THAN OR EQUAL TO THE NEW ONE
	
	// Extract transactions with greater or equal sort order.	
	$sql = sprintf("SELECT `sort_order`,`IDX` FROM `transactions` WHERE `sort_order` >= %d ORDER BY `sort_order` ASC", $sortOrder);
	echo $sql . "<br>";
	$result = $conn->query($sql);
	
	// Add 1 to the sort order and update the transaction
	while ($row = $result->fetch_assoc())
	{
		$thisIDX = $row['IDX'];
		$thisSO = $row['sort_order'];
		
		$sql2 = sprintf("UPDATE `transactions` SET `sort_order` = '%d' WHERE `transactions`.`IDX` = %d", $thisSO+1, $thisIDX);
		echo $sql2 . "<br>";
		$result2 = $conn->query($sql2);
	}
	
// INSERT THE NEW RECORD

	$sql = sprintf("INSERT INTO transactions(`sort_order`, `Date`, `Description`, `Amount`, `Account`, `Category`, `cleared`, `check_amount`) VALUES ('" . $sortOrder . "', '" . $transDate . "', '" . $transDesc . "', '" . $transAmnt . "', '" . $transAcct . "', '" . $transCat . "', '" . $cleared . "', '" . $checkAmnt . "')");
	$result = $conn->query($sql);
	/**/
?>
	
<!-- Links back to other pages -->

<a class="button" href="display_transactions.php">Display Transactions</a>
<a class="button" href="addTransaction.php">Add Transaction</a>
<a class="button" href="balance.php">Display Balances</a>
<br>


</body>
</html>
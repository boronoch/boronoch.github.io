<html>
<head>
<title>Archive Submit</title>
<!-- Placeholder for CSS
<link rel="stylesheet" type="text/css" href="brotherStyles.css">
-->

<!-- 	archive_submit.php
		<description>
-->
</head>

<body>

<?php

	// Read inputs
	$selectTransIDX = $_POST["selectTrans"];
	
	// DEBUG
	if ($selectTransIDX)
	{
		echo "Received input<br>";
	}
	else
	{
		echo "Did not receive input<br>";
		die;
	}
	
	// Declare classes and functions
	include 'classes.php';
	include 'cash2_functions.php';
	
	$versions->archive_submit = 13;
	$versions->functions = functions_ver();
	
	// Connect to database
	include 'databaseConnect.php';
	
	// Initialize Accounts, Funds, Categories
	include 'names.php';
	
	print_r($versions); echo "<br>";
	
	// read most recent archive	
	list($Accounts, $Categories, $Goals) = read_latest_cash_balances($conn, $Categories, $Accounts, $Goals, $categories_list, $accounts_list, $goals_list);
	
	// Create list from array
	$stSize = sizeof($selectTransIDX);
	if ($stSize > 0)
	{
		$stList = $selectTransIDX[0];
		if ($stSize > 1)
		{
			for ($x=1; $x <$stSize; $x++)
			{
				$stList = $stList . ", " . $selectTransIDX[$x];
			}
		}		
	}

	// Read selected transactions
	$sql = "SELECT * 
			FROM `transactions` 
			WHERE `IDX` IN (" . $stList . ")
			ORDER BY `sort_order` ASC, `IDX` ASC";
			
	$result = $conn->query($sql); // Object array
	
	// Create selectTransOBJ array
	if($result)
	{
		
		if ($result->num_rows > 0)
		{
		
			$k = 0;
			while($row = $result->fetch_assoc())
			{
			
				$selectTransOBJ[$k] = read_transaction($row);
				$k++;
			
			}
		
		}
		else
		{
			echo "No result<br>";
			die;
		}
		
	}
	else
	{
		echo "No result<br>";
		die;
	}
	
	// Start a table and display all archived transactions and the balance on each row. The table header goes here.
	echo "<table border='1'>";
	balances_header(true, true, true, true, $Accounts, $Categories, $Goals);
	
	// For each transaction	
	foreach ($selectTransOBJ as $thisTransaction)
	{	
		// Start Transaction
		$conn->begin_transaction();
		
		/* DEBUG
		echo "before:<br>";
		echo "<pre>";
		print_r($thisTransaction);
		echo "</pre><br>";
		echo $Accounts[$thisTransaction->account]->balance; // Need to use OtherNames here.
		*/
		
		// Calculate new balances (add new transaction to previous balances) (do this in the transaction so that it only happens if the move goes correctly
		$thisTransactionArray[0] = $thisTransaction;
		list($Accounts, $Categories, $Goals) = process_transactions($conn, $thisTransactionArray, $Accounts, $Categories, $Funds, $Goals, false);
		print_ledger_row($thisTransaction, $Accounts, $Categories, $Goals);
		
		/* DEBUG
		echo "<br>after:<br>";
		echo $Accounts[$thisTransaction->account]->balance; // Need to use OtherNames here.
		echo "<br>";
		*/
		
		// Add the row to the `trans_archive` table
		$sql = sprintf("INSERT INTO trans_archive(`IDX`, `trans_IDX`, `sort_order`, `Date`, `Description`, `Amount`, `Account`, `Category`) VALUES (NULL, '" . $thisTransaction->IDX . "', '" . $thisTransaction->sortOrder . "', '" . $thisTransaction->date . "', '" . $thisTransaction->description . "', '" . $thisTransaction->amount . "', '" . $thisTransaction->account . "', '" . $thisTransaction->category . "')");
						
		$result = $conn->query($sql);
		
		// Delete the row from the `transactions` table
		if ($result) // only delete if the previous INSERT was successful
		{
			$sql = "DELETE FROM `transactions` WHERE `transactions`.`IDX` = " . $thisTransaction->IDX;
			$result = $conn->query($sql);
			
			
			//DEBUG
			?>			
			<script>
				console.log("archive_submit foreach selectTransOBJ");
				console.log("$sql = <?php echo $sql;?>");
				console.log("$result = <?php echo $result;?>");
				console.log("");
			</script>		
			<?php
			
			
			// COMMIT the TRANSACTION
			$conn->commit();
			
		}
		else
		{
			echo "DELETE not attempted because INSERT failed<br>";
			?>			
			<script>
				console.log("DELETE not attempted because INSERT failed");
			</script>		
			<?php
		}
		
		
	}
	
	// Print the header row after this as well
	balances_header(true, true, true, true, $Accounts, $Categories, $Goals);
	echo "</table>";
	
	
	// DEBUG
	echo "Widget:<br>";
	echo $Accounts['Widget']->balance . "<br><br>";
	echo "Spending:<br>";
	echo $Categories['Spending']->balance . "<br><br>";
	
	// Insert new totals into cash_balances table
	$sql = "INSERT INTO `cash_balances` (
			`IDX`, 
			`Date`, 
			`Cash`, 
			`GE Savings`, 
			`GE Checking`, 
			`Chase`, 
			`Chase Check`, 
			`AmEx`, 
			`Southwest`, 
			`Bank`, 
			`Spending`, 
			`Living`, 
			`Charity`, 
			`Marann`, 
			`Ten Percent`, 
			`Emergency`, 
			`Gifts`, 
			`eMV`, 
			`TEAM`, 
			`Wedding`, 
			`Mwed`, 
			`Roth`, 
			`Card`, 
			`Work Exp`, 
			`Home Imp`, 
			`Car Exp`, 
			`New Car`,
			`Goals`,
			`next_is_pay`) 
			VALUES 
			(NULL,'" .
			date("Y-m-d") . "'," .
			$Accounts['Cash']->balance . "," .
			"45.41999816894531," . 
			$Accounts['Widget']->balance . "," .
			$Accounts['ChaseCredit']->balance . "," .
			$Accounts['ChaseCheck']->balance . "," .
			$Accounts['AmEx']->balance . "," .
			$Accounts['Southwest']->balance . "," .
			$Categories['Bank']->balance . "," .
			$Categories['Spending']->balance . "," .
			$Categories['Living']->balance . "," .
			$Categories['Charity']->balance . "," .
			$Categories['Marann']->balance . "," .
			$Categories['Ten Percent']->balance . "," .
			$Categories['Emergency']->balance . "," .
			$Categories['Gifts']->balance . "," .
			"4.12109207861371," .
			$Categories['Team']->balance . "," .
			$Categories['Wedding']->balance . "," .
			$Categories['Mwed']->balance . "," .
			$Categories['Roth']->balance . "," .
			$Categories['Card']->balance . "," .
			$Categories['Work Exp']->balance . "," .
			$Categories['Home Imp']->balance . "," .
			$Categories['Car Exp']->balance . "," .
			$Categories['New Car']->balance . "," .
			$Categories['Goals']->balance . "," .
			"0)";
			
	$result = $conn->query($sql);
	if ($result)
	{
		echo "wrote to cash_balances";
		$cashBalancesIDX = $conn->insert_id;
		
		// NOTE: This is hardcoded to write HVAC. If a second goal is added, need to update the code.
		$sql = "INSERT INTO `goal_balances` (`cashBalancesIDX`, `goal`, `balance`) 
				VALUES ('" . $cashBalancesIDX . "', 
						'HVAC', 
						'" . $Goals['HVAC']->balance . "')";
						
		$result = $conn->query($sql);
		if ($result)
		{
			echo "wrote to goal_balances";
		}
		else
		{
			echo "failed to write to goal_balances<br>";
			echo $sql;
			echo "<br>";
			echo $result;
		}
		
	}
	else
	{
		echo "failed to write to cash_balances<br>";
		echo $sql;
		echo "<br>";
		echo $result;
	}
	
	

?>

<br>
<a href="archive_select.php">Select more to archive.</a>

<p>End of PHP in archive_submit.php.</p>
</body>

</html>
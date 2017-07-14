<?php 

// Ver 3;

function functions_ver()
{
	$functions_ver = 29;
	return $functions_ver;
}

function read_latest_cash_balances($conn, $Categories, $Accounts, $categories_list, $accounts_list)
{
	$sql = sprintf("SELECT IDX FROM `cash_balances` WHERE 1");
	$result = $conn->query($sql);
	
	if($result)
	{		
		if ($result->num_rows > 0)
		{
			// DEBUG
			//print_r($result);
			//echo "<br><br>";
			
			$k=0;
			while($row = $result->fetch_assoc())
			{
				$idxs[$k] = $row['IDX'];
				$k++;
			}
			
			// DEBUG
			/*echo "idxs:<br>";
			print_r($idxs);
			echo "<br><br>";
			echo "Size of idxs = " . sizeof($idxs) . "<br><br>"; */
			
			sort($idxs);
			
			// DEBUG
			/*echo "idxs sorted:<br>";
			print_r($idxs);
			echo "<br><br>";
			echo "0 = " . $idxs[0] . "<br>";
			echo "40 = " . $idxs[40] . "<br>";
			echo "last = " . $idxs[sizeof($idxs)-1] . "<br><br>"; */
			
			$mostRecent = $idxs[sizeof($idxs)-1];
			
			?><script>
				console.log("Most recent idx: <?php echo $mostRecent; ?>");
			</script><?php
			
		}
		else
		{
			echo "0 results<br>";
		}
	}
	else
	{
		echo "Query failed. Result = " . $result . "<br>";
		
		$allVars = get_defined_vars();
		echo "<br><br>All Variables:<br><br><pre>";
		print_r($allVars);
		echo "</pre>";
	}
	
	list($Categories, $Accounts) = read_cash_balances_idx($conn, $mostRecent, $Categories, $Accounts, $categories_list, $accounts_list);
	
	return array ($Accounts, $Categories); 

}

function correlate_names($conn)
{

	$sql = sprintf("SELECT * FROM `accounts_categories` WHERE 1");
	$result = $conn->query($sql);

	// Find Other Names and correlate to name in database
	if($result)
	{
		if ($result->num_rows > 0)
		{
			$idx = 0;
			while($row = $result->fetch_assoc())
			{
				if (!is_null($row["Other Names"]))
				{
					$otherNames[$row["Other Names"]] = $row["Name"];
					
					$otherNamesList[$idx] = $row["Other Names"];
					$idx++;
				}
			}
		}
	}

	return array ($otherNames, $otherNamesList);
}

function read_cash_balances_idx($conn, $idx, $Categories, $Accounts, $categories_list, $accounts_list)
{

	// Check connection
	if ($conn->connect_error)
	{
		echo "Connection failed.";
		die("Connection failed: " . $conn->connect_error);
	}
	
	// DEBUG
	echo "Called read_cash_balances_idx for IDX = ", $idx, PHP_EOL;
	$debug = 0;
	
	$sql = sprintf("SELECT * FROM `cash_balances` WHERE `IDX` LIKE %d", $idx);
	$result = $conn->query($sql);
	
	list ($otherNames, $otherNamesList) = correlate_names($conn);
	
	// DEBUG
	echo "<br>OtherNames:<br>";
	print_r($otherNames);
	echo "<br>";
	echo "in_array(GE Checking, otherNames) = " . in_array('GE Checking', $otherNames) . "<br>";

	

	if($result)
	{
		
		if ($result->num_rows > 0)
		{
			
			// Development status
			//echo "Need to replace ismember with correct PHP function in cash2_functions.php, which I think is 'in_array'" . PHP_EOL;
			//echo "Need to replace findstr with correct PHP function in cash2_functions.php, which I think is 'array_search'" . PHP_EOL;
			
			// store data of each row
			while($row = $result->fetch_assoc())
			{
				
				// Iterate through fields of $row
				foreach($row as $column => $value)
				{
					if ($debug)
					{
						echo sprintf("column = %s, value = %s<br>", $column, $value);
					}
					
					if (strcmp($column,"IDX")==0)
					{
						$thisIDX = $value;
					}
					elseif (strcmp($column,"Date")==0)
					{ // Do nothing
					}
					elseif (in_array($column, $accounts_list))
					{
						//$idx1 = array_search($column, $accounts_list); // Looking for index of $column within $accounts_list
						$Accounts[$column]->balance = $value;

						if ($debug)
						{
							echo sprintf("Set Account[%s]->balance = %f<br>", $column, $value);
						}
					
					}
					elseif (in_array($column, $categories_list))
					{
						$Categories[$column]->balance = $value;
						
						if ($debug)
						{
							echo sprintf("Set Categories[%s]->balance = %f<br>", $column, $value);
						}
						
					}
					elseif (in_array($column, $otherNamesList))
					{
						echo "Need to process " . $column . " as " . $otherNames[$column] . "<br>";
						
						if (in_array($otherNames[$column], $accounts_list))
						{
							$Accounts[$otherNames[$column]]->balance = $value;

							if ($debug)
							{
								echo sprintf("Set Account[%s]->balance = %f<br>", $otherNames[$column], $value);
							}
						}
						elseif (in_array($otherNames[$column], $categories_list))
						{
							$Categories[$otherNames[$column]]->balance = $value;
						
							if ($debug)
							{
								echo sprintf("Set Categories[%s]->balance = %f<br>", $otherNames[$column], $value);
							}
						}
						
					}
					else
					{
						if ($debug)
						{
							echo sprintf("Column %s is not declared in database<br>", $column, $value);
						}
						
						?><script>console.log("Column <?php echo $column; ?> is not declared in database (accounts_categories or funds_goals)");</script><?php
					}								
				}							
			}
		}
		else
		{
			echo "0 results<br>";
		}
	}
	else
	{
		echo "Query failed. Result = " . $result . "<br>";
		
		$allVars = get_defined_vars();
		echo "<br><br>All Variables:<br><br><pre>";
		print_r($allVars);
		echo "</pre>";
	}
	
	// DEBUG
	if ($debug)
	{
		$allVars = get_defined_vars();
		echo "<br><br>All Variables at end of read_cash_balances_idx:<br><br><pre>";
		print_r($allVars);
		echo "</pre>";
		echo "<br><br>";
	}
	
	return array ($Categories, $Accounts);

}

function read_new_transactions($conn)
{
	$sql = "SELECT * FROM `transactions` ORDER BY `sort_order` ASC";
	$result = $conn->query($sql);
	
	if($result)
	{
		
		if ($result->num_rows > 0)
		{
		
			$k = 0;
			while($row = $result->fetch_assoc())
			{
			
				$newTransactions[$k] = read_transaction($row);
				$k++;
			
			}
		
		}
		
	}
	
	return $newTransactions;
}


function read_transaction($row)
{
	/* read_transaction	
		Read an entry out of the transactions table and store it in an object of class "transaction"

		Includes provision for $sortBy and $sortOrder to allow user to select a column to sort by and choose between ASC and DESC. Not implemented yet.
	*/
		
	$thisTransaction = new transaction();
	$thisTransaction->sortOrder = $row['sort_order'];	
	$thisTransaction->date = $row['Date'];
	$thisTransaction->description = $row['Description'];
	$thisTransaction->amount = floatval($row['Amount']);
	$thisTransaction->account= $row['Account'];
	$thisTransaction->category= $row['Category'];
	
	return $thisTransaction;
	
}

function build_distributions($conn)
{
	$sql = "SELECT * FROM `distribution` ORDER BY `Date` ASC";
	$result = $conn->query($sql);
	
	if($result)
	{
		
		if ($result->num_rows > 0)
		{
		
			$k = 0; 
			$sum = 0;
			while($row = $result->fetch_assoc())
			{
			
				$distributions[$k] = new distribution();
				
				$distributions[$k]->date = $row['Date'];
				
				$distributions[$k]->Bank = $row['Bank']; $sum += $distributions[$k]->Bank;
				$distributions[$k]->Spending = $row['Spending']; $sum += $distributions[$k]->Spending;
				$distributions[$k]->Charity = $row['Charity']; $sum += $distributions[$k]->Charity;
				$distributions[$k]->TenPercent = $row['TenPercent']; $sum += $distributions[$k]->TenPercent;
				$distributions[$k]->Marann = $row['Marann']; $sum += $distributions[$k]->Marann;
				$distributions[$k]->Wedding = $row['Wedding']; $sum += $distributions[$k]->Wedding;
				$distributions[$k]->HomeImp = $row['HomeImp']; $sum += $distributions[$k]->HomeImp;
				$distributions[$k]->Living = $row['Living']; $sum += $distributions[$k]->Living;
				$distributions[$k]->CarExp = $row['CarExp']; $sum += $distributions[$k]->CarExp;
				$distributions[$k]->Roth = $row['Roth']; $sum += $distributions[$k]->Roth;
				$distributions[$k]->Gifts = $row['Gifts']; $sum += $distributions[$k]->Gifts;
				$distributions[$k]->Emergency = $row['Emergency']; $sum += $distributions[$k]->Emergency;
				$distributions[$k]->NewCar = $row['NewCar']; $sum += $distributions[$k]->NewCar;
				
				$k++;
			
			}
		
		}
		
	}
	
	if ($sum != 1)
	{
		echo "<br>Sum of Distributions does not equal 1. It is " . $sum . "<br>";
	}
	
	return $distributions;
}

function print_ledger_row($transaction, $Accounts, $Categories)
{
	// Prints details of a transaction, the balance of all accounts, and the balance of all categories as a row in a table. If transaction is NULL, it is skipped, and those columns are not added. Start and end of table should be handled by caller.
	
	//echo "<tr>";
	//echo "<td>" . $transaction->date . "</td>";
	
	// DEBUG
	echo "<br>start of print_ledger_row<br>";
	print_r($transaction); echo "<br>";
	print_r($Accounts); echo "<br>";
	print_r($Categories); echo "<br><br>";
	
	?>
	<tr>
	<?php
		if (!is_null($transaction))
		{
			?>
			<td><?php echo $transaction->date;?></td>
			<td><?php echo $transaction->description;?></td>
			<td><?php echo $transaction->amount;?></td>
			<td><?php echo $account->description;?></td>
			<td><?php echo $account->category;?></td>
			<td></td>
		<?php
		}
		
		foreach ($Accounts as $col => $val)
		{
			?>
			<td><?php print_r($val);?></td>
			<?php
		}
		
		?><td></td><?php
		
		foreach ($Categories as $col => $val)
		{
			?>
			<td><?php print_r($val);?></td>
			<?php
		}
		
	?>
	</tr>
	<?php
}

function process_transactions($conn, $transactions, $Accounts, $Categories, $print_ledger)
{
	/* process_transactions
		Evaluate a transaction and make the appropriate adjustments to any affected accounts, categories, funds, and goals.
	*/
	
	// DEBUG
	echo "<br>Starting process_transactions.<br>";
	print_r($transactions);
	echo "<br><br>";
	
	$writeArchiveLedger = 0; //Ledger is copied from cash_functions.php, has not been tested in cash2_functions.php
	
	// Start a table to print results in
	if ($print_ledger)
	{
		echo "<table>";
		echo "<tr>
				<td>Date</td>
				<td>Description</td>
				<td>Amount</td>
				<td>Account</td>
				<td>Category</td>
				<td></td>";
		foreach ($Accounts as $col => $val)
		{
			?>
			<td><?php echo $col;?></td>
			<?php
		}

		?><td></td><?php
			
		foreach ($Categories as $col => $val)
		{
			?>
			<td><?php echo $col;?></td>
			<?php
		}
	}
	
	// For each transaction
	for ($idx = 0; $idx < sizeof($transactions); $idx++)
	{
	
		// Process Account
		if (in_array($transactions[$idx]->account, $Accounts))
		{
			// If $transactions[$idx]->account is an account, add
			$Accounts[$transactions[$idx]->account]->balance = $Accounts[$transactions[$idx]->account]->balance + $transactions[$idx]->amount;
		}
		elseif (in_array($transactions[$idx]->account, $Categories))
		{
			// If $transactions[$idx]->account is a category, subtract
			$Accounts[$transactions[$idx]->account]->balance = $Accounts[$transactions[$idx]->account]->balance - $transactions[$idx]->amount;
		}
		else
		{
			// If the "type" field is not account, fund, or category, display message in console
			?><script>
				console.log("Message 0000: Account <?php echo $transactions[$idx]->account;?> is not recognized. (cash2_functions.php)");
			</script><?php
		}
		
		// Process category
		if (strcmp($transactions[$idx]->category,"Income") == 0)						// INCOME
		{
			// If this is the first Income transaction, create $distributions object
			if (!isset($distributions))
			{
				$distributions = build_distributions($conn);
			}
			
			// Determine which Income Distribution column to use
			$thisDistDate = $distributions[0]->date;
			$thisDistIdx = 0;
			for ($idx2 = 1; $idx2 < sizeof($distributions); $idx2++)
			{
				if ($transactions[$idx]->date > $distributions[$idx2]->date)
				{
					$thisDistDate = $distributions[$idx2]->date;
					$thisDistIdx = $idx2;
				}				
								
			}
			
			// Debug
			echo "<br>Using idx " . $thisDistIdx . " for Income distribution on transaction " . $idx . "<br>";
			
			// CONTINUE EDITTING FROM HERE
			
			// -- need to iterate through each item in $distributions[$thisDistIdx] and add money to the category. Or alternatively, iterate through $Categories and check $distributions[$thisDistIdx] for each one.
			
			// Iterate through all fields in the distributions object and add money to the category
			foreach($distributions[$thisDistIdx] as $thisCat => $percent)
			{
				echo "<br>" . $thisCat . " = " . $distributions[$thisDistIdx]->$thisCat;
				
				echo "<br>" . $percent . " = " . $percent . "<br>";
			}
			
		}
		elseif (strcmp($transactions[$idx]->category,"Dividend") == 0)					// DIVIDEND
		{
			echo "<br> idx = " . $idx . " and cat is " . $transactions[$idx]->category;
			/* 
			Algorithm: Iterate through all categories and Funds twice. The first time, find the sum of all of them.
			The second time, give each fund or category this ammount: dividend * (category balance / sum)
			*/
			
			// Find sum
			$sum = 0;
			foreach ($Categories as $thisCat)
			{
				echo "<br>" . $thisCat->name . " = $" . $thisCat->balance; // DEBUG
				$sum += $thisCat->balance;
				echo "; sum = " . $sum;  // DEBUG
			}
			
			// Pay Dividends
			foreach ($Categories as $thisCat)
			{
				$thisDiv = ($thisCat->balance / $sum );
				$thisCat->balance += ($transactions[$idx]->amount * $thisDiv);
				
				// DEBUG
				echo "<br> Add $" . $thisDiv . " to " . $thisCat->name . " = $" . $thisCat->balance;
			}
			
		}
		elseif (in_array($transactions[$idx]->category, $Categories))		// Transfer
		{
			echo "<br> idx = " . $idx . ". TRANSFER: cat is " . $transactions[$idx]->category . " and account is " . $transactions[$idx]->account;
			
			$Categories[$transactions[$idx]->category] += $transactions[$idx]->amount;
		}
		elseif (strcmp($transactions[$idx]->category,"Transfer") == 0)					// Transfer
		{
			echo "<br> idx = " . $idx . " and cat is " . $transactions[$idx]->category;
			echo ". I thought this was obsolete.";
		}
		else
		{ 
			// DEBUG
			echo "<br> idx = " . $idx . " and cat is " . $transactions[$idx]->category;
			
			?><script>
				console.log("Category <?php echo $transactions[$idx]->category;?> is not declared.");
			</script><?php
		}
		
		if ($print_ledger)
		{
			print_ledger_row($transactions[$idx], $Accounts, $Categories);
		}
		
	}
	
	// Close table
	echo "</table>";
		
}

?>
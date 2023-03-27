<?php 

// Ver 3;

function functions_ver()
{
	$functions_ver = 85;
	return $functions_ver;
}

function read_latest_cash_balances($conn, $Categories, $Accounts, $Goals, $categories_list, $accounts_list, $goals_list)
{
	$sql = sprintf("SELECT IDX FROM `cash_balances` WHERE 1");
	$result = $conn->query($sql);
	
	if($result)
	{		
		if ($result->num_rows > 0)
		{
			
			$k=0;
			while($row = $result->fetch_assoc())
			{
				$idxs[$k] = $row['IDX'];
				$k++;
			}
			
			
			sort($idxs);
			
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
	
	list($Categories, $Accounts, $Goals) = read_cash_balances_idx($conn, $mostRecent, $Categories, $Accounts, $Goals, $categories_list, $accounts_list, $goals_list);
	
	return array ($Accounts, $Categories, $Goals); 

}

function correlate_names($conn)
{
	/* Read the "Other Names" field from the accounts_categories table in the database.
	Return:
		otherNamesList - an array with all entries found in the "Other Names" field of the table; the common names that are not standard in code.
		otherNames - 	 an array of key-value pairs in which the key is the "Other Name" and the value is the standard name.
	*/

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

function read_cash_balances_idx($conn, $idx, $Categories, $Accounts, $Goals, $categories_list, $accounts_list, $goals_list)
{

	// Check connection
	if ($conn->connect_error)
	{
		echo "Connection failed.";
		die("Connection failed: " . $conn->connect_error);
	}
	
	// DEBUG
	$debug = 0;
	if ($debug)
	{
		echo "Called read_cash_balances_idx for IDX = ", $idx, PHP_EOL;
	}
	
	$sql = sprintf("SELECT * FROM `cash_balances` WHERE `IDX` LIKE %d", $idx);
	$result = $conn->query($sql);
	
	list ($otherNames, $otherNamesList) = correlate_names($conn);

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
	
	// Read goals
	$sql = sprintf("SELECT * FROM `goal_balances` WHERE `cashBalancesIDX` LIKE %d", $idx);
	$result = $conn->query($sql);
	
	
	if($result)
	{
		
		if ($result->num_rows > 0)
		{
			
			// store data of each row
			while($row = $result->fetch_assoc())
			{				
				if (in_array($row['goal'], $goals_list))
				{
					$Goals[$row['goal']]->balance = $row['balance'];
					
					if ($debug)
					{
						echo sprintf("Set Goals[%s]->balance = %f<br>", $row['goal'], $row['balance']);
					}					
				}
				else
				{
					?><script>console.log("Goal <?php echo $row['goal']; ?> is not declared in database");</script><?php
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
	
	return array ($Categories, $Accounts, $Goals);

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
	$thisTransaction->IDX = $row['IDX'];
	$thisTransaction->sortOrder = $row['sort_order'];	
	$thisTransaction->date = $row['Date'];
	$thisTransaction->description = $row['Description'];
	$thisTransaction->amount = floatval($row['Amount']);
	$thisTransaction->account= $row['Account'];
	$thisTransaction->category= $row['Category'];
	
	return $thisTransaction;
	
}

function var_dump_pre($mixed = null) {
  echo '<pre>';
  var_dump($mixed);
  echo '</pre>';
  return null;
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
			
				//$distributions[$k] = new distribution();
				
				$distributions[$k] = new \stdClass();
				
				$distributions[$k]->Date = $row['Date'];
				
				foreach ($row as $key => $value)
				{
					//echo $key . " = " . $value . "<br>";
					
					if ( (strcmp($key,"IDX") !== 0) and (strcmp($key,"Date") !== 0 ) )					
					{
						$distributions[$k]->$key = $row[$key]; $sum += $distributions[$k]->$key;
												
					}
					
				}
				/*
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
				*/
				$k++;
				if ($sum > 1.001 or $sum < 0.999)
				{
					echo "<br>Sum of Distributions does not equal 1. It is " . $sum . "<br>";
				}
				$sum = 0;
			
			}
		
		}
		
	}
	/*
	echo "<br>";
	var_dump_pre($distributions);
	echo "<br>";
	*/
	
	
	return $distributions;
}

function echoCurrency($input)
{
	//echo gettype($input);
	//echo "<br>";
	
	if ($input < 0)
	{
		echo sprintf('-$%8.2f',-$input);
		return sprintf('-$%8.2f',-$input);
	}
	else
	{
		echo sprintf('$%8.2f',$input);
		return sprintf('$%8.2f',$input);
	}
}

function print_ledger_row($transaction, $Accounts, $Categories, $Goals)
{
	// Prints details of a transaction, the balance of all accounts, and the balance of all categories as a row in a table. If transaction is NULL, it is skipped, and those columns are not added. Start and end of table should be handled by caller.
	
	//echo "<tr>";
	//echo "<td>" . $transaction->date . "</td>";
	
	/* DEBUG 
		echo "<br>start of print_ledger_row<br>";
		echo "transaction: "; print_r($transaction); echo "<br>";
		echo "Accounts: "; print_r($Accounts); echo "<br>";
		echo "Categories: "; print_r($Categories); echo "<br><br>"; 		
	*/
	?>
	<tr>
	<?php
		if (!is_null($transaction))
		{
			?>
			<td><?php echo $transaction->sortOrder;?></td>
			<td><?php echo $transaction->date;?></td>
			<td><?php echo $transaction->description;?></td>
			<td><?php echoCurrency($transaction->amount);?></td>
			<td><?php echo $transaction->account;?></td>
			<td><?php echo $transaction->category;?></td>
			<td></td>
			
			<?php		
			foreach ($Accounts as $col => $val)
			{
				if (strcmp($col,"list") !== 0 ) 
				{
					?>
					<td><?php echoCurrency($val->balance);?>
					
					
					<!-- DEBUG
						<script>
							console.log("col = <?php //echo $col;?>; val = <?php //echo $val->name;?>");
						</script>
					-->
					</td>			
					<?php
				}
			}
			
			?><td> </td><?php
			
			foreach ($Categories as $col => $val)
			{
				if (strcmp($col,"list") !== 0 ) 
				{
					?>
					<td><?php echoCurrency($val->balance);?>
					
					<!-- DEBUG
					<script>
						console.log("col = <?php //echo $col;?>; val = <?php //echo $val->name;?>");
					</script>
					-->
					</td>
					<?php
				}
			}
			
			?><td> </td><?php
			
			foreach ($Goals as $col => $val)
			{
				if (strcmp($col,"list") !== 0 ) 
				{
					?>
					<td><?php echoCurrency($val->balance);?>
					<!---
					<script>
						console.log("col = <?php //echo $col;?>; val = <?php //echo $val->name;?>");
					</script>
					-->
					</td>
					<?php
				}
			}
			
			?>
			<td>Modify</td>
			<td>Delete</td>			
			<?php	
			
		}
	?>
	</tr>
	<?php
}

function balances_header($trans, $acc, $cat, $goal, $Accounts, $Categories, $Goals)
{

	echo "<tr>";
	
	if ($trans)
	{
		echo "
				<td>Sort</td>
				<td>Date</td>
				<td>Description</td>
				<td>Amount</td>
				<td>Account</td>
				<td>Category</td>
				<td> </td>";	
	}
	if ($acc)
	{
		foreach ($Accounts as $col => $val)
		{
			if (strcmp($col,"list") !== 0 ) 
			{
				?>
				<td><?php echo $col;?></td>
				<?php
			}
		}

		?><td></td><?php
	}
	
	if ($cat)
	{
		foreach ($Categories as $col => $val)
		{
			if (strcmp($col,"list") !== 0 ) 
			{
				?>
				<td><?php echo $col;?></td>
				<?php
			}
		}
		
		?><td></td><?php		
	}
	
	if ($goal)
	{
		
		foreach ($Goals as $col => $val)
		{
			if (strcmp($col,"list") !== 0 ) 
			{
				?>
				<td><?php echo $col;?></td>
				<?php
			}
		}
	}
	
	echo "
				<td>Modify</td>
				<td>Delete</td>";	
	
	echo "</tr>";
}


function process_transactions($conn, $transactions, $Accounts, $Categories, $Funds, $Goals, $print_command = true)
{
	/* process_transactions
		Evaluate a transaction and make the appropriate adjustments to any affected accounts, categories, funds, and goals.
		
		INPUTS:
		 - $print_ledger may be any of the following:
			- "all"
			- "none"
			- true (same as "all")
			- false (same as "none")
			- ommitted (default to "all")
			- any category: only print transactions of that category
			- any account: only print transactions of that account
			- an array of categories and/or accounts: only print transactions with an account or category in the list
			(no columns are excluded when printing, just rows. consider highlighting columns.)
	*/
	
	// DEBUG
	/*
		echo "<br>Starting process_transactions.<br>";
		print_r($transactions);
		echo "<br><br>"; */
	
	$writeArchiveLedger = 0; //Ledger is copied from cash_functions.php, has not been tested in cash2_functions.php
	
	
	list ($otherNames, $otherNamesList) = correlate_names($conn);
	
	/*
	// DEBUG
	echo "<pre>Accounts list: <br>";
	print_r($Accounts["list"]);
	echo "<br>";
	echo "Categories list: <br>";
	print_r($Categories["list"]);
	echo "<br><br>";
	echo "Categories array_keys: <br>";
	print_r(array_keys($Categories));
	echo "<br><br>";
	echo "Funds list: <br>";
	print_r($Funds["list"]);
	echo "<br><br>";
	echo "Goals list: <br>";
	print_r($Goals['list']);
	echo "<br><br></pre>";
	
	
	echo "print_ledger = " . $print_ledger . "<br>";	
	echo "sizeof transactions = " . sizeof($transactions) . "<br>";
	*/
	
	// Interpret $print_ledger variable
	if (is_bool($print_command))
	{
		$print_ledger = $print_command;
		$print_list = "all";
	} 
	elseif (is_string($print_command))
	{
		if (strcmp($print_command,"none") == 0)
		{
			$print_ledger = false;
			$print_list = "none"; // not used when print_ledger is false
		}		
		else
		{
			$print_ledger = true;
			$print_list = $print_command;
			?><script>
				console.log("cash2_functions 0003: Print Command is <?php echo $print_command;?>. Printing Ledger.");
			</script><?php
		}
	}
	elseif (is_array($print_command))
	{
		$print_ledger = true;
		$print_list = $print_command;
	}
	else 
	{
		?><script>
			console.log("Message 0004: Error: Print Command Data Type is <?php echo gettype($print_command);?>.");
		</script><?php
	}
	
	?><script>
		console.log("cash2_functions 0005: print_ledger is <?php echo $print_ledger;?> and print_list is <?php echo $print_list;?>.");
	</script><?php
	
	// Start a table to print results in
	if ($print_ledger)
	{
		echo "<table border='1'>";
		balances_header(true, true, true, true, $Accounts, $Categories, $Goals);
	}
	
	// For each transaction
	for ($idx = 0; $idx < sizeof($transactions); $idx++)
	{
	
		// Support "$transactions" as a single object or an array
		
 
		// Process Account
		if (in_array($transactions[$idx]->account, $Accounts["list"]))
		{
			// If $transactions[$idx]->account is an account, add
			$Accounts[$transactions[$idx]->account]->balance = $Accounts[$transactions[$idx]->account]->balance + $transactions[$idx]->amount;
		}
		elseif (in_array($transactions[$idx]->account, $Categories["list"], TRUE))
		{
			// If $transactions[$idx]->account is a category, subtract
			$Categories[$transactions[$idx]->account]->balance = $Categories[$transactions[$idx]->account]->balance - $transactions[$idx]->amount;
		}
		elseif (in_array($transactions[$idx]->account, $otherNamesList))
		{
			// Get the "standard" account name from the $otherNames key-value array, and then look that up in the Accounts list.
			$account_standard_name = $otherNames[$transactions[$idx]->account];
			
			if (in_array($account_standard_name, $Accounts["list"]))
			{						
				// If it yields an account, add
				$Accounts[$account_standard_name]->balance = $Accounts[$account_standard_name]->balance + $transactions[$idx]->amount;
			}
			else
			{
				?><script>
					console.log("Message 0001: Account <?php echo $account_standard_name;?> is not recognized. Index is <?php echo $idx;?> (cash2_functions.php)");
				</script><?php
			}
		}
		else
		{
			// If the "type" field is not account, fund, or category, display message in console
			?><script>
				console.log("Message 0000: Account <?php echo $transactions[$idx]->account;?> is not recognized. (cash2_functions.php)");
			</script><?php
		}
		
		// Process category
		if (in_array($transactions[$idx]->category, $Categories["list"]))
		{
			// Standard transaction
			$Categories[$transactions[$idx]->category]->balance += $transactions[$idx]->amount;
			
		}
		elseif (in_array($transactions[$idx]->category, $Funds["list"]))
		{
			// It's a subcategory under one of the main categories
			
			// Find the main category
			$category = $Funds[$transactions[$idx]->category]->category;
			
			// Execute transaction
			$Categories[$category]->balance += $transactions[$idx]->amount;
			
		}		
		elseif (strcmp($transactions[$idx]->category,"Income") == 0)						// INCOME
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
			//echo "<br>Using idx " . $thisDistIdx . " for Income distribution on transaction " . $idx . "<br>";
			
			// CONTINUE EDITTING FROM HERE
			
			// -- need to iterate through each item in $distributions[$thisDistIdx] and add money to the category. Or alternatively, iterate through $Categories and check $distributions[$thisDistIdx] for each one.
			
			/* DEBUG
			echo "Goals:";
			print_r($Goals);
			echo "<br>";
			*/
			
			// Iterate through all fields in the distributions object and add money to the category
			foreach($distributions[$thisDistIdx] as $thisCat => $percent)
			{
   
				
				if (in_array($thisCat, $Categories["list"]))
				{
					$Categories[$thisCat]->balance += ($transactions[$idx]->amount * $percent);
					
					if (strcmp($thisCat,"Goals") == 0 )
					{					
						foreach ($Goals["list"] as $goal => $val)
						{
							$Goals[$val]->balance += ($transactions[$idx]->amount * $percent * $Goals[$val]->distribution );														
						}
					}
					
				}				
				elseif (strcmp($thisCat,"Date") !== 0 )
				{
					//echo "<br>" . $thisCat . " = " . $percent;
					
					//echo "<br>" . $percent*100 . "% of " . $transactions[$idx]->amount . " is $" . ($transactions[$idx]->amount * $percent) . ". " . $thisCat . " changes from " . $Categories[$thisCat]->balance;
					
					// DEBUG
					?><script>console.log("Used the last elseif in income for <?php echo $thisCat; ?>");</script><?php
					
					$Categories[$thisCat]->balance += ($transactions[$idx]->amount * $percent);
					
					//echo " to " . $Categories[$thisCat]->balance . " .<br>";
					
					if (strcmp($thisCat,"Goals") == 0 )
					{					
						?><script>
							console.log("I'm in 1");
						</script><?php	
						
						foreach ($Goals["list"] as $goal => $val)
						{
							$Goals[$val]->balance += ($transactions[$idx]->amount * $percent * ($Goals[$val]->distribution / 100.0));														
						}
					}
				}
				
				
			}
			
		}
		elseif (strcmp($transactions[$idx]->category,"Dividend") == 0)					// DIVIDEND
		{
			// echo "<br> idx = " . $idx . " and cat is " . $transactions[$idx]->category;
			/* 
			Algorithm: Iterate through all categories and Funds twice. The first time, find the sum of all of them.
			The second time, give each fund or category this ammount: dividend * (category balance / sum)
			*/
			
			// Find sum
			$sum = 0;
			foreach ($Categories as $thisCat)
			{
				// DEBUG
				//echo "<br> gettype = " . gettype($thisCat);
				
				if (strcmp(gettype($thisCat), "array") == 0)
				{
					echo "<br> skipping lists variable. Need to find a better way of skipping this, or remove list from the array.";
				}
				else
				{
					//echo "<br>" . $thisCat->name . " = $" . $thisCat->balance; // DEBUG
					$sum += $thisCat->balance;
					//echo "; sum = " . $sum;  // DEBUG
				}
			}
			
			// Pay Dividends
			foreach ($Categories as $thisCat)
			{			
				if (strcmp(gettype($thisCat), "array") == 0)
				{
					echo "<br> skipping lists variable. Need to find a better way of skipping this, or remove list from the array.";
				}								
				else
				{
					$thisDiv = ($thisCat->balance / $sum );
					$thisCat->balance += ($transactions[$idx]->amount * $thisDiv);
					
					if (strcmp($thisCat->name,"Goals") == 0 )
					{					
						foreach ($Goals["list"] as $goal => $val)
						{
							$thisDiv = ($Goals[$val]->balance / $sum );
							$Goals[$val]->balance += ($transactions[$idx]->amount * $thisDiv);														
						}
					}
					
					// DEBUG
					//echo "<br> Add $" . $thisDiv . " to " . $thisCat->name . " = $" . $thisCat->balance;
				}
			}
			
		}
		elseif (in_array($transactions[$idx]->category, $Accounts["list"]))		// Transfer
		{
			echo "<br> idx = " . $idx . ". TRANSFER: cat is " . $transactions[$idx]->category . " and account is " . $transactions[$idx]->account;
			
			$Accounts[$transactions[$idx]->category]->balance -= $transactions[$idx]->amount;
		}
		elseif (in_array($transactions[$idx]->category, $otherNamesList))
		{
			// It's an Account, but not the "standard" syntax
			
			// Get the "standard" account name from the $otherNames key-value array, and then look that up in the Accounts list.
			$account_standard_name = $otherNames[$transactions[$idx]->category];
			
			//echo "<br> idx = " . $idx . ". TRANSFER: cat is " . $transactions[$idx]->category . " and account_standard_name = " . $account_standard_name;
			
			if (in_array($account_standard_name, $Accounts["list"]))
			{						
				// If it yields an account, subtract				
				$Accounts[$account_standard_name]->balance -= $transactions[$idx]->amount;
				
				//echo "<br>subtracted " . $transactions[$idx]->amount . " from " . $account_standard_name;
			}
			else
			{
				?><script>
					console.log("Message 0002: Account <?php echo $account_standard_name;?> is not recognized. (cash2_functions.php)");
				</script><?php
			}
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
			?><script>
				console.log("cash2_functions 0006: Category is <?php echo $transactions[$idx]->category;?>, Account is <?php echo $transactions[$idx]->account;?>, print_list is <?php echo $print_list;?>, InCategoryArray = <?php echo in_array($transactions[$idx]->category, $print_list);?> and InAccountArray = <?php echo in_array($transactions[$idx]->account, $print_list);?>.");
			</script><?php
			
			if ((strcmp($print_list,"all") == 0) or (strcmp($print_list,$transactions[$idx]->category) == 0) or (strcmp($print_list,$transactions[$idx]->account) == 0) or in_array($transactions[$idx]->category, $print_list) or in_array($transactions[$idx]->account, $print_list))
			{
				print_ledger_row($transactions[$idx], $Accounts, $Categories, $Goals);
			}
		}
		
	}
	
	// Print header row again at the bottom
	if ($print_ledger)
	{
		
		balances_header(true, true, true, true, $Accounts, $Categories, $Goals);
				
		// Close table
		echo "</table>";
		
	}
	
	
	
	return array ($Accounts, $Categories, $Goals);
		
}

?>
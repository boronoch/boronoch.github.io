<?php 

// Ver 2;

function read_cash_balances_idx($conn, $idx, $Funds, $Accounts, $funds_list, $accounts_list)
{
	
	// DEBUG
	echo "Called read_cash_balances_idx for IDX = ", $idx, PHP_EOL;
	
	$sql = sprintf("SELECT * FROM `cash_balances` WHERE `IDX` LIKE %d", $idx);
	$result = $conn->query($sql);

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
					if (strcmp($column,"IDX")==0)
					{
						$thisIDX = $value;
					}
					elseif (strcmp($column,"Date")==0)
					{ // Do nothing
					}
					elseif (in_array($column, $accounts_list))
					{
						$idx1 = array_search($column, $accounts_list); // Looking for index of $column within $accounts_list
						$Accounts[$idx1]->balance = $value;										
					}
					elseif (in_array($column, $funds_list))
					{
						$idx1 = array_search($column, $funds_list);
						$Funds[$idx1]->balance = $value;
					}
					else
					{
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
	$allVars = get_defined_vars();
	echo "<br><br>All Variables at end of read_cash_balances_idx:<br><br><pre>";
	print_r($allVars);
	echo "</pre>";
	echo "<br><br>";

}

?>
<?php 

// Ver 3;

function functions_ver($versions)
{
	$versions->functions = 7;
	return $versions;
}

function read_cash_balances_idx($conn, $idx, $Categories, $Accounts, $categories_list, $accounts_list)
{
	
	// DEBUG
	echo "Called read_cash_balances_idx for IDX = ", $idx, PHP_EOL;
	$debug = 1;
	
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
	$allVars = get_defined_vars();
	echo "<br><br>All Variables at end of read_cash_balances_idx:<br><br><pre>";
	print_r($allVars);
	echo "</pre>";
	echo "<br><br>";

}

?>
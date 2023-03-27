<?php

$func_ver = 10;

function process_account($lineAmnt, $lineAcct, $account)
{
/*
	INPUTS:
	 $lineAmnt: float value
	 $lineAcct: string
	 $account: 	an object of type account or category; has fields "bal" and "type"; 
				if "type" is "category", $account also has a "catParent" field
*/
	
	// $$lineAcct->bal = process_account(floatval($thisLine[2]), $thisLine[3],  $$thisLine[3]);
	
	if (isset($account)) // If the account exists as a variable
	{
		if (strcmp($account->type,"account") == 0)  // If the variable type is account, add
		{
			$account->bal = $account->bal + $lineAmnt;
		}
		elseif ((strcmp($account->type,"fund") == 0) Or (strcmp($account->type,"category") == 0)) // If the variable type is fund, subtract
		{
			$account->bal = $account->bal - $lineAmnt;
		}
		elseif (0) // If the variable type is, subtract from parent
		{
			// If this is a main category, add to it.
			if (strcmp($account->catParent,"Main") == 0)
			{
				$account->bal = $account->bal - $lineAmnt;
				
				if ($verbose_trans > 2)
				{
					?><script>
						console.log("Category: <?php echo $account . " = $" . $account->bal;?>");
					</script><?php 
				}
			}								
			else  // If it is not a main category, find the main category and add to that.		
			{
				$temp1 = $account->catParent;				
				$cntr = 0;
				
				if ($verbose_trans > 2)
				{
					?><script>
						console.log("lineCat: <?php echo $account . "; temp1: " . $temp1 . " and counter = " . $cntr;?>");
						console.log("temp1 parent = <?php echo $$temp1->catParent . " and strcmp = " . strcmp($$temp1->catParent,"Main");?>");
					</script><?php 
				}
				
				while ((strcmp($$temp1->catParent,"Main") !== 0) and ($cntr < 10))
				{
					$temp1 = $$temp1->catParent;
					$cntr = $cntr + 1;
					
					if ($verbose_trans > 2)
					{
						?><script>
							console.log("Temp1 parent: <?php echo $$temp1->catParent . " and counter = " . $cntr;?>");
						</script><?php 
					}
				
				}
				
				// Now that we found the Main category, add to that.
				$$temp1->bal = $$temp1->bal - $lineAmnt;
				
				if ($verbose_trans > 2)
				{
					?><script>
						console.log("Category: <?php echo $account . " = $" . $account->bal;?>");
						console.log("Category: <?php echo $temp1 . " = $" . $$temp1->bal;?>");
					</script><?php 
				}
			}
		}
		else // If the "type" field is not account, fund, or category, display message in console
		{
			?><script>
				console.log("Message 0000: type <?php echo $account->type;?> is not recognized. (line <?php echo $x;?> of transactions.csv, $lineAcct = <?php echo $lineAcct;?>)");
			</script><?php
			
			/*
			if ($printVarsFlag)
			{
				$allVars = get_defined_vars();
				printAllVars($allVars, $verbose_trans);
				$printVarsFlag = 0;
				
				if ($verbose_trans > 2)
				{
					echo "Debug:<br>";
					echo $account;
					echo "<br>";
					echo $Southwest->type;
					echo "<br>";
					echo $lineAcct;
					echo "<br>";
				}					
			}		
			*/
		}
					
	}
	else // If the account does not exist as a variable
	{
		?><script>
			console.log("Account <?php echo $lineAcct;?> is not declared. (line <?php echo $x;?> of transactions.csv)");
		</script><?php 
	}
	
	return $account;	
	
}

function read_cash_balances($accounts, $funds)
{
	// Read last row of cash_balances to initialize values
	
	//[...] = unpack_accounts($accounts);
	//[...] = unpack_funds($funds);
	
	// Connect to Database (http://www.w3schools.com/php/php_mysql_connect.asp)
	include 'databaseConnect.php'; /*
	$servername = "fdb6.awardspace.net";
	$username = "1544017_users";
	$password = "townHall1";
	$dbname = "1544017_users"; */

	$conn = new mysqli($servername, $username, $password, $dbname);

	// Check connection
	if ($conn->connect_error)
	{
		echo "Connection failed.";
		die("Connection failed: " . $conn->connect_error);
	}
	
	$sql = sprintf("SELECT * FROM `cash_balances` WHERE 1");
	$result = $conn->query($sql);

	if($result)
	{
		
		if ($result->num_rows > 0)
		{
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
					elseif (isset($$column))
					{
						$$column->bal = $value;				
						
					}
					else
					{
						?><script>console.log("Column <?php echo $column; ?> is not declared in names.csv");</script><?php
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

}

function echoCurrency($input)
{
	//echo gettype($input);
	//echo "<br>";
	
	if ($input < 0)
	{
		return sprintf('-$%8.2f',-$input);
	}
	else
	{
		return sprintf('$%8.2f',$input);
	}
}

function selectHighestIDX()
{
	// Connect to Database (http://www.w3schools.com/php/php_mysql_connect.asp)
	include 'databaseConnect.php'; /*
	$servername = "fdb6.awardspace.net";
	$username = "1544017_users";
	$password = "townHall1";
	$dbname = "1544017_users"; */

	$conn = new mysqli($servername, $username, $password, $dbname);

	// Check connection
	if ($conn->connect_error)
	{
		echo "Connection failed.";
		die("Connection failed: " . $conn->connect_error);
	}

	$sql = "SELECT * FROM `cash_balances` WHERE 1";


	?><script>
		console.log("Message 0000: ");
		console.log("$sql =  <?php echo $sql;?>");
		//console.log("$result =  <?php echo $result;?>");
	</script><?php

	$result = $conn->query($sql);

	// find highest IDX
	$idxs = array();
	$n=0;
	while($row = $result->fetch_assoc())
	{
		$idxs[$n] = $row["IDX"];
		$n=$n+1;
	}

	$maxIDX = max($idxs);

	?><script>
		console.log("Message 0001: ");
		console.log("$maxIDX = <?php echo $maxIDX;?>");
	</script><?php
	
	return $maxIDX;

}


?>
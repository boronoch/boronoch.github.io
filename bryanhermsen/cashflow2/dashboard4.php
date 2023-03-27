<html>
<head>
<title>Dashboard</title>
<!-- Placeholder for CSS
<link rel="stylesheet" type="text/css" href="brotherStyles.css">
-->

<!-- 	dashboard4.html
		<description>
-->
</head>

<body>

<?php

	// Declare classes and functions
	include 'classes.php';
	include 'cash2_functions.php';
	
	$versions->dashboard4 = 17;
	$versions->functions = functions_ver();
	
	// Connect to database
	include 'databaseConnect.php';
	
	// Initialize Accounts, Funds, Categories
	include 'names.php';	
	
	print_r($versions); echo "<br>";
	
	// Read last three entries from cash_balances (three with highest indices)
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
			
			$mostRecent[0] = $idxs[sizeof($idxs)-1];
			$mostRecent[1] = $idxs[sizeof($idxs)-2];
			$mostRecent[2] = $idxs[sizeof($idxs)-3];
			
			echo "Pulling indices " . $mostRecent[0] . ", " . $mostRecent[1] . ", " . $mostRecent[2] . " from cash_balances." . PHP_EOL;
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
	echo "<br><br>Most Recent:<br>";
	var_dump($mostRecent);
	
	// DEBUG
	echo "<p>Categories before read_cash_balances_idx:<br>";
	var_dump($Categories);
	echo "</p><p>Accounts  before read_cash_balances_idx:<br>";
	var_dump($Accounts);
	echo "</p>";
	
	list($Categories, $Accounts) = read_cash_balances_idx($conn, $mostRecent[0], $Categories, $Accounts, $categories_list, $accounts_list);
	
	// DEBUG
	echo "<p>Categories after read_cash_balances_idx:<br>";
	var_dump($Categories);
	echo "</p><p>Accounts  after read_cash_balances_idx:<br>";
	var_dump($Accounts);
	echo "</p>";
	
	// Process transactions that haven't been archived yet
	
	// Display balances including new transactions
	
	// Close connection
	$conn->close();

?>

<p>End of PHP in dashboard4.php.</p>
</body>

</html>
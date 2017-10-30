<html>
<head>
<title>Archive Confirm</title>
<!-- Placeholder for CSS
<link rel="stylesheet" type="text/css" href="brotherStyles.css">
-->

<!-- 	archive_select.php
		<description>
-->
</head>

<body>

<?php

	// Read inputs
	$selectAll = $_POST["selectAll"];
	$clearAll = $_POST["clearAll"];
	$selectTransIDX = $_POST["selectTrans"];
	
	// Declare classes and functions
	include 'classes.php';
	include 'cash2_functions.php';
	
	$versions->archive_confirm = 11;
	$versions->functions = functions_ver();
	
	// Connect to database
	include 'databaseConnect.php';
	
	// Initialize Accounts, Funds, Categories
	include 'names.php';
	
	print_r($versions); echo "<br>";
	
	// read most recent archive	
	list($Accounts, $Categories) = read_latest_cash_balances($conn, $Categories, $Accounts, $categories_list, $accounts_list);
	
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
	
	?>
		<p><h1>Transactions selected</h1></p><br>
	<?php
	
	// Read selected transactions
	$sql = "SELECT * 
			FROM `transactions` 
			WHERE `IDX` IN (" . $stList . ")
			ORDER BY `sort_order` ASC, `IDX` ASC";
			
	$result = $conn->query($sql); // Object array
	
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
		
	}
	
	// DEBUG
	echo "<br><br>selectTransIDX:<br><br><pre>";
	print_r($selectTransIDX); echo "<br>";
	echo "<br><br>stList:<br><br>";
	print_r($stList); echo "<br>";
	echo "<br><br>selectTransOBJ:<br><br>";
	print_r($selectTransOBJ); echo "<br>";
	echo "</pre>";
			
	?>
	<form method="post">
	<?php
	// Display transactions in a table with checked boxes
	echo "<table border='1'>";
	echo "<tr>
			<td> </td>
			<td>Date</td>
			<td>Description</td>
			<td>Amount</td>
			<td>Account</td>
			<td>Category</td>
			<td> - </td>";
	
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

	echo "</tr>";
	
		// process transactions
		foreach ($selectTransOBJ as $thisTransaction)
		{
			//echo "Categories list in archive_select for loop <br>";
			//print_r($Categories["list"]);
			
			$thisTransactionArray[0] = $thisTransaction;
			list($Accounts, $Categories) = process_transactions($conn, $thisTransactionArray, $Accounts, $Categories, $Funds, $Goals, false);
						
			?>
			<tr>
				<td><input type = 'checkbox'
							 name = 'selectTrans[]'
							 value= "<?php echo $thisTransaction->IDX;?> "
							 <?php echo " checked"; ?>
							>
				</td>
				<td><?php echo $thisTransaction->date;?></td>
				<td><?php echo $thisTransaction->description;?></td>
				<td><?php echoCurrency($thisTransaction->amount);?></td>
				<td><?php echo $thisTransaction->account;?></td>
				<td><?php echo $thisTransaction->category;?></td>
				<td> </td>			
				<?php
				
				foreach ($Accounts as $col => $val)
				{
					if (strcmp($col,"list") !== 0 ) 
					{
						?>
						<td><?php echoCurrency($val->balance);?>
						<script>
							console.log("col = <?php echo $col;?>; val = <?php echo $val->name;?>");
						</script>
						</td>			
						<?php
					}
				}
				
			echo "</tr>";
			
		}
	
	// Print headers again at the bottom of the table.
	echo "<tr>
			<td> </td>
			<td>Date</td>
			<td>Description</td>
			<td>Amount</td>
			<td>Account</td>
			<td>Category</td>
			<td> - </td>";
			
	foreach ($Accounts as $col => $val)
	{
		if (strcmp($col,"list") !== 0 ) 
		{
			?>
			<td><?php echo $col;?></td>
			<?php
		}
	}
	
	echo "</tr>";
	echo "</table>";
	
	// Display "Select All" and "Submit" buttons
	?>
	
		<table>
			<tr>
				<td>
					<?php
						if ($selectAll)
						{ ?>
							<input type="submit" value="Clear All" name="clearAll">
						<?php
						}
						else
						{
						?>
							<input type="submit" value="Select All" name="selectAll">
						<?php
						}
					?>
				</td>
				<td><input type="submit" value="Archive Selected" name="archive" formaction="archive_submit.php"></td>
			</tr>
		</table>
	</form>
	
	<br><br>
	<p><h1>Transactions not selected</h1></p><br>
	<?php
	
	// Read non-selected transactions
	$sql = "SELECT * 
			FROM `transactions` 
			WHERE `IDX` NOT IN (" . $stList . ")
			ORDER BY `sort_order` ASC, `IDX` ASC";
			
	$result = $conn->query($sql); // Object array
	
	if($result)
	{
		
		if ($result->num_rows > 0)
		{
		
			$k = 0;
			while($row = $result->fetch_assoc())
			{
			
				$nonSelectTransOBJ[$k] = read_transaction($row);
				$k++;
			
			}
		
		}
		
	}
	?>
	<form method="post">	
	<?php
	// Display transactions in a table with checked boxes
	echo "<table border='1'>";
	echo "<tr>
			<td> </td>
			<td>Date</td>
			<td>Description</td>
			<td>Amount</td>
			<td>Account</td>
			<td>Category</td>
			<td> - </td>";
	
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

	echo "</tr>";
	
		// process transactions
		foreach ($nonSelectTransOBJ as $thisTransaction)
		{
			//echo "Categories list in archive_select for loop <br>";
			//print_r($Categories["list"]);
			
			$thisTransactionArray[0] = $thisTransaction;
			list($Accounts, $Categories) = process_transactions($conn, $thisTransactionArray, $Accounts, $Categories, $Funds, $Goals, false);
						
			?>
			<tr>
				<td><input type = 'checkbox'
							 name = 'selectTrans[]'
							 value= "<?php echo $thisTransaction->IDX;?> "
							>
				</td>
				<td><?php echo $thisTransaction->date;?></td>
				<td><?php echo $thisTransaction->description;?></td>
				<td><?php echoCurrency($thisTransaction->amount);?></td>
				<td><?php echo $thisTransaction->account;?></td>
				<td><?php echo $thisTransaction->category;?></td>
				<td> </td>			
				<?php
				
				foreach ($Accounts as $col => $val)
				{
					if (strcmp($col,"list") !== 0 ) 
					{
						?>
						<td><?php echoCurrency($val->balance);?>
						<script>
							console.log("col = <?php echo $col;?>; val = <?php echo $val->name;?>");
						</script>
						</td>			
						<?php
					}
				}
				
			echo "</tr>";
			
		}
	
	// Print headers again at the bottom of the table.
	echo "<tr>
			<td> </td>
			<td>Date</td>
			<td>Description</td>
			<td>Amount</td>
			<td>Account</td>
			<td>Category</td>
			<td> - </td>";
			
	foreach ($Accounts as $col => $val)
	{
		if (strcmp($col,"list") !== 0 ) 
		{
			?>
			<td><?php echo $col;?></td>
			<?php
		}
	}
	
	echo "</tr>";
	echo "</table>";
	
	// Display "Select All" and "Submit" buttons
	?>
	
		<table>
			<tr>
				<td>
					<?php
						if ($selectAll)
						{ ?>
							<input type="submit" value="Clear All" name="clearAll">
						<?php
						}
						else
						{
						?>
							<input type="submit" value="Select All" name="selectAll">
						<?php
						}
					?>
				</td>
				<td><input type="submit" value="Archive Selected" name="archive" formaction="archive_confirm.php"></td>
			</tr>
		</table>
	</form>
	<?php
	
	// Close connection
	$conn->close();

?>

<p>End of PHP in archive_select.php.</p>
</body>

</html>
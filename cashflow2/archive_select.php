<html>
<head>
<title>Archive Select</title>
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
	$selectAll = $_GET["selectAll"];
	$clearAll = $_GET["clearAll"];
	
	// Declare classes and functions
	include 'classes.php';
	include 'cash2_functions.php';
	
	$versions->archive_select = 16;
	$versions->functions = functions_ver();
	
	// Connect to database
	include 'databaseConnect.php';
	
	// Initialize Accounts, Funds, Categories
	include 'names.php';
	
	print_r($versions); echo "<br>";
	
	// read most recent archive	
	list($Accounts, $Categories) = read_latest_cash_balances($conn, $Categories, $Accounts, $categories_list, $accounts_list);
			
	// read all unarchived transactions
	$newTransactions = read_new_transactions($conn);

	// Check to see if "Select All" was clicked.

	// Display transactions in a table with check boxes
	?>
	<form method="get">
	<?php
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
	/*	
	foreach ($Categories as $col => $val)
	{
		if (strcmp($col,"list") !== 0 ) 
		{
			?>
			<td><?php echo $col;?></td>
			<?php
		}
	}
	*/
	echo "</tr>";
	
		// process transactions
		foreach ($newTransactions as $thisTransaction)
		{
			//echo "Categories list in archive_select for loop <br>";
			//print_r($Categories["list"]);
			
			$thisTransactionArray[0] = $thisTransaction;
			list($Accounts, $Categories) = process_transactions($conn, $thisTransactionArray, $Accounts, $Categories, $Funds, false);
			
			//$Accounts = $Accounts_result;
			//$Categories = $Categories_result;
			
			?>
			<tr>
				<td><input type = 'checkbox'
							 name = 'selectTrans[]'
							 value= "<?php echo $thisTransaction->IDX;?>"
							 <?php if ($selectAll)
							 {
								echo " checked";
							 } ?>
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
				/*
				?><td> </td><?php
				
				foreach ($Categories as $col => $val)
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
			*/
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
	/*
	?><td></td><?php
		
	foreach ($Categories as $col => $val)
	{
		if (strcmp($col,"list") !== 0 ) 
		{
			?>
			<td><?php echo $col;?></td>
			<?php
		}
	}
	*/
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
<!DOCTYPE html>
<html>
<body>

<?php
	include 'classes.php';
	include 'databaseConnect.php';	
	include 'cash2_functions.php';
	$versions->display_transactions = 1;
?>

<script>
	console.log("Version: <?php echo $versions->display_transactions; ?>");
</script>

<?php
	// Read transactions from database
	$newTransactions = read_new_transactions($conn);
?>

<!-- Display the transactions in the database -->
<table>
	<tr>
		<th>Insert Before</th>
		<th>Sort Order</th>
		<th>Date</th>
		<th>Description</th>
		<th>Amount ($)</th>
		<th>Account</th>
		<th>Category</th>
	</tr>
	
	<?php 
		foreach ($newTransactions as $thisTransaction)
		{			
			?>
			<form action="addTransaction.php" method="get">
			<tr>
				<td><input type = 'submit'
							 value = 'Insert Before'
							>
					<input type = 'hidden'
							name = "insertBefore"
							value = <?php echo $thisTransaction->sortOrder; ?>
							>
				</td>
				<td><?php echo $thisTransaction->sortOrder;?></td>
				<td><?php echo $thisTransaction->date;?></td>
				<td><?php echo $thisTransaction->description;?></td>
				<td><?php echoCurrency($thisTransaction->amount);?></td>
				<td><?php echo $thisTransaction->account;?></td>
				<td><?php echo $thisTransaction->category;?></td>
			</tr>
			</form>	
				<?php
			
		}
		?>
	
</table>	


<?php
	// Close connection
	$conn->close();
?>

</body>
</html>
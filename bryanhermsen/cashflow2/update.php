<!DOCTYPE html>
<html>
<body>
<!-- Rev Hist 
v01 - Created form
v

-->

<!-- code version -->
<?php
	$ver = 9;
?>

<script>
	console.log("Version: <?php echo $ver; ?>");
</script>

<!-- Functions for cashflow app -->
<?php include 'cash_functions.php'; ?>


<?php

	// Read inputs
	$moveTrans = $_POST["moveTrans"];
	
	// Check for selected items
	if (!empty($moveTrans))
	{
		
		// Connect to Database (http://www.w3schools.com/php/php_mysql_connect.asp)
		/*$servername = "fdb6.awardspace.net";
		$username = "1544017_users";
		$password = "townHall1";
		$dbname = "1544017_users"; */
		include 'databaseConnect.php';

		$conn = new mysqli($servername, $username, $password, $dbname);

		// Check connection
		if ($conn->connect_error)
		{
			echo "Connection failed.";
			die("Connection failed: " . $conn->connect_error);
		}
		
		// Create list of checked items from array
		$mtSize = sizeof($moveTrans);
		if ($mtSize > 0)
		{
			$mtList = $moveTrans[0];
			if ($mtSize > 1)
			{
				for ($x=1; $x <$mtSize; $x++)
				{
					$mtList = $mtList . ", " . $moveTrans[$x];
				}
			}			
		}
		
		// Query: SELECT checked items based on IDX	
		$sql = "SELECT * 
				FROM `transactions` 
				WHERE `IDX` IN (" . $mtList . ")
				ORDER BY `sort_order` ASC, `IDX` ASC";
		$result = $conn->query($sql);
		
		// Print list to console 
		?><script>
			console.log("mtList: <?php echo $mtList; ?>");
			console.log("result type: <?php echo gettype($result); ?>");
			console.log(" ");
		</script><?php	
		
		
		if($result)
		{
			if ($result->num_rows > 0)
			{
				?>
				<!-- Create table header -->
				<table>
				<tr>
					<th>Date</th>
					<th>Description</th>
					<th>Amount</th>
					<th>Account</th>
					<th>Category</th>
					<th>(edit one at a time)</th>
				</tr>
				<?php 
				
				while($row = $result->fetch_assoc())
				{
					
					?>			
					<script>
						console.log("Displaying <?php echo $row['IDX'];?>");
						console.log("Date =  <?php echo $row['Date'];?>");
						console.log("Description = <?php echo $row['Description'];?>");
						console.log("Amount = <?php echo $row['Amount'];?>");
						console.log("Account = <?php echo $row['Account'];?>");
						console.log("Category = <?php echo $row['Category'];?>");
					</script>		
					
					<!-- Display row -->
					<form action="commit_update.php" method="post">
					<tr>
						<td><input type="date" name="transDate" value="<?php echo $row['Date'];?>"></td>
						<td><input type="text" name="transDesc" value="<?php echo $row['Description'];?>"></td>
						<td><input type="number" name="transAmnt" step=".01" value="<?php echo $row['Amount'];?>"></td>
						<td><select name="transAcct" value="<?php echo $row['Account'];?>">
							<option value="Cash" <?php if (strcmp($row['Account'],"Cash") == 0) { echo "selected"; }?>>Cash</option>
							<option value="Chase" <?php if (strcmp($row['Account'],"Chase") == 0) { echo "selected"; }?>>Chase</option>
							<option value="GE Checking" <?php if (strcmp($row['Account'],"GE Checking") == 0) { echo "selected"; }?>>GE Checking</option>
							<option value="Chase Check" <?php if (strcmp($row['Account'],"Chase Check") == 0) { echo "selected"; }?>>Chase Check</option>
							<option value="AmEx" <?php if (strcmp($row['Account'],"AmEx") == 0) { echo "selected"; }?>>AmEx</option>
							<option value="Southwest" <?php if (strcmp($row['Account'],"Southwest") == 0) { echo "selected"; }?>>Southwest</option>
							<option value="GE Savings" <?php if (strcmp($row['Account'],"GE Savings") == 0) { echo "selected"; }?>>GE Savings</option>		
							<option value="Income" <?php if (strcmp($row['Account'],"Income") == 0) { echo "selected"; }?>>Income</option>
							<option value="Dividend" <?php if (strcmp($row['Account'],"Dividend") == 0) { echo "selected"; }?>>Dividend</option>
                                                        <option value="Spending Other" <?php if (strcmp($row['Account'],"Spending Other") == 0) { echo "selected"; }?>>Spending Other</option>
                                                        <option value="Living" <?php if (strcmp($row['Account'],"Living") == 0) { echo "selected"; }?>>Living</option>
							<option value="Grocery" <?php if (strcmp($row['Account'],"Grocery") == 0) { echo "selected"; }?>>Grocery</option>
							<option value="Gasoline" <?php if (strcmp($row['Account'],"Gasoline") == 0) { echo "selected"; }?>>Gasoline</option>
                                                        <option value="Home Imp" <?php if (strcmp($row['Account'],"Home Imp") == 0) { echo "selected"; }?>>Home Imp</option>
							<option value="Charity" <?php if (strcmp($row['Account'],"Charity") == 0) { echo "selected"; }?>>Charity</option>
						</select></td>
						<td><select name="transCat" value="<?php echo $row['Category'];?>">
							<option value="Marann" <?php if (strcmp($row['Category'],"Marann") == 0) { echo "selected"; }?>>Marann</option>
							<option value="Spending Other" <?php if (strcmp($row['Category'],"Spending Other") == 0) { echo "selected"; }?>>Spending Other</option>
							<option value="Living" <?php if (strcmp($row['Category'],"Living") == 0) { echo "selected"; }?>>Living</option>
							<option value="Grocery" <?php if (strcmp($row['Category'],"Grocery") == 0) { echo "selected"; }?>>Grocery</option>
							<option value="Gasoline" <?php if (strcmp($row['Category'],"Gasoline") == 0) { echo "selected"; }?>>Gasoline</option>
							<option value="Income" <?php if (strcmp($row['Category'],"Income") == 0) { echo "selected"; }?>>Income</option>
							<option value="Home Imp" <?php if (strcmp($row['Category'],"Home Imp") == 0) { echo "selected"; }?>>Home Imp</option>
							<option value="Charity" <?php if (strcmp($row['Category'],"Charity") == 0) { echo "selected"; }?>>Charity</option>
							<option value="Bills" <?php if (strcmp($row['Category'],"Bills") == 0) { echo "selected"; }?>>Bills</option>
							<option value="Loans" <?php if (strcmp($row['Category'],"Loans") == 0) { echo "selected"; }?>>Loans</option>
							<option value="Bank Other" <?php if (strcmp($row['Category'],"Bank Other") == 0) { echo "selected"; }?>>Bank Other</option>
							<option value="Dividend" <?php if (strcmp($row['Category'],"Dividend") == 0) { echo "selected"; }?>>Dividend</option>
							<option value="Dining" <?php if (strcmp($row['Category'],"Dining") == 0) { echo "selected"; }?>>Dining</option>
							<option value="Ten Percent" <?php if (strcmp($row['Category'],"Ten Percent") == 0) { echo "selected"; }?>>Ten Percent</option>
							<option value="Emergency" <?php if (strcmp($row['Category'],"Emergency") == 0) { echo "selected"; }?>>Emergency</option>
							<option value="Gifts" <?php if (strcmp($row['Category'],"Gifts") == 0) { echo "selected"; }?>>Gifts</option>
							<option value="eMV" <?php if (strcmp($row['Category'],"eMV") == 0) { echo "selected"; }?>>eMV</option>
							<option value="NYC" <?php if (strcmp($row['Category'],"NYC") == 0) { echo "selected"; }?>>NYC</option>
							<option value="TEAM" <?php if (strcmp($row['Category'],"TEAM") == 0) { echo "selected"; }?>>TEAM</option>
							<option value="Wedding" <?php if (strcmp($row['Category'],"Wedding") == 0) { echo "selected"; }?>>Wedding</option>
							<option value="Mwed" <?php if (strcmp($row['Category'],"Mwed") == 0) { echo "selected"; }?>>Mwed</option>
							<option value="Roth" <?php if (strcmp($row['Category'],"Roth") == 0) { echo "selected"; }?>>Roth</option>
							<option value="Card" <?php if (strcmp($row['Category'],"Card") == 0) { echo "selected"; }?>>Card</option>
							<option value="Work Exp" <?php if (strcmp($row['Category'],"Work Exp") == 0) { echo "selected"; }?>>Work Exp</option>
							<option value="Car Exp" <?php if (strcmp($row['Category'],"Car Exp") == 0) { echo "selected"; }?>>Car Exp</option>
                                                        <option value="Cash" <?php if (strcmp($row['Category'],"Cash") == 0) { echo "selected"; }?>>Cash</option>
							<option value="Chase" <?php if (strcmp($row['Category'],"Chase") == 0) { echo "selected"; }?>>Chase</option>
							<option value="GE Checking" <?php if (strcmp($row['Category'],"GE Checking") == 0) { echo "selected"; }?>>GE Checking</option>
							<option value="Chase Check" <?php if (strcmp($row['Category'],"Chase Check") == 0) { echo "selected"; }?>>Chase Check</option>
							<option value="AmEx" <?php if (strcmp($row['Category'],"AmEx") == 0) { echo "selected"; }?>>AmEx</option>
							<option value="Southwest" <?php if (strcmp($row['Category'],"Southwest") == 0) { echo "selected"; }?>>Southwest</option>
						</select></td>
						<td><input type="submit" value="Submit"></td>
					</tr>
					<input type="hidden" name="IDX" value="<?php echo $row['IDX'];?>">
					</form>
					<?php 
				}
				
			}
			else
			{
				echo "Query returned 0 rows";		
			}
			
		}
		else
		{
			echo "Query failed";		
		}
					
	}
	else
	{
		echo "No transactions selected for updating";		
	}
	
	
?>
	
</body>
</html>
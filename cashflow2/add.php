<!DOCTYPE html>
<html>
<body>
<!-- Rev Hist 
v01 - Created form
v06 - It started working
v07 - Added header
v13 - added groundwork for check boxes for moving transactions to a different table
v21 - Check boxes are working and POSTing an array of IDX values. They are then being read in and put into the console
v23 - Added ORDER BY 
v25 - Archive button working
v33 - Add Income and Dividend to Categories
v34 - Added links to balance.html
v36 - Added form for Fund Transfers
v37 - Added form for Account Transfers
v38 - Started adding sort order
v49 - Saved as add.php.
v53 - Added code in Archive section to run names.php, calculate new values with archived items, and write to database
v63 - changed default ORDER by to sort_order
v65 - added Living category
v97 - added buttons for sort.php: move 2nd above 1st, switch places
v99 - added Car Exp to drop down list
v100- added Ledger for Archive operation
v102- added '' around date in archive, added Update button
v103- fixed default value in form
-->

<!-- code version -->
<?php
	$ver = 117;
?>

<!-- DEBUG parameters -->
<?php
	$debug_add = 1;
	$debug_archive = 1;
	$writeArchiveLedger = 1;
?>

<script>
	console.log("Version: <?php echo $ver; ?>");
</script>

<!-- Functions for cashflow app -->
<?php include 'cash_functions.php'; ?>


<!-- Form for entering new transactions -->
<table>
	<tr>
		<th>Date</th>
		<th>Description</th>
		<th>Amount</th>
		<th>Account</th>
		<th>Category</th>
		<th></th>
	</tr>

	<form action="add.php" method="post">
	<tr>
		<td><input type="date" name="transDate" value="<?php echo date("Y-m-d");?>"></td>
		<td><input type="text" name="transDesc"></td>
		<td><input type="number" name="transAmnt" step=".01"></td>
		<td><select name="transAcct">
			<option value="Cash">Cash</option>
			<option value="Chase">Chase</option>
			<option value="GE Checking">GE Checking</option>
			<option value="Chase Check">Chase Check</option>
			<option value="AmEx">AmEx</option>
			<option value="Southwest">Southwest</option>
			<option value="GE Savings">GE Savings</option>		
			<option value="Income">Income</option>
			<option value="Dividend">Dividend</option>
		</select></td>
		<td><select name="transCat">
			<option value="Marann">Marann</option>
			<option value="Spending Other">Spending Other</option>
			<option value="Living">Living</option>
			<option value="Grocery">Grocery</option>
			<option value="Gasoline">Gasoline</option>
			<option value="Income">Income</option>
			<option value="Home Imp">Home Imp</option>
			<option value="Charity">Charity</option>
			<option value="Bills">Bills</option>
			<option value="Loans">Loans</option>
			<option value="Bank Other">Bank Other</option>
			<option value="Dividend">Dividend</option>
			<option value="Dining">Dining</option>
			<option value="Ten Percent">Ten Percent</option>
			<option value="Emergency">Emergency</option>
			<option value="Gifts">Gifts</option>
			<option value="eMV">eMV</option>
			<option value="NYC">NYC</option>
			<option value="TEAM">TEAM</option>
			<option value="Wedding">Wedding</option>
			<option value="Mwed">Mwed</option>
			<option value="Roth">Roth</option>
			<option value="Card">Card</option>
			<option value="Work Exp">Work Exp</option>
			<option value="Car Exp">Car Exp</option>
		</select></td>
		<td><input type="submit" value="Submit"></td>
	</tr>
	</form>
</table>
<br><br>

<!-- Form for Account Transfers -->
Transfer between Accounts:<br>
<table>
	<tr>
		<th>Date</th>
		<th>Description</th>
		<th>Amount</th>
		<th>From</th>
		<th>To</th>
		<th></th>
	</tr>
	<form action="add.php" method="post">
	<tr>
		<td><input type="date" name="transDate" value="<?php echo date("Y-m-d");?>"></td>
		<td><input type="text" name="transDesc"></td>
		<td><input type="number" name="transAmnt" step=".01"></td>
		<td><select name="transCat">
			<option value="Cash">Cash</option>
			<option value="Chase">Chase</option>
			<option value="GE Checking">GE Checking</option>
			<option value="Chase Check">Chase Check</option>
			<option value="AmEx">AmEx</option>
			<option value="Southwest">Southwest</option>
			<option value="GE Savings">GE Savings</option>		
			<option value="Income">Income</option>
			<option value="Dividend">Dividend</option>
		</select></td>
		<td><select name="transAcct">
			<option value="Cash">Cash</option>
			<option value="Chase">Chase</option>
			<option value="GE Checking">GE Checking</option>
			<option value="Chase Check">Chase Check</option>
			<option value="AmEx">AmEx</option>
			<option value="Southwest">Southwest</option>
			<option value="GE Savings">GE Savings</option>
		</select></td>
		<td><input type="submit" value="Submit"></td>
	</form>
</table>
<br><br>

<!-- Form for Funds Transfers -->
Transfer between Funds: <br>
<table>
	<tr>
		<th>Date</th>
		<th>Description</th>
		<th>Amount</th>
		<th>From</th>
		<th>To</th>
		<th></th>
	</tr>
	<form action="add.php" method="post">
	<tr>
		<td><input type="date" name="transDate" value="<?php echo date("Y-m-d");?>"></td>
		<td><input type="text" name="transDesc"></td>
		<td><input type="number" name="transAmnt" step=".01"></td>
		<td><select name="transAcct">
			<option value="Marann">Marann</option>
			<option value="Spending">Spending</option>
			<option value="Living">Living</option>
			<option value="Home Imp">Home Imp</option>
			<option value="Charity">Charity</option>
			<option value="Bank">Bank</option>
			<option value="Ten Percent">Ten Percent</option>
			<option value="Emergency">Emergency</option>
			<option value="Gifts">Gifts</option>
			<option value="eMV">eMV</option>
			<option value="NYC">NYC</option>
			<option value="TEAM">TEAM</option>
			<option value="Wedding">Wedding</option>
			<option value="Mwed">Mwed</option>
			<option value="Roth">Roth</option>
			<option value="Card">Card</option>
			<option value="Work Exp">Work Exp</option>
			<option value="Income">Income</option>
			<option value="Dividend">Dividend</option>
		</select></td>
		<td><select name="transCat">
			<option value="Marann">Marann</option>
			<option value="Spending Other">Spending Other</option>
			<option value="Living">Living</option>
			<option value="Grocery">Grocery</option>
			<option value="Gasoline">Gasoline</option>
			<option value="Home Imp">Home Imp</option>
			<option value="Charity">Charity</option>
			<option value="Bills">Bills</option>
			<option value="Loans">Loans</option>
			<option value="Bank Other">Bank Other</option>
			<option value="Dining">Dining</option>
			<option value="Ten Percent">Ten Percent</option>
			<option value="Emergency">Emergency</option>
			<option value="Gifts">Gifts</option>
			<option value="eMV">eMV</option>
			<option value="NYC">NYC</option>
			<option value="TEAM">TEAM</option>
			<option value="Wedding">Wedding</option>
			<option value="Mwed">Mwed</option>
			<option value="Roth">Roth</option>
			<option value="Card">Card</option>
			<option value="Work Exp">Work Exp</option>
			<option value="Income">Income</option>
			<option value="Dividend">Dividend</option>
		</select></td>
		<td><input type="submit" value="Submit"></td>
	</form>
</table>
<br><br>

Sort by: 
<form action="add.php" method="post">
	<select name="sortBy">
		<option value="Date">Date</option>
		<option value="sort_order">Sort Order</option>
		<option value="Description">Description</option>
		<option value="Amount">Amount</option>
		<option value="Account">Account</option>
		<option value="Category">Category</option>
	</select>
	<select name="sortOrder">
		<option value="ASC">Ascending</option>
		<option value="DESC">Descending</option>
	</select>
	<input type="submit" value="Sort">
</form>

<?php

	// Read inputs
	$transDate = $_POST["transDate"];
	$transDesc = $_POST["transDesc"];
	$transAmnt = $_POST["transAmnt"];
	$transAcct = $_POST["transAcct"];
	$transCat =  $_POST["transCat"];
	$moveTrans = $_POST["moveTrans"];
	$selectAll = $_POST["selectAll"];
	$clearAll = $_POST["clearAll"];
	
	// Connect to database
	include 'databaseConnect.php';
	echo "include<br>";
	mysql_connect($servername, $username, $password) or die(mysql_error());
	echo "Debug: mysql_connect.<br>";
	mysql_select_db($dbname) or die(mysql_error());
	echo "mysql_select_db<br>";

	// If there is new input, add it to the database
	if ($transAmnt)
	{
	
		// Print to console
		?>
		<script>
			console.log("Adding new transaction to database");
			console.log("");
		</script>		
		<?php 
		
			
		//$sql = sprintf("INSERT INTO `transactions` (`IDX`, `Date`, `Description`, `Amount`, `Account`, `Category`) VALUES (NULL, \'2015-12-10\', \'Donation for lunch at work\', \'10.00\', \'Cash\', \'Dining\')");
		//$sql = sprintf("INSERT INTO transactions(`IDX`, `Date`, `Description`, `Amount`, `Account`, `Category`) VALUES (NULL, '2015-12-10', 'Donation for lunch at work', '10.00', 'Cash', 'Dining')");
		$sql = sprintf("INSERT INTO transactions(`IDX`, `Date`, `Description`, `Amount`, `Account`, `Category`) VALUES (NULL, '" . $transDate . "', '" . $transDesc . "', '" . $transAmnt . "', '" . $transAcct . "', '" . $transCat . "')");
		//$sql = sprintf("INSERT INTO `transactions` (`IDX`, `Date`, `Description`, `Amount`, `Account`, `Category`) VALUES (NULL, \'" . $transDate . "\', \'" . $transDesc . "\', \'" . $transAmnt . "\', \'" . $transAcct . "\', \'" . $transCat . "\')");
		$result = mysql_query($sql) or die(mysql_error());
		
		?>
		<script>
			console.log("SQL: <?php echo $sql; ?>");
			console.log("Result: <?php echo $result; ?>");
			console.log(" ");
		</script>		
		<?php 
		
		// Add sort order
		// Example: UPDATE `transactions` SET `sort_order` = '316' WHERE `transactions`.`IDX` = 316
		$next_idx = mysql_insert_id();		
		$sql = sprintf("UPDATE `transactions` SET `sort_order` = '" . $next_idx . "' WHERE `transactions`.`IDX` = " . $next_idx);
		$result = mysql_query($sql);
		
		?>
		<script>			
			console.log("next_idx: <?php echo $next_idx; ?>");	
			console.log("type(next_idx): <?php echo gettype($next_idx); ?>");
			console.log("SQL: <?php echo $sql; ?>");
			console.log("Result: <?php echo $result; ?>");
			console.log(" ");
		</script>		
		<?php 
		
		/*
		$sql = sprintf("SELECT MAX(`IDX`) FROM `transactions`");
		$result = mysql_query( $sql );
		$row = mysql_fetch_array( $result );
		$next_idx = $row['IDX'];
		
		?>
		<script>
			console.log("SQL: <?php echo $sql; ?>");
			console.log("Result: <?php echo $result; ?>");
			console.log("row: <?php echo $row; ?>");
			console.log("next_idx: <?php echo $next_idx; ?>");
			console.log("Amount: <?php echo $row['Amount']; ?>");
			console.log("IDX: <?php echo $row['IDX']; ?>");
			console.log("IDX: <?php echo mysql_insert_id(); ?>");			
			console.log(" ");
		</script>		
		<?php 
			
		*/
	}
	
	// If items are checked, archive or delete them
	if (!empty($moveTrans))
	{
	
		if (isset($_POST['archive']))
		{
			// Archive Selected
			
			// Initialize all account and fund variables
			include 'oldnames.php';
			
			// Read last row of cash_balances to initialize values
				// Connect to Database (http://www.w3schools.com/php/php_mysql_connect.asp)
				/*$servername = "fdb6.awardspace.net";
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
				
				// Find highest IDX
				$maxIDX = selectHighestIDX();
				?><script>
					console.log("add.php Message 0001: ");
					console.log("$maxIDX = <?php echo $maxIDX;?>");
				</script><?php
				
				//$sql = sprintf("SELECT * FROM `cash_balances` WHERE 1");
				$sql = sprintf("SELECT * FROM `cash_balances` WHERE `IDX` LIKE %d", $maxIDX);
				$result = $conn->query($sql);

				if($result)
				{
					
					if ($result->num_rows > 0)
					{
						// Create ledger
						if ($writeArchiveLedger)
						{	
							// Open the Ledger
							$csvArchiveLedger = fopen('uploads/Cash2016-ArchiveLedger.csv', 'w');
							
							// Start creating the header row of the Ledger
							$a_1 = array('Date', 'Description', 'Amount', 'Account', 'Category');
							$a_2 = array('', '');
							$a_4 = array('','Total', 'Bank', 'Spending', 'Charity', 'Marann');
							$a_5 = array();
							$a_7 = array();							
						}
						
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
									
									if ($debug_archive)
									{
										?><script>console.log("<?php echo $column; ?>->bal = <?php echo $$column->bal; ?>;  <?php echo $column; ?>->newBal = <?php echo $$column->newBal; ?>;");</script><?php
									}
									
									if ($writeArchiveLedger)
									{	
										// Continue creating the header row of the Ledger
										array_push($a_5, $column . "->bal" , $column . "->newBal");
										array_push($a_7, $$column->bal, $$column->newBal);
									}
									
								}
								else
								{
									?><script>console.log("Column <?php echo $column; ?> is not declared in names.csv");</script><?php
								}								
							}

							if ($writeArchiveLedger)
							{	
								// Continue creating the header row of the Ledger
								$fields = array_merge($a_1, $a_2, $a_5);
								fputcsv($csvArchiveLedger,$fields);
							}
						}

						// Write beginning balances from Cash Balances
						if ($writeArchiveLedger)
							{	
								// Write to Ledger
								$a_6 = array($row['Date'], 'Last Archive', ' ', ' ', ' ');								
								$fields = array_merge($a_6, $a_2, $a_7);
								fputcsv($csvArchiveLedger,$fields);
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

				
			// Before iterating through moveTrans, select sort_order and IDX, then sort by sort_order, then put the IDXs in an array that I can iterate over
			// Here is that code:
			
			// Create list from array
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
			
			$sql = "SELECT * 
					FROM `transactions` 
					WHERE `IDX` IN (" . $mtList . ")
					ORDER BY `sort_order` ASC, `IDX` ASC";
			$result1 = $conn->query($sql); 
			?><script>
				console.log("mtList: <?php echo $mtList; ?>");
				console.log("result1 type: <?php echo gettype($result1); ?>");
				console.log(" ");
			</script><?php	
						
			if($result1)
			{
				if ($result1->num_rows > 0)
				{
					?><script>
						console.log("DEBUG 1 result1 type: <?php echo gettype($result1); ?>");		
					</script><?php
					
					
					while($row = $result1->fetch_assoc())
					{
						
						?>			
						<script>
							console.log("Archiving <?php echo $row['IDX'];?>");
						</script>		
						<?php 
						// Start a TRANSACTION that includes selecting, inserting, calculating, and deleting
						$conn->begin_transaction();
						
						// Add the row to the `trans_archive` table
						$sql = sprintf("INSERT INTO trans_archive(`IDX`, `trans_IDX`, `sort_order`, `Date`, `Description`, `Amount`, `Account`, `Category`) VALUES (NULL, '" . $row['IDX'] . "', '" . $row['sort_order'] . "', '" . $row['Date'] . "', '" . $row['Description'] . "', '" . $row['Amount'] . "', '" . $row['Account'] . "', '" . $row['Category'] . "')");
						$result = $conn->query($sql);
						?>			
						<script>
							console.log("$sql = <?php echo $sql;?>");
							console.log("$result = <?php echo $result;?>");
						</script>		
						<?php
						
						// Calculate new account totals
						
							// Process account
							$lineAcct = $row['Account'];
							$$lineAcct = process_account(floatval($row['Amount']), $row['Account'],  $$lineAcct);
							
							// Process category
							$lineCat =  $row['Category'];
							$lineAmnt = floatval($row['Amount']);
							$lineDate = $row['Date'];
							?>			
							<script>
								console.log("$lineDate line 517 = <?php echo $lineDate;?>");
							</script>		
							<?php
							
							if (strcmp($lineCat,"Income") == 0)
							{
								// Determine which Income Distribution column to use
								$thisDistDate = $distDates[0];
								$thisDistIdx = 0;
								for ($idx = 1; $idx < $numDistDates; $idx++)
								{
									if (strtotime($lineDate) > strtotime($distDates[$idx]))
									{
										$thisDistDate = $distDates[$idx];
										$thisDistIdx = $idx;
									}				
													
								}
								
								// Iterate through all Funds and Categories and add Income according to incPct
								for ($idx = 0; $idx < (count($categoryNames)); $idx++)
								{
									if (${$categoryNames[$idx]}->incPct[$thisDistIdx] > 0 )  // If distribution percentage is greater than 0 for this category
									{
										${$categoryNames[$idx]}->bal = ${$categoryNames[$idx]}->bal + ($lineAmnt * ${$categoryNames[$idx]}->incPct[$thisDistIdx]);
									}
									
								}
								for ($idx = 0; $idx < ($fundsIdx); $idx++)
								{
									if (${$fundNames[$idx]}->incPct[$thisDistIdx] > 0 )  // If distribution percentage is greater than 0 for this category
									{
										${$fundNames[$idx]}->bal = ${$fundNames[$idx]}->bal + ($lineAmnt * ${$fundNames[$idx]}->incPct[$thisDistIdx]);
									}
								}
							
							}
							elseif (strcmp($lineCat,"Dividend") == 0)
							{
								/* 
								Algorithm: Iterate through all categories and Funds twice. The first time, find the sum of all of them.
								The second time, give each fund or category this ammount: dividend * (category balance / sum)
								*/
								
								// Find sum
								$sum = 0;
								for ($idx = 0; $idx < (count($categoryNames)); $idx++)
								{
									$sum += ${$categoryNames[$idx]}->bal + ${$categoryNames[$idx]}->bal;
								}
								for ($idx = 0; $idx < ($fundsIdx); $idx++)
								{
									$sum += ${$fundNames[$idx]}->bal + ${$fundNames[$idx]}->bal;
								}
								
								// Pay Dividends
								for ($idx = 0; $idx < (count($categoryNames)); $idx++)
								{
									${$categoryNames[$idx]}->bal = ${$categoryNames[$idx]}->bal + ($lineAmnt * ((${$categoryNames[$idx]}->bal + ${$categoryNames[$idx]}->bal) / $sum ));
								}
								for ($idx = 0; $idx < ($fundsIdx); $idx++)
								{
									${$fundNames[$idx]}->bal = ${$fundNames[$idx]}->bal + ($lineAmnt * ((${$fundNames[$idx]}->bal + ${$fundNames[$idx]}->bal) / $sum ));
								}
							
							}		
							elseif (isset($lineCat))
							{
								// Distinguish between Category and Fund
								if (strcmp($$lineCat->type,"category") == 0)  
								{
									
									// ************************************************************************
									// type CATEGORY 
									
									// If this is a main category, add to it.
									if (strcmp($$lineCat->catParent,"Main") == 0)
									{
										$$lineCat->bal = $$lineCat->bal + $lineAmnt;
										
										if ($verbose_trans > 2)
										{
											?><script>
												console.log("Category: <?php echo $lineCat . " = $" . $$lineCat->bal;?>");
											</script><?php 
										}
									}								
									else  // If it is not a main category, find the main category and add to that.		
									{
										$temp1 = $$lineCat->catParent;				
										$cntr = 0;
										
										if ($verbose_trans > 2)
										{
											?><script>
												console.log("lineCat: <?php echo $lineCat . "; temp1: " . $temp1 . " and counter = " . $cntr;?>");
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
										$$temp1->bal = $$temp1->bal + $lineAmnt;
										
										if ($verbose_trans > 2)
										{
											?><script>
												console.log("Category: <?php echo $lineCat . " = $" . $$lineCat->bal;?>");
												console.log("Category: <?php echo $temp1 . " = $" . $$temp1->bal;?>");
											</script><?php 
										}
									}
								}
								elseif (strcmp($$lineCat->type,"fund") == 0)
								{
									// ************************************************************************
									// type Fund

									$$lineCat->bal = $$lineCat->bal + $lineAmnt;
									if ($verbose_trans > 2)
									{
										?><script>
											console.log("Fund: <?php echo $lineCat . " = $" . $$lineCat->bal;?>");
										</script><?php
									}
									
								}
								else
								{
									?><script>
										console.log("trans.php warning 01: type <?php echo $$lineCat->type;?> is not recognized.");
									</script><?php 
								}
								
							}
							elseif (strcmp($lineCat,"Transfer") == 0)
							{
								// When Category is Transfer, Description should be an account name. 
								// Money is transferred from the Account column to the Description column.
								// (Added to Account, subtracted from Description)
								// The account in the Account column is handled above in the ACCOUNTS section.
								// Need to handle the transfer into the Description account here.
								
								$lineDesc = $$row['Description'];	
								
								$lineDesc->bal = $lineDesc->bal - $lineAmnt;
							}		
							else
							{
								?><script>
									console.log("Category <?php echo $lineCat;?> is not declared.");
								</script><?php 
							}
							
							?>			
							<script>
								console.log("$lineDate line 686 = <?php echo $lineDate;?>");
							</script>		
							<?php
						
						// Write to Ledger
						if ($writeArchiveLedger)
						{	
							$a_6 = array($row['Date'], $row['Description'], $row['Amount'], $row['Account'], $row['Category']);								
							$a_8 = array();
							
							
							// Iterate through fields of $row
							foreach($row as $column => $value)
							{
								if (strcmp($column,"IDX")==0)
								{
									$thisIDX = $value;
									?><script>console.log("$thisIDX set to <?php echo $thisIDX; ?>");</script><?php
								}
								elseif (strcmp($column,"Date")==0)
								{ // Do nothing								
									?><script>console.log("Date line");</script><?php
								}
								elseif (isset($$column))
								{									
									array_push($a_8, $$column->bal, $$column->newBal);
									?><script>console.log("<?php echo $$column->bal;?> and <?php echo $$column->newBal;?> added to $a_8");</script><?php									
								}
								else
								{
									?><script>console.log("Column <?php echo $column; ?> is not declared in names.csv");</script><?php
								}								
							}
							
							$fields = array_merge($a_6, $a_2, $a_8);
							fputcsv($csvArchiveLedger,$fields);
							
						}
						
						// Delete the row from the `transactions` table
						if ($result) // only delete if the previous INSERT was successful
						{
							$sql = "DELETE FROM `transactions` WHERE `transactions`.`IDX` = " . $row['IDX'];
							$result = $conn->query($sql);
							?>			
							<script>
								console.log("$sql = <?php echo $sql;?>");
								console.log("$result = <?php echo $result;?>");
								console.log("");
							</script>		
							<?php
							
							// COMMIT the TRANSACTION
							$conn->commit();
							
						}
						else
						{
							?>			
							<script>
								console.log("DELETE not attempted because INSERT failed");
							</script>		
							<?php
						}
						
						?>			
							<script>
								console.log("$lineDate line 718 = <?php echo $lineDate;?>");
							</script>		
							<?php
							
					}
					
				}
				else
				{
					echo "0 results<br>";
				}
			}
			else
			{
				?><script>
					console.log("SQL: <?php echo $sql; ?>");
					console.log("Result: <?php echo $result; ?>");
					console.log(" ");
				</script><?php		
			}
			
			echo "I am not dead yet 6.";
			
			
			
			?>			
			<script>
				console.log("$lineDate line 745 = <?php echo $lineDate;?>");
			</script>		
			<?php
			
			// ADD CODE ends here - remove these comments when addition is complete
			
			//foreach($moveTrans as $mT) // mT will be an IDX in the transactions database
			
			
			// DEBUG
			if ($debug_add)
			{
				?>
				<script>			
					console.log("DEBUG 6 Cash->bal type: <?php echo gettype($Cash->bal); ?>");					
					console.log(" ");
				</script>		
				<?php
			}
			
			// Insert new totals into cash_balances table
			$acctBals = array($Cash->bal, ${$accountNames[1]}->bal, ${$accountNames[2]}->bal, $Chase->bal, 
				${$accountNames[4]}->bal, $AmEx->bal, $Southwest->bal);
				
			$fundBals = array($Bank->bal, $Spending->bal, $Charity->bal, $Marann->bal, 
				${$fundNames[0]}->bal, $Emergency->bal);
				
			?>			
			<script>
				console.log("$lineDate line 774 = <?php echo $lineDate;?>");
				console.log("$lineDate type = <?php echo gettype($lineDate);?>");
			</script>		
			<?php
			
			//Example SQL from awardspace: INSERT INTO `cash_balances` (`IDX`, `Date`, `Cash`, `GE Savings`, `GE Checking`, `Chase`, `Chase Check`, `AmEx`, `Southwest`, `Bank`, `Spending`, `Living`, `Charity`, `Marann`, `Ten Percent`, `Emergency`, `Gifts`, `eMV`, `TEAM`, `Wedding`, `Mwed`, `Roth`, `Card`, `Work Exp`, `Home Imp`, `Car Exp`, `next_is_pay`) VALUES ('37', '2016-07-26', '308', '45.42', '15131.89', '-1080.41', '2009.33', '-232.56', '-2074.72', '-1907.34757445308', '37.8168102418991', '1400.6945036149', '4757.09992255776', '-1892.89640309769', '224.558773459563', '4866.72605482126', '-1.26231694926848', '3.99466891317851', '516.521162411845', '-1199.98539255198', '1872.06065931686', '8508.03704308808', '-3530.07857294888', '56.2066478659906', '428.279531246463', '-33.4745341824647', '0');
			// Example: 				   INSERT INTO `cash_balances` (`IDX`, `Date`, `Cash`, `GE Savings`, `GE Checking`, `Chase`, `Chase Check`, `AmEx`, `Southwest`, `Bank`, `Spending`, `Living`, `Charity`, `Marann`, `Ten Percent`, `Emergency`, `Gifts`, `eMV`, `TEAM`, `Wedding`, `Mwed`, `Roth`, `Card`, `Work Exp`, `Home Imp`, `Car Exp`, `next_is_pay`) VALUES ('43', '2016-08-12', '199', '45.42', '15633.89', '-1367.67', '339.6', '-108.2', '-1857.32', '-1959.8960624471', '-42.6543445427256', '1392.27562046535', '4185.03579986491', '-1630.68002945686', '248.159513716939', '4875.1330090556', '-1.26449752008799', '4.00156944514932', '514.413419285265', '-1155.63263061799', '1875.2945229265', '8388.8624214711', '-4329.75655518008', '56.3037412118976', '475.445015437994', '-10.319529761429', '1');
			
			$sql = "INSERT INTO cash_balances(
			`IDX`, 
			`Date`, 
			`Cash`, 
			`GE Savings`, 
			`GE Checking`, 
			`Chase`, 
			`Chase Check`, 
			`AmEx`, 
			`Southwest`, 
			`Bank`, 
			`Spending`, 
			`Living`, 
			`Charity`, 
			`Marann`, 
			`Ten Percent`, 
			`Emergency`, 
			`Gifts`, 
			`eMV`, 
			`TEAM`, 
			`Wedding`, 
			`Mwed`, 
			`Roth`, 
			`Card`, 
			`Work Exp`, 
			`Home Imp`, 
			`Car Exp`) 
			VALUES 
			(NULL,'" .
			date("Y-m-d") . "'," .
			$acctBals[0] . "," .
			$acctBals[1] . "," .
			$acctBals[2] . "," .
			$acctBals[3] . "," .
			$acctBals[4] . "," .
			$acctBals[5] . "," .
			$acctBals[6] . "," .
			$Bank->bal . "," .
			$Spending->bal . "," .
			$Living->bal . "," .
			$Charity->bal . "," .
			$Marann->bal . "," .
			${$fundNames[0]}->bal . "," .
			$Emergency->bal . "," .
			$Gifts->bal . "," .
			$eMV->bal . "," .
			$TEAM->bal . "," .
			$Wedding->bal . "," .
			$Mwed->bal . "," .
			$Roth->bal . "," .
			$Card->bal . "," .
			${$fundNames[13]}->bal . "," .
			${$fundNames[14]}->bal . "," .
			${$fundNames[15]}->bal . ")";
			
			
			// $result = mysql_query($sql);
			$result = $conn->query($sql);
			
			
			// Debug
			echo $sql;
			?>			
			<script>
				console.log("Result #846: <?php echo $result; ?>");
			</script>		
			<?php
			
			// Close connection
			$conn->close();
			
		
		}
		elseif (isset($_POST['delete']))
		{
			// Delete Selected
		
			foreach($moveTrans as $mT)
			{
				?>			
				<script>
					console.log("Deleting <?php echo $mT;?>");
				</script>		
				<?php 
				$sql = "DELETE FROM `transactions` WHERE `transactions`.`IDX` = " . $mT;
				$result = mysql_query($sql) or die(mysql_error());
				?>			
				<script>
					console.log("$sql = <?php echo $sql;?>");
					console.log("$result = <?php echo $result;?>");
					console.log("");
				</script>		
				<?php
			}
		
		}
		
	}
	
	// Get transactions from the database
	if (isset($_POST["sortBy"]) and isset($_POST["sortOrder"]))
	{
		$sql = "SELECT * FROM `transactions` ORDER BY `" . $_POST["sortBy"] . "`, `IDX` " . $_POST["sortOrder"];
	}
	else
	{
		$sql = "SELECT * FROM `transactions` ORDER BY `sort_order` ASC";
	}
	
	$result = mysql_query($sql) or die(mysql_error());
	?>
	
	<script>
		console.log("$sql = <?php echo $sql;?>");
		console.log("$result = <?php echo $result;?>");
		console.log("");
	</script>	
	
	<!-- Display the transactions in the database -->
	<form action="add.php" method="post">
	<table>
		<tr>
			<td>Date</td>
			<td>Description</td>
			<td>Amount ($)</td>
			<td>Account</td>
			<td>Category</td>				
		</tr>
		
		<?php 
			while ($row = mysql_fetch_array( $result ))
			{ 
			?>
				<tr>
					<td><?php echo $row['Date']; ?></td>
					<td><?php echo $row['Description']; ?></td>
					<td><?php echo $row['Amount']; ?></td>
					<td><?php echo $row['Account']; ?></td>
					<td><?php echo $row['Category']; ?></td>
					<!-- Create checkbox. When the box is checked, the value IDX is included in the moveTrans[] array that is posted -->
					<td><input type='checkbox' 
							   name='moveTrans[]' 
							   value="<?php echo $row['IDX']; ?>" 
							   <?php if ($selectAll)
							   {
									echo " checked";
							   } ?>							   
							   ></td>
				</tr>
			
			<?php
			}
		
		
		/*
		while ($row = mysql_fetch_array( $result ))
		{
			echo "<tr>";
			echo "<td>" . $row['Date'] . "</td>";
			echo "<td>" . $row['Description'] . "</td>";
			echo "<td>" . $row['Amount'] . "</td>";
			echo "<td>" . $row['Account'] . "</td>";
			echo "<td>" . $row['Category'] . "</td>";
			echo "<td><input type='checkbox' name='moveTrans[]' value='" . $row['IDX'] . "'></td>"; 
			echo "</tr>";
		}
		*/ ?>
		<tr>
			<td><input type="submit" value="Move multiple" name="moveMult" formaction="sort.php"></td>
			<td><input type="submit" value="Move 2nd above 1st" name="move2a1" formaction="sort.php"></td>
			<td><input type="submit" value="Switch places" name="swap" formaction="sort.php"></td>
			<td><input type="submit" value="Archive Selected" name="archive"></td>
			<td><input type="submit" value="Delete Selected" name="delete"></td>
			<td><?php
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
		</tr>
		<tr>
			<td><input type="submit" value="Update" name="update" formaction="update.php"></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		
	</table>	
	</form>
	
	<!--- Create links to balance -->
	<form action="balance.html" method="post">
		<input type="submit" value="Cash" name="account">
		<input type="submit" value="Chase" name="account">
		<input type="submit" value="GE Checking" name="account">
		<input type="submit" value="Chase Check" name="account">
		<input type="submit" value="AmEx" name="account">
		<input type="submit" value="Southwest" name="account">
	</form>
	
</body>
</html>
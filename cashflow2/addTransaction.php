<!DOCTYPE html>
<html>
<body>

<?php

	include 'classes.php';
	include 'databaseConnect.php';	
	include 'cash2_functions.php';
	$versions->addTransaction = 1;
	
	$sortOrder = $_GET["insertBefore"];
	echo $sortOrder;
	
	date_default_timezone_set('America/Chicago');

?>

<div>
	<form action="submit_transaction.php" method="post">
		<div>
			<label for="sortOrder">Sort Order:</label>
			<input name="sortOrder"  type="number" value="<?php echo $sortOrder;?>">
		</div>
		<div>
			<label for="transDate">Date:</label>
			<input name="transDate"  type="date" value="<?php echo date("Y-m-d");?>">
		</div>
			<label for="transDesc">Description:</label>
			<input name="transDesc" type="text">
		<div>
			<label for="transAmnt">Amount: $</label>
			<input name="transAmnt" type="number" step=".01">
		</div>
		<div>
			<label for="transAcct">Account:</label>
			<select name="transAcct">
				<option value="Cash">Cash</option>
				<option value="Chase">Chase</option>
				<option value="GE Checking">GE Checking</option>
				<option value="Chase Check">Chase Check</option>
				<option value="AmEx">AmEx</option>
				<option value="Southwest">Southwest</option>
				<option value="GE Savings">GE Savings</option>		
				<option value="Income">Income</option>
				<option value="Dividend">Dividend</option>
			</select>
		</div>
		<div>
			<label for="transCat">Category:</label>
			<select name="transCat">
				<option value="Grocery">Grocery</option>
				<option value="Gasoline">Gasoline</option>
				<option value="Spending Other">Spending Other</option>
				<option value="Living">Living</option>
				<option value="Income">Income</option>
				<option value="Card">Card</option>
				<option value="Home Imp">Home Imp</option>
				<option value="Charity">Charity</option>
				<option value="Bills">Bills</option>
				<option value="Loans">Loans</option>
				<option value="Bank Other">Bank Other</option>
				<option value="Dividend">Dividend</option>
				<option value="Roth">Roth</option>
				<option value="Ten Percent">Ten Percent</option>
				<option value="Emergency">Emergency</option>
				<option value="Gifts">Gifts</option>
				<option value="Work Exp">Work Exp</option>
				<option value="Car Exp">Car Exp</option>
				<option value="Marann">Marann</option>
				<option value="Dining">Dining</option>
				<option value="TEAM">TEAM</option>
				<option value="Wedding">Wedding</option>
				<option value="Mwed">Mwed</option>
			</select>
		</div>
		<div>
			<label for="cleared">Cleared:</label>
			<input type='checkbox' name="cleared" checked>
		</div>
		<div>
			<label for="check_amount">Check Amount:</label>
			<input name="check_amount" type='checkbox'>
		</div>
		<div>
			<input type="submit" value="Submit">
		</div>
	</form>
</div>

</body>
</html>
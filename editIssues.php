<html>
<head>
<style>

p.dbg {
	background-color:#D6D6D6;	
	display: inline;
}

</style>
</head>
<body>

Ver 09 - June 27, 2014<br>
<br>

<?php

if ($_POST["editIssues"])
{
	//echo "Edit Issues for " . $_POST["editIssues"] . ".";
	$uname = $_POST["editIssues"];
}
elseif ($_POST["delIssUname"])
{
	$uname = $_POST["delIssUname"];
	//echo "Edit Issues for " . $uname . ".";
	
}
else
{
	echo "name not received";
	echo "<br>";
	$uname = "test";
}
echo "Edit Issues for " . $uname . ".";
echo "<br>";

// Connect to database
mysql_connect("fdb6.awardspace.net", "1544017_users", "townHall1") or die(mysql_error());
mysql_select_db("1544017_users") or die(mysql_error());

// If a new issue was passed to this page, save it
$newIssue = $_POST["issName"];
$newState = $_POST["issState"];

if ($newIssue)
{
	$insertStr = sprintf("INSERT INTO pubissues(user, issue, statement)
	VALUES('%s','%s','%s');", $uname, $newIssue, $newState);
	
	//echo "<br>";
	//echo $insertStr;
	//echo "<br>";
	
	$result2 = mysql_query($insertStr) or die(mysql_error());	
}
else
{
	//echo "<br>";
	//echo "no new string to save";
	//echo "<br>";
}

// If a delete button was pressed, delete that issue
$rowNum = $_POST["Delete"];

if ($rowNum)
{
	$query4 = sprintf("SELECT * FROM `pubissues` WHERE `index`=%d LIMIT 0, 30 ", $rowNum);
	$result = mysql_query($query4);
	
	?>
	<br> <p class="dbg"><b>Deleting Record:</b><br>
	<?php
		while($row = mysql_fetch_array( $result ))
		{
			echo $row['index'];
			echo " - " .$row['issue'] . "<br>";
		}
		$deleteStr = sprintf("DELETE FROM pubissues WHERE `index`=%d", $rowNum);
		echo "<br>";
		$result = mysql_query($deleteStr) or die(mysql_error());
		echo "Deleted record " .$rowNum;
}


// Get all issues
$query6 = sprintf("SELECT * FROM `pubissues` WHERE `user`=\"%s\"", $uname);  // This works
$result = mysql_query($query6);


?>
</p><br>

<table> <!-- Table 1: 1 Row, 2 Columns -->
	<tr><td>
		<table>  <!-- Table 4: Row per Item, 1Column1 (Nested in [1,1] of Table 1) -->
		<tr><td>
			Placeholder for list of all issues.
		</td></tr>
		</table>
	</td>
	<td>
		<table> <!-- Table 2: 2 Rows, 1 Column (Nested in [1,2] of Table 1) -->
			<tr><td>
				<table cellpadding="10" cellspacing="10"> <!-- Table 3: Row per Item, 3 Columns (Nested in [1,1] of Table 2) -->
					<tr><td><b>Current Issues</b></td></tr>
						<?php					
							while($row = mysql_fetch_array( $result ))
							{			
								//result						
								echo "<tr>";
								echo "<th>" .$row['issue'] . "</th>";
								echo "<td>" .$row['statement'] . "</td>";						
								echo "<td>"
								?>
								<form method="post">
									<input type="hidden" name="delIssUname" value="<?php echo $uname; ?>">
									<button type="submit" formmethod="post" name="Delete" value="
									<?php								
										//echo "delete";
										echo $row['index'];
									?>
									">Delete</button>
							</form>
								<?php
						
								echo "</tr>";
								
												
							}
						?>				
					
				</table> <!-- End of Table 3 -->
			
			<tr><td> <!-- 2nd row in Table 2 -->
				<table> <!-- Table 5: Organize form. 3 Rows, 2 Column (Nested in [2,1] of Table 2) -->
					<tr><td>
					<form method="post">
						<label>New Issue:</label></td>
						<td><input type="text" name="issName" size="24"></td></tr>
						<tr><td><label for="issState">Your Stance:</label></td>
						<td><textarea rows="6" cols="50" name="issState" placeholder="Type your opinion here!"></textarea></td></tr>
						<tr><td></td><td><input type="submit" value="Submit"></td></tr>
						<tr><td><input type="hidden" name="editIssues" value="<?php echo $uname; ?>"></td></tr>
					</form>
					
				</table>				
			</td></tr>
			<tr><td></td><td>
				<form action="profile.html" method="post">
					<input type="hidden" name="uname" value="<?php echo $uname; ?>">
					<input type="submit" value="Return to Profile">
				</form>				
			</td></tr>
		</table>		
	</td>
	</tr>


</table>


</body>
</html>
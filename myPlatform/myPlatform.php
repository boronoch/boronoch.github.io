<html>
<head>
<link rel="stylesheet" type="text/css" href="mystyle.css">
</head>
<body>

<?php
	
	// Version
	$ver = 5;
	
	
	// Connect to Database (http://www.w3schools.com/php/php_mysql_connect.asp)
	$servername = "fdb6.awardspace.net";
	$username = "1544017_users"; //update
	$password = "townHall1"; //update
	$dbname = "1544017_users"; //update

	$conn = new mysqli($servername, $username, $password, $dbname);

	// Check connection
	if ($conn->connect_error)
	{
		die("Connection failed: " . $conn->connect_error);
	}
	else
	{
		//echo "Connected successfully. Version = " . $ver_dash . "<br>";
	}
	
	
	// For each topic in the database, create a card on the screen, starting with the unanswered ones	
		/* table:username 
		 - username (text)
		 - topicIDX (int)
		 - platformIDX (int)
		 - rating (int, [0,100])
		 - comments (text)
		 - refs (references, text)
		*/
		
	// Select all topics
	$sql = "SELECT * FROM `topics` WHERE 1";
	$result = $conn->query($sql);
	
	// Go through all topics and print the ones that don't have any feedback yet.
	while ($row = $result->fetch_assoc())
	{
		$sql2 = "SELECT * FROM `userPlatform` 
				WHERE `username` LIKE 'bhermsen' 
				AND `topicIDX`=" . $row['IDX'];
		$result2 = $conn->query($sql2);
		
		if (!($result2))
		{
			$sql3 = "SELECT * FROM `platforms` WHERE `topic`=" . $row['IDX'];
			$result3 = $conn->query($sql3);
			
			?>
			<form action="insertPlatform.php" method="post">
			<table class="platforms">
				<tr>
					<th>Category</th>
					<th>Topic</th>
					<th>Platform</th>
					<th>Comments</th>
					<th>References</th>
				</tr>
				<tr>
					<td><?php echo $row['category'];?></td>
					<td><?php echo $row['topic'];?></td>
					<td><select name="platformIDX">
						<?php
						while ($row3 = $result3->fetch_assoc())
						{
							?><option value="<?php echo $row3['enum'];?>"><?php echo $row3['platform'];?></option><?php
						}
						?>
					</select></td>
					<td><input type="text" name="comments"></td>
					<td><input type="text" name="references"></td>
					<input type="hidden" name="topicIDX" value="<?php echo $row['IDX']; ?>">
					<input type="hidden" name="username" value="bhermsen">
				</tr>
			</table>
			<input type="submit" value="Submit">
			</form>
		<?php
		}
	}

?>

Need to create "insertPlatform.php" in order for these forms to work.

</body>
</html>
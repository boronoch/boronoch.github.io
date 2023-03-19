<html>
<head>
<link rel="stylesheet" type="text/css" href="mystyle.css">

<!-- Purpose: To add topics and platform enumerations to the database -->
</head>
<body>

<?php

	//Version
	$ver = 2;

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
	
	
	// Tables	
		/* table:topics 
		 - IDX
		 - category
		 - topic		 
		
		table:platforms 
		 - IDX
		 - topic
		 - enum
		 - platform
		*/
		
	?>
		<form action="insertTopic.php" method="post">
		<table class="platforms">
			<tr>
				<td>Issue: <input type="text" name="topic"></td>
			</tr>
			<tr>
				<td>Category: <select name="category">
					<option value="Economy">Economy</option>
					<option value="FiscalPolicy">Fiscal Policy</option>
					<option value="InternationalAffairs">International Affairs</option>
					<option value="SocialIssues">Social Issues</option>
					<option value="Immigration">Immigration</option>
				</select></td>
			</tr>
		</table>
		<table class="platforms">
			<tr>
				<td>Add up to 5 popular platforms for this Issue:</td>
			</tr>
			<tr><td><input type="text" name="platform1"></td></tr>
			<tr><td><input type="text" name="platform2"></td></tr>
			<tr><td><input type="text" name="platform3"></td></tr>
			<tr><td><input type="text" name="platform4"></td></tr>
			<tr><td><input type="text" name="platform5"></td></tr>.
			<tr><td><input type="submit" value="Submit"></td></tr>
		</table>

		</form>
	<?php

?>



</body>
</html>
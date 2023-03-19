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
		echo "Connected successfully. insertPlatform.php Version = " . $ver . "<br>";
	}
	
	// Prepare and Bind SQL Statment
	$stmt = $conn->prepare("INSERT INTO userFreeForm (username, jurisdiction, category, issue, platform, importance, informed, comment, ref)
		VALUES (?,?,?,?,?,?,?,?,?)");
	$stmt->bind_param("sssssiiss", $username, $jurisdiction, $category, $issue, $platform, $importance, $informed, $comment, $ref);
	
	// Read inputs from platForm.php
	$username = $_POST["username"];
	$jurisdiction = $_POST["jurisdiction"];
	$category = $_POST["category"];
	$issue = $_POST["issue"];
	$platform = $_POST["platform"];
	$importance = $_POST["importance"];
	$informed = $_POST["informed"];
	$comment = $_POST["comment"];
	$ref = $_POST["ref"];
		
	// Execute Statement
	$stmt->execute();
	
	echo "New record created successfully";
	
	// Close
	$stmt->close();
	$conn->close();
	
	
?>

<br>
<p>
	<a href="platForm.php">Add another</a>
</p>


</body>
</html>
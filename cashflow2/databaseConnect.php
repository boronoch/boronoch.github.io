<?php 

$versions->databaseConnect = 2;

// Connect to Database (http://www.w3schools.com/php/php_mysql_connect.asp)
$servername = "fdb6.awardspace.net";
$username = "1544017_users";
$password = "townHall1";
$dbname = "1544017_users";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error)
{
	echo "Connection failed.";
	die("Connection failed: " . $conn->connect_error);
}
else
{
	// DEBUG
	echo "Connection successful.<br>";
}
	
?>
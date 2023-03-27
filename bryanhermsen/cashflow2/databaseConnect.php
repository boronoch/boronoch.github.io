<?php 

$versions->databaseConnect = 7;

// Connect to Database (http://www.w3schools.com/php/php_mysql_connect.asp)
//$servername = "localhost:3306";
$servername = "127.0.0.1";
$username = "u344052086_user";
$password = "db101586";
$dbname = "u344052086_db";

//$conn = new mysqli($servername, $username, $password, $dbname);
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error)
{
	echo "Connection failed.<br>";
	echo "Version " . $versions->databaseConnect . "<br>";
	echo "pass = " . $password . "<br>";
	die("Connection failed: " . $conn->connect_error);
}
else
{
	// DEBUG
	echo "Connection successful.<br>";
}
	
?>
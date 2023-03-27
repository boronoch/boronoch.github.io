<?php

	
	// Receive input from newProfile.html
	$uname = $_POST["uname"];
	$passw = $_POST["passw"];
	$firstName = $_POST["firstN"];
	$lastName = $_POST["lastN"];
	$street = $_POST["street"];
	$city = $_POST["city"];
	$state = $_POST["state"];
	$zip = $_POST["zip"];
	
	// Connect to database
	mysql_connect("fdb6.awardspace.net", "1544017_users", "townHall1") or die(mysql_error());
	mysql_select_db("1544017_users") or die(mysql_error());
	
	// Write to the database
	$insertStr = sprintf("INSERT INTO userdata(user, pass, First, Last, Street, City, State, Zip)
	VALUES('%s','%s','%s','%s','%s','%s','%s','%d');", $uname, $passw, $firstName, $lastName, $street, $city, $state, $zip);
	
	$result2 = mysql_query($insertStr) or die(mysql_error());
	

?>
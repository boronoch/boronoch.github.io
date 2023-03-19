<html>
<head>
<link rel="stylesheet" type="text/css" href="mystyle.css">

<!-- Purpose: To add topics and platform enumerations to the database -->
</head>
<body>

<?php

	// Version 
	$ver = 3;

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
		
	// Receive input
	$topic = $_POST["topic"];
	$category = $_POST["category"];
	$platform1 = $_POST["platform1"];
	$platform2 = $_POST["platform2"];
	$platform3 = $_POST["platform3"];
	$platform4 = $_POST["platform4"];
	$platform5 = $_POST["platform5"];

	
	if (($topic) And ($category))
	{
		$sql = "INSERT INTO `topics`(`IDX`, `category`, `topic`) 
				VALUES (NULL,'" . $category . "','" . $topic . "')";
				
		if ($conn->query($sql) === TRUE) {
			$topicIDX = $conn->insert_id;
			echo "New topic added to the database";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
		
		$enumIDX=0;
		
		if ($platform1)
		{
			$enumIDX++;
			
			$sql = "INSERT INTO `platforms`(`IDX`, `topic`, `enum`, `platform`) 
				VALUES (NULL,'" . $topicIDX . "','" . $enumIDX . "','" . $platform1 . "')";
				
			if ($conn->query($sql) === TRUE) {
				?>			
				<script>
					console.log("Added platform 1 as enum <?php echo $enumIDX;?> of topic <?php echo $topicIDX;?>");
				</script>		
				<?php 
			} else {
				echo "Error: " . $sql . "<br>" . $conn->error;
			}
		}
		if ($platform2)
		{
			$enumIDX++;
			
			$sql = "INSERT INTO `platforms`(`IDX`, `topic`, `enum`, `platform`) 
				VALUES (NULL,'" . $topicIDX . "','" . $enumIDX . "','" . $platform2 . "')";
				
			if ($conn->query($sql) === TRUE) {
				?>			
				<script>
					console.log("Added platform 2 as enum <?php echo $enumIDX;?> of topic <?php echo $topicIDX;?>");
				</script>		
				<?php 
			} else {
				echo "Error: " . $sql . "<br>" . $conn->error;
			}
		}
		if ($platform3)
		{
			$enumIDX++;
			
			$sql = "INSERT INTO `platforms`(`IDX`, `topic`, `enum`, `platform`) 
				VALUES (NULL,'" . $topicIDX . "','" . $enumIDX . "','" . $platform3 . "')";
				
			if ($conn->query($sql) === TRUE) {
				?>			
				<script>
					console.log("Added platform 3 as enum <?php echo $enumIDX;?> of topic <?php echo $topicIDX;?>");
				</script>		
				<?php 
			} else {
				echo "Error: " . $sql . "<br>" . $conn->error;
			}
		}
		if ($platform4)
		{
			$enumIDX++;
			
			$sql = "INSERT INTO `platforms`(`IDX`, `topic`, `enum`, `platform`) 
				VALUES (NULL,'" . $topicIDX . "','" . $enumIDX . "','" . $platform4 . "')";
				
			if ($conn->query($sql) === TRUE) {
				?>			
				<script>
					console.log("Added platform 4 as enum <?php echo $enumIDX;?> of topic <?php echo $topicIDX;?>");
				</script>		
				<?php 
			} else {
				echo "Error: " . $sql . "<br>" . $conn->error;
			}
		}
		if ($platform5)
		{
			$enumIDX++;
			
			$sql = "INSERT INTO `platforms`(`IDX`, `topic`, `enum`, `platform`) 
				VALUES (NULL,'" . $topicIDX . "','" . $enumIDX . "','" . $platform5 . "')";
				
			if ($conn->query($sql) === TRUE) {
				?>			
				<script>
					console.log("Added platform 5 as enum <?php echo $enumIDX;?> of topic <?php echo $topicIDX;?>");
				</script>		
				<?php 
			} else {
				echo "Error: " . $sql . "<br>" . $conn->error;
			}
		}
		
		
	}

?>

<form action="myPlatform.php" method="post">
	<input type="submit" value="Return to Issues screen" name="fromInsertTopic">
</form>



</body>
</html>
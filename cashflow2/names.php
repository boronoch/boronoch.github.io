<?php

/*  names.php
	Initialize variables for accounts, categories, funds, and goals
*/

	$versions->names = 14;
	echo "Starting names.php version " . $versions->names . ".<br>";

	// Query table
	$sql = sprintf("SELECT * FROM `accounts_categories` WHERE 1");
	$result = $conn->query($sql);

	// Initialize Accounts and Categories
	if($result)
	{
		if ($result->num_rows > 0)
		{
			$accounts_idx = 0;
			$categories_idx = 0;
			while($row = $result->fetch_assoc())
			{
				// DEBUG
				/*print_r($row); 
				echo "<br>Start echo: ";
				echo $row["IDX"] . " ";
				echo $row["Name"] . " ";
				echo $row["Type"];
				echo "<br>";*/
				
			
				if (strcmp($row["Type"], "Account")==0)
				{
					$accounts_list[$accounts_idx] = $row["Name"];
					$Accounts[$row["Name"]] = new account();
					$Accounts[$row["Name"]]->name = $row["Name"];

					$accounts_idx += 1;
				}
				elseif (strcmp($row["Type"], "Category")==0)
				{
					$categories_list[$categories_idx] = $row["Name"];
					$Categories[$row["Name"]] = new category();
					$Categories[$row["Name"]]->name = $row["Name"];
					
					$categories_idx += 1;
				}
				else
				{
					echo "Did not recognize type '" . $row["Type"] . "'<br>";
				}
			}
		}
		else
		{
			echo "0 results from accounts_categories<br>";
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
	
	// Query table
	$sql = sprintf("SELECT * FROM `funds_goals` WHERE 1");
	$result = $conn->query($sql);

	// Initialize Funds and Goals
	if($result)
	{
		if ($result->num_rows > 0)
		{
			$funds_idx = 0;
			$goals_idx = 0;
			while($row = $result->fetch_assoc())
			{
				if (strcmp($row["Type"], "Fund")==0)
				{
					$funds_list[$funds_idx] = $row["Name"];
					
					$Funds[$row["Name"]] = new fund();					
					$Funds[$row["Name"]]->name = $row["Name"];
					$Funds[$row["Name"]]->active = $row["Active"];
					$Funds[$row["Name"]]->category = $row["Category"];
					$Funds[$row["Name"]]->target = $row["Goal"];
					
					$funds_idx += 1;
					
				}
				elseif (strcmp($row["Type"], "Goal")==0)
				{
					$goals_list[$goals_idx] = $row["Name"];
					$Goals[$row["Name"]]->name = $row["Name"];
					$Goals[$row["Name"]]->target = $row["Goal"];
					
					$goals_idx += 1;
				}
				else
				{
					echo "Did not recognize type '" . $row["Type"] . "'";
				}
			}
		}
		else
		{
			echo "0 results from funds_goals<br>";
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
	
	// DEBUG
	/*
		$allVars = get_defined_vars();
		echo "<br><br>All Variables at the end of names.php:<br><br><pre>";
		print_r($allVars);
		echo "</pre>"; */
		echo "<p>Finished names.php</p>"
	
?>
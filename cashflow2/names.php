<?php

/*  names.php
	Initialize variables for accounts, categories, funds, and goals
*/

	$versions->names = 9;
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
				print_r($row); 
				echo "<br>Start echo: ";
				echo $row["IDX"] . " ";
				echo $row["Name"] . " ";
				echo $row["Type"];
				echo "<br>";
				
			
				if (strcmp($row["Type"], "Account")==0)
				{
					$accounts_list[$accounts_idx] = $row["Name"];
					$Accounts[$accounts_idx] = new account();
					$Accounts[$accounts_idx]->name = $row["Name"];

					$accounts_idx += 1;
				}
				elseif (strcmp($row["Type"], "Category")==0)
				{
					$categories_list[$categories_idx] = $row["Name"];
					
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
					
					$Funds[$funds_idx] = new fund();					
					$Funds[$funds_idx]->name = $row["Name"];
					$Funds[$funds_idx]->active = $row["Active"];
					$Funds[$funds_idx]->category = $row["Category"];
					$Funds[$funds_idx]->target = $row["Goal"];
					
					$funds_idx += 1;
					
				}
				elseif (strcmp($row["Type"], "Goal")==0)
				{
					$goals_list[$goals_idx] = $row["Name"];
					
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
		$allVars = get_defined_vars();
		echo "<br><br>All Variables:<br><br><pre>";
		print_r($allVars);
		echo "</pre>";
		echo "<p>Finished names.php</p>"
	
?>
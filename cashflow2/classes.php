<?php

	class account
	{
		public $name = null;
		public $balance = 0;
		public $dateBalanced = "1986-01-07";
	}
	
	class category
	{
		public $name;
		public $balance = 0;
	}
	
	class distribution
	{
		public $Bank = 0;
		public $Spending = 0;
		public $Charity = 0;
		
	}
	
	class fund
	{
		public $name = null;
		public $active = true;
		public $balance = 0;
		public $category = null;
		public $target = 0;
	}
	
	
	class goal
	{
		public $name;
		public $active = true;
		public $target = 0;
		public $balance = 0;
		public $priority = 99;
		public $category = "Goals";
	}
	
	class transaction
	{
		public $sortOrder = 0;
		public $date;
		public $description;
		public $amount = 0;
		public $account;
		public $category;		
	}

	class version
	{
		public $dashboard4;
		public $names1;
		public $databaseConnect;
		public $classes = 6;
		public $functions;
	}
	
	// Version
	$versions = new version();
	
	$versions->classes = 7;
?>
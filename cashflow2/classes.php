<?php

	class fund
	{
		public $name = null;
		public $active = true;
		public $balance = 0;
		public $category = null;
		public $target = 0;
	}
	
	class distribution
	{
		public $Bank = 0;
		public $Spending = 0;
		public $Charity = 0;
		
	}
	
	class account
	{
		public $name = null;
		public $balance = 0;
		public $dateBalanced = "1986-01-07";
	}
	
	class version
	{
		public $dashboard4 = 1;
		public $names = 1;
		public $databaseConnect = 1;
		public $classes = 1;
	}

	// Version
	$versions = new version();
	
	$versions->classes = 2;
	print_r($versions);
?>
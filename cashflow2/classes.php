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
		public $date;
		public $Bank = 0;
		public $Spending = 0;
		public $Charity = 0;
		public $TenPercent = 0;
		public $Marann = 0;
		public $Wedding = 0;
		public $HomeImp = 0;
		public $Living = 0;
		public $CarExp = 0;
		public $Roth = 0;
		public $Gifts = 0;
		public $Emergency = 0;
		public $NewCar = 0;
				
		
	}
	
	class filter
	{
		public $startDate;
		public $endDate;
		public $startSort;
		public $endSort;
		public $account;
		public $category;		
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
		public $balance = 0;
		public $distribution = 0;
		public $target = 0;
		public $priority = 99;
		public $category = "Goals";
	}
	
	class transaction
	{
		public $IDX;
		public $sortOrder = 0;
		public $date;
		public $description;
		public $amount = 0;
		public $account;
		public $category;		
	}

	class version
	{
		public $addTransaction;
		public $archive_confirm;
		public $archive_select;
		public $archive_submit;
		public $classes = 8;
		public $dashboard4;
		public $databaseConnect;
		public $display_transactions;
		public $names;
		public $submit_transaction;
		public $functions;
		
	}
	
	// Version
	$versions = new version();
	
	$versions->classes = 10;
?>
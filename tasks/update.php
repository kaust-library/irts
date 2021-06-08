<?php
	ini_set('display_errors', 1);
	
	set_include_path('../');
	
	//include core configuration and common function files
	include_once 'include.php';
	
	//Create task summary to send
	$taskSummary = '';
	
	$totalChanged = 0;
	
	$updates = array("");
	
	if(isset($_GET['update']))
	{
		$updates = array($_GET["update"]);
		unset($_GET["update"]);
	}
	
	foreach($updates as $update)
	{	
		set_time_limit(0);
		
		//Record count variable
		$recordTypeCounts = array('all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'unchanged'=>0);	
		
		include 'updates/'.$update.'.php';
		
		if(function_exists($update))
		{
			$results = $update($recordTypeCounts);
			
			$totalChanged += $results['changedCount'];
			
			$taskSummary .= PHP_EOL.$results['summary'];
		}
	}

	if($totalChanged !== 0)
	{
		//Settings for task report email
		$to = IR_EMAIL;
		$subject = "Results of Update Tasks";
		
		//Complete task message to send
		$taskSummary = 'Update Report:'.PHP_EOL.$taskSummary;

		$headers = "From: " .IR_EMAIL. "\r\n";

		//Send
		mail($to,$subject,$taskSummary,$headers);
	}
?>
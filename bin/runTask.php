<?php
	ini_set('display_errors', 1);
	ini_set('max_execution_time', 60);

	set_include_path('../');

	//include core configuration and common function files
	include_once 'include.php';

	if(isset($_GET['task']))
	{
		$task = $_GET['task'];

		$summary = '';

		$flaggedForReview = 0;

		$totalChanged = 0;

		if(isset($_GET['process']))
		{
			//Accept a list of comma separated processes
			if(strpos($_GET["process"], ',')!==FALSE)
			{
				$processes = explode(',',$_GET["process"]);
			}
			else
			{
				$processes = array($_GET["process"]);
			}
		}
			
		foreach($processes as $process)
		{
			$report = '';

			$errors = array();

			//Record count variable
			$recordTypeCounts = array('all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'unchanged'=>0, 'skipped' =>0);

			if($task === 'update')
			{
				include 'updates/'.$process.'.php';
			}
			else
			{
				include 'tasks/'.$process.'.php';
			}

			if(function_exists($process))
			{
				$results = $process($report, $errors, $recordTypeCounts);

				$totalChanged += $results['changedCount'];

				$summary .= PHP_EOL.$results['summary'];
			}

			set_time_limit(0);
		}

		if($totalChanged !== 0)
		{
			//Settings for task report email
			$to = IR_EMAIL;
			$subject = "Results of $task task";

			//Complete task message to send
			$summary = ucfirst($task).' Report:'.PHP_EOL.$summary;

			$headers = "From: " .IR_EMAIL. "\r\n";

			//Send
			mail($to,$subject,$summary,$headers);
		}
	}
	else
	{
		echo 'Submit task name, either "harvest", "update" or "export"';
	}
?>

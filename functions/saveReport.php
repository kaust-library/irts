<?php
	//Define function to save copy of a process report and summary
	function saveReport($process, $report, $recordTypeCounts, $errors)
	{
		global $irts;
		
		$summary = $process.':'.PHP_EOL;
		
		foreach($recordTypeCounts as $type => $count)
		{
			$summary .= ' - '.$count.' '.$type.' items'.PHP_EOL;
		}
		
		$summary .= ' - Error count: '.count($errors).PHP_EOL;
		
		foreach($errors as $error)
		{
			if(isset($error['type']))
			{
				$report .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
			}
			else
			{
				$report .= ' - '.print_r($error, TRUE).PHP_EOL;
			}
		}
		
		$report .= PHP_EOL.$summary;
		
		if($recordTypeCounts['all']-$recordTypeCounts['unchanged']!==0||count($errors)!==0)
		{		
			//Log process summary
			insert($irts, 'messages', array('process', 'type', 'message'), array($process, 'summary', $summary));
			
			//Log full process report
			insert($irts, 'messages', array('process', 'type', 'message'), array($process, 'report', $report));
		}
		
		return $summary;
	}

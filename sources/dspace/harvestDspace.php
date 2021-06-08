<?php
	//Define function to harvest repository metadata via DSpace REST API
	function harvestDspace($source)
	{
		global $irts, $newInProcess, $errors;

		$dSpaceAuthHeader = loginToDSpaceRESTAPI();

		$report = '';

		$errors = array();

		//Record count variable
		$recordTypeCounts = array('all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'unchanged'=>0,'skipped'=>0);
		
		//New and modified items harvest
		$result = $irts->query("SELECT * FROM `metadata` m WHERE `source` LIKE 'repository' AND `field` = 'dc.internalItemId' AND `deleted` IS NULL AND idInSource IN (SELECT DISTINCT idInSource FROM `metadata` WHERE `source` = 'repository' AND added > (SELECT timestamp FROM messages WHERE process = 'dspace' ORDER BY timestamp DESC LIMIT 2,1) AND `deleted` IS NULL)");
		
		//New items harvest
		//$result = $irts->query("SELECT * FROM `metadata` m WHERE `source` LIKE 'repository' AND `field` = 'dc.internalItemId' AND `deleted` IS NULL AND NOT EXISTS (SELECT * FROM `metadata` WHERE `source` = 'dspace' AND `idInSource` = m.`value` AND `deleted` IS NULL)");
		
		//Complete reharvest based on OAI-PMH results
		//$result = $irts->query("SELECT * FROM `metadata` m WHERE `source` LIKE 'repository' AND `field` = 'dc.internalItemId' AND `deleted` IS NULL");
		
		//Complete reharvest based on dspace REST API results
		//$result = $irts->query("SELECT DISTINCT idInSource as value FROM `metadata` m WHERE `source` LIKE 'dspace' AND `deleted` IS NULL");

		while($row = $result->fetch_assoc())
		{
			$recordTypeCounts['all']++;

			$recordType = '';

			$idInSource = $row['value'];

			$json = getItemMetadataFromDSpaceRESTAPI($idInSource, $dSpaceAuthHeader);

			if(is_string($json))
			{
				$recordType = processDspaceRecord($idInSource, $json, $report);
				$recordTypeCounts[$recordType]++;
				set_time_limit(0);
			}
			else
			{
				$report .= $idInSource.') Skipped: '.print_r($json, TRUE).PHP_EOL;
				$recordTypeCounts['skipped']++;
				sleep(20);
			}
			sleep(1);
		}

		$summary = saveReport($source, $report, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$summary);
	}

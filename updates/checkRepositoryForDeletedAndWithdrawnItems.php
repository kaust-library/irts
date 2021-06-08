<?php
	//Define function to mark IRTS metadata for deleted and withdrawn items as deleted
	function checkRepositoryForDeletedAndWithdrawnItems($report, $errors, $recordTypeCounts)
	{
		global $irts;

		if(isset($_GET['handle']))
		{
			$results = array($_GET['handle']);
		}
		else
		{
			$results = getValues($irts, "SELECT DISTINCT idInSource FROM metadata WHERE source = 'repository' AND deleted IS NULL", array('idInSource'));
		}

		foreach($results as $handle)
		{
			$recordTypeCounts['all']++;

			$oai = simplexml_load_file(REPOSITORY_OAI_URL.'verb=GetRecord&metadataPrefix=oai_dc&identifier='.REPOSITORY_OAI_ID_PREFIX.$handle);

			if(isset($oai->error))
			{
				if((string)$oai->error[0]['code'] === 'idDoesNotExist')
				{
					update($irts, 'sourceData', array('deleted'), array(date("Y-m-d H:i:s"), $handle), 'idInSource', ' AND deleted IS NULL');

					update($irts, 'metadata', array('deleted'), array(date("Y-m-d H:i:s"), $handle), 'idInSource', ' AND deleted IS NULL');

					$report .= " - metadata for $handle marked as deleted.".PHP_EOL;

					$recordTypeCounts['deleted']++;
				}
			}
			else
			{
				$recordTypeCounts['unchanged']++;
			}
		}

		$summary = saveReport(__FUNCTION__, $report, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$summary);
	}

<?php
	//Define function to harvest repository metadata via OAI-PMH
	function harvestRepository($source)
	{
		global $irts, $newInProcess, $errors;

		$fromDate = '';

		$sourceReport = '';

		//Record count variable
		$recordTypeCounts = array('all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'unchanged'=>0);

		$errors = array();

		//Old versions of versioned items are no longer accessible via OAI-PMH, but they are via REST API, based on either item id or handle.

		//bypass option can be used to avoid the normal harvest and instead harvest metadata based on a handle sent as a parameter, or a list of handles retrieved from the database based on a custom query
		if(!isset($_GET['bypass']))
		{
			//pass empty fromDate parameter to run full reharvest
			if(!isset($_GET['fromDate']))
			{
				$fromDate = getValues($irts, "SELECT value FROM metadata WHERE source LIKE '$source' AND field LIKE 'dspace.date.modified' ORDER BY value DESC LIMIT 1", array('value'), 'singleValue');
			}
			else
			{
				$fromDate = $_GET['fromDate'];
			}
			$sourceReport .= 'From Date: '.$fromDate.PHP_EOL;

			$token = '';

			if(empty($fromDate))
			{
				$oai = simplexml_load_file(REPOSITORY_OAI_URL.'verb=ListIdentifiers&metadataPrefix=xoai');
			}
			else
			{
				$oai = simplexml_load_file(REPOSITORY_OAI_URL.'verb=ListIdentifiers&metadataPrefix=xoai&from='.$fromDate);
			}

			if(isset($oai->ListIdentifiers->resumptionToken))
			{
				$total = $oai->ListIdentifiers->resumptionToken['completeListSize'];
			}
			else
			{
				$total = count($oai->ListIdentifiers->header);
			}
			unset($oai);

			while($recordTypeCounts['all']<$total)
			{
				if(!empty($token))
				{
					$oai = simplexml_load_file(REPOSITORY_OAI_URL.'verb=ListRecords&resumptionToken='.$token.'');
				}
				elseif(empty($fromDate))
				{
					$oai = simplexml_load_file(REPOSITORY_OAI_URL.'verb=ListRecords&metadataPrefix=xoai');
				}
				elseif(!empty($fromDate))
				{
					$oai = simplexml_load_file(REPOSITORY_OAI_URL.'verb=ListRecords&metadataPrefix=xoai&from='.$fromDate);
				}
				else
				{
					break;
				}

				if(!empty($oai))
				{
					$sourceReport .= 'Total: '.$total.PHP_EOL;
					if(isset($oai->ListRecords))
					{
						foreach($oai->ListRecords->record as $item)
						{
							$recordTypeCounts['all']++;
							if($recordTypeCounts['all']===$total+1)
							{
								break 2;
							}

							$sourceReport .= 'Number:'.$recordTypeCounts['all'].PHP_EOL;

							//process item
							$recordType = processRepositoryRecord($item, $sourceReport);

							$sourceReport .= ' - '.$recordType.PHP_EOL;

							$recordTypeCounts[$recordType]++;

							flush();
							set_time_limit(0);
						}
					}
				}
				$token = $oai->ListRecords->resumptionToken;
			}
		}
		else
		{
			if(isset($_GET['handle']))
			{
				$handles = array($_GET['handle']);
			}
			else
			{
				$handles = getValues($irts, "SELECT idInSource FROM `metadata`
					WHERE `source` LIKE 'repository' AND `field` LIKE 'dc.rights.embargodate'
			    AND idInSource NOT IN (
			        SELECT idInSource FROM `metadata` WHERE `source` LIKE 'repository'
			        AND `field` LIKE 'dspace.bitstream.embargo'
			        AND deleted IS NULL
			    )
			    AND idInSource IN (
			        SELECT idInSource FROM `metadata` WHERE `source` LIKE 'repository'
			        AND `field` LIKE 'dc.type'
			        AND value LIKE 'Article'
			        AND deleted IS NULL
			    )
			    AND idInSource IN (
			        SELECT idInSource FROM `metadata` WHERE `source` LIKE 'repository'
			        AND `field` LIKE 'dspace.bitstream.url'
			        AND deleted IS NULL
			    )
			    AND deleted IS NULL
			    ORDER BY `metadata`.`value` ASC", array('idInSource'), 'arrayOfValues');
			}

			foreach($handles as $handle)
			{
				$recordTypeCounts['all']++;

				$oai = simplexml_load_file(REPOSITORY_OAI_URL.'verb=GetRecord&metadataPrefix=xoai&identifier='.REPOSITORY_OAI_ID_PREFIX.$handle);

				foreach($oai->GetRecord->record as $item)
				{
					$sourceReport .= 'Number:'.$recordTypeCounts['all'].PHP_EOL;

					//process item
					$recordType = processRepositoryRecord($item, $sourceReport);

					$sourceReport .= ' - '.$recordType.PHP_EOL;

					$recordTypeCounts[$recordType]++;

					flush();
					set_time_limit(0);
				}
			}
		}

		$sourceSummary = saveReport($source, $sourceReport, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$sourceSummary);
	}

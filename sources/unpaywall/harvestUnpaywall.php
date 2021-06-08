<?php
/*

**** This function harvests and processes item records retrieved from the Unpaywall API (https://unpaywall.org/products/api) for existing repository records with DOIs but no uploaded bitstreams. Documentation of the unpaywall data format is at: https://unpaywall.org/data-format

** Parameters :
	No parameters required


** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 November 2019 - 10:30 AM

*/

//--------------------------------------------------------------------------------------------

function harvestUnpaywall($source)
{
	global $irts, $errors;

	$report = '';
	$errors = array();

	//Record count variable
	$recordTypeCounts = array('all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'unchanged'=>0,'added - green status'=>0,'added - gold status'=>0,'added - hybrid status'=>0,'ignored - bronze status'=>0,'ignored - closed status'=>0,'ignored - figshare result only'=>0);

	// get all the dois that don't have a file in DSpace
	$dois = getValues($irts, "SELECT DISTINCT value FROM `metadata` where `source` = 'repository' and `field` = 'dc.identifier.doi' and `deleted` IS NULL
		AND `idInSource` NOT IN (
			 SELECT `idInSource` FROM `metadata` where `field` = 'dspace.bitstream.url' and `deleted` IS NULL )", array('value'), 'arrayOfValues');

	$recentlyChecked = getValues($irts, "SELECT `idInSource` FROM metadata WHERE `source` = 'irts' AND `field` = 'irts.check.unpaywall' AND deleted IS NULL AND `added` >= '".THREE_MONTHS_AGO."'", array('idInSource'), 'arrayOfValues');

	// for each doi get the unpaywall result
	foreach ($dois as $doi)
	{
		// get the irts record ids to be checked
		$idsInIRTS = getValues($irts, setSourceMetadataQuery('irts', NULL, NULL, 'dc.identifier.doi', $doi), array('idInSource'), 'arrayOfValues');

		$check = FALSE;

		// if it has been checked in the last 3 months we will not check it again (we will also check items that have never been checked)
		foreach ($idsInIRTS as $idInIRTS )
		{
			if(!in_array($idInIRTS, $recentlyChecked))
			{
					$check = TRUE;
			}
		}

		if($check)
		{
			$completed = FALSE;

			$recordTypeCounts['all']++;

			$responseJson = queryUnpaywall($doi);

			if(is_string($responseJson))
			{
				// convert it to array
				$response = json_decode($responseJson, TRUE);

				$recordType = saveSourceData($report, 'unpaywall', $doi, $responseJson, 'JSON');

				$recordTypeCounts[$recordType]++;

				if($recordType !== 'unchanged')
				{
					$report .= $doi.PHP_EOL;

					$report .= ' - '.$recordType;

					// if there is no useful result mark the item as checked
					if(in_array($response['oa_status'], array('closed','bronze')))
					{
						$action = 'ignored - '.$response['oa_status'].' status';

						$completed = TRUE;
					}
					elseif(!empty($response['best_oa_location']))
					{
						if(strpos($response['best_oa_location']['url'], 'https://figshare.com')!==FALSE)
						{
							$action = 'ignored - figshare result only';

							$completed = TRUE;
						}
						else
						{
							$action = 'added - '.$response['oa_status'].' status';

							// mark unpaywall check as inProcess
							foreach ($idsInIRTS as $idInIRTS )
							{
								$result = saveValue('irts', $idInIRTS, 'irts.check.unpaywall', 1, 'inProcess' , NULL);
							}
						}
					}
					$report .= ' - '.$action;

					$recordTypeCounts[$action]++;
				}
				else
				{
					//If there was no change, just update the date on the existing entry so that it will not be rechecked for the next 3 months
					foreach ($idsInIRTS as $idInIRTS )
					{
						$existingEntries = getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, NULL, 'irts.check.unpaywall'), array('rowID','value'));

						foreach($existingEntries as $existingEntry)
						{
							update($irts, 'metadata', array("added"), array(date("Y-m-d H:i:s"), $existingEntry['rowID']), 'rowID');
						}
					}
				}

				if($completed)
				{
					// mark unpaywall check as completed
					foreach ($idsInIRTS as $idInIRTS )
					{
						$result = saveValue('irts', $idInIRTS, 'irts.check.unpaywall', 1, 'completed' , NULL);

						if($result['status']==='unchanged')
						{
							update($irts, 'metadata', array("added"), array(date("Y-m-d H:i:s"), $result['rowID']), 'rowID');
						}
					}
				}
			}
		}
	}

	$summary = saveReport($source, $report, $recordTypeCounts, $errors);

	return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$summary);
}

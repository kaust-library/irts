<?php
	//Define function to harvest EuropePMC metadata records
	function harvestEuropePMC($source)
	{
		global $irts, $newInProcess, $errors;

		$sourceReport = '';

		$errors = array();

		//Record count variable
		$recordTypeCounts = array('all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'unchanged'=>0);
	
		//Daily harvest just checks most recent 50 items
		$results = queryEuropePMC('affiliation',INSTITUTION_ABBREVIATION);			

		foreach($results['resultList']['result'] as $result)
		{
			$recordTypeCounts['all']++;
			
			$sourceReport .= 'id: '.$result['id'].PHP_EOL;
			
			$recordType = processEuropePMCRecord($result);

			$sourceReport .= ' - '.$recordType.PHP_EOL;

			$recordTypeCounts[$recordType]++;

			$doi = '';
			if(isset($result['doi']))
			{
				$doi = $result['doi'];
			}
			
			$title = '';
			if(isset($result['title']))
			{
				$title = $result['title'];
			}

			//Check for doi by title
			/* if(empty($doi))
			{
				$doi = retrieveCrossrefDOIByCitation($title, $autnames, '');
			} */
			
			if(!empty($doi))
			{
				$existingRecords = checkForExistingRecords($doi, 'dc.identifier.doi', $sourceReport);					
			}
			else
			{
				//$existingRecords = checkForExistingRecords($title, 'dc.title', $sourceReport);
				
				$existingRecords = 'not empty for now, need to do better title check and crossref check by citation...';
				
				//what about checking by URL, does scopus offer a publisher URL field?
			}
			
			if(empty($existingRecords))
			{
				//Check for existing IRTS entry
				$irtsID = 'europePMC_'.$result['id'];
				
				$query = "SELECT `idInSource` FROM `metadata` WHERE source LIKE 'irts' AND (idInSource LIKE '$irtsID' OR (field = 'dc.identifier.doi' AND value = '$doi'))";

				$check = $irts->query($query);

				if($check->num_rows === 0)
				{
					$type = 'Article';
					
					$field = 'dc.type';

					$rowID = mapTransformSave('irts', $irtsID, '', $field, '', 1, $type, NULL);

					$field = 'status';

					$rowID = mapTransformSave('irts', $irtsID, '', $field, '', 1, 'inProcess', NULL);

					if(!empty($doi))
					{
						$field = 'dc.identifier.doi';

						$rowID = mapTransformSave('irts', $irtsID, '', $field, '', 1, $doi, NULL);
						
						if(identifyRegistrationAgencyForDOI($doi, $sourceReport)==='crossref')
						{
							$recordTypeCounts['all']++;

							$sourceData = retrieveCrossrefMetadataByDOI($doi, $sourceReport);

							if(!empty($sourceData))
							{
								$recordType = processCrossrefRecord($sourceData, $sourceReport);
							}
						}
					}
					$newInProcess++;
				}
			}
			else
			{
				print_r($existingRecords);
			}
			
			ob_flush();
			set_time_limit(0);
		}
		
		$sourceSummary = saveReport($source, $sourceReport, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$sourceSummary);
	}

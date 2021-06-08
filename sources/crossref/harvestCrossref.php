<?php
	//Define function to harvest Crossref metadata records
	function harvestCrossref($source)
	{
		global $irts, $newInProcess, $errors;
		
		$report = '';

		$errors = array();

		//Record count variable
		$recordTypeCounts = array('all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'unchanged'=>0,'DOIs with unknown status or needing metadata reharvest'=>0,'new DOIs from any source'=>0,'DOIs retrieved by querying faculty ORCID'=>0,'DOIs retrieved by querying affiliation'=>0,'DOIs retrieved by querying funder'=>0);

		$dois = array();		
		
		if(isset($_GET['reprocess']))
		{
			$dois['Reprocess already harvested Crossref metadata'] = getValues($irts, "SELECT * FROM `metadata` WHERE `source` LIKE 'crossref' AND `field` LIKE 'dc.identifier.doi' AND deleted IS NULL", array('value'));
		}
		elseif(isset($_GET['reharvest']))
		{
			$dois['all Crossref DOIs'] = getValues($irts, "SELECT DISTINCT value FROM `metadata` 
				WHERE `source` = 'crossref'
				AND `field` LIKE 'dc.identifier.doi' 
				AND deleted IS NULL", array('value'));
		}
		else
		{
			$iterationsOfCrossrefUpdate = 1;

			//Change this to decide how many of the available updates to run
			while($iterationsOfCrossrefUpdate <= 5)
			{
				if($iterationsOfCrossrefUpdate === 1)
				{
					//Check for DOIs that may have had an unknown status when first harvested or which have not been checked for updated Crossref metadata in the last year
					$result = $irts->query("SELECT DISTINCT LOWER(value) doi FROM `metadata` 
						WHERE `field` LIKE 'dc.identifier.doi' 
						AND deleted IS NULL
						AND (
								value IN ( 
									SELECT idInSource FROM `metadata` 
									WHERE source = 'doi'
									AND field = 'doi.agency.id'
									AND `value` LIKE 'crossref' 
									AND deleted IS NULL
								)
							OR
								value IN ( 
									SELECT idInSource FROM `metadata` 
									WHERE source = 'doi'
									AND field = 'doi.status'
									AND `value` LIKE 'unknown' 
									AND deleted IS NULL
								)
						)
						AND value NOT IN ( 
							SELECT idInSource FROM `sourceData` 
							WHERE `source` LIKE 'crossref' 
							AND `added` > '".ONE_YEAR_AGO."' 
							AND deleted IS NULL
						)
						AND value NOT IN ( 
							SELECT REPLACE(`idInSource`,'doi_','') FROM `metadata` 
							WHERE `source` LIKE 'irts' 
							AND `field` LIKE 'irts.check.crossref'
							AND `added` > '".ONE_YEAR_AGO."' 
							AND deleted IS NULL
						)");

					if($result->num_rows!==0)
					{
						while($row = $result->fetch_assoc())
						{
							$dois['DOI with unknown status or needing metadata reharvest'][] = $row['doi'];
							
							$recordTypeCounts['DOIs with unknown status or needing metadata reharvest']++;
						}
					}
				}
				elseif($iterationsOfCrossrefUpdate === 2)
				{
					//Check for new DOIs in metadata from any source
					$result = $irts->query("SELECT DISTINCT LOWER(value) doi FROM `metadata` WHERE field = 'dc.identifier.doi' AND LOWER(value) NOT IN (SELECT LOWER(`idInSource`) FROM metadata WHERE `source` = 'doi')");

					if($result->num_rows!==0)
					{
						while($row = $result->fetch_assoc())
						{
							$dois['new DOI from any source'][] = $row['doi'];
							$recordTypeCounts['new DOIs from any source']++;
						}
					}
				}
				elseif($iterationsOfCrossrefUpdate === 3)
				{
					//Harvest for direct query of Crossref by ORCIDs

					//Get list of active faculty ORCIDs to check
					$persons = getValues($irts, "SELECT DISTINCT m.`idInSource` FROM `metadata` m
					WHERE `source` LIKE 'local'
					AND `field` LIKE 'local.person.title'
					AND `value` LIKE '%Professor%'
					AND parentRowID NOT IN (
						SELECT parentRowID FROM metadata
						WHERE source='local'
						AND idInSource = m.idInSource
						AND field = 'local.date.end')", array('idInSource'));

					foreach($persons as $idInSource)
					{
						$orcid = getValues($irts, "SELECT `value` FROM `metadata` WHERE `source` LIKE 'local' AND `idInSource` LIKE '$idInSource' AND `field` LIKE 'dc.identifier.orcid'", array('value'), 'singleValue');

						if(!empty($orcid))
						{
							$report .= 'ORCID: '.$orcid.PHP_EOL;

							$url = CROSSREF_API.'works?filter=orcid:'.$orcid.',from-created-date:'.ONE_WEEK_AGO.'&select=DOI&mailto='.urlencode(IR_EMAIL);

							//$url = CROSSREF_API.'works?filter=orcid:'.$orcid.',from-created-date:2019-01-01&select=DOI&mailto='.urlencode(IR_EMAIL);

							$report .= 'URL:'.$url.PHP_EOL;

							$results = file_get_contents($url);
							$results = json_decode($results);

							$total = $results->{'message'}->{'total-results'};
							$report .= 'Total:'.$total.PHP_EOL;

							if($total > 0)
							{
								$report .= ' - '.$total.' items for ORCID: '.$orcid.PHP_EOL;

								foreach($results->{'message'}->{'items'} as $result)
								{
									$recordTypeCounts['DOIs retrieved by querying faculty ORCID']++;
									
									$doi = strtolower($result->{'DOI'});

									$check = $irts->query("SELECT source, idInSource FROM `metadata` WHERE source = 'crossref' AND field = 'dc.identifier.doi' AND value = '$doi'");

									//if no existing Crossref record in table
									if($check->num_rows === 0)
									{
										$dois['DOI retrieved by querying faculty ORCID'][]=$doi;
									}
								}
							}
						}
						ob_flush();
					}
				}
				elseif($iterationsOfCrossrefUpdate === 4)
				{
					//Harvest for direct query of Crossref by affiliation
					$url = CROSSREF_API.'works?rows=0&query.affiliation='.INSTITUTION_ABBREVIATION.'&query.affiliation='.INSTITUTION_CITY.'&sort=published&order=desc&filter=from-created-date:'.ONE_WEEK_AGO.'&mailto='.urlencode(IR_EMAIL);
					
					//$url = CROSSREF_API.'works?rows=0&query.affiliation='.INSTITUTION_ABBREVIATION.'&query.affiliation='.INSTITUTION_CITY.'&sort=published&order=desc&select=DOI&mailto='.urlencode(IR_EMAIL);

					$report .= 'URL:'.$url.PHP_EOL;

					$results = file_get_contents($url);
					$results = json_decode($results);

					$total = $results->{'message'}->{'total-results'};
					$report .= ' - '.$total.' items for affiliations with: '.INSTITUTION_ABBREVIATION.' or '.INSTITUTION_CITY.PHP_EOL;

					while($recordTypeCounts['DOIs retrieved by querying affiliation']<$total)
					{
						$url = CROSSREF_API.'works?rows=50&query.affiliation='.INSTITUTION_ABBREVIATION.'&query.affiliation='.INSTITUTION_CITY.'&sort=published&order=desc&filter=from-created-date:'.ONE_WEEK_AGO.'&mailto='.urlencode(IR_EMAIL);
						
						//$url = CROSSREF_API.'works?offset='.$recordTypeCounts['DOIs retrieved by querying affiliation'].'&rows=50&query.affiliation='.INSTITUTION_ABBREVIATION.'&query.affiliation='.INSTITUTION_CITY.'&sort=published&order=desc&select=DOI&mailto='.urlencode(IR_EMAIL);

						$report .= 'URL:'.$url.PHP_EOL;

						$results = file_get_contents($url);
						$results = json_decode($results);

						foreach($results->{'message'}->{'items'} as $result)
						{
							$recordTypeCounts['DOIs retrieved by querying affiliation']++;
							
							$doi = strtolower($result->{'DOI'});

							$check = $irts->query("SELECT source, idInSource FROM `metadata` WHERE source = 'crossref' AND field = 'dc.identifier.doi' AND value = '$doi'");

							//if no existing Crossref record in table
							if($check->num_rows === 0)
							{
								$dois['DOI retrieved by querying affiliation'][]=$doi;
							}
						}
					}
				}
				elseif($iterationsOfCrossrefUpdate === 5)
				{
					//Harvest for direct query of Crossref by funder
					$url = CROSSREF_API.'funders?query='.INSTITUTION_ABBREVIATION.'&mailto='.urlencode(IR_EMAIL);
					
					$report .= 'URL:'.$url.PHP_EOL;

					$results = file_get_contents($url);
					$results = json_decode($results);

					$total = $results->{'message'}->{'total-results'};
					
					$report .= ' - '.$total.' Funder IDs for '.INSTITUTION_ABBREVIATION.PHP_EOL;

					foreach($results->{'message'}->{'items'} as $result)
					{
						$funderID = $result->{'id'};
						
						$url = CROSSREF_API.'works?rows=0&filter=funder:'.$funderID.',from-created-date:'.ONE_WEEK_AGO.'&sort=published&order=desc&mailto='.urlencode(IR_EMAIL);
						
						//$url = CROSSREF_API.'works?rows=0&filter=funder:'.$funderID.'&sort=published&order=desc&select=DOI&mailto='.urlencode(IR_EMAIL);
					
						$report .= 'URL:'.$url.PHP_EOL;

						$results = file_get_contents($url);
						$results = json_decode($results);

						$total = $results->{'message'}->{'total-results'};
						
						$report .= ' - '.$total.' items for Funder ID: '.$funderID.PHP_EOL;
						
						$funderLinkedDOIs = 0;

						while($funderLinkedDOIs<$total)
						{
							$url = CROSSREF_API.'works?offset='.$funderLinkedDOIs.'&rows=50&filter=funder:'.$funderID.',from-created-date:'.ONE_WEEK_AGO.'&sort=published&order=desc&mailto='.urlencode(IR_EMAIL);
							
							//$url = CROSSREF_API.'works?offset='.$funderLinkedDOIs.'&rows=50&filter=funder:'.$funderID.'&sort=published&order=desc&select=DOI&mailto='.urlencode(IR_EMAIL);

							$report .= 'URL:'.$url.PHP_EOL;

							$results = file_get_contents($url);
							$results = json_decode($results);

							foreach($results->{'message'}->{'items'} as $result)
							{
								$funderLinkedDOIs++;
								$recordTypeCounts['DOIs retrieved by querying funder']++;
								
								$doi = strtolower($result->{'DOI'});

								$check = $irts->query("SELECT source, idInSource FROM `metadata` WHERE source = 'crossref' AND field = 'dc.identifier.doi' AND value = '$doi'");

								//if no existing Crossref record in table
								if($check->num_rows === 0)
								{
									$dois['DOI retrieved by querying funder'][]=$doi;
								}
							}
						}
					}
				}
				$iterationsOfCrossrefUpdate++;
			}
		}

		//The key will be a label used to help processors know why this DOI was harvested
		foreach($dois as $key => $values)
		{
			$values = array_unique($values);
			
			foreach($values as $doi)
			{
				$report .= 'DOI: '.$doi.PHP_EOL;

				if(identifyRegistrationAgencyForDOI($doi, $report)==='crossref')
				{
					$recordTypeCounts['all']++;

					if(isset($_GET['reprocess']))
					{
						$sourceData = json_decode(getValues($irts, "SELECT sourceData FROM `sourceData` WHERE source = 'crossref' AND idInSource = '$doi' AND format = 'JSON' AND deleted IS NULL", array('sourceData'), 'singleValue'), TRUE);
					}
					else
					{
						$sourceData = retrieveCrossrefMetadataByDOI($doi, $report);
					}

					if(!empty($sourceData))
					{
						$recordType = processCrossrefRecord($sourceData, $report);
						
						$report .= ' - '.$recordType.PHP_EOL;

						$recordTypeCounts[$recordType]++;

						$existingRecords = checkForExistingRecords($doi, 'dc.identifier.doi', $report);

						if(empty($existingRecords))
						{
							$irtsID = 'crossref_'.$doi;
							
							$query = "SELECT `idInSource` FROM `metadata` WHERE source LIKE 'irts' AND (idInSource LIKE '$irtsID' OR (field = 'dc.identifier.doi' AND value = '$doi'))";

							$check = $irts->query($query);

							if($check->num_rows === 0)
							{
								// add the label to the record 
								$field = 'irts.label';
								
								$rowID = mapTransformSave('irts', $irtsID, '', $field, '', 1, $key, NULL);
								
								$field = 'dc.type';

								$type = getValues($irts, setSourceMetadataQuery('crossref', $doi, NULL, $field), array('value'), 'singleValue');

								$rowID = mapTransformSave('irts', $irtsID, '', $field, '', 1, $type, NULL);

								$field = 'status';

								$rowID = mapTransformSave('irts', $irtsID, '', $field, '', 1, 'inProcess', NULL);

								$field = 'dc.identifier.doi';

								$rowID = mapTransformSave('irts', $irtsID, '', $field, '', 1, $doi, NULL);

								$newInProcess++;
							}
						}
					}
				}
				
				if($key === 'DOI with unknown status or needing metadata reharvest')
				{
					// save a check row 
					$result = saveValue('irts', 'doi_'.$doi, 'irts.check.crossref', 1, 'completed' , NULL);
					
					if($result['status']==='unchanged')
					{
						update($irts, 'metadata', array("added"), array(date("Y-m-d H:i:s"), $result['rowID']), 'rowID');
					}
				}
				sleep(1);
			}
		}

		$sourceSummary = saveReport($source, $report, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$sourceSummary);
	}
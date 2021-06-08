<?php
	//Define function to harvest Scopus metadata records
	function harvestScopus($source)
	{
		global $irts, $newInProcess, $errors, $report;

		$report = '';

		$errors = array();

		//Record count variable
		$recordTypeCounts = array('all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'skipped'=>0,'unchanged'=>0);

		$iterationsOfHarvest = 1;

		$entries = array();

		//Change this to decide how many of the available updates to run
		while($iterationsOfHarvest <= 3)
		{
			if($iterationsOfHarvest === 1)
			{
				//For the daily harvest, we will only check the most recent 200 items
				$xml = queryScopus('affiliation', NULL, $recordTypeCounts['all'], 200);

				//Strip namespaces due to problems in accessing elements with namespaces even with xpath, temporary solution?
				$xml = str_replace('dc:', '', $xml);
				$xml = str_replace('opensearch:', '', $xml);
				$xml = str_replace('prism:', '', $xml);

				$xml = simplexml_load_string($xml);

				if((int)$xml->totalResults !== 0)
				{
					foreach($xml->entry as $item)
					{
						$recordTypeCounts['all']++;

						$eid = '';
						$eid = (string)$item->eid;

						$check = $irts->query("SELECT * FROM `sourceData` WHERE source = 'scopus' AND idInSource LIKE '$eid'");

						//if no existing scopus record in database
						if(mysqli_num_rows($check) === 0)
						{
							//echo $eid.PHP_EOL;

							if(!isset($entries[$eid]))
							{
								$entries[$eid] = $item;
							}
						}
					}
				}
			}
			elseif($iterationsOfHarvest === 2)
			{
				//Check for DOIs harvested from other sources
				$result = $irts->query("SELECT value FROM metadata WHERE source = 'irts'
				AND field = 'dc.identifier.doi'
				AND idInSource IN (
					SELECT idInSource FROM metadata WHERE source = 'irts'
					AND field = 'irts.status'
					AND value IN ('inProcess')
					AND deleted IS NULL)
				AND value NOT IN (
					SELECT value FROM metadata WHERE source = 'scopus'
					AND field = 'dc.identifier.doi'
					AND deleted IS NULL)
				AND deleted IS NULL");

				if($result->num_rows!==0)
				{
					while($row = $result->fetch_assoc())
					{
						$inProcessDOI = $row['value'];

						$xml = queryScopus('doi', $inProcessDOI);

						//Strip namespaces due to problems in accessing elements with namespaces even with xpath, temporary solution?
						$xml = str_replace('dc:', '', $xml);
						$xml = str_replace('opensearch:', '', $xml);
						$xml = str_replace('prism:', '', $xml);

						$xml = simplexml_load_string($xml);

						if((int)$xml->totalResults !== 0)
						{
							foreach($xml->entry as $item)
							{
								$recordTypeCounts['all']++;

								$eid = '';
								$eid = (string)$item->eid;

								$check = $irts->query("SELECT * FROM `sourceData` WHERE source = 'scopus' AND idInSource LIKE '$eid'");

								//if no existing scopus record in database
								if(mysqli_num_rows($check) === 0)
								{
									echo $eid.PHP_EOL;

									if(!isset($entries[$eid]))
									{
										$entries[$eid] = $item;
									}
								}
							}
						}
					}
				}
			}
			elseif($iterationsOfHarvest === 3)
			{
				//Check for EIDs without known DOIs
				$result = $irts->query("SELECT idInSource FROM metadata title WHERE title.source = 'scopus'
					AND title.field = 'dc.title'
					AND title.idInSource NOT IN (
						SELECT idInSource FROM metadata WHERE source = 'scopus'
						AND field = 'dc.identifier.doi'
						AND deleted IS NULL)
					AND NOT EXISTS (
						SELECT idInSource FROM metadata WHERE source = 'irts'
						AND idInSource = CONCAT('scopus_',title.idInSource)
						AND deleted IS NULL)
					AND NOT EXISTS (
						SELECT idInSource FROM metadata WHERE source = 'repository'
						AND field = 'dc.title'
						AND value = title.value
						AND deleted IS NULL)");

				if($result->num_rows!==0)
				{
					while($row = $result->fetch_assoc())
					{
						$eid = $row['idInSource'];

						$xml = queryScopus('eid', $eid);

						//Strip namespaces due to problems in accessing elements with namespaces even with xpath, temporary solution?
						$xml = str_replace('dc:', '', $xml);
						$xml = str_replace('opensearch:', '', $xml);
						$xml = str_replace('prism:', '', $xml);

						$xml = simplexml_load_string($xml);

						if((int)$xml->totalResults !== 0)
						{
							foreach($xml->entry as $item)
							{
								$recordTypeCounts['all']++;

								$eid = '';
								$eid = (string)$item->eid;

								if(!isset($entries[$eid]))
								{
									$entries[$eid] = $item;
								}
							}
						}
					}
				}
			}
			$iterationsOfHarvest++;
		}

		foreach($entries as $eid => $item)
		{
			$report .= PHP_EOL.'EID: '.$eid.PHP_EOL;

			$sourceData = retrieveScopusRecord('abstract', 'eid', $eid);

			//print_r($sourceData);

			if(is_string($sourceData))
			{
				if(strpos($sourceData, '<statusCode>RESOURCE_NOT_FOUND</statusCode>') !== FALSE)
				{
					$report .= '- Resource Not Found'.PHP_EOL;

					$recordTypeCounts['skipped']++;
				}
				else
				{
					//Strip namespaces due to problems in accessing elements with namespaces even with xpath, temporary solution?
					$namespaces = array('dc','opensearch','prism','dn','ait','ce','cto','xocs');
					foreach($namespaces as $namespace)
					{
						$sourceData = str_replace('<'.$namespace.':', '<', $sourceData);

						$sourceData = str_replace('</'.$namespace.':', '</', $sourceData);
					}

					$sourceData = simplexml_load_string($sourceData);

					//remove bibliography from saved and processed record
					unset($sourceData->item->bibrecord->tail);

					$recordType = saveSourceData($report, $source, $eid, $sourceData->asXML(), 'XML');

					$report .= ' - '.$recordType.PHP_EOL;

					$recordTypeCounts[$recordType]++;

					$record = processScopusRecord($sourceData);

					//print_r($record);

					$functionReport = saveValues($source, $eid, $record, NULL);

					//$report .= $functionReport;

					//Check if item should have entry added to IRTS
					$doi = '';
					if(isset($item->doi))
					{
						$doi = (string)$item->doi;
					}

					$title = '';
					if(isset($item->title))
					{
						$title = $item->title->asXML();
						$title = preg_replace('!\s+!', ' ', $title);

						$tags = array('<inf>','</inf>','<sup>','</sup>');
						foreach($tags as $tag)
						{
							$title = str_replace($tag, '', $title);
						}
						$title = simplexml_load_string($title)[0];
					}

					if(!empty($doi))
					{
						$existingRecords = checkForExistingRecords($doi, 'dc.identifier.doi', $report);
					}
					else
					{
						//what about checking by URL, does scopus offer a publisher URL field?
						$authors = implode('; ', getValues($irts, setSourceMetadataQuery('scopus', $eid, NULL, "dc.contributor.author"), array('value'), 'arrayOfValues'));

						$doi = retrieveCrossrefDOIByCitation($title, $authors);

						if(!empty($doi))
						{
							$report .= '- DOI: '.$doi.' found for EID: '.$eid.' with title: '.$title.' and authors: '.$authors.PHP_EOL;

							$field = 'dc.identifier.doi';

							$rowID = mapTransformSave('scopus', $eid, '', $field, '', 1, $doi, NULL);

							$existingRecords = checkForExistingRecords($doi, 'dc.identifier.doi', $report);
						}
						else
						{
							$report .= '- No DOI found for EID: '.$eid.' with title: '.$title.' and authors: '.$authors.PHP_EOL;

							$existingRecords = checkForExistingRecords($title, 'dc.title', $report);

							if(!empty($existingRecords))
							{
								$report .= '- Title match for existing records'.PHP_EOL;
							}
							else
							{
								$report .= '- No DOI and no title match for existing records'.PHP_EOL;

								//$existingRecords = array('not confirmed');
							}
						}
					}

					//Check for existing IRTS entry
					$irtsID = 'scopus_'.$eid;
					if(empty($existingRecords))
					{
						$query = "SELECT `idInSource` FROM `metadata` WHERE source LIKE 'irts' AND (idInSource LIKE '$irtsID' OR (field = 'dc.identifier.doi' AND value LIKE '$doi'))";

						$check = $irts->query($query);

						if($check->num_rows === 0)
						{
							$type = '';
							if(isset($item->subtypeDescription))
							{
								$type = (string)$item->subtypeDescription;

								$articleTypes = array('Review','Editorial','Letter','Short Survey','Note');

								if(in_array($type, $articleTypes))
								{
									$type = 'Article';
								}

								if($type === 'Chapter')
								{
									$type = 'Book Chapter';
								}
							}

							$field = 'dc.type';

							$rowID = mapTransformSave('irts', $irtsID, '', $field, '', 1, $type, NULL);

							$field = 'status';

							$rowID = mapTransformSave('irts', $irtsID, '', $field, '', 1, 'inProcess', NULL);

							if(!empty($doi))
							{
								$field = 'dc.identifier.doi';

								$rowID = mapTransformSave('irts', $irtsID, '', $field, '', 1, $doi, NULL);

								if(identifyRegistrationAgencyForDOI($doi, $report)==='crossref')
								{
									$recordTypeCounts['all']++;

									$sourceData = retrieveCrossrefMetadataByDOI($doi, $report);

									if(!empty($sourceData))
									{
										$recordType = processCrossrefRecord($sourceData, $report);
									}
								}
							}
							$newInProcess++;
						}
					}
					else
					{
						$report .= implode(' ; ', $existingRecords).PHP_EOL;
					}
				}
			}
			else
			{
				$recordTypeCounts['skipped']++;

				$report .= '- Unexpected, non-string response from Scopus API'.PHP_EOL;
			}

			flush();
			set_time_limit(0);
		}

		$sourceSummary = saveReport($source, $report, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$sourceSummary);
	}

<?php
	//Define function to harvest DataCite results
	function harvestDataCite($source)
	{
		global $irts, $newInProcess, $errors, $report;

		$report = '';

		$errors = array();

		//Record count variable
		$recordTypeCounts = array('all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'unchanged'=>0,'skipped'=>0,'error'=>0,'ignored based on a relation'=>0,'skipped based on type'=>0,'added to harvest based on relation'=>0);

		$replaceStr = array('DOI:', 'http://dx.doi.org/', 'https://doi.org/', 'http://doi.org/');

		$articleDOIs = array();
		$datasetDOIs = array();

		if(isset($_GET['harvestType']))
		{
			$harvestType = $_GET['harvestType'];

			$report .= 'Harvest type: '.$harvestType.PHP_EOL;

			if(isset($_GET['doi']))
			{
				if($harvestType === 'relations')
				{
					$articleDOIs = array($_GET["doi"]);
				}
				elseif($harvestType === 'metadata')
				{
					$datasetDOIs = array($_GET["doi"]);
				}
			}
			else
			{
				if($harvestType === 'relations')
				{
					$articleDOIs = getValues($irts, "SELECT `value` FROM `metadata`
					WHERE `source` = 'irts'
					AND `field` = 'dc.identifier.doi'
					AND `value` LIKE 'DOI:%'
					AND `deleted` IS NULL", array('value'));
				}
				elseif($harvestType === 'metadata')
				{
					$datasetDOIs = getValues($irts, "SELECT `idInSource` FROM `metadata`
					WHERE `source` = 'datacite'
					AND `deleted` IS NULL", array('idInSource'));
				}
			}
		}
		else
		{
			// get all the article DOIs in the repository
			$articleDOIs = getValues($irts, "SELECT DISTINCT value FROM `metadata`
				WHERE `source` = 'repository'
				AND `field` = 'dc.identifier.doi'
				AND value NOT IN (
					 SELECT REPLACE(`idInSource`,'doi_','') FROM metadata
					 WHERE `source` = 'irts'
					 AND `field` = 'irts.check.datacite'
					 AND deleted IS NULL
					 AND added >= '".THREE_MONTHS_AGO."'
				)
				AND `deleted` IS NULL", array('value'));

			// get related dataset DOIs from IRTS or the repository (entries in IRTS would have been made during initial processing, while entries in the repository may have been added manually)
			$datasetDOIs = getValues($irts, "SELECT `value` FROM `metadata`
				WHERE `source` IN ('irts','repository')
				AND `field` LIKE 'dc.relation.issupplementedby'
				AND `value` LIKE 'DOI:%'
				AND SUBSTRING_INDEX(value,'DOI:',-1) NOT IN (
					SELECT `idInSource` FROM sourceData
					WHERE `source` = 'datacite'
					AND deleted IS NULL
					AND added >= '".THREE_MONTHS_AGO."'
				)
				AND `deleted` IS NULL", array('value'));
		}

		$report .= '- '.count($articleDOIs).' article DOIs to check: '.PHP_EOL;

		foreach($articleDOIs as $key => $articleDOI)
		{
			$report .= $key.') '.$articleDOI.PHP_EOL;

			$relatedDatasets = queryDatacite($articleDOI, 'relations');

			// check if the response is as expected
			if(is_string($relatedDatasets))
			{
				$relatedDatasets = json_decode($relatedDatasets, TRUE);

				if(empty($relatedDatasets['data']))
				{
					//This is a secondary query because at the current time some data repositories (namely Dryad) prefix the related article DOIs in their metadata in a way that the format has to be queried separately
					$response = queryDatacite('"doi:'.$articleDOI.'"', 'relations');

					if(is_string($response))
					{
						$relatedDatasets = json_decode($response, TRUE);
					}
				}

				if(!empty($relatedDatasets['data']))
				{
					$report .= ' - '.count($relatedDatasets['data']).' related DOIs found:'.PHP_EOL;

					foreach ($relatedDatasets['data'] as $relatedDatasetDOI)
					{
						$report .= '  - '.$relatedDatasetDOI['id'].PHP_EOL;

						// add DOI to list of datasetDOIs to harvest metadata for below
						$datasetDOIs[] = $relatedDatasetDOI['id'];
					}
				}

				//Mark check of article DOI as complete
				$result = saveValue('irts', 'doi_'.$articleDOI, 'irts.check.datacite', 1, 'completed' , NULL);

				if($result['status']==='unchanged')
				{
					update($irts, 'metadata', array("added"), array(date("Y-m-d H:i:s"), $result['rowID']), 'rowID');
				}
			}
			else
			{
				$report .= '- error: '.print_r($relatedDatasets, TRUE).PHP_EOL;
			}
			ob_flush();
			flush();
			set_time_limit(0);
		}

		$report .= '- '.count($datasetDOIs).' dataset DOIs to check: '.PHP_EOL;

		$datasetDOIsIterator = new ArrayIterator($datasetDOIs);

		foreach($datasetDOIsIterator as $key => $datasetDOI)
		{
			$recordTypeCounts['all']++;

			// clean DOI
			$datasetDOI = str_replace($replaceStr, '', $datasetDOI);

			$report .= $key.') '.$datasetDOI.PHP_EOL;

			if(!empty($datasetDOI))
			{
				// get the result from the API
				$response = queryDatacite($datasetDOI, 'metadata');

				if(is_string($response))
				{
					$recordType = saveSourceData($report, $source, $datasetDOI, $response, 'JSON');

					$report .= ' - '.$recordType.PHP_EOL;

					$recordTypeCounts[$recordType]++;

					//convert record to local record array structure
					$record = processDataciteRecord($response);

					//if there is data
					if(!empty($record))
					{
						// check if the doi has _d, this is used by Figshare for data files that are part of a dataset record referred to by the base DOI
						if(preg_match('/10.6084\/m9.figshare.c(.*)_d(.*)/', $datasetDOI))
						{
							$doiWithoutD = substr($datasetDOI, 0, strpos($datasetDOI, '_d'));

							// add new relation to the metadata
							$record['dc.relation.ispartof'][]['value'] =  'DOI:'.$doiWithoutD;
						}

						// Always check if the DOI of the Dataset is in the DB with the relation "isidenticalto" with another dataset
						$hasIdentical = getValues($irts, "SELECT `idInSource` FROM `metadata`
							WHERE `source` = 'datacite'
							AND `field` LIKE 'dc.relation.isidenticalto'
							AND `value` LIKE 'DOI:".$datasetDOI."'
							AND `deleted` IS NULL", array('idInSource'), 'arrayOfValues');

						if(!empty($hasIdentical))
						{
							foreach($hasIdentical as $identicalDOI)
							{
								// add return relation to the dataset_A record
								$record['dc.relation.isidenticalto'][]['value'] =  'DOI:'.$identicalDOI;
							}
						}
						
						// get the article DOIs that are associated with this dataset DOI
						$articleDOIs = getValues($irts, "SELECT `value` FROM `metadata`
							WHERE `idInSource` IN (
								SELECT `idInSource` FROM `metadata`
								WHERE source IN ('irts','repository')
								AND field = 'dc.relation.issupplementedby'
								AND `value` LIKE 'DOI:$datasetDOI'
								AND `deleted` IS NULL
							)
							AND field = 'dc.identifier.doi'
							AND `deleted` IS NULL", array('value'), 'arrayOfValues');

						foreach($articleDOIs as $articleDOI)
						{
							$record['dc.relation.issupplementto'][]['value'] = 'DOI:'.$articleDOI;
						}

						if(isset($record['dc.relation.issupplementto']))
						{
							$uniqueIds = array_unique(array_column($record['dc.relation.issupplementto'], 'value'));
							
							$record['dc.relation.issupplementto'] = array();
							
							foreach($uniqueIds as $uniqueId)
							{
								$record['dc.relation.issupplementto'][]['value'] = $uniqueId;
							}
						}

						$functionReport = saveValues($source, $datasetDOI, $record, NULL);

						// check the dataset relations
						$result = handleDataciteRelations($record, $datasetDOI);

						// check the result based on the relation
						if($result['saveA'])
						{
							if($record['dc.type'][0]['value'] !== 'Data File')
							{
								//Add entry to IRTS for processing if it is a new item (this function includes the check for existing records)
								$status = addToProcess($source, $datasetDOI, $datasetDOI, 'dc.identifier.doi', $record['dc.type'][0]['value']);
								$report .= ' - '.$status.PHP_EOL;
								if($status === 'inProcess')
								{
									$newInProcess++;
								}
							}
							else
							{
								$recordTypeCounts['skipped based on type']++;
								$report .= ' - skipped based on type'.PHP_EOL;
							}
						}
						else
						{
							$recordTypeCounts['ignored based on a relation']++;
							$report .= ' - ignored based on a relation'.PHP_EOL;
						}

						if(!empty($result['getB']))
						{
							foreach($result['getB'] as $doiB)
							{
								$datasetDOIsArray = iterator_to_array($datasetDOIsIterator, false);
								if(!in_array($doiB, $datasetDOIsArray))
								{
									$datasetDOIsIterator->append($doiB);
									$recordTypeCounts['added to harvest based on relation']++;
									$report .= ' - '.$doiB.' added to harvest based on relation'.PHP_EOL;
								}
							}
						}
					}
					else
					{
						$recordTypeCounts['skipped']++;
					}
				}
				else
				{
						$recordTypeCounts['error']++;
						$report .= print_r($response, TRUE);
				}
			}
			ob_flush();
			flush();
			set_time_limit(0);
		}
		echo $report;

		$sourceSummary = saveReport($source, $report, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$sourceSummary);
	}

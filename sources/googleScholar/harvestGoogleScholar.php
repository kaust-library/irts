<?php
	//Define function to harvest Google Scholar results
	function harvestGoogleScholar($source)
	{
		global $irts, $newInProcess, $errors;

		$report = '';

		$errors = array();

		$records = array();

		//Record count variable
		$recordTypeCounts = array('all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'unchanged'=>0,'skipped'=>0, 'check for DOI'=>0);

		$max = 20;

		while($recordTypeCounts['all'] < $max)
		{
			$queryResult = queryGoogleScholar($recordTypeCounts['all']);

			$report .= $queryResult['url'].PHP_EOL;

			foreach($queryResult['result']->getElementsByTagName('div') as $div)
			{
				$class = $div->getAttribute('class');

				if($class === 'gs_r gs_or gs_scl')
				{
					$recordTypeCounts['all']++;

					$record = processGoogleScholarRecord($div);

					$report .= print_r($record, TRUE);

					if(!empty($record['googleScholar.cluster.id'][0]['value']))
					{
						$clusterID = $record['googleScholar.cluster.id'][0]['value'];

						$report .= '-- '.$clusterID.PHP_EOL;

						$recordType = saveSourceData($report, $source, $clusterID, $div->ownerDocument->saveHTML($div), 'HTML');

						$report .= ' - '.$recordType.PHP_EOL;

						$recordTypeCounts[$recordType]++;

						$functionReport = saveValues($source, $clusterID, $record, NULL);

						$records[$clusterID] = $record;
					}
				}
				ob_flush();
			}
			$sleepInterval = rand(300,900);
			echo 'Now sleep for '.$sleepInterval.' seconds' . PHP_EOL;
			ob_flush();
			flush();
			set_time_limit(0);
			//Insert pauses into the harvest so that Google Scholar does not mistake us for a machine recursively querying their site
			sleep($sleepInterval);
		}

		// get records that have not been recently rechecked and which do not have a DOI identified
		$clusterIDs = getValues($irts, "SELECT idInSource FROM `metadata` WHERE `source` LIKE 'googleScholar'
			AND `field` LIKE 'dc.title'
			AND deleted IS NULL
			AND idInSource NOT IN (
			    SELECT idInSource  FROM `metadata`
			    WHERE `source` LIKE 'googleScholar'
			    AND `field` LIKE 'dc.identifier.doi'
					AND deleted IS NULL
			)
			AND idInSource NOT IN (
				 SELECT REPLACE(`idInSource`,'googleScholar_','') FROM metadata
				 WHERE `source` = 'irts'
				 AND `field` = 'irts.check.googleScholar'
				 AND deleted IS NULL
				 AND added >= '".THREE_MONTHS_AGO."'
			)", array('idInSource'));

		foreach($clusterIDs as $clusterID)
		{
			$report .= '-- '.$clusterID.PHP_EOL;

			$recordTypeCounts['check for DOI']++;

			$sourceData = getValues($irts, "SELECT sourceData FROM `sourceData`
					WHERE `source` LIKE 'googleScholar'
					AND `idInSource` LIKE '$clusterID'
					AND `deleted` IS NULL", array('sourceData'), 'singleValue');

			if(!empty($sourceData))
			{
				$div = new DOMDocument();
				libxml_use_internal_errors(true);
				$div->loadHTML($sourceData);

				$record = processGoogleScholarRecord($div);

				$report .= print_r($record, TRUE).PHP_EOL;

				$functionReport = saveValues($source, $clusterID, $record, NULL);

				$records[$clusterID] = $record;
			}
		}

		foreach($records as $clusterID => $record)
		{
			echo $clusterID.': '.print_r($record).PHP_EOL;

			if(!empty($record['dc.identifier.doi'][0]['value']))
			{
				$doi = $record['dc.identifier.doi'][0]['value'];

				if(identifyRegistrationAgencyForDOI($doi, $report)==='crossref')
				{
					$recordTypeCounts['all']++;

					$sourceData = retrieveCrossrefMetadataByDOI($doi, $report);

					if(!empty($sourceData))
					{
						$report .= '-- '.$doi.PHP_EOL;

						$recordType = processCrossrefRecord($sourceData, $report);

						$type = getValues($irts, setSourceMetadataQuery('crossref', $doi, NULL, "dc.type"), array('value'), 'singleValue');

						$status = addToProcess('crossref', $doi, $doi, 'dc.identifier.doi', $type);

						if($status === 'inProcess')
						{
							$newInProcess++;
						}

						$recordTypeCounts[$recordType]++;

						$report .= '-- '.$recordType.PHP_EOL;
					}
				}
			}
			elseif(!empty($record['dc.identifier.arxivid'][0]['value']))
			{
				$arxivID = $record['dc.identifier.arxivid'][0]['value'];

				$xml = retrieveArxivMetadata('arxivID', $arxivID);

				foreach($xml->entry as $item)
				{
					$report .= '-- '.$item->id.PHP_EOL;

					$result = processArxivRecord($item);

					$recordType = $result['recordType'];

					$idInSource = $result['idInSource'];

					$status = addToProcess('arxiv', $idInSource, $idInSource, 'dc.identifier.arxivid', 'Preprint');

					if($status === 'inProcess')
					{
						$newInProcess++;
					}

					$recordTypeCounts[$recordType]++;

					$report .= '-- '.$recordType.PHP_EOL;
				}
			}
			//Mark check for DOI as complete
			$result = saveValue('irts', 'googleScholar_'.$clusterID, 'irts.check.googleScholar', 1, 'completed', NULL);

			if($result['status']==='unchanged')
			{
				update($irts, 'metadata', array("added"), array(date("Y-m-d H:i:s"), $result['rowID']), 'rowID');
			}
		}

		print_r($errors);

		$summary = saveReport($source, $report, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$summary);
	}

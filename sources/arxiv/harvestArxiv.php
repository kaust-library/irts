<?php
	//Define function to harvest arxiv metadata via REST API
	function harvestArxiv($source)
	{
		global $irts, $newInProcess, $errors, $report;

		$report = '';

		$errors = array();
		
		$year = date("Y");

		//Record count variable
		$recordTypeCounts = array('all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'unchanged'=>0,'skipped'=>0);

		/* 
		//Google Scholar based harvest
		$max = 50;
		$result = queryGoogleScholar($count, $max, $institutionNames, $fullTextString); 
		*/
		
		//Author name harvest direct from arXiv
		//Get list of active faculty to check
		$persons = getValues($irts, "SELECT DISTINCT idInSource FROM `metadata`
				WHERE `source` LIKE 'local'
				AND `field` LIKE 'local.person.title'
				AND (`value` LIKE '%Prof %' OR `value` LIKE '%Professor%' OR `value` LIKE '%Prof.%')
				AND value NOT LIKE '%Former%'
				AND value NOT LIKE '%Visiting%'
				AND value NOT LIKE '%Emeritus%'
				AND `deleted` IS NULL
				AND `parentRowID` NOT IN (
					SELECT `parentRowID` FROM metadata
					WHERE source LIKE 'local'
					AND field = 'local.date.end'
					AND deleted IS NULL
					AND `parentRowID` IS NOT NULL)", array('idInSource'));

		foreach($persons as $idInSource)
		{
			$report .= $idInSource.PHP_EOL;
			
			$name = getValues($irts, "SELECT `value` FROM `metadata` WHERE `source` LIKE 'local' AND `idInSource` LIKE '$idInSource' AND `field` LIKE 'local.person.name'", array('value'), 'singleValue');
			
			$report .= '-- '.$name.PHP_EOL;
			
			if(in_array($name, array('')))
			{
				$report .= ' -- Skipped - Name is too common'.PHP_EOL;
			}
			else
			{
				$xml = retrieveArxivMetadata('name', $name);

				foreach($xml->entry as $item)
				{
					$report .= ' -- '.$item->id.PHP_EOL;
					
					$recordTypeCounts['all']++;
					
					if(strpos($item->published, $year)!==FALSE)
					{			
						$result = processArxivRecord($item);
						
						$recordType = $result['recordType'];
						
						$idInSource = $result['idInSource'];
						
						$status = addToProcess('arxiv', $idInSource, $idInSource, 'dc.identifier.arxivid', 'Preprint');
						
						if($status === 'inProcess')
						{
							$newInProcess++;
						}
					}
					else
					{
						$recordType = 'skipped';
					}
					
					$recordTypeCounts[$recordType]++;
					
					$report .= '  -- '.$recordType.PHP_EOL;
				}
			}
		}
		
		$summary = saveReport($source, $report, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$summary);
	}

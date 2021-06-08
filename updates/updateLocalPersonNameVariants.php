<?php	
	//Define function to add additional name variants to local person records when needed
	function updateLocalPersonNameVariants($report, $errors, $recordTypeCounts)
	{
		global $irts;
		
		$source = 'local';
		
		//$people = getValues($irts, "SELECT idInSource, value FROM metadata WHERE source = 'local' AND field = 'local.person.name' ORDER BY idInSource DESC", array('idInSource', 'value'), 'arrayOfValues');
		
		$people = getValues($irts, "SELECT idInSource, value FROM metadata WHERE source = 'local' AND field = 'local.person.name' AND idInSource NOT IN (SELECT idInSource FROM metadata WHERE source = 'local' AND field = 'local.name.variant' AND deleted IS NULL) AND deleted IS NULL ORDER BY idInSource DESC", array('idInSource', 'value'), 'arrayOfValues');
		
		$recordTypeCounts['all'] = count($people);
		
		foreach($people as $person)
		{
			$variantsAdded = generateNameVariants($person['idInSource'], $person['value']);
			
			if(count($variantsAdded) === 0)
			{
				$recordTypeCounts['unchanged']++;
			}
			else
			{
				$report .= $person['idInSource'].' -- '.$person['value'].PHP_EOL.'-- '.count($variantsAdded).' variants added: '.implode('; ', $variantsAdded).PHP_EOL;
				$recordTypeCounts['modified']++;
			}
		}

		$summary = saveReport(__FUNCTION__, $report, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$summary);
	}

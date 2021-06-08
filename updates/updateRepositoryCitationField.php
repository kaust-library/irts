<?php	
	//Define function to update repository record dc.identifier.citation fields based on DOI content negotiation
	function updateRepositoryCitationField($report, $errors, $recordTypeCounts)
	{
		global $irts;
		
		// init		
		$sources = array('datacite', 'crossref');
		$dSpaceAuthHeader = loginToDSpaceRESTAPI();
		
		$result = $irts->query("SELECT idInSource, value doi FROM `metadata` 
			WHERE `source` LIKE 'repository' 
			AND `field` LIKE 'dc.identifier.doi' 
			AND `place` = 1 
			AND `deleted` IS NULL
			AND (
				`idInSource` IN (
				SELECT `idInSource` FROM `metadata` 
					WHERE `source` LIKE 'repository' 
					AND `field` LIKE 'dc.identifier.citation' 
					AND `value` = 'Array' 
					AND `deleted` IS NULL
				)
				OR
				`idInSource` NOT IN (
				SELECT `idInSource` FROM `metadata` 
					WHERE `source` LIKE 'repository' 
					AND `field` LIKE 'dc.identifier.citation' 
					AND `deleted` IS NULL
				)
			)
		
			AND (
				`value` IN (
				SELECT `idInSource` FROM `metadata` 
					WHERE `source` LIKE 'doi' 
					AND `field` LIKE 'doi.agency.id' 
					AND `value` IN ( '".implode("','", $sources)."' )
					AND `deleted` IS NULL
				)
				OR
				`value` NOT IN (
				SELECT `idInSource` FROM `metadata` 
					WHERE `source` LIKE 'doi' 
					AND `field` LIKE 'doi.agency.id' 
					AND `deleted` IS NULL
				)
			) AND idInSource NOT IN (
					 SELECT REPLACE(`idInSource`,'repository_','') FROM metadata
					 WHERE `source` = 'irts'
					 AND `field` = 'irts.check.citation'
					 AND deleted IS NULL
					 AND added >= '".THREE_MONTHS_AGO."'
				)");
			
		while($row = $result->fetch_assoc())
		{			
			$idInSource = $row['idInSource'];
			
			$report .= $idInSource.PHP_EOL;
			
			$internalItemId = getValues($irts, "SELECT DISTINCT(`value`) FROM `metadata` WHERE `source` LIKE 'repository' AND `field` LIKE 'dc.internalItemId' AND `deleted` IS NULL AND `idInSource` = '$idInSource'", array('value'), 'singleValue');
			
			$doi = $row['doi'];
			
			$report .= $internalItemId.PHP_EOL;

			$recordTypeCounts['all']++;	
			
			$json = getItemMetadataFromDSpaceRESTAPI($internalItemId, $dSpaceAuthHeader);			
		
			if(is_string($json))
			{
				$metadata = dSpaceJSONtoMetadataArray($json);
				$citation = getValues($irts, setSourceMetadataQuery('doi', $doi, NULL, 'dc.identifier.citation'), array('value'), 'singleValue');
				
				if(empty($citation) || $citation === 'Array') {
					
					$citation = getCitationByDOI($doi);
					
				}
				
				if(!empty($citation)&&is_string($citation)&&$citation!=='Array')
				{
					$recordTypeCounts['modified']++;
					
					// remove the tags
					$citation = standardizeTheUseOfTags($citation , True);
					
					$metadata['dc.identifier.citation'][0]['value'] = $citation;
					
					$report .= $metadata['dc.identifier.citation'][0]['value'].PHP_EOL;

					$metadata = appendProvenanceToMetadata($internalItemId, $metadata, __FUNCTION__);

					$json = prepareItemMetadataAsDSpaceJSON($metadata);

					$response = putItemMetadataToDSpaceRESTAPI($internalItemId, $json, $dSpaceAuthHeader);				
					
					// save a check row 
					$insertResult = saveValue('irts', 'repository_'.$idInSource, 'irts.check.citation', 1, 'completed' , NULL);
					
					if($insertResult['status']==='unchanged') {
						update($irts, 'metadata', array("added"), array(date("Y-m-d H:i:s"), $insertResult['rowID']), 'rowID');
					}					
				}				
			}
			else
			{
				$recordTypeCounts['skipped']++;
				$report .= print_r($json, TRUE);
			}
			
			sleep(5);
			set_time_limit(0);
		}

		$summary = saveReport(__FUNCTION__, $report, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$summary);
	}

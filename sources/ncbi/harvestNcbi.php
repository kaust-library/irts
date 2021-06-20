<?php

/*

**** This file responsible for harvesting the metadata for Genbank from NCBI.

** Parameters :
	none

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 7 May 2020 - 1:30 PM

*/

//--------------------------------------------------------------------------------------------
function harvestNcbi($source)
{
	global $irts, $newInProcess, $errors, $report;

	$report = '';

	$errors = array();

	//Record count variable
	$recordTypeCounts = array('accession numbers to check'=>0,'all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'unchanged'=>0,'No internal id'=>0,'skipped'=>0);

	$bioprojectIDs = array();

	$accessionNumbers = array();

	$orgSearchResults = array();
	
	// get all the accession numbers from the database
	$accessionNumbersToCheck = array();
	
	$existingAccessionNumbers =  getValues($irts, "SELECT `idInSource` FROM `sourceData`
		WHERE `source` = 'ncbi'
		AND `deleted` IS NULL", array('idInSource'), 'arrayOfValues');	
	
	$dataAvailabilityStatements =  getValues($irts, "SELECT value FROM `metadata`
		WHERE `source` LIKE 'irts'
		AND `field` LIKE 'dc.description.dataAvailability'
		AND `value` LIKE '%PRJ%'
		AND deleted IS NULL", array('value'), 'arrayOfValues');
		
	foreach($dataAvailabilityStatements as $dataAvailabilityStatement)
	{
		preg_match_all('/PRJ[A-Z]{2}[0-9]*//* ', $dataAvailabilityStatement, $accessionNumbersGroups);
		
		foreach($accessionNumbersGroups as $accessionNumbers)
		{
			foreach($accessionNumbers as $accessionNumber)
			{
				if(!in_array($accessionNumber, $existingAccessionNumbers))
				{
					$accessionNumbersToCheck[] = $accessionNumber;
				}
			}
		}
	}

	$report .= '- '.count($accessionNumbersToCheck).' accession numbers to check: '.PHP_EOL;

	$recordTypeCounts['accession numbers to check'] = count($accessionNumbersToCheck);

	// for each accession number
	foreach($accessionNumbersToCheck as $accessionNumber)
	{
		$report .= 'Accession Number: '.$accessionNumber.PHP_EOL;
		
		// Get the internal ID using the accession number
		$xml = simplexml_load_string(queryNCBI('esearch.fcgi', 'bioproject', $accessionNumber, 'term'));

		// Some searches have more than one internalID result, but only the first one is actually for this accession number
		foreach ($xml->IdList->Id as $internalID)
		{
			if(!empty($internalID))
			{
				$internalID = (string)$internalID[0];

				$report .= ' - Found Bioproject internal ID to check:'.$internalID.PHP_EOL;

				$bioprojectIDs[] = $internalID;

				$accessionNumbers[$internalID] = $accessionNumber;
			}
			else
			{
				$report .= ' - No Bioproject internal ID found'.PHP_EOL;

				$recordTypeCounts['No internal id found']++;
			}
		}
	}
	
	// get all bioproject ids
	$searchKey = INSTITUTION_NAME.'%20or%20'.INSTITUTION_ABBREVIATION.'&field=Organization&retmax=500';
	
	$report .= 'Searching by: '.$searchKey.PHP_EOL;
	
	$xml = simplexml_load_string(queryNCBI('esearch.fcgi', 'bioproject',  $searchKey, 'term'));

	foreach ($xml->IdList->Id as $internalID)
	{
		if(!empty($internalID))
		{
			$internalID = (string)$internalID;
			
			$report .= ' - Found Bioproject internal ID to check:'.$internalID.PHP_EOL;
			
			$existingInternalID = getValues($irts, "SELECT `idInSource` FROM `metadata`
				WHERE `source` LIKE 'ncbi'
				AND `field` LIKE 'ncbi.DocumentSummary.Project.ProjectID.ArchiveID.id'
				AND `idInSource` = '$internalID'
				AND `deleted` IS NULL
				ORDER BY `place` ASC", array('rowID'), 'arrayOfValues');
			
			if(empty($existingInternalID))
			{
				$bioprojectIDs[] = $internalID;

				$orgSearchResults[] = $internalID;
			}
			else
			{
				$report .= ' - skip - already harvested'.PHP_EOL;
			}
		}
	}

	foreach($bioprojectIDs as $internalID)
	{
		$report .= 'Checking: '.$internalID.PHP_EOL;
		
		$process = FALSE;
		
		$recordTypeCounts['all']++;

		// Get the data using internal ID NCBI_API_URL.db=bioproject&id=
		$xml = queryNcbi('efetch.fcgi', 'bioproject', $internalID);

		$xml = new SimpleXMLElement($xml);

		// check if the accessionNumber for the internalID in the DB
		$accessionNumber = ((string)$xml->DocumentSummary->Project->ProjectID->ArchiveID['accession']);

		if(isset($accessionNumbers[$internalID]))
		{
			if($accessionNumber === $accessionNumbers[$internalID])
			{
				$process = TRUE;
			}
		}
		
		if(in_array($internalID, $orgSearchResults))
		{
			$process = TRUE;
		}
		
		if($process)
		{
			// Save the xml file in the source
			$recordType = saveSourceData($report, $source, $accessionNumber, $xml->asXML(), 'XML');
			
			$report .= ' - '.$recordType.PHP_EOL;

			$recordTypeCounts[$recordType]++;

			// process the xml file
			$record = processNcbiRecord($xml, $accessionNumber);

			// save NCBI record
			$functionReport = saveValues($source, $accessionNumber, $record, NULL);

			// process only items with IsSupplementTo relation
			$isSupplementTo = getValues($irts, "SELECT `rowID` FROM `metadata`
				WHERE `source` LIKE 'ncbi'
				AND `idInSource` = '$accessionNumber'
				AND `field` LIKE 'dc.relation.issupplementto'
				AND `deleted` IS NULL
				ORDER BY `place` ASC", array('rowID'), 'arrayOfValues');
				
			$creatorOrg = getValues($irts, "SELECT `value` FROM `metadata`
				WHERE `source` LIKE 'ncbi'
				AND `idInSource` = '$accessionNumber'
				AND `field` LIKE 'dc.creator'
				AND `deleted` IS NULL", array('value'), 'singleValue');

			if(!empty($isSupplementTo)&&institutionNameInString($creatorOrg))
			{
				$status = addToProcess('ncbi', $accessionNumber, $accessionNumber, 'dc.identifier.bioproject', 'Bioproject');
				$report .= ' - '.$status.PHP_EOL;
				if($status === 'inProcess')
				{
					$newInProcess++;
				}
			}
			else
			{
				$report .= ' - missing relation to article or match for institution name in org string'.PHP_EOL;
			}
		}
		else
		{
			$report .= ' - skipped'.PHP_EOL;
			
			$recordTypeCounts['skipped']++;
		}
		ob_flush();
	}

	$sourceSummary = saveReport($source, $report, $recordTypeCounts, $errors);

	return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$sourceSummary);
}

<?php

/*

**** This file is responsible for converting the metadata from NCBI to the array structure needed to be saved locally.

** Parameters :
	$input: the record metadata as xml

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 11 May 2020 - 1:30 PM

*/

//--------------------------------------------------------------------------------------------

function processNcbiRecord($input, $accessionNumber)
{
	global $irts, $report;

	$source = 'ncbi';
	$record = array();

	$record['dc.type'][]['value'] =  'Bioproject';
	$record['dc.type'][]['value'] =  'Dataset';

	// convert the XML file to array
	$xml2array = xml2array($input);
	
	// get the haspart relation
	if(isset($input->DocumentSummary->Project->ProjectDescr->LocusTagPrefix))
	{
		foreach($input->DocumentSummary->Project->ProjectDescr->LocusTagPrefix as $key => $attributes)
		{
			$value = (string)($attributes->attributes());
			if(!empty($value))
			{
				$record['dc.relation.haspart'][]['value'] = 'biosample:'.$value;
			}
		}
	}

	// get the article DOI that is associated with this accession number
	$articleDOIs = getValues($irts, "SELECT `value` FROM `metadata`
		WHERE `idInSource` IN (
			SELECT `idInSource` FROM `metadata`
			WHERE source = 'irts'
			AND (field = 'dc.relation.issupplementedby'
			AND `value` LIKE 'accessionNumber:$accessionNumber'
			AND `deleted` IS NULL)
			OR
			(field = 'dc.description.dataAvailability'
			AND `value` LIKE '%$accessionNumber%'
			AND `deleted` IS NULL)
		)
		AND field = 'dc.identifier.doi'
		AND `deleted` IS NULL", array('value'), 'arrayOfValues');

	foreach($articleDOIs as $articleDOI)
	{
		$record['dc.relation.issupplementto'][] = 'DOI:'.$articleDOI;
	}

	$record = iterateOverNcbiFields($record, $source, $xml2array);

	$record['dc.publisher'][]['value'] = 'NCBI';
	$record['dc.relation.url'][]['value'] = 'https://www.ncbi.nlm.nih.gov/bioproject/?term='.$accessionNumber;

	if(isset($record['dc.relation.issupplementto']))
	{
		$uniqueIds = array_unique($record['dc.relation.issupplementto']);
		
		$record['dc.relation.issupplementto'] = array();
		
		foreach($uniqueIds as $uniqueId)
		{
			$record['dc.relation.issupplementto'][]['value'] = $uniqueId;
		}
	}

	return $record;
}

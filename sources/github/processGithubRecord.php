<?php

/*

**** This file is responsible of processing github record.

** Parameters :
	$sourceData: array

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 25 August 2019 - 1:30 PM

*/

//--------------------------------------------------------------------------------------------


function processGithubRecord($input)
{
	global $irts, $errors, $report;

	$source = 'github';
	
	$githubURL = $input['html_url'];
	$githubID = $input['full_name'];
	
	$output = array();
	$specialFields = array('created_at','description','language');

	// add publisher
	$output['dc.publisher'][]['value'] = 'Github';

	foreach($input as $field => $value)
	{
		if(in_array($field, $specialFields))
		{
			if($field === 'created_at' && !isset($output['dc.date.issued']))
			{
				$date =  substr($value, 0, 10);
				$output['dc.date.issued'][]['value'] = $date;
			}
			elseif($field === 'description')
			{
				$output['dc.description.abstract'][]['value'] = $value;

				// add title
				$output['dc.title'][]['value'] =  $githubID.': '.$value;	
			}
		}
		else
		{
			if(!is_array($value))
			{
				$currentField = mapField($source, $field, '');
				$output[$currentField][]['value'] = $value;
			}
			else
			{
				foreach($value as $childField => $childValue)
				{
					if(is_array($childValue))
					{
						$childValue = json_encode($childValue);
					}
					
					if ($childField === 'content')
					{	
						$childValue = base64_decode($childValue);
					}
					
					$currentField = $source.'.'.$field.'.'.$childField;
					$output[$currentField][]['value'] = $childValue;
				}
			}
		}
	}
	
	// add the relation to the output
	$articleDOIs = getValues($irts, "SELECT `value` FROM `metadata` 
		WHERE `idInSource` IN ( 
			SELECT `idInSource` FROM `metadata` 
			WHERE source IN ('irts','repository')
			AND
			(
				(
					`field` = 'dc.relation.issupplementedby' 
					AND value like 'URL:$githubURL%' 
					AND `deleted` IS NULL
				)
				OR
				(
					field = 'dc.description.abstract'
					AND `value` LIKE '%$githubURL%'
					AND `deleted` IS NULL
				)
			)
		) 
		AND field = 'dc.identifier.doi' 
		AND `deleted` IS NULL", array('value'), 'arrayOfValues');

	if(!empty($articleDOIs))
	{	
		foreach ($articleDOIs as $articleDOI)
		{
			$output['dc.relation.issupplementto'][]['value'] = 'DOI:'.$articleDOI;
		}
	}
	
	// add the relation to the output
	$preprintArXivIDs = getValues($irts, "SELECT `value` FROM `metadata` 
		WHERE `idInSource` IN ( 
			SELECT `idInSource` FROM `metadata` 
			WHERE source IN ('irts','repository')
			AND
			(
				(
					`field` = 'dc.relation.issupplementedby' 
					AND value like 'URL:$githubURL%' 
					AND `deleted` IS NULL
				)
				OR
				(
					field = 'dc.description.abstract'
					AND `value` LIKE '%$githubURL%'
					AND `deleted` IS NULL
				)
			)
		) 
		AND field = 'dc.identifier.arxivid' 
		AND `deleted` IS NULL", array('value'), 'arrayOfValues');

	if(!empty($preprintArXivIDs))
	{	
		foreach ($preprintArXivIDs as $preprintArXivID)
		{
			$output['dc.relation.issupplementto'][]['value'] = 'arXiv:'.$preprintArXivID;
		}
	}

	// add github URL and ID
	$output['dc.relation.url'][]['value'] = $githubURL;
	
	$output['dc.identifier.github'][]['value'] = $githubID;

	// get the DOI from datacite 
	$resultFromDatacite = queryDatacite($githubID, 'title');
	
	//print_r($resultFromDatacite);
	
	if(is_string($resultFromDatacite))
	{
		$resultFromDatacite = json_decode($resultFromDatacite, TRUE);

		if(!empty($resultFromDatacite['data']))
		{
			$report .= ' - '.count($resultFromDatacite['data']).' DataCite DOIs found:'.PHP_EOL;

			foreach($resultFromDatacite['data'] as $recordFromDatacite)
			{
				$report .= '  - '.$recordFromDatacite['id'].PHP_EOL;
				
				$dataciteDOI = $recordFromDatacite['id'];
				
				$dataciteDOI = str_replace('https://doi.org/', '', $dataciteDOI);
				
				//echo $dataciteDOI.PHP_EOL;
				
				$response = queryDatacite($dataciteDOI, 'metadata');

				if(is_string($response))
				{
					$recordType = saveSourceData($report, 'datacite', $dataciteDOI, $response, 'JSON');

					$report .= ' - '.$recordType.PHP_EOL;

					//convert record to local record array structure
					$recordFromDatacite = processDataciteRecord($response);
					
					$functionReport = saveValues('datacite', $dataciteDOI, $recordFromDatacite, NULL);
				
					$output['dc.identifier.doi'][]['value'] = $dataciteDOI;
				}
			}
		}
	}
	
	//print_r($output);

	return $output;
}
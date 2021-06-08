<?php
//Define function to process a retrieved Datacite JSON record
function processDataciteRecord($input)
{
	global $irts, $errors, $report;

	$source = 'datacite';

	$output = array();

	$specialFields = array('creators','dates','descriptions','subjects','relatedIdentifiers','publisher');

	$sourceData = json_decode($input, TRUE);

	//Skip records with empty attributes
	if(!isset($sourceData['data']['attributes']))
		return $output;

	$data = $sourceData['data']['attributes'];

	// Only Figshare collections will be treated as datasets, other Figshare DOIs (with .c. and _d OR without either) are for individual files
	if(preg_match('/10.6084\/m9.figshare.c(.*)_d(.*)/', $data['doi']))
	{
		$output['dc.type'][]['value'] = 'Data File';
	}
	elseif(preg_match('/10.6084\/m9.figshare.c/', $data['doi']))
	{
		$output['dc.type'][]['value'] = 'Dataset';
	}
	elseif(preg_match('/10.6084\/m9.figshare/', $data['doi']))
	{
		$output['dc.type'][]['value'] = 'Data File';
	}
	else
	{
		$output['dc.type'][]['value'] = 'Dataset';
	}

	foreach($data as $field => $value)
	{
		if(in_array($field, $specialFields))
		{
			if($field === 'creators')
			{
				//get authers and affiliation and Orcid ID
				$authors = array();

				foreach ($value as $seq => $authorInfo)
				{
					$authors[$seq]['value'] = $authorInfo['name'];

					//get affiliation
					if(!empty($authorInfo['affiliation']))
					{
						foreach ($authorInfo['affiliation'] as $affiliation)
						{
							$authors[$seq]['children']['dc.contributor.affiliation'][]['value'] = $affiliation;
						}
					}

					//get ORCID iDs
					if(isset($authorInfo['nameIdentifiers'][0]))
					{
						if($authorInfo['nameIdentifiers'][0]['nameIdentifierScheme'] === 'ORCID')
						{
							$authors[$seq]['children']['dc.identifier.orcid'][]['value'] = str_replace('https://orcid.org/','',$authorInfo['nameIdentifiers'][0]['nameIdentifier']);
						}
					}
				}
				$output['dc.creator'] = $authors;
			}
			elseif($field === 'dates')
			{
				foreach ($value as $dateInfo)
				{
					if($dateInfo['dateType'] === 'Issued')
					{
						$output['dc.date.issued'][]['value'] = $dateInfo['date'];
					}
				}
			}
			elseif($field === 'descriptions')
			{
				//sometimes description is null
				if(is_array($value))
				{
					foreach ($value as $descriptionInfo)
					{
						if($descriptionInfo['descriptionType'] === 'Abstract')
						{
							$output['dc.description.abstract'][]['value'] = $descriptionInfo['description'];
						}
					}
				}
			}
			elseif($field === 'subjects')
			{
				foreach ($value as $subjectInfo)
				{
					$output['dc.subject'][]['value'] = $subjectInfo['subject'];
				}
			}
			//get the related identifiers
			elseif($field === 'relatedIdentifiers')
			{
				foreach($value as $key => $relatedIdentifier)
				{
					$output['dc.relation.'.strtolower($relatedIdentifier['relationType'])][]['value'] = $relatedIdentifier['relatedIdentifierType'].':'.str_replace('doi:', '', $relatedIdentifier['relatedIdentifier']);
				}
			}
		}

		//We also want to save all other fields, for which we will iterate further
		if(!is_array($value))
		{
			$currentField = mapField($source, $field, '');

			$output[$currentField][]['value'] = $value;
		}
		else
		{
			foreach($value as $childField => $childValue)
			{
				if(!is_array($childValue))
				{
					if(is_numeric($childField))
					{
						$currentField = mapField($source, $field, '');
					}
					else
					{
						$currentField = mapField($source, $childField, '');
					}

					$output[$currentField][]['value'] = $childValue;
				}
				else
				{
					foreach($childValue as $grandChildField => $grandChildValue)
					{
						//Save any further levels as JSON strings
						if(is_array($grandChildValue))
						{
							$grandChildValue = json_encode($grandChildValue);
						}

						$currentField = mapField($source, $source.'.'.$field.'.'.$grandChildField, '');

						$output[$currentField][]['value'] = $grandChildValue;
					}
				}
			}
		}
	}
	
	if(strpos($data['doi'], '10.5517/ccdc') !== FALSE)
	{
		$output['dc.title'][0]['value'] = $output['dc.title'][0]['value'].' : '.$output['dc.subject'][6]['value'];
	}

	return $output;
}

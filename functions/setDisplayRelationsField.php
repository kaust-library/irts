<?php
/*

**** This function sets the display.relations field in a metadata record.

** Parameters :
	$record : array of metadata to which display.relations field will be appended

** Created by : Yasmeen alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 22 March 200- 1:27 AM

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------


function setDisplayRelationsField($record)
{
	global $irts;

	$errors = array();
	
	//Relation fields and labels for display in DSpace
	$relationsToDisplayInDspace = array('dc.relation.issupplementto' => 'Is Supplement To', 'dc.relation.issupplementedby' => 'Is Supplemented By', 'dc.relation.haspart' => 'Has Part', 'dc.relation.ispartof' => 'Is Part Of', 'dc.relation.isreferencedby' => 'Is Referenced By', 'dc.relation.references' => 'References');
	
	$prefixes = array('DOI' => array('baseURL' => 'https://doi.org/', 'field' => 'dc.identifier.doi'), 'bioproject' => array('baseURL' => 'https://www.ncbi.nlm.nih.gov/bioproject/?term=', 'field' => 'dc.identifier.bioproject'), 
	'biosample' => array('baseURL' => 'https://www.ncbi.nlm.nih.gov/biosample/?term=', 'field' => 'dc.identifier.bioproject'), 
	'github' => array('baseURL' => 'https://github.com/', 'field' => 'dc.identifier.github'),
	'Handle' => array('baseURL' => 'http://hdl.handle.net/', 'field' => 'dc.identifier.uri'),
	'arXiv' => array('baseURL' => 'https://arxiv.org/abs/', 'field' => 'dc.identifier.arxivid'));
	
	$displayRelationsString = '';

	foreach($relationsToDisplayInDspace as $relationType => $relationLabel)
	{
		$relatedItems = array();
		if(isset($record[$relationType]))
		{
			foreach($record[$relationType] as $relatedIdentifier)
			{
				$relatedItem = array();
				foreach($prefixes as $prefix => $prefixData)
				{
					$prefixBaseURL = $prefixData['baseURL'];
					$prefixField = $prefixData['field'];
					
					if(strpos($relatedIdentifier, $prefix.':') !== FALSE)
					{
						$relatedItem[$prefix] = str_replace($prefix.':', '', $relatedIdentifier);
						
						$relatedItem['itemID'] = getValues($irts, setSourceMetadataQuery('dspace', NULL, NULL, $prefixField, $relatedItem[$prefix]), array('idInSource'), 'singleValue');
						
						if(!empty($relatedItem['itemID']))
						{
							$relatedItem['Handle'] = str_replace($prefixes['Handle']['baseURL'], '', getValues($irts, setSourceMetadataQuery('dspace', $relatedItem['itemID'], NULL, $prefixes['Handle']['field']), array('value'), 'singleValue'));
							
							$relatedItem['Type'] = getValues($irts, setSourceMetadataQuery('dspace', $relatedItem['itemID'], NULL, 'dc.type'), array('value'), 'singleValue');

							$relatedItem['Citation'] = getValues($irts, setSourceMetadataQuery('dspace', $relatedItem['itemID'], NULL, 'dc.identifier.citation'), array('value'), 'singleValue');
							
							if(empty($relatedItem['Citation']))
							{
								if(!empty($relatedItem['DOI']))
								{
									$relatedItem['Citation'] = getCitationByDOI($relatedItem['DOI']);
								}
								
								if(empty($relatedItem['Citation'])||is_array($relatedItem['Citation']))
								{
									$relatedItem['Citation'] = 'Title: '.getValues($irts, setSourceMetadataQuery('dspace', $relatedItem['itemID'], NULL, 'dc.title'), array('value'), 'singleValue').
									'. Publication Date: '.getValues($irts, setSourceMetadataQuery('dspace', $relatedItem['itemID'], NULL, 'dc.date.issued'), array('value'), 'singleValue');
								}
							}
							$relatedItems[] = $relatedItem;
						}
					}
				}
			}
		}
		
		if(!empty($relatedItems))
		{
			$displayRelationsString .= '<b>'.$relationLabel.':</b><br/> <ul>';
			
			foreach($relatedItems as $relatedItem)
			{
				$displayRelationsString .= '<li><i>['.$relatedItem['Type'].']</i> <br/> '.$relatedItem['Citation'].'.';
				
				foreach($prefixes as $prefix => $prefixData)
				{
					$prefixBaseURL = $prefixData['baseURL'];
					$prefixField = $prefixData['field'];
					
					if(!empty($relatedItem[$prefix]))
					{
						$displayRelationsString .= ' '.$prefix.': <a href="'.$prefixBaseURL.$relatedItem[$prefix].'" >'.$relatedItem[$prefix].'</a>';
					}
				}
				
				$displayRelationsString .= '</a></li>';
			}
			
			$displayRelationsString .= '</ul>';
		}
	}
	
	if(!empty($displayRelationsString))
	{
		if(isset($record['display.relations']))
		{
			if($record['display.relations'][0] !== $displayRelationsString)
			{
				$status = 'changed';
				
				unset($record['display.relations']);
				
				$record['display.relations'][] = $displayRelationsString;
			}
			else
			{
				$status = 'unchanged';
			}
		}
		else
		{
			$status = 'new';
			
			$record['display.relations'][] = $displayRelationsString;
		}
	}
	else
	{
		$status = 'not set';
	}

	return array('status' => $status, 'record' => $record);
}

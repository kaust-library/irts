<?php

/*

**** This file responsible is responsible for setting the inverse relation on an existing record when a new record is added that has a relationship to the existing record.

** Parameters :
	$itemID: DSpace item ID of the new item.
	

** Created by : Yasmeen alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 22 March 200- 1:27 AM 

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------
function setInverseRelations($itemID)
{
	//init 
	global $irts;
	$dSpaceAuthHeader = loginToDSpaceRESTAPI();
	$report = '';
	$errors = array();
	
	$source = 'dspace';
	$relations = array();
	
	//$prefixes = array('DOI' => 'dc.identifier.doi' , 'bioproject' => 'dc.identifier.bioproject', 'biosample' => 'dc.identifier.biosample', 'Handle' => 'dc.identifier.uri');
	$prefixes = array('DOI' => 'dc.identifier.doi','bioproject' => 'dc.identifier.bioproject','github' => 'dc.identifier.github','arXiv' => 'dc.identifier.arxivid', 'Handle' => 'dc.identifier.uri');
	
	$identifierToAdd = '';
	foreach($prefixes as $prefix => $field)
	{
		$identifierToAdd = getValues($irts, setSourceMetadataQuery($source, $itemID, NULL, $field), array('value'), 'singleValue');
		
		if(!empty($identifierToAdd))
		{
			$identifierToAdd = $prefix.':'.$identifierToAdd;
			
			break;
		}
	}
	
	if(!empty($identifierToAdd))
	{
		// create inverse relations array
		$inverseRelations = array('dc.relation.issupplementto'=> 'dc.relation.issupplementedby', 'dc.relation.ispartof' => 'dc.relation.haspart', 'dc.relation.isreferencedby' => 'dc.relation.references');
		
		$inverseRelations = array_merge($inverseRelations, array_flip($inverseRelations));
		
		//get relation field values
		foreach($inverseRelations as $relationField => $inverseField)
		{
			$relations[$relationField] = getValues($irts, setSourceMetadataQuery($source, $itemID, NULL, $relationField), array('value'));
			
			if(!empty($relations[$relationField]))
			{
				$report .= '<br> - Relation Field:'.$relationField.PHP_EOL;
				foreach($relations[$relationField] as $relatedIdentifier)
				{
					$report .= '<br> -- Related Identifier:'.$relatedIdentifier.PHP_EOL;
					
					foreach($prefixes as $prefix => $field)
					{
						if(strpos($relatedIdentifier, $prefix.':') !== FALSE)
						{
							$relatedIdentifier = str_replace($prefix.':', '', $relatedIdentifier);
							
							$relatedItemID = getValues($irts, setSourceMetadataQuery('dspace', NULL, NULL, $field, $relatedIdentifier), array('idInSource'), 'singleValue');
							
							if(!empty($relatedItemID))
							{
								$report .= '<br> -- Related Item ID:'.$relatedItemID.PHP_EOL;
								
								$json = getItemMetadataFromDSpaceRESTAPI($relatedItemID, $dSpaceAuthHeader);
								
								//pause to prevent continuous calls from causing API errors
								sleep(5);
								
								$metadata = json_decode($json, TRUE);

								$record = dSpaceMetadataToArray($metadata);
								
								if(!isset($record[$inverseField]))
								{
									$record[$inverseField][] = $identifierToAdd;
								}
								elseif(!in_array($identifierToAdd, $record[$inverseField]))
								{
									$record[$inverseField][] = $identifierToAdd;
								}
								
								$result = setDisplayRelationsField($record);
								
								$report .= '<br> -- status of display.relations field: '.$result['status'];
								
								if(in_array($result['status'], array('new','changed')))
								{
									//update the metadata for the related record in DSpace
									$record = $result['record'];
									
									$record = appendProvenanceToMetadata($relatedItemID, $record, __FUNCTION__);

									$json = prepareItemMetadataAsDSpaceJSON($record);

									$response = putItemMetadataToDSpaceRESTAPI($relatedItemID, $json, $dSpaceAuthHeader);
									
									if(is_array($response))
									{
										$report .= '<br> -- SET INVERSE RELATIONS FAILURE: <br> -- Response received was: '.print_r($response, TRUE).'<br> -- Posted JSON was: '.$json.PHP_EOL;
									}
									else
									{
										$report .= '<br> -- Inverse relation set successfully.'.PHP_EOL;
									}
									
									//pause to prevent continuous calls from causing API errors
									sleep(5);
								}
							}
						}
					}
				}
			}
		}
	}
	
	return($report);
}
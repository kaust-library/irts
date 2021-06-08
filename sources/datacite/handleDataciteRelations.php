<?php

/*

**** This file is responsible of handling the dataset relation before save it to database.

** Parameters :
	$datasetDOI: unique identifier for the dataset.
	$articleDOI:  unique identifier for the article
	$relation: The type of the relation between the dataset and the article.

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 29 December 2019 - 1:30 PM

*/

//--------------------------------------------------------------------------------------------

function handleDataciteRelations($record, $datasetDOI_A)
{
	global $irts, $errors, $report;

	//default is to create a new entry in IRTS for dataset A
	$saveA = TRUE;

	//List of dataset DOI Bs that need to have a record harvested
	$doiBsToGet = array();

	// how to handle each type of relation
	$relationActions =  array(
		'ispartof' => 'save B',
		'isversionof' => 'save only one',
		'isnewversionof' => 'update/add A',
		'isidenticalto'=> 'save only one',
		'ispreviousversionof'=> 'save B',
		'hasversion'=> 'save only one'
	);

	// check each relation
	foreach($record as $field => $values)
	{
		if(strpos($field, 'dc.relation.') !== FALSE && $field !== 'dc.relation.url')
		{
			$relation = str_replace('dc.relation.', '', $field);
			
			if(isset($relationActions[$relation]))
			{
				$action = $relationActions[$relation];
				
				foreach($values as $value)
				{
					if(strpos($value['value'], 'DOI:') !== FALSE)
					{
						$datasetDOI_B = str_replace('DOI:', '', $value['value']);
						
						// check if dataset B has already been harvested from DataCite
						$existingDataciteRecords = checkForExistingRecords($datasetDOI_B, 'dc.identifier.doi', $report, 'datacite');

						if(empty($existingDataciteRecords))
						{
							// If no record has been harvested for dataset B, we will send it back to the harvest process to be added
							$doiBsToGet[] = $datasetDOI_B;
						}

						// check if dataset B has already been added to the repository
						$existingRepositoryRecords = checkForExistingRecords($datasetDOI_B, 'dc.identifier.doi', $report);

						// check if dataset B has already been added to irts for processing
						$existingIRTSRecords = checkForExistingRecords($datasetDOI_B, 'dc.identifier.doi', $report, 'irts');

						if($relation === 'ispartof')
						{
							$addInverseRelation = TRUE;

							$place = 1;

							$hasparts = getValues($irts, "SELECT value 'relatedID' FROM `metadata`
								WHERE `source` = 'datacite'
								AND `idInSource` = '$datasetDOI_B'
								AND `field` LIKE 'dc.relation.haspart'
								ORDER BY `place` ASC", array('relatedID'), 'arrayOfValues' );

							if(!empty($hasparts))
							{
								// check if the relation already exists
								if(in_array('DOI:'.$datasetDOI_A, $hasparts))
								{
									$addInverseRelation = FALSE;
								}
							}

							if($addInverseRelation)
							{
								$place = count($hasparts)+1;
								
								// add the relation
								$result = saveValue('datacite', $datasetDOI_B, 'dc.relation.haspart', $place, 'DOI:'.$datasetDOI_A, null);
							}
						}

						if($action === 'save B')
						{
								$doiBsToGet[] = $datasetDOI_B;
								$saveA = FALSE;
								$report .= ' - '.$relation.': '.$datasetDOI_B.PHP_EOL;
						}
						elseif($action === 'save only one')
						{
							//if dataset B has the same doi as dataset A, save A
							if($datasetDOI_B !== $datasetDOI_A)
							{
								if(!empty($existingRepositoryRecords)||!empty($existingIRTSRecords))
								{
									// Don't save A
									$saveA = FALSE;
									$report .= ' - '.$relation.': '.$datasetDOI_B.PHP_EOL;
								}
								elseif(preg_match('/10.6084\/m9.figshare.(.*).v(.*)/', $datasetDOI_A))
								{
									$saveA = FALSE;
									$report .= ' - '.$relation.': '.$datasetDOI_B.PHP_EOL;
								}
							}
						}
						elseif($action === 'update/add A')
						{
							// 	// check if database B exist in the database
							// 	$existingRecords = checkForExistingRecords($datasetDOI_B, 'dc.identifier.doi', $report);

							// 	// if B exist we need to update the record with A
							// 	if(!empty($existingRecords)) {

							// 		// show B in the main page of the record to be updated
							// 		return FALSE;
							// 	}

							// if all the relations are empty, then we should save A
							// if the relation is isnewversionof, always save A and send the B DOI to the checkForExistingRecords to display it on the process page => ( This has been handled in displayItemDetails.php)
						}
					}
				}
			}
		}
	}

	return array('saveA'=>$saveA,'getB'=>$doiBsToGet);
}

<?php

/*

This file runs the queries to support the Open Access dashboard once a day and saves the result for later use, this is needed because the queries can take a long time to run and we want the dashboard page to load quickly

Created by : Yasmeen Alsaedy
Date :  9 June 2019 , 11:00 AM

*/

// -----------------------------------------------------------------------------------------------------------------

function updateOADashboardData($report, $errors, $recordTypeCounts){

	global $irts;

	// prepare data as associative array before saving as json at the end
	$allResults = array();

	// List of queries
	//All subject to OA policy
	$total = getValues($irts, "SELECT COUNT( DISTINCT `idInSource`) count FROM `metadata` 
			WHERE `source`= 'repository'
			AND `idInSource` IN (
				SELECT DISTINCT `idInSource` FROM `metadata` 
				WHERE `source`= 'repository' 
				AND field = 'dc.type'
				AND `value` IN('".implode("','",PUBLICATION_TYPES)."')
			) 
			AND `field`= 'dc.date.issued' 
			AND `value` >= '".OAPOLICY_START_DATE."'
			AND deleted IS NULL", array('count'), 'singleValue');

	// Deposited in repository
	$publicationHandlesWithFile = getValues($irts, "SELECT DISTINCT m1.`idInSource` FROM `metadata` m1 
		LEFT JOIN `metadata` m2 USING(`idInSource`) 
		LEFT JOIN `metadata` m3 USING(`idInSource`) 
		WHERE m1.`source` = 'repository'
			AND m1.`field`= 'dc.date.issued' 
			AND m1.`value` >='".OAPOLICY_START_DATE."'
			AND m1.`deleted` IS NULL
			AND m2.`field`= 'dc.type'
			AND m2.value IN('".implode("','",PUBLICATION_TYPES)."')
			AND m2.`deleted` IS NULL
			AND m3.`field`= 'dspace.bitstream.url'
			AND m3.`deleted` IS NULL", array('idInSource'));

	// save each result in the array
	$allResults['total'] = $total;
	$allResults['countWithFile'] = count($publicationHandlesWithFile);
	$allResults['percentWithFile'] = ($allResults['countWithFile'] / $total) * 100;
	
	//Without files in repository
	$allResults['countNoFile'] = ($total-$allResults['countWithFile']);
	$allResults['percentNoFile'] = ($allResults['countNoFile'] / $total) * 100;	

	// --------------------------------------------------------------------------------

	foreach (YEARS_TO_TRACK as $year)
	{
		$allResults['ByYearAndType']['All Types'][$year]['total'] = 0;
			
		$allResults['ByYearAndType']['All Types'][$year]['withFile'] = 0;
			
		$allResults['ByYearAndType']['All Types'][$year]['noFile'] = 0;
	}

	//By Type By Year
	foreach(PUBLICATION_TYPES as $type)
	{
		foreach (YEARS_TO_TRACK as $year)
		{
			//All items for year and type
			$queryAll = "SELECT COUNT(DISTINCT `idInSource`) count
					FROM `metadata` 					 
					WHERE `source` = 'repository'
					AND `field`= 'dc.date.issued' 
					AND `value` LIKE '$year%'
					AND `deleted` IS NULL
					AND `idInSource` IN(
						SELECT DISTINCT `idInSource` FROM `metadata` 
						WHERE `source`= 'repository' 
						AND field = 'dc.type'
						AND value LIKE '$type'
						AND `deleted` IS NULL
					)";
					
			$queryWithFile = $queryAll."
					AND `idInSource` IN(
						SELECT DISTINCT `idInSource` FROM `metadata` 
						WHERE `source`= 'repository' 
						AND field = 'dspace.bitstream.url'
						AND `deleted` IS NULL
					)";

			// save each result in the array
			$allResults['ByYearAndType'][$type][$year]['total'] = getValues($irts, $queryAll, array('count'), 'singleValue');
					
			$allResults['ByYearAndType'][$type][$year]['withFile'] = getValues($irts, $queryWithFile, array('count'), 'singleValue');
				
			$allResults['ByYearAndType'][$type][$year]['noFile'] = (
					$allResults['ByYearAndType'][$type][$year]['total'] - $allResults['ByYearAndType'][$type][$year]['withFile']
				);
			
			$allResults['ByYearAndType']['All Types'][$year]['total'] = (
					$allResults['ByYearAndType']['All Types'][$year]['total'] + $allResults['ByYearAndType'][$type][$year]['total']
				);
				
			$allResults['ByYearAndType']['All Types'][$year]['withFile'] = (
					$allResults['ByYearAndType']['All Types'][$year]['withFile'] + $allResults['ByYearAndType'][$type][$year]['withFile']
				);
			
			$allResults['ByYearAndType']['All Types'][$year]['noFile'] = (
					$allResults['ByYearAndType']['All Types'][$year]['noFile'] + $allResults['ByYearAndType'][$type][$year]['noFile']
				);
		}
	}

	//Insert the json in the database ( irts messages)
	$json = json_encode($allResults); //Convert $result into a json formated string

	$irts->query("INSERT INTO `messages`(`process`, `type`, `message`, `timestamp`) VALUES ('OADashboardData','upload','$json', '".date("Y-m-d H:i:s")."')");
}
?>

<?php
/*

This update runs the queries needed for the irts dashboard once a day and saves the result. The queries take a long time to run, so we don't want to run them each time the dashboard page is loaded.

Created by : Yasmeen Alsaedy
Date :  24 January 2020 , 11:00 AM

*/

// -------------------------------------------------------------------------------------

function updateIRTSDashboardData($report, $errors, $recordTypeCounts){
		
	// init 
	global $irts;

	$chartsData = array();
	
	$sources = getValues($irts, "SELECT DISTINCT source FROM `metadata` where source IN ('".implode("','", PUBLICATION_SOURCES)."')", array('source'));

	// ----------- Compare Different Sources by Cumulative Count ------------

	// chart datasets
	$datasets = '';
	$nonEmptyCountSource = array();

	foreach( YEARS_TO_TRACK as $year){

		// create associative array the key should be the year and the values is an array filled by 0 
		$years[$year] = array();
	}

	foreach( $sources as $key => $source) {

		foreach(YEARS_TO_TRACK as $year){			
			
			if( $source != 'repository' ) {
				
				$cumulativeCountPerSource = getValues($irts, 
					"SELECT count( DISTINCT `idInSource` ) as count FROM
						   `metadata` where  `idInSource` IN( select  DISTINCT `idInSource`
						FROM
						  `metadata`
						WHERE
						source ='".$source."' AND field = 'dc.identifier.doi' AND
						  `value` IN(
						  SELECT
							`value`
						  FROM
							`metadata`
						  WHERE
							`field` LIKE 'dc.identifier.doi' AND SOURCE = 'repository' AND `deleted` IS NULL
						) AND `deleted` IS NULL ) AND value like '".$year."%' AND `field`  = 'dc.date.issued' AND `deleted` IS NULL" , array('count'), 'singleValue');
			} else {
				
				$cumulativeCountPerSource = getValues($irts, 
					"SELECT
					 count( DISTINCT `idInSource` ) as count
					FROM
					  `metadata`
					WHERE
					  `field` = 'dc.date.issued' AND source ='repository' AND
					VALUE LIKE
					  '".$year."%' AND `idInSource` NOT IN(
					  SELECT DISTINCT
						`idInSource`
					  FROM
						`metadata`
					  WHERE
						`field` LIKE 'dc.type' AND SOURCE = 'repository' AND `value` IN( '".implode("','",PUBLICATION_TYPES)."') AND `deleted` IS NULL ) AND `deleted` IS NULL" , array('count'), 'singleValue');
			}
			
			// fill the years array based on the year and the source position
			$chartsData['CumulativeSourcesCount'][$source][$year] = $cumulativeCountPerSource;
		}
	}

	// -----------------------------------------------------------------------------

	$json = json_encode($chartsData);
	$irts->query("INSERT INTO `messages`(`process`, `type`, `message`, `timestamp`) VALUES ('irtsDashboardData','upload','$json', '".date("Y-m-d H:i:s")."')");
}	

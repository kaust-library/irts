<?php

/*

**** This file is responsible for preparing the tables and charts displayed in public/dashboards/irtsAdmin.php

** Parameters :
	No parameters required.

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 21 July 2020 - 12:25 AM

*/

//--------------------------------------------------------------------------------------------

header('Content-Type: text/html; charset=UTF-8');

//This allows users to use the back button smoothly without receiving a warning from the browser
header('Cache-Control: no cache');
session_cache_limiter('private_no_expire');

ini_set('display_errors', 1);

$includeDirectory = '../../';

set_include_path($includeDirectory);

//include core configuration and common function files
include_once 'include.php';

// init 
$table = '';
$chart = '';

if(isset($_GET['itemType']) && isset($_GET['title'])){

	//init
	$itemType = $_GET['itemType'];
	$keys = array();
	$chartValues = array();
	$colors =  array("#139DA7","#938982", "#EE850F", "#F9C213", "#00FFFF");
	$onlyTheseTypes = array('Book Chapter', 'Article', 'Conference Paper');
	$dataColor = array();
	
	$colorIndex = 0;
	$currentYearInTheLoop = '2010';
	$allTypes = array();
	$currentYear = date("Y");
	$maxYAxis = 0;

	// If $itemType is a string
	if(is_string($itemType))
	{
		// For chart 1 & 2 check if the user selected ALL 
		if(strpos($itemType, 'ALL') !== false)
			$whereType  = '';
		else
			$whereType = "AND value = '$itemType'";
	}

//-------------------------------------------------------------------------------------

	if(strpos($_GET['title'], 'ProcessingTime') !== false) {

		// chart
		 $table = '';

		// get the fields for the step
		$timePerMonthPerYear = getValues($irts, "SELECT YEAR(`added`) as year, MONTH(`added`) as month, SEC_TO_TIME( SUM( TIME_TO_SEC(`value`) ) ) as cumulativeTime, count(`rowID`) as numOfItems FROM `metadata` WHERE `field` = 'irts.process.timeElapsed' AND `idInSource` IN ( SELECT `idInSource` FROM `metadata` WHERE source = 'irts' and `field` = 'dc.type' ".$whereType." AND `deleted` IS NULL ) AND `deleted` IS NULL GROUP BY YEAR(`added`), MONTH(`added`) ASC", array('year', 'month', 'cumulativeTimeInMins'));

		if(!empty($timePerMonthPerYear)){

			$table .= '
						<table class="table table-bordered" id= "TableProcessingTime" >
						  <tr>
							<th>Year</th>
							<th>Month</th>
							<th>Cumulative Time In Mins Per Month</th>
							<th>Total Number of Items</th>
							<th>Average Time Per Month for one item</th>
					  </tr>';
		}		

		foreach ($timePerMonthPerYear as $time) {

			// get the average time for one item per month 
			list($hrs, $mins, $sec) = explode(":", strval($time['cumulativeTime']));
			$mins += $hrs * 60;
			$mins += $sec/60;
			$minsAve = intval($mins/$time['numOfItems']);

			// if the mins and hours is 0, the sec will be 0 even if it contain some fractions 
			// to solve this multiply the seconds by 60 
			$secAve =  ( ($mins/$time['numOfItems']) - intval($mins/$time['numOfItems']) ) * 60;

			$averageTime = date("H:i:s",mktime(0,$minsAve,$secAve,0,0,0));
			$table .= 	'<tr>
							<td>'.$time['year'].'</td>
							<td>'.$time['month'].'</td>
							<td>'.$time['cumulativeTime'].'</td>
							<td>'.$time['numOfItems'].'</td>
							<td>'.$averageTime.'</td>
			  			</tr>';

			$monthName = date("F", mktime(0, 0, 0, $time['month'], 10)); // March
			array_push($keys, ($monthName.';'.$time['year']));

			// TO CHANGE THE SEC TO DECIMAL NUMBER
			$secAve = intval($secAve);
			$secAve = ( $secAve > 10? ($secAve/100) : ($secAve/10) );

			array_push($chartValues, round(($minsAve + $secAve), 2));
			
			// each year should have one color 
			if($currentYearInTheLoop != $time['year']){

				$colorIndex += 1;
				$currentYearInTheLoop = $time['year'];

				if($colorIndex >= count($colors))
					$colorIndex = 0;
			}

			array_push($dataColor, $colors[$colorIndex]);
		}

		$table .=  '</table>';
		$chart = ' new Chart(document.getElementById("bar-chart1"), {
	  type: "bar",

	  data: {
	    labels: ["'.implode('","', $keys).'"],
	    datasets: [{
	      label: "Average Processing Time In Minutes ('.$itemType.')",
	      xAxisID:"xAxis1",
	       backgroundColor: ["'.implode('","', $dataColor).'"],
	      data: ["'.implode('","', $chartValues).'"]
	      }],
	  },


	  options:{
	  	  legend: {
			labels: {
			  boxWidth: 0
			}
		},
	    scales:{
	      xAxes:[
	        {
	          id:"xAxis1",
	          type:"category",
	          ticks:{
	            callback:function(label){
	              var month = label.split(";")[0];
	              return month;
	            }
	          }
	        },
	        {
	          id:"xAxis2",
	          type:"category",
	          gridLines: {
	            drawOnChartArea: false, // only want the grid lines for one axis to show up
	          },
	          ticks:{
	            callback:function(label){
	              var year = label.split(";")[1];
	         	  return year;
	            
	            }
	          }
	        }],
	      yAxes:[{
	        ticks:{
	          beginAtZero:true
	        }
	      }]
	    }
	  }
	}); ';

		echo $table;
		echo $chart;

	}

// -----------------------------------------------------------------------------------

	elseif (strpos($_GET['title'], 'AverageDays') !== false) {

		// init
		$dataPerYearPerMonth = array();
		$avePerYearPerMonth = array();
		$chartMedianValues = array();

		$table = ' <table class="table table-bordered" id= "TableAverageDays" >
			  <tr>
				<th>Year</th>
				<th>Month</th>
				<th>Cumulative Days Per Month</th>
				<th>Total Number of Items</th>
				<th>Average Days Per Month for one item</th>
				<th>Median Day Per Month</th>
		  </tr>';

		$dates = getValues($irts, "SELECT inProcess.idInSource idInSource , inProcess.added inProcess, completed.added completed, YEAR(inProcess.added) year, MONTH(inProcess.added) month FROM metadata inProcess LEFT JOIN metadata completed USING(`idInSource`) 
			WHERE inProcess.`idInSource` IN (SELECT `idInSource` FROM `metadata` 
					WHERE source = 'irts' 
					AND `field` = 'dc.type' 
					".$whereType."
					AND `deleted` IS NULL)
			AND inProcess.source = 'irts' 
			AND inProcess.field = 'irts.status' 
			AND inProcess.value = 'inProcess' 
			AND completed.source = 'irts' 
			AND completed.field = 'irts.status' 
			AND completed.value = 'completed' ", array('idInSource', 'inProcess', 'completed') );
			
			foreach ($dates as $item) {
			
				$diff = abs(strtotime($item['completed']) - strtotime($item['inProcess']));
				$dataPerYearPerMonth[$item['year']][$item['month']][] = $diff;

			}			

			foreach ($dataPerYearPerMonth as $year => $months) {

				foreach ($months as $month => $values) {
					
					// get the average 
					$numOfValues = count($values);
					$average = array_sum($values)/$numOfValues;
					
					// get the fullDays in this month
					$fullDays = floor(array_sum($values)/(60*60*24));

					// get the median per month
					$median = findMedian($values);
					
					// if the days near zero round it up
					if($fullDays < 1) {
						$fullDays  = ceil($average/(60*60*24)); 
						$averagefullDays  = ceil($average/(60*60*24)); 
						$medianfullDays  = ceil($median/(60*60*24)); 

					}else {
						
						// get the fullDays in this month
						$fullDays = floor(array_sum($values)/(60*60*24));
						$averagefullDays  = floor($average/(60*60*24)); 
						$medianfullDays  = floor($median/(60*60*24)); 
					}

					$averagefullHours = floor(($average-($averagefullDays*60*60*24))/(60*60));  
					 
					$avePerYearPerMonth[$year][$month]['minutes'] = floor(($average-($averagefullDays*60*60*24)-($averagefullHours*60*60))/60);  

					$table .= '<tr>
							<td>'.$year.'</td>
							<td>'.$month.'</td>
							<td>'.$fullDays.'</td>
							<td>'.$numOfValues.'</td>
							<td>'.$averagefullDays.'</td>
							<td>'.$medianfullDays.'</td>
			  			</tr>';
			  		
			  		$monthName = date("F", mktime(0, 0, 0, $month, 10)); // March
					array_push($keys, ($monthName.';'.$year));
					array_push($chartValues, $averagefullDays);
					array_push($chartMedianValues, $medianfullDays);

					// each year should have one color 
					if($currentYearInTheLoop != $year){

						$colorIndex += 1;
						$currentYearInTheLoop = $year;

						if($colorIndex >= count($colors))
							$colorIndex = 0;
					}

					array_push($dataColor, $colors[$colorIndex]);

					// to make the two chart end with the same max y axis
					$maxYAxis = max([$averagefullDays, $medianfullDays, $maxYAxis]);
				}
			}

	$table .=  '</table>';
	$chart = ' new Chart(document.getElementById("bar-chart2"), {
	  type: "bar",
	  data: {
	    labels: ["'.implode('","', $keys).'"],
	    datasets: [{
	      label: "Average Processing Time In Days ('.$itemType.')",
	      xAxisID:"xAxis1",
	       backgroundColor: ["'.implode('","', $dataColor).'"],
	      data: ["'.implode('","', $chartValues).'"]
	      }],
	  },

	  options:{
	  	  legend: {
			labels: {
			  boxWidth: 0
			}
		},
	    scales:{
	      xAxes:[
	        {
	          id:"xAxis1",
	          type:"category",
	          ticks:{
	            callback:function(label){
	              var month = label.split(";")[0];
	              return month;
	            }
	          }
	        },
	        {
	          id:"xAxis2",
	          type:"category",
	          gridLines: {
	            drawOnChartArea: false, // only want the grid lines for one axis to show up
	          },
	          ticks:{
	            callback:function(label){
	              var year = label.split(";")[1];
	         	  return year;
	            
	            }
	          }
	        }],
	      yAxes:[{
	        ticks:{
	          beginAtZero:true,
	          max: '.($maxYAxis + 5 ).',
	          stepSize: '.ceil( ($maxYAxis + 5 ) / 5).'
	        }
	      }]
	    }
	  }
	});

	new Chart(document.getElementById("bar-chart3"), {
	  type: "bar",
	   scaleStepWidth : '.$maxYAxis.',
	  data: {
	    labels: ["'.implode('","', $keys).'"],
	    datasets: [{
	      label: "Median Processing Time In Days ('.$itemType.')",
	      xAxisID:"xAxis1",
	       backgroundColor: ["'.implode('","', $dataColor).'"],
	      data: ["'.implode('","', $chartMedianValues).'"]
	      }],
	  },

	  options:{
	  	  legend: {
			labels: {
			  boxWidth: 0
			}
		},
	    scales:{
	      xAxes:[
	        {
	          id:"xAxis1",
	          type:"category",
	          ticks:{
	            callback:function(label){
	              var month = label.split(";")[0];
	              return month;
	            }
	          }
	        },
	        {
	          id:"xAxis2",
	          type:"category",
	          gridLines: {
	            drawOnChartArea: false, // only want the grid lines for one axis to show up
	          },
	          ticks:{
	            callback:function(label){
	              var year = label.split(";")[1];
	         	  return year;
	            
	            }
	          }
				}],
			  yAxes:[{
				ticks:{
				  beginAtZero:true,
				  max: '.($maxYAxis + 5 ).',
				  stepSize: '.ceil(($maxYAxis + 5 ) / 5).'
				}
			  }]
			}
		  }
		}); 
	';
		
		echo $table;
		echo $chart;	
	} 
	elseif (strpos($_GET['title'], 'CumulativeSourcesCount') !== false) {
		
		// chart datasets
		$datasets = '';
		$nonEmptyCountSource =array();
		$years = range(2009, $currentYear);
		$datasets = '';
		$values = array();		

		//get the last upload data
		$data = $irts->query("SELECT `message` FROM `messages` where `process` = 'irtsDashboardData' ORDER BY `timestamp` DESC limit 1");

		$data = mysqli_fetch_all($data, MYSQLI_ASSOC );
		$result = json_decode($data[0]['message'], true);

		$itemTypes = $_GET['itemType'];
	
		// prepare the table		
		$table = ' <table class="table table-bordered" id= "TableCumulativeSourcesCount"  style="border:1px solid black;">
				  <tr>
					<th style="border:1px solid black;" >Source</th>';
					
		foreach($years as $year ){
			
			$table .= '<th style="border:1px solid black;" >'.$year.'</th>';
			
		}	

		$table .= '</tr><tr>';
		
		foreach( $itemTypes as $key => $itemType ) {
			
			$results  = $result['CumulativeSourcesCount'][$itemType];
			$table .= '<tr><td style="border:1px solid black;" >'.$itemType.'</td>';
			$counterColor = 0;
		
			foreach($results as $year => $count){
				
				$table .= '<td style="border: 1px solid black;">'.$count.'</td>';
				$values[$year][$key] = $count;

				if($count != 0 ){					
				
					if( $count  > $maxYAxis)
						$maxYAxis =  $count;				
				}
			}
		
			$table .= '</tr>';		
		}		
	
		foreach($values as $year => $counts){		
		
			$datasets .= ',{
				label: ["'.$year.'"],
				data: ["'.implode('","', $counts).'"],
				backgroundColor: "'.$colors[array_rand($colors)].'" }';
		}
		
		$table .=  '</table>';
		$chart = 'new Chart(document.getElementById("bar-chart5"), {
					type: "bar",
					  data: {
						   labels: ["'.implode('","', $itemTypes).'"],
						   datasets: [
								'.substr($datasets, 1).'
						  ]
					  }, options: {
							      legend: {
									display: false
								  },
							   scales:{
									
									yAxes:[{
										ticks:{
										  beginAtZero:true,
										
										  	max: '.($maxYAxis + 5 ).',
											stepSize: '.ceil(($maxYAxis + 5 ) / 5).'
										}
									}]
							   }
							}					  
					}); ';

		echo $table;
		echo $chart;		
	}	

// ------------------------ Rejected Items ---------------------------------------

elseif (strpos($_GET['title'], 'RejectedItems') !== false) {
		
		// init
		$rejectedItemsPerSource = array();
		$sources = array();
		$counts = array();
		$maxYAxis = 0;
		$dataColor = array();
		
		// get the rejected items per source
		$rejectedItems = getValues($irts, "SELECT `idInSource` FROM `metadata` WHERE`field` = 'irts.status' AND `value` = 'rejected' AND `deleted` IS NULL", array('idInSource'));
	
		// prepare the table
		
		$table = '<table class="table table-bordered" id= "TableRejectedItems"  style="border:1px solid black;">
				    <tr>
				<th>Source</th>
				<th>Count</th>
		  </tr>';
					
		// count the rejected Items
		foreach( $rejectedItems as $item ) {
			
			$pos = strpos($item, '_');
			$source = substr($item, 0, $pos);
			if($source){
				
				if( !isset($rejectedItemsPerSource[$source]))
					$rejectedItemsPerSource[$source] = 0;
					
				$rejectedItemsPerSource[$source]++;
			}
		}

		arsort($rejectedItemsPerSource);
		
		// prepare the table
		foreach( $rejectedItemsPerSource as $source => $count ) {
			
				$table .= '<tr>
							<td>'.$source.'</td>
							<td>'.$count.'</td>
			  			</tr>';
				$sources[] = $source;
				$counts[] = $count;
				
				if( $count  > $maxYAxis)
						$maxYAxis =  $count;
						
				// add color for each source
				if($colorIndex >= count($colors))
					$colorIndex = 0;
				
				$dataColor[] = $colors[$colorIndex];
				$colorIndex += 1;
		}
		
		$table .=  '</table>';
		$chart = ' new Chart(document.getElementById("bar-chart6"), {
		  type: "bar",

		  data: {
			labels: ["'.implode('","', $sources).'"],
			datasets: [{
			  label: "Rejected items Per Source",
			   backgroundColor: ["'.implode('","', $dataColor).'"],
			  data: ["'.implode('","', $counts).'"]
			  }],
		  },


		  options:{
			  legend: {
				labels: {
				  boxWidth: 0
				}
			},
			scales:{

			  yAxes:[{
				ticks:{
				  beginAtZero:true,
				  max: '.($maxYAxis + 5 ).',
				  stepSize: '.ceil(($maxYAxis + 5 ) / 5).'
				}
			  }]
			}
		  }
		}); ';

		echo $table;
		echo $chart;		
	}
}

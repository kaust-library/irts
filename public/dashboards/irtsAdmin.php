<?php

/*

**** This file is responsible for displaying information about how long it is taking to process items, as well as comparison information for the different sources.

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

$pageTitle = 'Metadata Review Center: IRTS Dashboard';

$pageLink = '../dashboards/irtsDashboard.php';

//start HTML
echo '<!DOCTYPE html>
<html>
<style>

table {
font-size:12px;
margin:20px;
border: 2px solid black;
border-collapse: collapse;
}

</style>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />

	<title>'.$pageTitle.'</title>

	<!-- Back Button -->
	<a style="position:absolute;top:15px;right:0;margin-right:10px;font-size: 15px;" href="../forms/reviewCenter.php"> Back &gt; </a>

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

	<!-- Chart JS -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"> </script>

	<!-- Local Dashboard CSS -->
  <link rel="stylesheet" href="../css/dashboard.css">

</head>
<body>
	<div class="container">
		<header>
		</header>
		<h2 class="text-center"><u><b><a href='.$pageLink.'>'.$pageTitle.'</a></b></u></h2>
		<hr>
	</div>

	<div id="wrap" style="width=1200; hight=1500; ">
	<div class="container" >';

// ------------------------ Average Time --------------------

// init year
$currentyear = date("Y");

// get types from the DB
$itemTypes = getValues($irts, "SELECT DISTINCT m.`value` as itemType
	FROM `metadata` m
	LEFT JOIN metadata m2 USING(idInSource)
	WHERE m.`source` LIKE 'irts'
	AND m.`field` LIKE 'dc.type'
	AND m.deleted IS NULL
	AND m2.field LIKE 'irts.status'
	AND m2.value LIKE 'inProcess'
	ORDER BY m.`value` ASC",  array('itemType'));

// Title & dropdown list
echo '<div class="jumbotron">
<h2 id="subtitles">Average time in minutes it takes a staff member to process an item</h2>
<div class="dropdown">
<select onclick="selectedType(this.value)" id="selectedTypeID" >
';

array_unshift($itemTypes, 'ALL');
foreach ($itemTypes as $itemType) {

  echo '<option value="'.$itemType.'_ProcessingTime_1" >'.$itemType.'</option>';

}

 echo '
</select>
</div>';

// chart
echo '
<div id="graph-container" style="display:none" ></div>
<div id="graph-container1" ><canvas  id="bar-chart1" width="400" height="200"></canvas></div>
<div id="TableProcessingTimeDiv"></div>';

echo '
<br><button class="button-three" id= "TableProcessingTimebutton" style="margin-left: 10px;margin-top:10px;background-color:80715D">Copy this table</button>
</div>

';

// --- average number of days between an item being marked as inProcess till it is marked Completed ------

// Title & dropdown list
echo '<div class="jumbotron">
<h2 id="subtitles">Average number of days from when an item is first harvested to when processing has been completed by library staff</h2>
<div class="dropdown">
<select  onclick="selectedType(this.value)" id="selectedTypeID" >
';

foreach ($itemTypes as $itemType) {

  echo '<option value="'.$itemType.'_AverageDays_2" >'.$itemType.'</option>';

}

 echo '
</select>
</div>';

// chart
echo '
<div id="graph-container" style="display:none" ></div>
<div id="graph-container2" >

<div class="row" style="margin:20px;" id="twoChartInRow">

  <div id="field2" class="col">
    <canvas  id="bar-chart2" width="400" height="200"></canvas>
  </div>

  <div id="field3" class="col">
    <canvas  id="bar-chart3" width="400" height="200"></canvas>
  </div>

</div>
</div>

<div id="TableAverageDaysDiv"></div>

<br><button class="button-three" id= "TableAverageDaysbutton" style="margin-left: 10px;margin-top:10px;background-color:80715D">Copy this table</button>
</div>
';

// ------------ Publication sources that are actually being used ----------------------

$sources = getValues($irts, "SELECT DISTINCT source FROM `metadata` where source IN ('".implode("','", PUBLICATION_SOURCES)."')", array('source'));

// --------------- Compare different Sources by cumulative item count ----------------

// Title & dropdown list
echo '<div class="jumbotron">
<h2 id="subtitles">Compare Different Sources by Cumulative Count </h2>
';

$halfNumberOfSources = floor( (sizeof($sources) / 2));

$designCounter = 0;

echo '<div class="row" style="margin:20px;" id="displaySources">
		<div class=" col">';  

// ----------------------- List of sources -----------------

foreach ($sources as $source) {
	
	// to display the sources beside each other 

	if($designCounter == $halfNumberOfSources){
		
		echo '</div><div  class=" col">';
		$designCounter  = 0;

	}

	echo '<div styel="margin:3px;display: inline;">
		<input class="sources"  type="checkbox" name="mycheckboxes" onclick="getCheckedBoxes()" value="'.$source.'"><p style="margin-left:3px;display: inline;">'.$source
		.'</p>
		</div>';
	
	$designCounter++;
}

echo '</div>
</div>';

// chart
echo '
<div id="graph-container" style="display:none" ></div>
<div id="graph-container4" ><canvas  id="bar-chart5" width="400" height="200"></canvas></div>
<div id="TableCumulativeSourcesCountDiv"></div>

<br><button class="button-three" id= "TableCumulativeSourcesCountbutton" style="margin-left: 10px;margin-top:10px;background-color:80715D">Copy this table</button>
</div>
';

// ------------------------------------- Rejected items -----------------------------

echo '<div class="jumbotron">
<h2 id="subtitles">Rejected items by source</h2>
';

// chart
echo '
<div id="graph-container" style="display:none" ></div>
<div id="graph-container5" ><canvas  id="bar-chart6" width="400" height="200"></canvas></div>
<div id="TableRejectedItemsDiv"></div>';

echo '
<br><button class="button-three" id= "TableRejectedItemsbutton" style="margin-left: 10px;margin-top:10px;background-color:80715D">Copy this table</button>
</div>

';

// --------------------------------------------------------------------------------------------------------------------

echo '</div>
	</div>
	</body>';

echo '<script>

// <!------------- Process and display the chart ------------->

loadchart();
function loadchart(){

selectedType("none_RejectedItems_5");
	
}

function selectedType(str) {

  var strArray = str.split("_");
  var itemType = "itemType=" + strArray[0];
  var title = strArray[1];
  var num = parseInt(strArray[2]);

  if( num === 2 ){

    var fieldNum = [2,3];

    var div = document.getElementById(("graph-container"+num));
    var prevRow = document.getElementById("twoChartInRow");
    var newRow = document.createElement("div");
    newRow.classList.add("row");
    newRow.setAttribute("id", "twoChartInRow");
    newRow.style.margin = "20px";
   //div.replaceChild(newRow, prevRow);

    div.removeChild(prevRow);
    div.appendChild(newRow);

    fieldNum.forEach(function(id){

       // var pervCol = document.getElementById("field"+id);
        var newCol = document.createElement("div");
        newCol.classList.add("col");
        newCol.setAttribute("id", "field"+id );
        newRow.appendChild(newCol);

        childId = "bar-chart"+id;
        //child = document.getElementById(childId);
        var canvas = document.createElement("canvas");
        canvas.id = childId;

        // Appdend the canvas as child to the div
    //    newCol.replaceChild(canvas, child);
         newCol.appendChild(canvas);

      });
  } if( num != 2 ) {

      // To remove the previous canvas 
	  
      var div = document.getElementById(("graph-container"+num));
	  
	  if(num >= 3 ) {
		  
		Chartnum = num + 1;
	
	  } else {
		  
		  Chartnum = num;
	  }
	  
	  var childId = ("bar-chart"+Chartnum);
			
      child = document.getElementById(childId);

     // Add new canvas to the div ( to add new line chart when division selected )
     var canvas = document.createElement("canvas");
     canvas.id = childId;
		
      // Replace the old canvas with the new one 
	 div.replaceChild(canvas, child);
	
	} // if of else statement

	sendRequest("graph-container", title, itemType);
}

 // <! --------------------------- Load the table always ------------------------------>
 
// Pass the checkbox name to the function
function getCheckedBoxes() {
		
	// To remove the previous canvas for the filtred line graph

	var div = document.getElementById(("graph-container4"));
	var childId = "bar-chart5";
	child = document.getElementById(childId);
	div.removeChild(child);

	if(typeof(cumlChart) != "undefined"){
		cumlChart.destroy();
	}
	
	// Add new canvas to the div ( to add new line chart when division selected )
	var canvas = document.createElement("canvas");
	canvas.id = childId;
	canvas.width = "400";
	canvas.height = "200";

	// Appdend the canvas as child to the div
	div.appendChild(canvas);
		
	var checkboxes = document.getElementsByName("mycheckboxes");
	var checkboxesChecked = [];
	var checkboxesCheckedstr = "";  
  
	// loop over them all
	for (var i=0; i<checkboxes.length; i++) {
		// And stick the checked ones onto an array...
		if (checkboxes[i].checked) {
			//checkboxesChecked.push(checkboxes[i].value);
			checkboxesCheckedstr += "itemType[]=" + checkboxes[i].value + "&";
		}
	}
  
	// to remove the last &
	sendRequest("graph-container", "CumulativeSourcesCount", checkboxesCheckedstr.substring(0, checkboxesCheckedstr.length-1));
}

// <! --------------------------- Send Request to jquery file ------------------------------>

function sendRequest(graphContainer, title, itemType){
	
	var xhttp;
	xhttp = new XMLHttpRequest();

	xhttp.onreadystatechange = function() {

  if (this.readyState == 4 && this.status == 200) {

    var a = this.responseText.split("</table>");

      document.getElementById(graphContainer).innerHTML =  Function(a[1])();

      var tableId = "Table"+title+"Div";
    	document.getElementById(tableId).innerHTML = a[0];

    }

  };
  xhttp.open("GET", "../components/getTableAndChart.php?"+itemType+"&title="+title, true);
  xhttp.send();

}

// <!------------- Copy Table Processing Time chart - bar chart ------------->

function selectNode(node){
  let range  =  document.createRange();
  range.selectNodeContents(node)
  let select =  window.getSelection()
  select.removeAllRanges()
  select.addRange(range)
}

let TableProcessingTimebutton = document.querySelector("#TableProcessingTimebutton");
TableProcessingTimebutton.addEventListener("click",function(){
var strTable = "#TableProcessingTime";
let table = document.querySelector(strTable);
	selectNode(table);
	 document.execCommand("copy")
});

let TableAverageDaysbutton = document.querySelector("#TableAverageDaysbutton");
TableAverageDaysbutton.addEventListener("click",function(){
var strTable = "#TableAverageDays";
let table = document.querySelector(strTable);
  selectNode(table);
   document.execCommand("copy")
});

let TableCumulativeSourcesCountbutton = document.querySelector("#TableCumulativeSourcesCountbutton");
TableCumulativeSourcesCountbutton.addEventListener("click",function(){
var strTable = "#TableCumulativeSourcesCount";
let table = document.querySelector(strTable);
  selectNode(table);
   document.execCommand("copy")
});

let TableRejectedItemsbutton = document.querySelector("#TableRejectedItemsbutton");
TableRejectedItemsbutton.addEventListener("click",function(){
var strTable = "#TableRejectedItemsDiv";
let table = document.querySelector(strTable);
  selectNode(table);
   document.execCommand("copy")
});

</script>';

include 'snippets/html/footer.php';

?>

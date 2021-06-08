<?php
/*

**** This file is responsible for displaying the open access dashboard.

** Parameters :
	No parameters required

** Created by : Daryl Grenz and Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April - 10:30 AM

*/

//--------------------------------------------------------------------------------------------
header('Content-Type: text/html; charset=UTF-8');

ini_set('display_errors', 1);

$includeDirectory = '../../';

set_include_path($includeDirectory);

//include core configuration and common function files
include_once 'include.php';

//get the last upload data
$data = json_decode(getValues($irts, "SELECT `message` FROM `messages` WHERE `process` = 'OADashboardData' ORDER BY `timestamp` DESC LIMIT 1", array('message'), 'singleValue'), TRUE);

// labels
$noFileInRepositoryLabel = 'No file deposited';
$fileDepositedLabel = 'File deposited';

//-------------------------Chart #1------------------------------
echo '<!DOCTYPE html>
<html>

<head>
	<div class="masthead">

		<img src="../images/logo.png" style="display: block;  margin-left: auto;  margin-right: auto;">

	 </div>

	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" />
    <title>'.INSTITUTION_ABBREVIATION.' Open Access Dashboard</title>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"> </script>
  <link rel="stylesheet" href="../css/dashboard.css">

</head>
<body>
	<div class="text-anim">
		<div id="container-anim">
		'.INSTITUTION_ABBREVIATION.' Open Access Dashboard
		</div>
	</div>
	<hr>

	<div id="wrap" style="width=1200; height=1500; ">
		<div class="container" >

<!–– ///////////// Overall compliance with the open access policy - First Pie Chart ///////////// -->

<div class="jumbotron" >

	<div id="canvas-holder" style="position: relative;left:200px" >
				<canvas id="pie-chart1" ></canvas>
	</div>
	<h2 id="subtitles">Deposit Rate (since adoption of open access policy)</h2>

	<table class="table table-bordered" id= "Table1" style="width:75%;">
	  <tr>
		<th>

		</th>
		<th>Number of Publications</th>
		<th>Percent</th>
	  </tr>
	  <tr>
		<td>Since '.date("F j, Y", strtotime(OAPOLICY_START_DATE)).'</td>
		<td>'.$data['total'].'</td>
		<td>100%</td>
	  </tr>
	  <tr>
		<td> <p style="background-color:#938982; display: inline;">&nbsp;&nbsp;</p> '.$fileDepositedLabel.'</td>
		<td>'.$data['countWithFile'].'</td>
		<td>'.round($data['percentWithFile']).'%</td>
	  </tr>
	  <tr>
		<td> <p style="background-color:#EE850F; display: inline;">&nbsp;&nbsp;</p> '.$noFileInRepositoryLabel.'</td>
		<td>'.$data['countNoFile'].'</td>
		<td>'.round($data['percentNoFile']).'%</td>
	  </tr>

	</table>

		    <div class="sub-main">
		      <button class="button-three" id= "button" >Copy this table</button>
		    </div>

	</div>

<!--  //////////////// The Charts script code ////////////////// -->

<script type="text/javascript">

<!----- Select the table ---- >

let button = document.querySelector("#button");
function selectNode(node){
  let range  =  document.createRange();
  range.selectNodeContents(node)
  let select =  window.getSelection()
  select.removeAllRanges()
  select.addRange(range)
}
button.addEventListener("click",function(){
	var strTable = "#Table1";
let table = document.querySelector(strTable);
  selectNode(table);
  document.execCommand("copy")

})

<!-- Since adoption of open access policy - First Pie Chart -->

new Chart(document.getElementById("pie-chart1"), {
    type: "pie",
    data: {
      datasets: [{
      	label: "",
        backgroundColor: ["#938982","#EE850F"],
        data: ['.$data['countWithFile'].','.$data['countNoFile'].']
      }]
    },
    options: {
      title: {
        display: true
      }
    }
});

</script>
';

//----------------------------------------------Chart #3--------------------------------------------------------

echo '

<!–– ///////////// Type - line Chart with filter ///////////// -->

	<div class="jumbotron">
	<h2 id="subtitles"> Deposit Rate by <u>type</u> for all years </h2>

<div class="dropdown">
<select onclick="selectChartType()" id="selectedGraphType">
';

/*
foreach(PUBLICATION_TYPES as $key => $type)
{
  echo '<option value="'.$key.'">'.$type.'</option>';
}

echo '<option value="'.($key+1).'">All Types</option>';
*/

foreach($data['ByYearAndType'] as $type => $years)
{
  echo '<option value="'.str_replace(' ', '_', $type).'">'.$type.'</option>';
}

echo '</select>
</div>
<div id="graph-container-type" > <canvas id="line-chart3" width="400" height="200"></canvas> </div>

<!–– ///////////// tables depend on the selected category ///////////// -->

';

foreach($data['ByYearAndType'] as $type => $years)
{
	$type = str_replace(' ', '_', $type);
	
	echo '<table class="table table-bordered" id="'.$type.'Table" style="display:none;width:60%;margin-left: 30px;margin-top:30px ">
	  <tr>
		<th>Year</th>
		<th>Total</th>
		<th>'.$fileDepositedLabel.'</th>
		<th>'.$noFileInRepositoryLabel.'</th>
		<th>'.$fileDepositedLabel.' percent</th>
		<th>'.$noFileInRepositoryLabel.' percent</th>
	  </tr>
	  ';
	
	foreach($years as $year => $counts)
	{
		echo '<tr>
			<td>'.$year.'</td>
			<td>'.$counts['total'].'</td>
			<td>'.$counts['withFile'].'</td>
			<td>'.$counts['noFile'].'</td>';
			
			if($counts['total']==0)
			{
				echo '<td>0%</td>
				<td>0%</td>';
			}
			else
			{
				echo '<td>'.round(($counts['withFile']/$counts['total'])*100, 2).'%</td>
				<td>'.round(($counts['noFile']/$counts['total'])*100, 2).'%</td>';
			}
			
		echo '</tr>';
	}
	echo '</table>';
}

foreach($data['ByYearAndType'] as $type => $years)
{
	$type = str_replace(' ', '_', $type);
	
	echo '<button class="button-three" id="'.$type.'Tablebutton" style="display:none;margin-left: 30px;margin-top:30px ">Copy this table</button>';
}

echo '</div>
<script type="text/javascript">

<!--   Deposit rate by type and year - line Chart with filter -->

function selectChartType(){

	// To remove the previous canvas for the filtered line graph
	var div = document.getElementById("graph-container-type");
	child = document.getElementById("line-chart3");
	//div.removeChild(child);

	// Add new canvas to the div ( to add new line chart when type selected )
 	var canvas = document.createElement("canvas");
 	canvas.id = "line-chart3";

 	// Append the canvas as child to the div
 	div.replaceChild(canvas, child);

    var e = document.getElementById("selectedGraphType");
    var strUser = e.options[e.selectedIndex].value;
	';
	
	foreach($data['ByYearAndType'] as $type => $years)
	{
		$type = str_replace(' ', '_', $type);
		
		echo 'var '.$type.'Table = document.getElementById("'.$type.'Table");
		
			'.$type.'Table.style.display = "none";
			
			var '.$type.'Tablebutton = document.getElementById("'.$type.'Tablebutton");
			
			'.$type.'Tablebutton.style.display = "none";
			
			// '.$type.'
			if ( strUser == "'.$type.'" ) {

				new Chart(document.getElementById("line-chart3"), {
				  type: "line",
				  data: {
					labels:["'.implode('","', array_keys($years)).'"],
					datasets: [{
						data: ["'. implode('","', array_column($years, 'withFile')).'"],
						label: "'.$fileDepositedLabel.'",
						borderColor:"#938982",
						fill: false
					  }, {
						data: ["'. implode('","',  array_column($years, 'noFile')).'"],
						label: "'.$noFileInRepositoryLabel.'",
						borderColor: "#EE850F",
						fill: false
					  }
					]
				  },
				  options: {
					title: {
					  display: true,
					  text: "'.$type.'"
					}
				  }
				});

				'.$type.'Table.style.display = "block";
				'.$type.'Tablebutton.style.display = "block";
			}
			';
	}

echo '} // end of the function';

foreach($data['ByYearAndType'] as $type => $years)
{
	$type = str_replace(' ', '_', $type);
	
	echo 'let '.$type.'Tablebutton = document.querySelector("#'.$type.'Tablebutton");
		'.$type.'Tablebutton.addEventListener("click",function(){
		var strTable = "#'.$type.'Table";
		let table = document.querySelector(strTable);
		selectNode(table);
		document.execCommand("copy")
	});
	';
}

echo '</script>
	</body>
	</html>';
?>

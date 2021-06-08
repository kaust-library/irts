<?php

/*

**** This file displays a form that can be used for administrators to manage item type templates.

** Parameters :
	No parameters required.

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 21 July 2020 - 12:25 AM

*/

//-------------------------------------------------------------------------------------

header('Content-Type: text/html; charset=UTF-8');

//This allows users to use the back button smoothly without receiving a warning from the browser
header('Cache-Control: no cache');
session_cache_limiter('private_no_expire');

ini_set('display_errors', 1);

$includeDirectory = '../../';

set_include_path($includeDirectory);

//include core configuration and common function files
include_once 'include.php';

$pageTitle = 'Metadata Review Center: Template Management Form';
$backButton = ' <a style="position:absolute;top:15px;right:0;margin-right:10px;font-size: 15px;" href="reviewCenter.php"> Back &gt; </a>';
$pageTitle  = $pageTitle . $backButton;

$pageLink = 'templateForm.php?selected='.$_GET['selected'];

//initialize the session.
session_start();

// check for authenticated user
if(!isset($_SESSION['username']))
{
	$location = 'reviewCenter.php';

	include_once 'snippets/login.php';	
} else {

include_once 'snippets/html/header.php';

include_once 'snippets/html/startBody.php';
	// CSS for html 

echo '
<style>

	#body-div {
		
	  border-radius: 5px;
	  background-color: #f2f2f2;
	  padding: 20px;
	  margin: 40px 100px 20px 100px;

	}
	
	.wrapper {
		
		text-align: center;
	}

	.button {
		top: 50%;
	}
	
	#selected-div{
		
		margin: 50px;
		
	}	
		
	.place-class {
		
		display:inline;
		color: #000000;
		width:25px;
		height:35px;
	
	}	
	
	.rows.over  {
	  border: 3px dotted #666;
	}

	.rows  {
	
	  cursor: move;
	}
	
</style>';

// -------------------------- Delete template ------------------------------

echo' <form action="'.$pageLink.'" method="GET" >

		<input type="hidden" name="selected" value="'.$_GET['selected'].'">
		<div  id="wrapper" >
				<button type="submit" style="margin-top: 10px;left:7.5%;position: relative;" name="deleteTempBtn" value="deleteTemp" id="deletebtn" class="btn btn-danger rounded button">DELETE TEMPLATE</button>
		</div>
	
	</form>';
if(isset($_GET['deleteTempBtn'])){		
		
		echo' <form action="'.$pageLink.'" method="post" name="templateForm" id="templateForm">
				<div class="col">
				<div class="wrapper" id="wrapper" >';		
		
		$template = $_GET['selected'];
		echo ' Are sure you want to delete <b><u> '.$template.' template</u></b>?';		
		
		echo 	'<input type="hidden" name="selected" value="'.$template.'">
				
				<div class="col">
						<button type="submit" style="margin-top: 10px;text-align:center;cursor: pointer;" id="savebtn" value="delete" name="deleteTemplateConfirmed" class="btn btn-warning rounded button">Delete</button>
						<button type="submit" style="margin-top: 10px;text-align:center;" id="savebtn" value="cancel"  name="cancelButton" class="btn btn-primary rounded button">Cancel</button>
				</div>
				</div>
		
				</form>';	
}

if(isset($_POST['deleteTemplateConfirmed'])) {	
	
	deleteFromTheTemplate(array(), $_GET['selected'], True);
	
}

// ------------------------------------------------------------------------------------


// html body 
echo '<body>
<div id="body-div" >';

// title
echo '<h2 class="text-center"><b>'.$_GET['selected'].' Template </b></h2>
	<hr>';

// -------------------------------------------------------------------------------------

if(isset($_POST['saveTempbtn'])){	

	if(isset($_POST['record'])){
		
		$record = $_POST['record'];
		saveTemplate($record, $_POST['idInSource']);		
		
		echo '
			<div class="alert alert-success" id="message">
				<p><b>Successfully saved</b></p>
				<p>The data have been saved to the database<p>
			</div>
			';		
	}	
	
	if(isset($_POST['selecteditemType'])){
		
			// save the clone template 
			$value = $_POST['selecteditemType'];
			
			// if there is no clone don't save it
			if(!empty($value))
				saveValue('irts', 'itemType_'.$_GET['selected'], 'irts.form.template', '1', $value, null);
	}	
	
	// clear the session
	$_SESSION['stepNames'] = array();
	$_SESSION['fields']  = array();
	$_SESSION['deletedStepANDFields'] = array();
}

// -------------------------------------------------------------------------------------

if(isset($_POST['removeStep'])  || isset($_POST['deleteField'])){
		
		echo' <form action="'.$pageLink.'" method="post" name="templateForm" id="templateForm">
				<div class="col">';		
		
		if(!(isset($_POST['deleteField']))) {
			$stepName = $_POST['removeStep'];
			echo ' Are sure you want to delete <b><u> '.$stepName.' Step</u></b>?';

		} else {			
		
			$fieldAndStep = explode("_", $_POST['deleteField']);
			$stepName = $fieldAndStep[1];
			echo' Are sure you want to delete <b><u> '.$fieldAndStep[0].' field</u></b>?
			<input type="hidden" name="deletedField" value="'.$fieldAndStep[0].'">';
		}
		
		echo 	'<input type="hidden" name="deleteFieldFrom" value="'.$stepName.'">
				</div>
				<div class="wrapper" id="wrapper" >
						<button type="submit" style="margin-top: 10px;text-align:center;cursor: pointer;" id="savebtn" value="delete" name="deleteButton" class="btn btn-warning rounded button">Delete</button>
						<button type="submit" style="margin-top: 10px;text-align:center;" id="savebtn" value="cancel"  name="cancelButton" class="btn btn-primary rounded button">Cancel</button>
				</div>
		
				</form>';	
}

// -------------------------------------------------------------------------------------

if(isset($_POST['addField']) ){
		
		$stepName = $_POST['addField'];
	
		echo' <form action="'.$pageLink.'" method="post" name="templateForm" id="templateForm">

			<div class="col">
			 Field Name <input style="width:300px" type="text" name="Newfield" id="fileDOI" required>
			 	 <input type="hidden" name="addFieldTo" value="'.$stepName.'">
			</div>

				<div class="wrapper" id="wrapper" >
					<button type="submit" style="margin-top: 10px;text-align:center;" id="savebtn" class="btn btn-success rounded button">Add a field</button>
			
			</div>
	
			</form>';	
}

// ------------------------------------------------------------------------------------

elseif( isset($_POST['AddNewStep'])){	
	
	echo' <form action="'.$pageLink.'" method="post" name="templateForm" id="templateForm">

		<div class="col">
		 Step Name <input style="width:300px" type="text" name="stepNames[]" id="fileDOI" required>
		</div>

	<div class="wrapper" id="wrapper" >
				<button type="submit" style="margin-top: 10px;text-align:center;" id="savebtn" class="btn btn-success rounded button">Save Step</button>
    </div>
	
	</form>';
}

// ------------------------------------------------------------------------------------

// The main page  tamplete 
elseif(isset($_GET['selected']) && !empty($_GET['selected'])  ) {	
	
	// if the template is new ask the user to enter a name 
	if($_GET['selected'] === 'New'){
		
		echo' <form action="templateForm.php" method="GET" name="templateForm" id="templateForm">

		<div class="col">
		 Template Name <input style="width:300px" type="text" name="selected" id="fileDOI" required>
		</div>

		<div class="wrapper" id="wrapper" >
					<button type="submit" style="margin-top: 10px;text-align:center;" id="savebtn" class="btn btn-success rounded button">Save Step</button>
		</div>
		
		</form>';		
	
	} else {	
	
		// if the user wants to delete step or field

		if(isset($_POST['deleteButton'])){
			
			if(strpos($_POST['deleteButton'], 'delete') !== false){				
				
				$stepName = $_POST['deleteFieldFrom'];
				
				// if the deleted was a step
				if(!isset($_POST['deletedField'])){
					
					// if the step in the session variable
					if(in_array($stepName, $_SESSION['stepNames'])){					
						
						$key = array_search($stepName, $_SESSION['stepNames']);
					
						if ($key !== false) {
							unset($_SESSION['stepNames'][$key]);
						}

						unset($_SESSION['fields'][$stepName]);
						
					} else {
						
						if(!in_array($stepName, $_SESSION['deletedStepANDFields']))
							$_SESSION['deletedStepANDFields'][$_POST['deleteFieldFrom']][] = $stepName;						
					}					
				} 
				
				// if the deleted was a field
				else {
					
					$field = $_POST['deletedField'];
					
					// if the field in the session
					if(isset($_SESSION['fields'][$stepName])) {
						
						$key = array_search($field, $_SESSION['fields'][$stepName]);
						
						if ($key !== false) {
							unset($_SESSION['fields'][$stepName][$key]);
						}
						
					} else {
						
						if(!in_array($field, $_SESSION['deletedStepANDFields']))
							$_SESSION['deletedStepANDFields'][$_POST['deleteFieldFrom']][] =  $field;						
					}				
				}				
			}
		}			

		//init
		$selected = '';
		$inputFields = array('label' => 'irts.form.label', 'baseURL' => 'irts.form.baseURL', 'note' => 'irts.form.note','inputType' => 'irts.form.inputType');
		$inputTypes = array('dropdown', 'radiobutton');
		// get the steps for the seleted template
		$idInSource = 'itemType_'.$_GET['selected'];
		
		// create session variable 
		if(empty($_SESSION['stepNames']) ){			
			
			$_SESSION['stepNames'] = array();		
		
		}
		
		if(empty($_SESSION['fields']) ){			

			$_SESSION['fields']  = array();
		
		}
		
		// create session variable
		if(empty($_SESSION['deletedStepANDFields'])){
			
			$_SESSION['deletedStepANDFields'] = array();
			
		}
		
		if(!isset($_SESSION['templateName'])){
			
			$_SESSION['templateName'] = $_GET['selected'];
			
		}
		
		// if the template changed delete all the pervious session
		if(strpos($_SESSION['templateName'],$_GET['selected']) === false){			
			
			$_SESSION['stepNames'] = array();
			$_SESSION['fields']  = array();
			$_SESSION['deletedStepANDFields'] = array();
			$_SESSION['templateName'] = $_GET['selected'];	
		
		} 		

		// cloned form template
		$clonedTemplate = getValues($irts, "SELECT value FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = '$idInSource ' AND field = 'irts.form.template' AND `deleted` IS NULL order by place ASC", array('value'), 'singleValue');

		$templates = getValues($irts, "SELECT DISTINCT `idInSource`  FROM `metadata` WHERE `source` LIKE 'irts' AND `idInSource` LIKE 'itemType_%' AND `deleted` IS NULL", array('idInSource'));		

		echo '<form action="'.$pageLink.'" method="post" name="templateForm" onclick="HideTheMessage()">
			<input type="hidden" name="idInSource" value="'.$idInSource.'">';

		echo '	<div class="dropdown"  >
		<p  style="display:inline"> Cloned from : </p>
		<select  id="selecteditemType" name="selecteditemType">';

		 echo '<option value="" ></option>';

			foreach ($templates as $template) {

				// select the cloned template to be displayed
				if($template === $clonedTemplate){
					
					$selected = 'selected';

				} else {

					$selected = '';
				}

			 echo '<option  name ="record[clonedTemplate][]"  value="'.$template.'" '.$selected.'>'.$template.'</option>';
		}

		echo '</select>
		</div>
		
		<!--- Creating new step  --->
			<p style="margin:5px 5px 5px 0px"> Add New Step 
			
			<button id="add_newStep" name="AddNewStep" class="input-group-append btn btn-success add-more" type="submit" style="display:inline;width:26px;height:25px;margin:2px;"><p style="margin:-8px 0px 100px -4px">+</p></button></p>
		
		';
		

		$stepRowIDs = getValues($irts, "SELECT `rowID`, value, place FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = '$idInSource' AND field = 'irts.form.step' AND value NOT IN ('".implode("','", $_SESSION['deletedStepANDFields'])."') AND `deleted` IS NULL order by place ASC", array('rowID', 'value', 'place'), 'arrayOfValues');
		
		// if there some steps added from the form not saved in the db yet 
		if(isset($_POST['stepNames'])){
			
			$stepNames = $_POST['stepNames'];		
		
			foreach($stepNames as $stepName){
				
				// insert the unsaved steps in the session				
				if(!in_array($stepName, $_SESSION['stepNames']))
					array_push($_SESSION['stepNames'], $stepName);
			
			}
		} 
		
		// add the step to the session
		// to display the steps add it to the stepRowIDs
		foreach($_SESSION['stepNames'] as $unsavedStep){
			
			array_push($stepRowIDs, array('rowID'=> -1, 'value'=> $unsavedStep, 'place' => count($stepRowIDs)+1));
			
		}
		
		if(isset($_POST['Newfield'])){
			
			$field = $_POST['Newfield'];			
		
			// if the user added field to this step before
			if(isset($_SESSION['fields'][$_POST['addFieldTo']])){
				
				// check if the field is already exists
				if(!in_array($field, $_SESSION['fields'][$_POST['addFieldTo']]))
					$_SESSION['fields'][$_POST['addFieldTo']][] =  $field;
					
			} else {
				
				$_SESSION['fields'][$_POST['addFieldTo']][] = $field;				
				
			}			
		}		
		
		echo '<div id="TheBoxForStep" class="containerForTheBox" >';
		// display
		foreach ($stepRowIDs as $stepRowID ) {			
		
			echo '<div draggable="true"  class=" container rowStepId" id="container_'.$stepRowID['value'].'" style="border: 2px solid;margin-bottom:10px; ">
			
					'.$stepRowID['value'].' :   <input type= "field" name ="record['.$stepRowID['value'].'][place]"  class="form-control" id="place'.$stepRowID['value'].'" value= "'.$stepRowID['place'].'" placeholder="Step place" title="place" style="color: #000000;hight:40px;width:40px;display:inline">					
					
					<div class="input-group" id="'.$stepRowID['value'].'" >
						
						<!--- delete a step  --->
					
							<button id="remove_'.$stepRowID['value'].'" class="input-group-append btn btn-danger remove-me" style="width:5px;height:25px;margin:2px"  
								value= "'.$stepRowID['value'].'" name ="removeStep" type="submit" ><p style="margin:-8px 0px 100px -4px">-</p></button>

								
						<!--- Add a new field  --->
							
							
							<button id="add_'.$stepRowID['value'].'" class="input-group-append btn btn-success add-more"  style="width:26px;height:25px;margin:2px;" 
								value= "'.$stepRowID['value'].'" name="addField" type="submit" ><p style="margin:-8px 0px 100px -4px">+</p></button>
						
					</div>
					<br>' ;

			// get the fields for the step
			$fields = getValues($irts, "SELECT `value` FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = '$idInSource ' AND  parentRowID = '".$stepRowID['rowID']."' AND field = 'irts.form.fields' AND value NOT IN ('".implode("','", $_SESSION['deletedStepANDFields'])."') AND `deleted` IS NULL ", array('value'));			
			
			// add the fields to the session
			if(!empty($fields)){
				
				if(strpos($fields[0], ',') !== false){

					$fields = explode(',', $fields[0]);
					
					// if the deleted field is in array with multipa
					$fields = array_diff($fields, $_SESSION['deletedStepANDFields']);
				}
			} 
			
			if(isset($_SESSION['fields'][$stepRowID['value']] )) {
				
				$fields = array_merge($fields,  $_SESSION['fields'][$stepRowID['value']]);			
				
			}
			
			echo '<div id="TheBoxForField" class="containerForTheBox" >';
			// for each fields disply the label and the field
			foreach ($fields as $value) {
		
				// get rowID for each individual field
				$rowID = getValues($irts, "SELECT `rowID` FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = '$idInSource' AND field = 'irts.form.field' AND value = '$value' AND `deleted` IS NULL ", array('rowID'), 'singleValue');
				
				if(!empty($rowID) || !empty($fields)) {

					// if the step added throught the form				
					
					// starting the container for to display the field's information 
					echo '<div draggable="true" id="changeValueToField"  class="rows row rowFieldId" style="margin:20px;" id="row_'.$value.'_field">
							
								 <div id="field" class="col">
									<input type="field" class="form-control" id="field" title="field" name ="record['.$stepRowID['value'].']['.$value.'][field][]" value= "'.$value.'" placeholder="field"  style="color: #000000;">
								</div>

							   ';

					foreach ($inputFields as $key => $inputField) {

						$inputValue = getValues($irts, "SELECT `value` FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = '$idInSource' AND field = '".$inputField."' AND parentRowID = '".$rowID."' AND `deleted` IS NULL ", array('value'), 'singleValue');

						if(strpos($inputField, 'irts.form.inputType') === false) {

							echo '<div id="'.$key.'" class="col">
										 <input type= "field" name ="record['.$stepRowID['value'].']['.$value.']['.$key.'][]"  class="form-control" id="'.($value.'_'.$key).'" value= "'.$inputValue.'" placeholder="'.$key.'" title="'.$key.'" style="color: #000000;">
								   </div>';

						} else {

								echo '<div id="'.$key.'" class="col">
										<div class="dropdown"  >
									<select  id="selecteditemType'.$value.'" name ="record['.$stepRowID['value'].']['.$value.'][inputType][]" title="inputType" style="height:35px;width:150px" >';

									if(empty($inputValue))
										$selected = 'selected';
									else
										$selected = '';

									echo '<option value="" '.$selected.'></option>';

								foreach ($inputTypes as $inputType) {

										$selected  = '';

										if(strpos($inputType, $inputValue) !== false){

											$selected = 'selected';

										} 

									echo '<option value="'.$inputType.'" '.$selected.'>'.$inputType.'</option>';
								}

								echo '</select>
								</div>
								 </div>';
						}
					}
					
					$inputFieldValues = getValues($irts,"SELECT `value` FROM `metadata` WHERE `source` = 'irts' AND field = 'irts.form.values' AND parentRowID = '".$rowID."' AND `deleted` IS NULL ", array('value'), 'singleValue');
					
					if(empty($inputFieldValues)){
						
						$title = "Add the values separated by comma";
					
					} else {
						
						$title = $inputFieldValues;
					
					}
					
					// Add values for the inputType
					echo '<div id="'.$key.'" class="col">
							<input type= "field" name ="record['.$stepRowID['value'].']['.$value.'][inputValue][irts.form.values][]"  class="form-control" id="irts.form.values" title="'.$title.'" placeholder="Add the values separated by comma" value="'.$inputFieldValues.'" style="color: #000000;">
						</div>';

					// end of displaying the template's info
					 echo '	<!--- delete field  --->
					
								<button id="remove_'.$value.'_field" value="'.$value.'_'.$stepRowID['value'].'"
								class="input-group-append btn btn-danger remove-me" style="width:5px;height:25px;margin:3px;display:inline" name="deleteField" ><p style="margin:-8px 0px 100px -4px">-</p></button>
								
						</div> ';
				}
			}

			echo '</div></div>';
		}

	// echo '<div id="container_Step" ></div>';

		echo '</div><div class="wrapper" id="wrapper" >
					<button type="submit" style="margin-top: 10px;text-align:center;" name="saveTempbtn" class="btn btn-success rounded button">Save Template</button>
				</div>
			</form>';
	}
} 
	echo '</body>';

	echo '<script>
	
	document.addEventListener("DOMContentLoaded", (event) => {

	  var dragSrcEl = null;
	  
	  function handleDragStart(e) {
		this.style.opacity = "0.4";
		
		dragSrcEl = this;

		e.dataTransfer.effectAllowed = "move";
		e.dataTransfer.setData("text/html", this.innerHTML);
	  }

	  function handleDragOver(e) {
		if (e.preventDefault) {
		  e.preventDefault();
		}

		e.dataTransfer.dropEffect = "move";
		
		return false;
	  }

	  function handleDragEnter(e) {
		this.classList.add("over");
	  }

	  function handleDragLeave(e) {
		this.classList.remove("over");
	  }

	  function handleDrop(e) {
		if (e.stopPropagation) {
		  e.stopPropagation(); // stops the browser from redirecting.
		}
		
		if (dragSrcEl != this) {
		  dragSrcEl.innerHTML = this.innerHTML;
		  this.innerHTML = e.dataTransfer.getData("text/html");
		}
		
		return false;
	  }
	  
	  
     function handleDragEnd(e) {
		this.style.opacity = "1";
		
		items.forEach(function (item) {
		  item.classList.remove("over");
		});
	  }
	 
	  let items = document.querySelectorAll(".containerForTheBox .rows");
	  items.forEach(function(item) {
		item.addEventListener("dragstart", handleDragStart, false);
		item.addEventListener("dragenter", handleDragEnter, false);
		item.addEventListener("dragover", handleDragOver, false);
		item.addEventListener("dragleave", handleDragLeave, false);
		item.addEventListener("drop", handleDrop, false);
		item.addEventListener("dragend", handleDragEnd, false);
	  });
	});

	function HideTheMessage() {	

		var message = document.getElementById("message");
		if(message){
			document.getElementById("message").style.display = "none";
		}		
	}
	</script>';

	include 'snippets/html/footer.php';
}

// if the form if empty and there is only one step to delete
if(!empty($_SESSION['deletedStepANDFields'] ) && !isset($_POST['record'])) {
	
	// print_r($_SESSION['deletedStepANDFields'] );
	deleteFromTheTemplate($_SESSION['deletedStepANDFields'], $_GET['selected']);
	$_SESSION['deletedStepANDFields'] = array();	
	
}

?>	
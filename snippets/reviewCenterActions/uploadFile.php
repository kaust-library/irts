<?php

ini_set('display_errors', 1);

$includeDirectory = '../../';

set_include_path($includeDirectory);

//include core configuration and common function files
include_once 'include.php';

// style the header
include_once 'snippets/html/header.php';

//init
$form = '';
$currentDir = '/data/www/irts/public_html/';
$upload_path = 'upload/';

// database
global $irts;

$formHeader = 'Upload a file to Dspace from your computer ..';
					
echo '<div class="container">'.$formHeader.'<hr></div>';

// init the form 
$form .='	<style>

	.form-control{

		width: 70%;
		color:#000000;
		font-size:15px;
	}

	.form-group{
		width:30%;
		padding-top:10px;
	}

	.submit{
		
		margin:10px 0px 0px 0px;
		width:8%;
		height:4%;
		font-size:80%;
	}

	.btn{
		left:30px;
	}
	</style>
			<div class="container">

				<div class="jumbotron">

	<h1 class="h3 mb-3 font-weight-normal" style="padding-top:10px"></h1>

	<div class="container">';

 
if(isset($_GET['Message'])){

	if($_GET['Message'] == 'IncorrectDOI' ) {
		$form .='
		<div class="alert alert-danger" id="message">
			<p><b>Warning</b></p>
			<p>Incorrect DOI or handle.<p>
		</div>
		';
		
	} elseif($_GET['Message'] == 'IncorrectExtension') {
		
		$form .='
		<div class="alert alert-danger" id="message">
			<p><b>Warning</b></p>
			<p>Incorrect Extension ( only accept PDFs ).<p>
		</div>
		';
		
	}else{

		$form .='
		<div class="alert alert-danger" id="message">
			<p><b>Warning</b></p>
			<p>Please, enter a DOI or handle<p>
		</div>
		';

	}
}

if(!isset($_POST['uploadSelections'])) {

	$uploadSelections = 'formType=uploadFile';
}else{

	$uploadSelections = $_POST['uploadSelections'];
}


# upload the file to database and to the dspace with the embrog if there ( Steps )
if( !(isset($_POST['doi'])) &&  !isset($_FILES['file']['tmp_name']) && !isset($_POST['uploadToDspace']) && !isset($_POST['transferType']) ){
	// diplay the html


	$form .= '
		<form action="reviewCenter.php?formType=uploadFile" method="post" name="fileUpload" id="pdfImport" enctype="multipart/form-data" >
			 <div class="row">';

			if(!isset($_POST['idInIRTS']) && !isset($_POST['handle'])) {

				$form .= '
				<div class="col">
					 <input onclick="HideTheMessage()" placeholder="DOI"  type="text" name="doi" id="fileDOI" >
				</div>

				<div class="col">
					 <input onclick="HideTheMessage()" placeholder="handel"  type="text" name="handle" id="fileHandle" >
				</div>


					';

			}else{

				$doi =  getValues($irts, "SELECT `value`  FROM `metadata` WHERE `idInSource` = '".$_POST['idInIRTS']."' and `source` = 'irts' and `field` = 'dc.identifier.doi' and `deleted` IS NULL", array('value'), 'singleValue');
				$form .='<input type="hidden" name="doi" value="'.$doi.'">';
			}

		


			if(isset($_POST['handle'])){

				$handle = $_POST['handle'];
				$form .= '<input type="hidden" name="handle" value="'.$handle.'">';

			}


			if(isset($_POST['idInIRTS'])){

				$idInIRTS = $_POST['idInIRTS'];
				$form .= '<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">';

			}



			if(isset($_POST['itemID'])){

				$form .= '<input type="hidden" name="itemID" value="'.$_POST['itemID'].'">';

			}


					$form .='
	
						<div class="w-100"></div>
							
						<div class="col" style="left:3%">
						<input onclick="HideTheMessage()"  style="padding-top:40px;left:40%" type="file" name="Uploadfile[]" id="files" accept=".pdf" style="text-align:center" required multiple>

						</div>

						<div class="col">

						<input type="hidden" name="uploadToDspace" value="uploadToDspace">
						<input type="hidden" name="uploadSelections" value="'.$uploadSelections.'" >
						<div class="col" style="padding-top:40px;left:4%"  id="buttonContainer">
						 <button  type="submit" id="submit" name="upload" height="42" width="42" class="btn btn-info"  >Next</button>
						</div>

			</div>
		</form>';
	}


// get the DOI and seach for the data
elseif( isset($_POST['doi']) &&  isset($_FILES['Uploadfile']['tmp_name']) && isset($_POST['uploadToDspace']) && !isset($_POST['transferType'])  ) {
	
	



		// init
		$doi = '';
		$handle = '';
		$itemID ='';

		// by defult
		$source = 'dspace';
		$id = '';

	 	$form .= '<form action="reviewCenter.php?formType=uploadFile" method="post" name="fileUpload" id="pdfImport" enctype="multipart/form-data" required>
		<div class="row">';


		if(!empty($_POST['itemID'])){

	
			$doi =  getValues($irts, "SELECT `value`FROM `metadata` WHERE `idInSource`  = '".$_POST['itemID']."' and `source` = '".$source."' and `field` = 'dc.identifier.doi' and `deleted` IS NULL", array('value'), 'singleValue');
			$itemID = $_POST['itemID'];


		}
		else
		{
			
			if(!empty($_POST['doi'])) {

				$doi = $_POST['doi'];
				$itemID =  getValues($irts, "SELECT `idInSource` FROM `metadata` WHERE `value` = '".$doi."' and `source` = '".$source."' and `field` = 'dc.identifier.doi' and `deleted` IS NULL", array('idInSource'), 'singleValue');
			
			}else{

				$handle =  $_POST['handle'];
				$itemID = getValues($irts, "SELECT `idInSource` FROM `metadata` WHERE `value` =  ( SELECT `value` FROM `metadata` WHERE `source` LIKE 'repository' AND `idInSource` = '".$handle."' AND `field` = 'dc.identifier.doi' AND `deleted` IS NULL limit 1) and `source` = '".$source."'and `field` = 'dc.identifier.doi' and `deleted` IS NULL ", array('idInSource'), 'singleValue');
			}
		}

		
		// if the DOI is incorrect 
		if(!empty($itemID )) {
			
				//save the file
				$countfiles = count($_FILES['Uploadfile']['name']);
				
				$embargodate =  getValues($irts, setSourceMetadataQuery($source, $itemID, NULL, 'dc.rights.embargodate'), array('value'), 'singleValue');

				if(empty($handle)){
					$handleURL =	getValues($irts, "SELECT `value` as handle FROM `metadata` 
									WHERE `source` LIKE '".$source."' 
									AND `field` = 'dc.identifier.uri'
									AND `idInSource` like '".$itemID."'
									AND deleted IS NULL limit 1", array('handle'), 'singleValue');

					$handle = str_replace('http://hdl.handle.net/', '', $handleURL);
				} else {

					$handleURL = 'http://hdl.handle.net/'.$handle;
					
				}		

				
				// display the information for this DOI
				$citation = '<div class="col"><p><b> Title</b>: '.getValues($irts, setSourceMetadataQuery($source, $itemID, NULL, 'dc.title'), array('value'), 'singleValue').'<br>'.' 

				<b>Authors</b>: '.implode('; ', getValues($irts, setSourceMetadataQuery($source, $itemID, NULL, 'dc.contributor.author'), array('value'))).'<br>'.' 

				<b>Journal</b>: '.getValues($irts, setSourceMetadataQuery($source, $itemID, NULL, 'dc.identifier.journal'), array('value'), 'singleValue').'<br>'.'  

				<b>Publication Date</b>: '.getValues($irts, setSourceMetadataQuery($source, $itemID, NULL, 'dc.date.issued'), array('value'), 'singleValue').'<br>'.' 

				<b>Embargo date</b>: '.$embargodate.'<br>'.'

				<b>DOI: </b>'.$doi.'<br>
				<b>Handle: </b><a href="'.$handleURL.'">'.$handleURL.'</a></p><br>
				
				<input type="hidden" name = "embargodate" value="'.$embargodate.'">
				<input type="hidden" name = "handle" value="'.$handle.'">
				<input type="hidden" name = "action" value="uploadFile">
				<input type="hidden" name="uploadSelections" value="'.$uploadSelections.'">
				<input type="hidden" name="itemID" value="'.$itemID.'">
				
				<input type="hidden" name="transferType" value="addFileByURL">';
				
				$form .= $citation;
		
				 for($i=0;$i<$countfiles;$i++) {
					
					$fileName = $_FILES['Uploadfile']['name'][$i];
					
					if(strpos($fileName, '.pdf') !== false) {
					
						move_uploaded_file($_FILES['Uploadfile']['tmp_name'][$i], $currentDir.$upload_path.$fileName);
						
						
						sleep(2);
						$fileURL = 'https://'.$_SERVER['HTTP_HOST'].'/irts/'.$upload_path.$fileName;
						$form .= '<b>File name: </b><a href="'.($fileURL).'">'.$fileName.'</a></p>
						';
	

						$form .= '<input type="hidden" name="fileURLs[]" value="'.$fileURL.'">
								<input type="hidden" name = "fileName[]" value="'.$fileName.'">';

					} else {
						// return to the main page with warning

						$Message = 'IncorrectExtension';
						header("Location: reviewCenter.php?formType=uploadFile&Message=" . urlencode($Message));
						die();
						
					}
			
			}
			
			$form .= '
					<button  type="submit" id="submit" name="upload" class="btn btn-info"  >Upload</button>
						</div>
							</div>
								</form>';
		
			
		} else{
			// return to the main page with warning

			$Message = 'IncorrectDOI';
			header("Location: reviewCenter.php?formType=uploadFile&Message=" . urlencode($Message));
			die();
		}

	} // check if the file is uploaded
		elseif(isset($_POST['fileURLs'])  ) {

		
		// assign the information 
		$itemID = $_POST['itemID'];
		$handle = $_POST['handle'];
		$_GET['itemType'] = 'uploadfile';
		$record = array( 'dc.rights.embargodate' => array(0=>$_POST['embargodate']));

		// transfer tha file to Dspace 
		include_once "snippets/reviewCenterActions/transfer.php";

		$form .= '<form action=" reviewCenter.php?'.$uploadSelections.'" method="post" name="fileUpload" id="pdfImport" enctype="multipart/form-data" required>
		 <div class="row">

			<div class="col">
				 '.$message.'
			</div>
	
			<div class="w-100"></div>
				
			<div class="col">
			</div>

			<div class="col">

			</div>
			<div class="col" style="padding-top:40px;left:10%" id="buttonContainer">
			 <button  type="submit" id="submit" name="upload"
				class="btn btn-info"  >Next item</button>
			</div>

		</div>
		</form>';





			// delete the file from the  upload dirctory 
			$fileNames = $_POST['fileName'];
			foreach($fileNames as $fileName){
				unlink($currentDir.$upload_path.$fileName);	
			}



	}



	$form .= '	</div>  
	</div>
	</div>
	<script>


		function HideTheMessage() {	

			var message = document.getElementById("message");
			if(message){
				document.getElementById("message").style.display = "none";
			}
	
		}


	</script>';

	echo $form;
	
	// style the footer
	include_once 'snippets/html/footer.php';

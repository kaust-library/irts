<?php

/*

**** This function is responsible for displaying the interface for adding a DOI to record and harvesting the related metadata.

** Parameters :
	No parameters required.

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 20 January 2020 - 3:52 PM

*/


// check if the DOI not empty
if(isset($_POST['doi']) && preg_match('/10.\w*\/\w*/', $_POST['doi']))
{
	$report = '<br>Add DOI - DOI: '.$doi.'<br>';
	$doi = $_POST['doi'];

	// harvest the metadata
	$sourceData = retrieveCrossrefMetadataByDOI($doi, $report);

	if(identifyRegistrationAgencyForDOI($doi, $report)==='crossref')
	{
		$sourceData = retrieveCrossrefMetadataByDOI($doi, $report);

		if(!empty($sourceData))
		{
			$recordType = processCrossrefRecord($sourceData, $report);

			$report .= ' - '.$recordType.'<br>';

			// save the DOI to the IRTS process record
			$rowID = saveValue('irts', $_POST['idInIRTS'], 'dc.identifier.doi', 1, $doi, NULL);

			// add the doi to the source record
			$rowID = saveValue($_POST['source'], $_POST['id'], 'dc.identifier.doi', 1, $doi, NULL);

			$url = 'reviewCenter.php?'.str_replace(' ', '+', $_POST['URL']).'&idInIRTS='.$idInIRTS;

			$report = " - The DOI has been added. Click on the link to process the item: <a href='".$url."'>$idInIRTS</a><br>";

			echo $report ;
		}
	}
}
else
{
	//	# print the form
	$strpos = strpos($idInIRTS, '_');
	$source = substr($idInIRTS, 0 , $strpos);
	$id = substr($idInIRTS, $strpos + 1);

	$type = getValues($irts, setSourceMetadataQuery($source, $id, NULL, 'dc.type'), array('value'), 'singleValue');
	$title = getValues($irts, setSourceMetadataQuery($source, $id, NULL, 'dc.title'), array('value'), 'singleValue');

	$searchTitle = str_replace(' ', '+', $title);
	$authors = getValues($irts, setSourceMetadataQuery($source, $id, NULL, 'dc.contributor.author'), array('value'), 'arrayOfValues');
	
	$journal = getValues($irts, setSourceMetadataQuery($source, $id, NULL, 'dc.identifier.journal'), array('value'), 'singleValue');

	$citation = $title.' '.implode('; ', $authors).' '.$journal.' '.getValues($irts, setSourceMetadataQuery($source, $id, NULL, 'dc.date.issued'), array('value'), 'singleValue');

	//echo $citation;

	echo '
	<div class="col-sm-12 alert-warning border border-dark rounded">
	<br>
	<b> -- Item details:</b>
		<ul>
		  <li> <b>Type:</b> '.$type.'</li>
		  <li> <b>Title:</b> '.$title.'</li>
		  <li> <b>Authors:</b> '.implode('; ', $authors).'</li>
		  <li> <b>Journal or Conference:</b> '.$journal.'</li>
		</ul>

	<b> -- Important Guidelines : </b> Below are the steps that can be used to obtain DOIs: <br>
			<ol>
			  <li> Check Crossref by using this link to <a href="https://search.crossref.org/?q='.$searchTitle.'"> Search By Title </a>.</li>
			  <li> If there is no match, use this link to <a href="https://search.crossref.org/?q='.$citation.'"> Search By Citation </a> in Crossref.</li>

			</ol>

	<b> -- Check the DOI : </b>
	  Please make sure that the item\'s type and authors match the details associated with the DOI.<br>

		<ul style="list-style-type:none;">
		  <li>To check that go to <a href="https://dx.doi.org/"> Resolve a DOI</a> and enter the DOI in the search box.</li>
		</ul>

	<br>
	</div>
	
	<br>
	
	<div class="col-lg-6">
	<form  action="reviewCenter.php?'.$selections.'" method="POST" enctype="multipart/form-data" autocomplete="off">

	  <label for="doi">DOI:</label>
	  <input placeholder="10.xxxxx/xxxxxxx" class="form-control" rows="1" name="doi" required></input>
	  <br>
	  <input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
	  <input type="hidden" name="source" value="'.$source.'">
	  <input type="hidden" name="id" value="'.$id.'">
	  <input type="hidden" name="URL" value="'.$selections.'">
	<button class="btn btn-primary" type="submit" name="action" value="addItemDOIManually">Add DOI To The Record</button>
	</form>
	</div>
	
	<br>
	
	<div class="col-sm-12 alert-danger border border-dark rounded">
	<br>
	<b> -- If you can not find a DOI, </b> try to locate the publisher landing page for the item and <b>add the link in the "Related URL" field</b> in the main form <br>
			<ol>
			  <li> <a href="https://scholar.google.com/scholar?hl=en&as_sdt=0%2C5&q='.$searchTitle.'"> Search Google Scholar by Title by using this link</a>.</li>
			  <li> <a href="https://www.google.com/search?source=hp&q=%22'.$searchTitle.'%22"> Search Google by Title by using this link</a>.</li>
			  <li> Sometimes searching by title will not find the right page, but searching the publisher website directly will, to find the publisher site you can <a href="https://www.google.com/search?source=hp&q=%22'.str_replace(' ', '+', $journal).'%22"> Search Google by Journal or Conference Name by using this link</a>. You will then need to open the publisher site and search it directly by title.</li>
			</ol>
	<br>
	</div>
	';
}

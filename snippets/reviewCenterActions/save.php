<?php
	//print_r($record);
	
	include_once "snippets/saveValues.php";

	if( $_GET['itemType'] == 'Unpaywall')
	{
		// check if there is more than one ID in IRTS with this DOI
		$irtsIDs =  getValues($irts, "SELECT `idInSource` FROM `metadata` WHERE `source` = 'irts' AND `field` = 'dc.identifier.doi' AND `value` = '".$_POST['doi']."' AND `deleted` IS NULL ORDER BY `added` ASC", array('idInSource'), 'arrayOfValues');

		foreach ($irtsIDs as $idInIRTS)
		{
			$result = saveValue('irts', $idInIRTS, 'irts.check.unpaywall', 1, 'completed' , NULL);
			$parentRowID = $result['rowID'];

			$result = saveValue('irts', $idInIRTS, 'irts.processedBy', 1, $reviewer, $parentRowID);
		}
	}
	else
	{
		$result = saveValue('irts', $idInIRTS, 'irts.status', 1, 'completed' , NULL);
		$parentRowID = $result['rowID'];

		$result = saveValue('irts', $idInIRTS, 'irts.processedBy', 1, $reviewer, $parentRowID);
	}

	if(isset($_SESSION["variables"]["startTime"]))
	{
		//Set endTime for this item
		$endTime=date("Y-m-d H:i:s");

		$timeStart = new DateTime($_SESSION["variables"]["startTime"]);
		$timeEnd = new DateTime($endTime);
		$timeElapsed = $timeStart->diff($timeEnd)->format("%H:%I:%S");

		$result = saveValue('irts', $idInIRTS, 'irts.process.timeElapsed', 1, $timeElapsed, $parentRowID);
	}

	echo '<br>The full metadata record for this item has been saved locally, no metadata or files have yet been transferred to DSpace.';

	$updateButton = '<form method="post" action="reviewCenter.php?'.$selections.'">
					<input type="hidden" name="page" value="'.($page).'">
					<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
				<input type="hidden" name="handle" value="{handle}">
					<input type="hidden" name="transferType" value="updateAllMetadata">
					<button class="btn btn-lg btn-warning" type="submit" name="action" value="transfer">Update all metadata in the existing DSpace record</button>
					</form>';

	echo listExistingRecords($record, $updateButton);

	if($_GET['itemType']!='Unpaywall')
	echo '<form method="post" action="reviewCenter.php?'.$selections.'">
		<input type="hidden" name="transferType" value="createNewItem">
		<input type="hidden" name="page" value="'.($page).'">
		<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
		<button class="btn btn-lg btn-success" type="submit" name="action" value="transfer">Add as new item in DSpace</button>
	</form>';

	//echo $message;

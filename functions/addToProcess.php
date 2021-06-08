<?php	
/*

**** This file is responsible for adding a new entry to IRTS for processing if there is no existing matching entry.

** Parameters :
	$source : name of the source system.
	$idInSource : id of this record in the source system.
	$idToCheck : id used to check for existing records from other sources, may be different from the idInSource
	$idField : standard field name in the format namespace.element.qualifier .
	
** Output : returns the status of the entry (existing, inProcess).

** Created by : Daryl Grenz
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 07 November 2019 - 3:21 PM

*/

//------------------------------------------------------------------------------------------------------------

	function addToProcess($source, $idInSource, $idToCheck, $idField, $itemType)
	{			
		global $irts;
		
		//default status to return
		$status = 'existing';
		
		$existingRecords = checkForExistingRecords($idToCheck, $idField, $report);

		if(empty($existingRecords))
		{
			//Check for existing IRTS entry
			$irtsID = $source.'_'.$idInSource;

			$query = "SELECT `idInSource` FROM `metadata` WHERE source LIKE 'irts' AND idInSource LIKE '$irtsID'";

			$check = $irts->query($query);

			if($check->num_rows === 0)
			{
				$status = 'inProcess';

				$result = saveValue('irts', $irtsID, 'dc.type', 1, $itemType, NULL);

				$result = saveValue('irts', $irtsID, 'irts.status', 1, $status, NULL);

				$result = saveValue('irts', $irtsID, $idField, 1, $idToCheck, NULL);
			}
		}
		return $status;
	}	

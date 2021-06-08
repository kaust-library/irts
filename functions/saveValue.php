<?php
/*

**** This file is responsible for saving an individual value to the metadata table after checking whether it is a replacement for an existing value.

** Parameters :
	$source : name of the source system.
	$idInSource : id of this record in the source system.
	$field : standard field name in the format namespace.element.qualifier .
	$place : the order of the values.
	$value : the metadata value.
	$parentRowID : if row is the child of another row, this will be the parent row's rowID, otherwise it will be NULL.

** Output : returns an associative array with the rowID of the value and its status (new, updated, unchanged).

** Created by : Daryl Grenz
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019 - 1:30 PM

*/

//------------------------------------------------------------------------------------------------------------

	function saveValue($source, $idInSource, $field, $place, $value, $parentRowID)
	{
		global $irts;

		//empty row id and status to return if error
		$rowID = NULL;
		$status = NULL;

		$existingValue = '';
		$existingRow = '';

		if(is_string($value))
		{
			$value = trim($value);
		}

		//check for existing entry
		if($parentRowID === NULL)
		{
			$check = select($irts, "SELECT rowID, value FROM metadata WHERE source LIKE ? AND idInSource LIKE ? AND parentRowID IS NULL AND field LIKE ? AND place LIKE ? AND deleted IS NULL", array($source, $idInSource, $field, $place));
		}
		else
		{
			$check = select($irts, "SELECT rowID, value FROM metadata WHERE source LIKE ? AND idInSource LIKE ? AND parentRowID LIKE ? AND field LIKE ? AND place LIKE ? AND deleted IS NULL", array($source, $idInSource, $parentRowID, $field, $place));
		}

		//if not existing
		if(mysqli_num_rows($check) === 0)
		{

			insert($irts, 'metadata', array('source', 'idInSource', 'parentRowID', 'field', 'place', 'value'), array($source, $idInSource, $parentRowID, $field, $place, $value));
			$rowID = $irts->insert_id;

			$status = 'new';
		}
		else
		{
			$row = $check->fetch_assoc();
			$existingValue = $row['value'];
			$existingRowID = $row['rowID'];

			//insert if value changed
			if($existingValue != $value)
			{
				insert($irts, 'metadata', array('source', 'idInSource', 'parentRowID', 'field', 'place', 'value'), array($source, $idInSource, $parentRowID, $field, $place, $value));

				$newRowID = $irts->insert_id;
				update($irts, 'metadata', array("deleted", "replacedByRowID"), array(date("Y-m-d H:i:s"), $newRowID, $existingRowID), 'rowID');

				//mark any children of the existing row as deleted as well
				markExtraMetadataAsDeleted($source, $idInSource, $existingRowID, '', '', '');

				$rowID = $newRowID;

				$status = 'updated';
			}
			else
			{
				//in this case the row ID may be needed as the parentRowID for a child element whose value has changed
				$rowID = $existingRowID;
				$status = 'unchanged';
			}
		}
		return array('rowID'=>$rowID, 'status'=>$status);
	}

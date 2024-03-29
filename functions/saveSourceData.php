<?php
	//Define function to save original XML, JSON or HTML (what about PDF?) data from any source
	function saveSourceData(&$sourceReport, $source, $idInSource, $sourceData, $format)
	{
		global $irts, $errors;

		//check for existing entry
		$check = select($irts, "SELECT rowID, sourceData FROM sourceData WHERE source LIKE ? AND idInSource LIKE ? AND deleted IS NULL", array($source, $idInSource));

		//if not existing
		if(mysqli_num_rows($check) === 0)
		{
			$recordType = 'new';

			if(!insert($irts, 'sourceData', array('source', 'idInSource', 'sourceData', 'format'), array($source, $idInSource, $sourceData, $format)))
			{
				$error = end($errors);
				$sourceReport .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
			}
		}
		else
		{
			$row = $check->fetch_assoc();
			$existingData = $row['sourceData'];
			$existingRowID = $row['rowID'];
			
			//if sourceData has changed, mark old sourceData as replaced
			if($existingData !== $sourceData)
			{
				$recordType = 'modified';

				if(!insert($irts, 'sourceData', array('source', 'idInSource', 'sourceData', 'format'), array($source, $idInSource, $sourceData, $format)))
				{
					$error = end($errors);
					$sourceReport .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
				}
				$newRowID = $irts->insert_id;

				if(!update($irts, 'sourceData', array("deleted", "replacedByRowID"), array(date("Y-m-d H:i:s"), $newRowID, $existingRowID), 'rowID'))
				{
					$error = end($errors);
					$sourceReport .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
				}
			}
			else
			{
				$recordType = 'unchanged';
			}
		}
		return $recordType;
	}

<?php
	//Define function to harvest information from local tables or databases
	function harvestLocal($source)
	{
		global $irts, $ioi, $errors;

		$report = '';

		$errors = array();

		//Record count variable
		$recordTypeCounts = array('all'=>0,'new'=>0,'updated'=>0,'deleted'=>0,'unchanged'=>0,'skipped'=>0);

		//Get date to check or else use today's date
		if(isset($_GET['date']))
		{
			$date = $_GET['date'];
		}
		else
		{
			$date = TODAY;
		}
		
		//Get date to check or else use today's date
		if(isset($_GET['mode']))
		{
			$modes = array($_GET['mode']);
		}
		else
		{
			$modes = array('added','deleted');
		}

		if(in_array('added',$modes))
		{
			$result = $ioi->query("SELECT * FROM `metadata` 
				WHERE `source` LIKE '$source' 
				AND `parentRowID` IS NULL
				AND `deleted` IS NULL
				AND added LIKE '$date%'");

			while($row = $result->fetch_assoc())
			{
				$recordTypeCounts['all']++;

				$oldRowID = $row['rowID'];

				$idInSource = $row['idInSource'];
				$field = $row['field'];
				$place = $row['place'];
				$value = $row['value'];

				if($field === 'local.person.name')
				{
					//Do not override locally updated names when new data is uploaded
					if(!empty(getValues($irts, "SELECT `value` FROM `metadata` WHERE source = '$source' AND idInSource = '$idInSource' AND `field` = '$field' AND place = '$place' AND deleted IS NULL", array('value'), 'singleValue')))
					{
						$recordTypeCounts['skipped']++;

						$report .= $recordTypeCounts['all'].')'.PHP_EOL.print_r($row, TRUE).PHP_EOL.' -- skipped'.PHP_EOL;

						continue;
					}
				}

				if($field === 'local.person.email')
				{
					//make emails lowercase
					$value = strtolower($value);

					if(strpos($value, LDAP_ACCOUNT_SUFFIX)===FALSE)
					{
						$recordTypeCounts['skipped']++;

						$report .= $recordTypeCounts['all'].')'.PHP_EOL.print_r($row, TRUE).PHP_EOL.' -- skipped'.PHP_EOL;

						continue;
					}
				}

				$parent = saveValue($source, $idInSource, $field, $place, $value, NULL);

				$recordTypeCounts[$parent['status']]++;

				if(in_array($parent['status'],array('new','updated')))
				{
					$report .= $recordTypeCounts['all'].')'.PHP_EOL.print_r($row, TRUE).PHP_EOL.' -- '.$parent['status'].PHP_EOL;
				}

				$childResult = $ioi->query("SELECT * FROM `metadata` WHERE `source` LIKE '$source' AND `deleted` IS NULL AND `parentRowID` = '$oldRowID'");

				while($childRow = $childResult->fetch_assoc())
				{
					$recordTypeCounts['all']++;

					$child = saveValue($source, $childRow['idInSource'], $childRow['field'], $childRow['place'], $childRow['value'], $parent['rowID']);

					$recordTypeCounts[$child['status']]++;

					if(in_array($child['status'],array('new','updated')))
					{
						$report .= $recordTypeCounts['all'].')'.PHP_EOL.print_r($row, TRUE).PHP_EOL.' -- '.$child['status'].PHP_EOL;
					}
				}
			}
			
			$result = $ioi->query("SELECT * FROM `metadata` 
				WHERE `source` LIKE '$source' 
				AND `parentRowID` IS NOT NULL
				AND `deleted` IS NULL
				AND `added` LIKE '$date%'
				AND `parentRowID` NOT IN
				(
					SELECT `rowID` FROM `metadata` 
					WHERE `source` LIKE '$source' 
					AND `parentRowID` IS NULL
					AND `deleted` IS NULL
					AND `added` LIKE '$date%'
				)");

			while($row = $result->fetch_assoc())
			{
				$recordTypeCounts['all']++;

				$oldRowID = $row['rowID'];

				$idInSource = $row['idInSource'];
				$field = $row['field'];
				$place = $row['place'];
				$value = $row['value'];

				$parent = saveValue($source, $idInSource, $field, $place, $value, NULL);

				$recordTypeCounts[$parent['status']]++;

				if(in_array($parent['status'],array('new','updated')))
				{
					$report .= $recordTypeCounts['all'].')'.PHP_EOL.print_r($row, TRUE).PHP_EOL.' -- '.$parent['status'].PHP_EOL;
				}

				$childResult = $ioi->query("SELECT * FROM `metadata` WHERE `source` LIKE '$source' AND `deleted` IS NULL AND `parentRowID` = '$oldRowID'");

				while($childRow = $childResult->fetch_assoc())
				{
					$recordTypeCounts['all']++;

					$child = saveValue($source, $childRow['idInSource'], $childRow['field'], $childRow['place'], $childRow['value'], $parent['rowID']);

					$recordTypeCounts[$child['status']]++;

					if(in_array($child['status'],array('new','updated')))
					{
						$report .= $recordTypeCounts['all'].')'.PHP_EOL.print_r($row, TRUE).PHP_EOL.' -- '.$child['status'].PHP_EOL;
					}
				}
			}
		}
		
		if(in_array('deleted',$modes))
		{
			$result = $ioi->query("SELECT * FROM `metadata` 
				WHERE `source` LIKE '$source' 
				AND `parentRowID` IS NULL
				AND `deleted` LIKE '$date%'");

			while($row = $result->fetch_assoc())
			{
				$report .= $recordTypeCounts['all'].')'.PHP_EOL.print_r($row, TRUE).PHP_EOL;
				
				$recordTypeCounts['all']++;

				$iOIparentRowID = $row['rowID'];
				$idInSource = $row['idInSource'];
				$field = $row['field'];
				$place = $row['place'];
				$value = $row['value'];

				if(empty(getValues($ioi, "SELECT `value` FROM `metadata` 
					WHERE source = '$source' 
					AND idInSource = '$idInSource' 
					AND `parentRowID` IS NULL
					AND `field` = '$field' 
					AND place = '$place'
					AND value = '$value'						
					AND deleted IS NULL", array('value'), 'singleValue')))
				{
					$iRTSparentRowID = getValues($irts, "SELECT `rowID` FROM `metadata` 
						WHERE source = '$source' 
						AND idInSource = '$idInSource' 
						AND `parentRowID` IS NULL
						AND `field` = '$field' 
						AND place = '$place'
						AND value = '$value'
						AND deleted IS NULL", array('rowID'), 'singleValue');
						
					if(!empty($iRTSparentRowID))
					{
						$recordTypeCounts['deleted']++;

						$report .= 'IRTS Row ID: '.$iRTSparentRowID.PHP_EOL.' -- deleted'.PHP_EOL.PHP_EOL;

						$irts->query("UPDATE metadata SET deleted = '".date("Y-m-d H:i:s")."' WHERE rowID = '$iRTSparentRowID'");
						
						$childRowIDs = getValues($irts, "SELECT `rowID` FROM `metadata` 
						WHERE source = '$source' 
						AND idInSource = '$idInSource' 
						AND `parentRowID` = '$iRTSparentRowID'
						AND deleted IS NULL", array('rowID'), 'arrayOfValues');
						
						foreach($childRowIDs as $childRowID)
						{
							$irts->query("UPDATE metadata SET deleted = '".date("Y-m-d H:i:s")."' WHERE rowID = '$childRowID'");
							
							$report .= 'IRTS Child Row ID: '.$childRowID.PHP_EOL.' -- deleted'.PHP_EOL.PHP_EOL;
						}
					}
					else
					{
						$recordTypeCounts['skipped']++;

						$report .= ' -- skipped - has no matching undeleted IRTS entry'.PHP_EOL.PHP_EOL;
					}
				}
				else
				{
					$recordTypeCounts['skipped']++;

					$report .= ' -- skipped - has matching undeleted IOI entry'.PHP_EOL.PHP_EOL;
				}
			}
		}

		$summary = saveReport($source, $report, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$summary);
	}

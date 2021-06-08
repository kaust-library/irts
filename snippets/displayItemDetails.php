<?php
	$record = getRecord($source, $idInSource, $template);

	$doi = '';
	if(isset($record['dc.identifier.doi'][0]))
	{
		$doi = $record['dc.identifier.doi'][0];
	}
	
	
	$label = getValues($irts, "SELECT value FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = '$idInIRTS' AND `field` = 'irts.label' AND `deleted` IS NULL ORDER BY added DESC", array('value'), 'singleValue');
	
	if(!empty($label))
	{
		echo "
		<span class='badge badge-pill badge-primary'>$label</span>";
	}
	
	$status = getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, NULL, 'irts.status'), array('value'), 'singleValue');

	echo listExistingRecords($record);

	echo "<b>idInIRTS:</b> $idInIRTS --> <b>Current Status:</b> $status";
	
	$processDate = getValues($irts, "SELECT added FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = '$idInIRTS' AND `parentRowID` IS NULL AND `field` = 'irts.status' AND `deleted` IS NULL", array('added'), 'singleValue');
	
	if(!empty($processDate))
	{
		echo " --> <b>Date Set:</b> $processDate";
	}
	
	$note = getValues($irts, "SELECT value FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = '$idInIRTS' AND `field` = 'irts.note' AND `deleted` IS NULL", array('value'), 'singleValue');
	
	if(!empty($note))
	{
		echo " --> <b>Note:</b> $note";
	}
	
	$statusRowID = getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, NULL, 'irts.status'), array('rowID'), 'singleValue');
	
	$processor = getValues($irts, "SELECT value FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = '$idInIRTS' AND `field` = 'irts.processedBy' AND `deleted` IS NULL ORDER BY added DESC", array('value'), 'singleValue');
	
	if(!empty($processor))
	{
		echo " --> <b>Processed By:</b> $processor";
	}



	echo "<br><b>Information from $source: </b>";

	// if the item is a dataset, display the initial information in two sections
	if (in_array($_GET['itemType'], HANDLING_RELATIONS) )
	{
		echo displayArticleAndDatasetSections($record, $template);
	}
	else
	{
		foreach($record as $field=>$value)
		{
			//if no initial steps are set, show all fields, otherwise show only initial step fields
			if(!isset($template['steps']['initial'])||in_array($field, $template['steps']['initial']))
			{
				echo '<br><b>'.$template['fields'][$field]['label'].':</b> ';

				if(is_array($value))
				{
					$values = array();
					foreach($value as $key => $value)
					{
						if(is_int($key))
						{
							if(strpos($value, 'http')===0)
							{

								$values[] = '<a href="'.$value.'" target="_blank">'.$value.'</a>';
							}
							elseif(isset($template['fields'][$field]['baseURL']))
							{
								$values[] = '<a href="'.$template['fields'][$field]['baseURL'].$value.'" target="_blank">'.$value.'</a>';
							}
							else
							{
								$values[] = $value;
							}
						}
					}
					$value = implode('; ',$values);
				}
				echo $value;
			}
		}
	}
?>

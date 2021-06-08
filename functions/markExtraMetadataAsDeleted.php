<?php
	//Define function to mark existing entries with place greater than current count as deleted
	function markExtraMetadataAsDeleted($source, $idInSource, $parentRowID, $field, $place, $currentFields)
	{			
		global $irts;
		
		if(!empty($parentRowID)&&empty($field)&&empty($place)&&empty($currentFields))
		{
			//Mark all children of a deleted row as deleted
			$check = $irts->query("SELECT rowID FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID LIKE '$parentRowID' AND deleted IS NULL");

			markMatchedRowsAsDeleted($check, $source, $idInSource);
		}
		elseif(!empty($field)&&is_int($place))
		{
			//mark existing entries with place greater than current count as deleted
			if($parentRowID === NULL)
			{
				$check = $irts->query("SELECT rowID FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID IS NULL AND field LIKE '$field' AND place > '$place' AND deleted IS NULL");
			}
			else
			{
				$check = $irts->query("SELECT rowID FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID LIKE '$parentRowID' AND field LIKE '$field' AND place > '$place' AND deleted IS NULL");
			}
			
			markMatchedRowsAsDeleted($check, $source, $idInSource);
		}
		elseif(!empty($currentFields))
		{
			//Mark metadata fields previously but no longer used on the item as deleted
			if(is_null($parentRowID))
			{
				$previousFields = getValues($irts, "SELECT DISTINCT field FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID IS NULL AND deleted IS NULL", array('field'));
			}
			else
			{
				$previousFields = getValues($irts, "SELECT DISTINCT field FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID LIKE '$parentRowID' AND deleted IS NULL", array('field'));
			}
			
			foreach($previousFields as $previousField)
			{
				if(!in_array($previousField, $currentFields))
				{					
					if(is_null($parentRowID))
					{
						$check = $irts->query("SELECT rowID FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID IS NULL AND field LIKE '$previousField' AND deleted IS NULL");	
					}
					else
					{
						$check = $irts->query("SELECT rowID FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID LIKE '$parentRowID' AND field LIKE '$previousField' AND deleted IS NULL");	
					}
					
					markMatchedRowsAsDeleted($check, $source, $idInSource);
				}
			}		
		}
	}
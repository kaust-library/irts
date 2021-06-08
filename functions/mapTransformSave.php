<?php	
	//Define function to save extracted metadata values from any source after mapping and transforming
	function mapTransformSave($source, $idInSource, $element, &$field, $parentField, $place, $value, $parentRowID)
	{			
		global $irts;
		
		//empty row id to return if conditions not met
		$rowID = NULL;
		
		$field = mapField($source, $field, $parentField);
		
		if(is_string($value))
		{
			$value = trim($value);
		}
		
		$value = transform($source, $field, $element, $value);
				
		$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
		
		return $result['rowID'];
	}	

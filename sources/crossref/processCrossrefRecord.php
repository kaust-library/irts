<?php	
	//Define function to process crossref results
	function processCrossrefRecord($sourceData, &$sourceReport = '')
	{
		global $irts, $newInProcess, $errors;
		
		$source = 'crossref';
		
		$sourceDataAsJSON = json_encode($sourceData);
		
		$idInSource = $sourceData['DOI'];
		
		//Save copy of item JSON
		$recordType = saveSourceData($sourceReport, $source, $idInSource, $sourceDataAsJSON, 'JSON');
		
		//List of metadata fields used on the item with keys as field names and values as field place counts
		$originalFieldsPlaces = array();

		//Current field names will reflect the mappings to standard fields and will sometimes differ from the original field names
		$currentFields = array();
		
		foreach($sourceData as $field => $value)
		{
			$fieldParts = array();
			
			$parentRowID = NULL;
			
			iterateOverCrossrefFields($source, $idInSource, $originalFieldsPlaces, $currentFields, $field, $fieldParts, $value, $parentRowID);
		}		
		
		$currentFields = array_unique($currentFields);
		
		markExtraMetadataAsDeleted($source, $idInSource, NULL, '', '', $currentFields);

		$dateFields = array('crossref.date.published-online', 'crossref.date.published-print', 'crossref.date.created');

		foreach($dateFields as $dateField)
		{
			//echo $dateField.' date searched'.PHP_EOL;
			
			$value = getValues($irts, setSourceMetadataQuery($source, $idInSource, NULL, $dateField), array('value'), 'singleValue');

			if(!empty($value))
			{
				//echo $dateField.' date found '.$value.PHP_EOL;
				
				//echo TODAY.PHP_EOL;
				
				if($value <= TODAY)
				{
					//echo 'Issue date found'.PHP_EOL;
					
					$result = saveValue($source, $idInSource, 'dc.date.issued', 1, $value, NULL);
					
					break 1;
				}
			}
		}
		
		return $recordType;
	}

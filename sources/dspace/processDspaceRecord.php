<?php	
	//Define function to process DSpace JSON metadata for a single repository item
	function processDspaceRecord($idInSource, $json, &$report)
	{
		global $irts;
		
		$source = 'dspace';
		
		//if coming from harvest script for reprocessing
		if(is_array($json))
		{
			$json = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT);
		}
		
		$recordType = saveSourceData($report, $source, $idInSource, $json, 'JSON');
		
		$metadata = dSpaceJSONtoMetadataArray($json);
		
		//List of metadata fields in the current record
		$currentFields = array_keys($metadata);
		
		foreach($metadata as $field => $values)
		{
			$place = 0;
			foreach($values as $value)
			{
				$place++;
				
				if(in_array($field, ORCID_ENABLED_FIELDS))
				{
					$entryParts = explode('::', $value['value']);
						
					$value['value'] = $entryParts[0];
					
					$value['dspace.authority.key'] = $entryParts[1];
					
					if(isset($entryParts[2]))
					{
						$value['dc.identifier.orcid'] = $entryParts[2];
					}
				}						
				
				$parentRowID = mapTransformSave($source, $idInSource, '', $field, '', $place, $value['value'], NULL);
				
				$childPlace = 1;				
				if($value['language'] !== NULL)
				{
					$childField = "dspace.metadata.language";
					$rowID = mapTransformSave($source, $idInSource, '', $childField, '', $childPlace, $value['language'], $parentRowID);
				}
				else
				{
					$childField = "dspace.metadata.language";
					markExtraMetadataAsDeleted($source, $idInSource, $parentRowID, $childField, 0, '');
				}
				
				if(isset($value['dspace.authority.key']))
				{
					$childField = "dspace.authority.key";
					$authorityRowID = mapTransformSave($source, $idInSource, '', $childField, '', $childPlace, $value['dspace.authority.key'], $parentRowID);
					
					if(isset($value['dc.identifier.orcid']))
					{
						$childField = "dc.identifier.orcid";
						$rowID = mapTransformSave($source, $idInSource, '', $childField, '', $childPlace, $value['dc.identifier.orcid'], $authorityRowID);
					}
				}						
			}
			markExtraMetadataAsDeleted($source, $idInSource, NULL, $field, $place, '');
		}
		markExtraMetadataAsDeleted($source, $idInSource, NULL, '', '', $currentFields);
		return $recordType;
	}	
		
<?php	
	//Define function to save metadata values for a subelement
	function saveRepositoryMetadata($source, $idInSource, $subelement, $field)
	{
		//language no longer stored as separate column in table, may be stored as child value if needed...
		//$language = (string)$subelement[0]['name'];
		$place = 0;
		$index = 0;
		$countfields = $subelement[0]->count();
		foreach($subelement->field as $value)
		{
			if((string)$value['name']=='value')
			{
				$place++;
				
				$value = (string)$value;				
				
				$parentRowID = mapTransformSave($source, $idInSource, '', $field, '', $place, $value, NULL);
				
				$authority = '';
				$authorityID = '';
				if($index < $countfields-1)
				{									
					$currentChildFields = array();
					if((string)$subelement->field[$index+1]['name']=='authority')
					{
						$childPlace = 1;
						$childValue = (string)$subelement->field[$index+1];
						$childField = "dspace.authority.key";
						$currentChildFields[] = $childField;
						$rowID = mapTransformSave($source, $idInSource, '', $childField, '', $childPlace, $childValue, $parentRowID);
					}
					
					if((string)$subelement->field[$index+3]['name']=='authorityID')
					{
						$childPlace = 1;
						$childValue = (string)$subelement->field[$index+3];
						$childField = "dc.identifier.orcid";
						$currentChildFields[] = $childField;
						$rowID = mapTransformSave($source, $idInSource, '', $childField, '', $childPlace, $childValue, $parentRowID);
					}
					markExtraMetadataAsDeleted($source, $idInSource, $parentRowID, '', '', $currentChildFields);
				}				
			}	
			$index++;
		}
		
		markExtraMetadataAsDeleted($source, $idInSource, NULL, $field, $place, '');
	}	
		
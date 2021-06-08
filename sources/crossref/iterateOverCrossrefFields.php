<?php	
	//Define function to iterate over fields
	function iterateOverCrossrefFields($source, $idInSource, &$originalFieldsPlaces, &$currentFields, $field, &$fieldParts, $value, &$parentRowID)
	{
		$hierarchicalFields = array('author','funder','link','license','assertion');
		
		if(!empty($value))
		{			
			if(!is_numeric($field))
			{
				$fieldParts[] = $field;
			}
			
			//for arrays we have to iterate further
			if(!is_array($value))
			{
				$currentField = $source.'.'.implode('.', $fieldParts);
				
				if(isset($originalFieldsPlaces[$currentField]))
				{
					$originalFieldsPlaces[$currentField]++;
				}
				else
				{
					$originalFieldsPlaces[$currentField] = 1;
				}
				
				$rowID = mapTransformSave($source, $idInSource, '', $currentField, '', $originalFieldsPlaces[$currentField], (string)$value, $parentRowID);
				
				$currentFields[] = $currentField;
				
				//Remove excess parts...				
				if(!is_numeric($field))
				{
					array_pop($fieldParts);
				}
			}				
			else
			{
				foreach($value as $childField => $childValue)
				{
					if($childField === 'date-parts')
					{
						//catch date arrays								
						$currentField = 'crossref.date.'.implode('.', $fieldParts);
						
						if(isset($originalFieldsPlaces[$currentField]))
						{
							$originalFieldsPlaces[$currentField]++;
						}
						else
						{
							$originalFieldsPlaces[$currentField] = 1;
						}
						
						$rowID = mapTransformSave($source, $idInSource, '', $currentField, '', $originalFieldsPlaces[$currentField], $childValue[0], $parentRowID);
						
						$currentFields[] = $currentField;
					}
					
					if(in_array(implode('.', $fieldParts), $hierarchicalFields))
					{
						if(is_null($parentRowID))
						{						
							//catch names								
							$currentField = $source.'.'.$fieldParts[0].'.name';
							
							if(isset($originalFieldsPlaces[$currentField]))
							{
								$originalFieldsPlaces[$currentField]++;
							}
							else
							{
								$originalFieldsPlaces[$currentField] = 1;
							}
							
							$name = '';
							if(isset($childValue['given']))
							{
								$name = $childValue['family'].', '.$childValue['given'];
							}
							elseif(isset($childValue['family']))
							{
								$name = $childValue['family'];
							}
							elseif(isset($childValue['name']))
							{
								$name = $childValue['name'];
							}
							elseif(isset($childValue['URL']))
							{
								$name = $childValue['URL'];
							}
							
							if(!empty($name))
							{							
								$parentRowID = mapTransformSave($source, $idInSource, '', $currentField, '', $originalFieldsPlaces[$currentField], $name, NULL);
												
								$currentFields[] = $currentField;
							}
						}
					}					
					
					iterateOverCrossrefFields($source, $idInSource, $originalFieldsPlaces, $currentFields, $childField, $fieldParts, $childValue, $parentRowID);
				}
				
				if(is_numeric($field)&&!is_numeric($childField)&&strpos(implode('.', $fieldParts),'author.affiliation')!==FALSE)
				{
					//leave the parentRowID untouched - this allows for multiple affiliations to be added with a single person as the parent
				}
				else
				{
					$parentRowID = NULL;
				}
				
				if(!is_numeric($field)&&is_numeric($childField))
				{
					array_pop($fieldParts);					
				}				
			}
		}
	}

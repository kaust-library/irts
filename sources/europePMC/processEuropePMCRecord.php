<?php
	//Define function to process Europe PMC results
	function processEuropePMCRecord($result)
	{
		global $irts, $newInProcess, $errors, $sourceReport;
		
		$source = 'europePMC';
		
		$idInSource = $result['id'];
		
		echo $idInSource.PHP_EOL;
		
		$sourceDataAsJSON = json_encode($result);
		
		//Save copy of item as JSON
		$recordType = saveSourceData($sourceReport, $source, $idInSource, $sourceDataAsJSON, 'JSON');
		
		foreach($result as $field => $value)
		{
			if($field === 'authorList')
			{
				$place = 1;
				foreach($value['author'] as $author)
				{
					$field = 'dc.contributor.author';
					
					if(isset($author['collectiveName']))
					{
						$value = $author['collectiveName'];						
					}
					else
					{
						$value = $author['lastName'].', '.$author['firstName'];	
					}
					
					//echo $value;
					
					$parentRowID = mapTransformSave($source, $idInSource, '', $field, '', $place, $value, NULL);
					
					if(isset($author['affiliation']))
					{
						$values = explode('; ',$author['affiliation']);
						
						$affplace = 1;
						foreach($values as $value)
						{
							if(strpos($value,'@')!==FALSE)
							{
								if(strpos($value,'. ')!==FALSE)
								{
									$parts = explode('. ',$value);
									
									if(strpos($parts[1],'@')!==FALSE)
									{
										$field = 'irts.author.correspondingEmail';
										
										$rowID = mapTransformSave($source, $idInSource, '', $field, '', 1, substr($parts[1],0,-1), $parentRowID);
										
										$value = $parts[0].'.';
									}
								}
								else
								{
									$field = 'irts.author.correspondingEmail';
										
									$rowID = mapTransformSave($source, $idInSource, '', $field, '', 1, substr($value,0,-1), $parentRowID);
									
									$value = '';
								}
							}

							if(!empty($value))
							{
								$field = 'dc.contributor.affiliation';

								$rowID = mapTransformSave($source, $idInSource, '', $field, '', $affplace, $value, $parentRowID);
								
								$affplace++;
							}
						}
					}
					
					if(isset($author['authorId']))
					{
						//print_r($author['authorId']);
						
						if((string)$author['authorId']['type']==='ORCID')
						{
							$field = 'dc.identifier.orcid';

							$rowID = mapTransformSave($source, $idInSource, '', $field, '', 1, $author['authorId']['value'], $parentRowID);
						}
					}

					$place++;
				}
			}			
			elseif(is_array($value))
			{
				$childPlace = 1;
				foreach($value as $childField => $childValue)
				{
					$childField = "$field.$childField";
					if(is_array($childValue))
					{
						$grandChildPlace = 1;
						foreach($childValue as $grandChildField => $grandChildValue)
						{
							if(is_numeric($grandChildField))
							{
								$grandChildField = "$childField";
								$grandChildPlace = $grandChildField;
							}
							else
							{
								$grandChildField = "$childField.$grandChildField";
							}
							
							if(is_array($grandChildValue))
							{
								$grandChildValue = json_encode($grandChildValue);
							}
							
							$rowID = mapTransformSave($source, $idInSource, '', $grandChildField, '', $grandChildPlace, $grandChildValue, NULL);
						}
					}
					else
					{
						$rowID = mapTransformSave($source, $idInSource, '', $childField, '', $childPlace, $childValue, NULL);
						
						//$childPlace++;
					}					
				}
			}
			elseif($field !== 'citedByCount')
			{
				$rowID = mapTransformSave($source, $idInSource, '', $field, '', 1, $value, NULL);
			}
		}			
		
		return $recordType;
	}

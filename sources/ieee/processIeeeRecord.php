<?php
	//Define function to process IEEE results
	function processIeeeRecord($sourceData)
	{
		global $irts, $newInProcess, $errors, $sourceReport;

		$source = 'ieee';
		
		//Remove rank field as this is just the order of items in the search results list and will change any time there are new items in the list
		unset($sourceData['rank']);

		$sourceDataAsJSON = json_encode($sourceData);

		$idInSource = $sourceData['article_number'];

		//Save copy of item JSON
		$recordType = saveSourceData($sourceReport, $source, $idInSource, $sourceDataAsJSON, 'JSON');

		//Current field names will reflect the mappings to standard fields and will sometimes differ from the original field names
		$currentFields = array();
		
		//Number of entries in a field may change even if the field is still used
		$currentFieldsPlaces = array();

		foreach($sourceData as $field => $values)
		{
			$place = 1;

			if(!is_array($values))
			{
				$rowID = mapTransformSave($source, $idInSource, '', $field, '', $place, (string)$values, NULL);
				$currentFields[] = $field;
				
				if($field === 'dc.identifier.journal')
				{
					//Save publication title also as conference name for conference papers
					if($sourceData['content_type']==='Conferences')
					{
						$field = 'dc.conference.name';
						$rowID = mapTransformSave($source, $idInSource, '', $field, '', $place, (string)$values, NULL);
						$currentFields[] = $field;
					}
				}
				elseif($field === 'ieee.content_type')
				{
					$field = 'dc.type';

					if($values==='Conferences')
					{
						$type = "Conference Paper";
					}
					else
					{
						$type = "Article";
					}
					
					$rowID = mapTransformSave($source, $idInSource, '', $field, '', $place, $type, NULL);
					$currentFields[] = $field;
				}
			}
			else
			{
				//print_r($values);
				if($field === 'authors')
				{
					foreach($values['authors'] as $author)
					{
						$field = 'dc.contributor.author';
						$parentRowID = mapTransformSave($source, $idInSource, '', $field, '', $place, (string)$author['full_name'], NULL);
						$currentFields[] = $field;
						$currentFieldsPlaces[] = array($field=>$place);

						if(isset($author['affiliation']))
						{
							if(strpos($author['affiliation'], ' (e-mail: ')!==FALSE)
							{
								$parts = explode(' (e-mail: ', $author['affiliation']);
								
								$author['affiliation'] = $parts[0];
								
								$email = explode(')', $parts[1])[0];
								
								$field = 'irts.author.correspondingEmail';
								$rowID = mapTransformSave($source, $idInSource, '', $field, '', 1, $email, $parentRowID);
								$currentFields[] = $field;
							}
							
							$field = 'dc.contributor.affiliation';
							$rowID = mapTransformSave($source, $idInSource, '', $field, '', 1, (string)$author['affiliation'], $parentRowID);
							$currentFields[] = $field;
						}

						$field = 'author_order';
						$rowID = mapTransformSave($source, $idInSource, '', $field, '', 1, (string)$author['author_order'], $parentRowID);
						$currentFields[] = $field;

						$place++;
					}
				}
				elseif($field === 'index_terms')
				{
					if(isset($values['ieee_terms']))
					{
						$place = 1;
						foreach($values['ieee_terms']['terms'] as $term)
						{
							$field = 'dc.subject.ieee';
							$rowID = mapTransformSave($source, $idInSource, '', $field, '', $place, (string)$term, NULL);							

							$place++;
						}
						$currentFields[] = $field;
						$currentFieldsPlaces[] = array($field=>$place);
					}
					
					if(isset($values['author_terms']))
					{
						$place = 1;
						foreach($values['author_terms']['terms'] as $term)
						{
							$field = 'dc.subject';
							$rowID = mapTransformSave($source, $idInSource, '', $field, '', $place, (string)$term, NULL);

							$place++;
						}
						$currentFields[] = $field;
						$currentFieldsPlaces[] = array($field=>$place);
					}
				}
			}
		}

		$currentFields = array_unique($currentFields);

		markExtraMetadataAsDeleted($source, $idInSource, NULL, '', '', $currentFields);
		
		foreach($currentFieldsPlaces as $field => $place)
		{
			markExtraMetadataAsDeleted($source, $idInSource, '', $field, $place, '');
		}

		return $recordType;
	}

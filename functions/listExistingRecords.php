<?php
	//Define function to display list of existing records
	function listExistingRecords($record, $button = '')
	{
		global $irts;
		
		$message = '';

		$existingRecords['all'] = array();
		$existingRecords['repository'] = array();
		
		// check if there is a isnewversionof relation to update the record if there is an existing one
		if(!empty($record['dc.relation.isnewversionof']))
		{
			foreach($record['dc.relation.isnewversionof'] as $oldVersionID)
			{
				if(strpos($oldVersionID, 'DOI:') !== FALSE)
				{
					$record['dc.identifier.doi'][] = str_replace('DOI:', '', $oldVersionID);
				}
			}			
		}

		$fieldsToCheck = array('dc.title','dc.identifier.arxivid','dc.identifier.doi');
		$fieldsChecked = array();

		foreach($fieldsToCheck as $field)
		{
			if(!empty($record[$field]))
			{
				foreach($record[$field] as $value)
				{
					$handles = checkForExistingRecords($value, $field, $message, 'repository');
					
					$existingFields = array('dc.type','dc.title','dc.identifier.uri', 'dc.identifier.doi','dc.identifier.arxivid');
					
					foreach($handles as $handle)
					{
						foreach($existingFields as $existingField)
						{					
							$existingRecords['repository'][$handle][$existingField] = getValues($irts, setSourceMetadataQuery('repository', $handle, NULL, $existingField), array('value'));
						}
						$existingRecords['repository'][$handle]['matchedFields'][] = $field;
					}
					
					//Checking DSpace REST API retrieved records handles the cases where the item has not yet been harvested via OAI-PMH (as that may take up to 20 minutes)
					$existingDspaceItemIDs = checkForExistingRecords($record[$field][0], $field, $message, 'dspace');
					if(!empty($existingDspaceItemIDs))
					{
						foreach($existingDspaceItemIDs as $itemID)
						{
							$handle = str_replace('http://hdl.handle.net/', '', getValues($irts, setSourceMetadataQuery('dspace', $itemID, NULL, 'dc.identifier.uri'), array('value'), 'singleValue'));
							
							foreach($existingFields as $existingField)
							{					
								$existingRecords['repository'][$handle][$existingField] = getValues($irts, setSourceMetadataQuery('dspace', $itemID, NULL, $existingField), array('value'));
							}
							$existingRecords['repository'][$handle]['matchedFields'][] = $field;
						}
					}
					$fieldsChecked[] = $field;
				}
			}
		}
		$existingRecords['all'] = array_merge($existingRecords['all'], $existingRecords['repository']);
		
		/* 
			//Checking irts may be useful for knowing that a record with a matching field has been rejected or marked as having a problem...
			$existingRecords['irts'] = checkForExistingRecords($record[$field][0], $field, $message, 'irts');
			
			$existingRecords['all'] = array_merge($existingRecords['all'], $existingRecords['irts']);
		*/

		if(empty($existingRecords['all']))
		{
			//echo '<div class="alert-success">No existing records in DSpace with matching values for '.implode(' or ', $fieldsChecked).'</div>';
			
			echo " <span class='badge badge-pill badge-success'>No existing records in DSpace with matching values for ".implode(' or ', $fieldsChecked)."</span> <br/>";
		}
		else
		{
			foreach($existingRecords as $existingSource=>$handles)
			{
				if($existingSource === 'repository')
				{
					foreach($handles as $handle => $existing)
					{
						if(!empty($existing['dc.title'][0]))
						{
							$matchedFields = implode(', ', array_unique($existing['matchedFields']));
							
							$titleMatch = '';	
							
							if(strpos($matchedFields, 'dc.title') !== FALSE)
							{								
								if(strtolower(trim($existing['dc.title'][0])) === strtolower(trim($record['dc.title'][0])))
								{
									$titleMatch = ' <span class="badge badge-pill badge-success">Exact Match</span>';
								}	
								else
								{
									$titleMatch = ' <span class="badge badge-pill badge-danger">Partial Match</span>';
								}	
							}							
								
							echo '<div class="col-sm-12 alert-warning border border-dark rounded"><b>Existing record in repository with matching '.$matchedFields.'.</b>';

							echo '<br>Type: '.$existing['dc.type'][0];

							echo '<br>Title: '.$existing['dc.title'][0].$titleMatch;

							echo '<br>Handle: <a href="'.$existing['dc.identifier.uri'][0].'">'.$existing['dc.identifier.uri'][0].'</a>';
							
							if(!empty($existing['dc.identifier.arxivid'][0]))
							{
								echo '<br>arXiv ID: '.implode('; ', $existing['dc.identifier.arxivid']);
							}
							
							if(!empty($record['dc.identifier.doi'][0]))
							{
								$doi = $record['dc.identifier.doi'][0];
								if(empty($existing['dc.identifier.doi'][0]))
								{
									//echo '<div class="alert-danger">Existing record has no DOI.</div>';
									
									echo '<span class="badge badge-pill badge-danger">Existing record has no DOI.</span>';
								}
								else
								{
									echo '<br>DOI: '.implode('; ', $existing['dc.identifier.doi']);
									
									if(!in_array(strtolower($doi), array_map('strtolower', $existing['dc.identifier.doi'])))
									{
										//echo '<div class="alert-danger">New record DOI ('.$doi.') not in existing record.</div>';
										
										echo '<span class="badge badge-pill badge-danger">New record DOI ('.$doi.') not in existing record</span>';
									}
								}
							}
							elseif(!empty($existing['dc.identifier.doi'][0]))
							{
								echo '<br>DOI: '.implode('; ', $existing['dc.identifier.doi']);
							}
							
							if(!empty($button))
							{
								echo str_replace('{handle}', $handle, $button);
							}
							
							echo '</div>';
						}
					}
				}
			}
		}
		return $message;
	}

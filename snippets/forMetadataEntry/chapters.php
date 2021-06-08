<?php
	if(empty($value))
	{
		echo '<div class="input-group col-sm-12 border border-dark rounded" id="'.$inputGroupID.'"><textarea class="form-control" rows="'.$textareaRows.'" name="record['.$field.']['.$valueCount.']">'.$value.'</textarea>';
		
		echo '<button id="remove_'.$inputGroupID.'" class="input-group-append btn btn-danger remove-me" >-</button><button id="add_'.$inputGroupID.'" class="input-group-append btn btn-success add-more" type="button">+</button>';
	}
	else
	{
		echo '<div class="input-group col-sm-12 border border-dark rounded" id="'.$inputGroupID.'"><input type="hidden" name="record['.$field.']['.$valueCount.']" value="'.$value.'" />';
	}
	
	$doi = str_replace('DOI:','',$value);
	
	if(!empty($doi))
	{
		echo 'DOI: <a href="https://doi.org/'.$doi.'">'.$doi.'</a><br>Citation: '.getCitationByDOI($doi).'<br><br>';
		
		$record[$field]['irts.withdraw.handle'][$valueCount][] = getValues($irts, setSourceMetadataQuery('repository', NULL, NULL, 'dc.identifier.doi', $doi), array('idInSource'), 'singleValue');
	}
	
	if(empty($record[$field]['irts.withdraw.handle'][$valueCount][0]))
	{
		unset($record[$field]['irts.withdraw.handle'][$valueCount]);
		if($record['irts.contributor.type'][0]==='Editors')
		{
			$record[$field]['irts.add.doi'][$valueCount][] = '';
		}
	}

	if(!empty($template['fields'][$field]['field']))
	{
		foreach($template['fields'][$field]['field'] as $child)
		{
			if(isset($record[$field][$child][$valueCount]))
			{
				if(empty($template['fields'][$child]['note']))
				{
					$label = $template['fields'][$child]['label'];
				}
				else
				{
					$label = $template['fields'][$child]['label'].' ('.$template['fields'][$child]['note'].')';
				}

				$childValueCount = 0;

				echo '<div class="form-group col-sm-12"><label for="record['.$field.']['.$child.']['.$valueCount.']"><b>'.$label.':</b></label>';
				
				foreach($record[$field][$child][$valueCount] as $value)
				{
					$childInputGroupID = $inputGroupID.'_'.$child.'_'.$childValueCount;

					if($child === 'irts.withdraw.handle')
					{
						echo '<br><input type="checkbox" name="record['.$field.']['.$child.']['.$valueCount.'][]" value="'.$value.'"> Existing book chapter record with Handle: <a href="https://hdl.handle.net/'.$value.'">'.$value.'</a> should be <b>removed</b> from the repository.</input>';
					}
					if($child === 'irts.add.doi')
					{
						echo '<br><input type="checkbox" name="record['.$field.']['.$child.']['.$valueCount.'][]" value="'.$doi.'"> Book chapter with DOI: <a href="https://doi.org/'.$doi.'">'.$doi.'</a> should be <b>added</b> to the repository.</input>';
					}
				}

				echo '</div><br><br>';
			}
		}
	}
	echo '</div>';
?>
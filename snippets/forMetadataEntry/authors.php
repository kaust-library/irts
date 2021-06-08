<?php
	echo '<div class="input-group col-sm-12 border border-dark rounded" id="'.$inputGroupID.'"><input type="hidden" name="record['.$field.']['.$valueCount.']" value="'.$value.'" />'.$value;

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

				if($child === 'dc.identifier.orcid')
				{
					foreach($record[$field][$child][$valueCount] as $value)
					{
						if(!empty($value))
						{
							$childInputGroupID = $inputGroupID.'_'.$child.'_'.$childValueCount;

							echo '<input type="hidden" name="record['.$field.']['.$child.']['.$valueCount.'][]" value="'.$value.'"> <a href="'.$template['fields'][$field]['baseURL']. $value . '"><img id="orcid-id-icon" src="https://orcid.org/sites/default/files/images/orcid_24x24.png" width="24" height="24" alt="ORCID iD icon"/>'.$template['fields'][$field]['baseURL']. $value .'</a></input>';
						}
						else
						{
							echo '<input type="hidden" name="record['.$field.']['.$child.']['.$valueCount.'][]" value="'.$value.'" />';
						}
						$childValueCount++;
					}
				}
				else
				{
					echo '<div class="form-group col-sm-12"><label for="record['.$field.']['.$child.']['.$valueCount.']">'.$label.':</label>';

					if($child === 'dc.contributor.affiliation')
					{
						
						foreach($record[$child] as $value)
						{
							$childInputGroupID = $inputGroupID.'_'.$child.'_'.$childValueCount;

							if(in_array($value, $record[$field][$child][$valueCount]))
							{
								echo '<br><input type="checkbox" name="record['.$field.']['.$child.']['.$valueCount.'][]" value="'.$value.'" checked> '.$value.'</input>';
							}
							else
							{
								echo '<br><input type="checkbox" name="record['.$field.']['.$child.']['.$valueCount.'][]" value="'.$value.'"> '.$value.'</input>';
							}

							$childValueCount++;
						}
					}
					else
					{
						foreach($record[$field][$child][$valueCount] as $value)
						{
							$childInputGroupID = $inputGroupID.'_'.$child.'_'.$childValueCount;

							echo '<div class="input-group" id="'.$childInputGroupID.'"><textarea class="form-control" rows="1" name="record['.$field.']['.$child.']['.$valueCount.'][]">'.$value.'</textarea><button id="remove_'.$childInputGroupID.'" class="input-group-append btn btn-danger remove-me" >-</button><button id="add_'.$childInputGroupID.'" class="input-group-append btn btn-success add-more" type="button">+</button></div>';

							$childValueCount++;
						}
					}
					echo '</div>';
				}
			}
		}
	}
	echo '</div>';
?>
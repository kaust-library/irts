<?php
	if($field === 'dc.related.datasetDOI')
	{
		$citation = getCitationByDOI($value);
		
		echo '<div class="input-group col-sm-12 border border-dark rounded" id="'.$inputGroupID.'">';
		
		echo '<a href="https://doi.org/'.$value.'">'.$value.'</a> - Editable: <textarea class="form-control" rows="1" name="record['.$field.']['.$valueCount.']">'.$value.'</textarea><br><br>';
		
		//echo '<input type="hidden" name="record['.$field.']['.$valueCount.']" value="'.$value.'" /><a href="https://doi.org/'.$value.'">'.$value.'</a>';
		
		if(!empty($citation)&&is_string($citation))
		{
			echo '&nbsp;-- Citation: '.$citation;
		}
		
		echo '<br><br>';
	}
	elseif(in_array($field, array('dc.related.datasetURL','dc.related.codeURL')))
	{
		//echo '<textarea class="form-control" rows="'.$textareaRows.'" name="record['.$field.']['.$valueCount.']">'.$value.'</textarea>';
		
		echo '<div class="input-group col-sm-12 border border-dark rounded" id="'.$inputGroupID.'"><br>';
		
		echo '<a href="'.$value.'">'.$value.'</a> - Editable: <textarea class="form-control" rows="1" name="record['.$field.']['.$valueCount.']">'.$value.'</textarea><br><br>';
		
		//echo '<input type="hidden" name="record['.$field.']['.$valueCount.']" value="'.$value.'" /><a href="'.$value.'">'.$value.'</a><br><br>';
	}
	elseif($field === 'dc.related.accessionNumber')
	{
		echo '<div class="input-group col-sm-12 border border-dark rounded" id="'.$inputGroupID.'"><br>';
		
		echo '<textarea class="form-control" rows="1" name="record['.$field.']['.$valueCount.']">'.$value.'</textarea><br><br>';
	}
	elseif($field === 'dc.description.dataAvailability')
	{
		echo '<div class="input-group col-sm-12 border border-dark rounded" id="'.$inputGroupID.'"><br><input type="hidden" name="record['.$field.']['.$valueCount.']" value="'.$value.'" />'.$value.'<br><br>';
	}

	if(!empty($template['fields'][$field]['field']))
	{
		foreach($template['fields'][$field]['field'] as $child)
		{
			if(empty($template['fields'][$child]['note']))
			{
				$label = $template['fields'][$child]['label'];
			}
			else
			{
				$label = $template['fields'][$child]['label'].' ('.$template['fields'][$child]['note'].')';
			}

			echo '<div class="form-group col-sm-12"><label for="record['.$field.']['.$child.']['.$valueCount.']">'.$label.' (between the '.$type.' and this '.substr($template['fields'][$field]['label'],0,-1).'):</label>';

			if($template['fields'][$child]['inputType']==='dropdown')
			{
				echo '<select name="record['.$field.']['.$child.']['.$valueCount.']">';
				
				//echo $template['fields'][$field]['values'];
				
				$listValues = explode(',',$template['fields'][$child]['values']);

				foreach($listValues as $listValue)
				{
					echo '<option value="'.$listValue.'">'.$listValue.'</option>';
				}
				echo '</select>';
			}
			echo '</div>';			
		}
	}
	echo '</div>';
?>
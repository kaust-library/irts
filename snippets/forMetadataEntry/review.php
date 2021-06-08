<?php
	if(in_array($field, array('dc.contributor.author','dc.contributor.editor','dc.relation.haspart')))
	{
		echo '<div class="input-group col-sm-12 border border-dark rounded" id="'.$inputGroupID.'"><textarea class="form-control" rows="'.$textareaRows.'" name="record['.$field.']['.$valueCount.']">'.$value.'</textarea>';
	}
	else
	{
		echo '<div class="input-group col-sm-12" id="'.$inputGroupID.'"><textarea class="form-control" rows="'.$textareaRows.'" name="record['.$field.']['.$valueCount.']">'.$value.'</textarea>';
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

				echo '<div class="form-group col-sm-12"><label for="record['.$field.']['.$child.']['.$valueCount.']">'.$label.':</label>';

				$childValueCount = 0;

				foreach($record[$field][$child][$valueCount] as $value)
				{


					$childInputGroupID = $inputGroupID.'_'.$child.'_'.$childValueCount;

					echo '<div class="input-group col-sm-12" id="'.$childInputGroupID.'"><textarea class="form-control" rows="'.$textareaRows.'" name="record['.$field.']['.$child.']['.$valueCount.'][]">'.$value.'</textarea></div>';

					$childValueCount++;
				}
				echo '</div>';
			}
		}
	}
	echo '</div>';
?>
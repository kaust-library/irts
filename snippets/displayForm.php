<?php
	//expand relations to allow reassignment of relation type by dropdown
	if($step == 'relations')
	{
		$relationCount = 0;
		foreach($record as $field=>$values)
		{
			if(strpos($field, 'dc.relation.') !== FALSE && $field !== 'dc.relation.url')
			{
				foreach($values as $value)
				{
					$record['dc.relationType'][$relationCount] = str_replace('dc.relation.','',$field);
					
					$record['dc.relationType']['dc.relatedIdentifier'][$relationCount][] = $value;
					
					$relationCount++;
				}
				unset($record[$field]);
			}
		}
	}

	foreach($record as $field=>$values)
	{
		//for review step show entire record, otherwise only show fields in the current step
		if($step==='review'||in_array($field, $template['steps'][$step]))
		{
			if(empty($template['fields'][$field]['note']))
			{
				$label = $template['fields'][$field]['label'];
			}
			else
			{
				$label = $template['fields'][$field]['label'].' ('.$template['fields'][$field]['note'].')';
			}

			echo '<div class="form-group"><label for="record['.$field.']">'.$label.':</label>';

			$valueCount = 0;

			if(empty($values))
			{
				$values = array(0=>'');
			}

			foreach($values as $key => $value)
			{
				//subfields will have strings (field names) as keys
				if(is_int($key))
				{
					$inputGroupID = $field.'_'.$valueCount;

					//check if the value are not array
					$textareaRows = (int)round(strlen($value)/100);

					if($textareaRows===0)
					{
						$textareaRows = 1;
					}

					if($step === 'authors'&&$field==='dc.contributor.author')
					{
						include "snippets/forMetadataEntry/$step.php";
					}
					elseif($step === 'chapters')
					{
						include "snippets/forMetadataEntry/$step.php";
					}
					elseif($step === 'dataRelations')
					{
						if(empty($value))
						{
							echo ' None';
						}
						else
						{
							include "snippets/forMetadataEntry/$step.php";
						}
					}
					elseif($step === 'review')
					{
						include "snippets/forMetadataEntry/$step.php";
					}
					else
					{
						echo '<div class="input-group col-sm-12" id="'.$inputGroupID.'">';

						//Default to textarea input
						if(!isset($template['fields'][$field]['inputType']))
						{
							// if the title or the description contain a dollar sign, pop up message
							if($field == 'dc.title' || $field == 'dc.description.abstract')
							{
								if( substr_count($value, '$') > 1 )
								{
									echo '<div class="col-sm-12 alert-warning border border-dark rounded"><b> -- Important notes : <br></b>* This text contains dollar signs; Please fix it if needed as following : <br>
										• Case 1 : Use of $ to refer to dollars, if there are two $, the text in between will not be rendered normally and they need to be replaced with $\$$ . For example: 5$ and 6$ => 5$\$$ and 6$\$$<br>
										• Case 2 : Use of two $ as the delimiter, in our implementation this will make the formula display on a new line, should be replaced by $. For example: $N_t$ <br>

										</div>';
								}
							}

							// if the author has a local affiliation, show a green background 
							if(isset($record[$field]['dc.contributor.affiliation'][$valueCount]) && (in_array($_GET['itemType'] , HANDLING_RELATIONS) ) &&  ( $field == 'dc.contributor.author' || $field == 'dc.creator'))
							{
								if( institutionNameInString(implode('","', $record[$field]['dc.contributor.affiliation'][$valueCount])))
								{
									echo '<textarea class="form-control" style="background:#bcf5bc;" rows="'.$textareaRows.'" name="record['.$field.']['.$valueCount.']">'.$value.'</textarea>';
								}
								else
								{
									echo '<textarea class="form-control" rows="'.$textareaRows.'" name="record['.$field.']['.$valueCount.']">'.$value.'</textarea>';
								}
							}
							else
							{
								echo '<textarea class="form-control" rows="'.$textareaRows.'" name="record['.$field.']['.$valueCount.']">'.htmlentities($value).'</textarea>';
							}

							if(!empty($template['fields'][$field]['field']))
							{
								foreach($template['fields'][$field]['field'] as $child)
								{	
									if($step==='initial' || $child === 'dc.version')
									{
										if(isset($record[$field][$child]))
										{
											foreach($record[$field][$child][$valueCount] as $value)
											{	
												// if the child is d.version we don't want it to be hidden
												$inputType = 'hidden';
												if($child === 'dc.version')
													$inputType = 'text';

												echo '<input type="'.$inputType .'" name="record['.$field.']['.$child.']['.$valueCount.'][]" value="'.$value.'" />';
											}
										}
									}
								}
							}

							echo '<button id="remove_'.$inputGroupID.'" class="input-group-append btn btn-danger remove-me" >-</button><button id="add_'.$inputGroupID.'" class="input-group-append btn btn-success add-more" type="button">+</button>';

						}
						elseif($template['fields'][$field]['inputType']==='dropdown')
						{
							echo '<select name="record['.$field.']['.$valueCount.']">';
							
							$listValues = explode(',',$template['fields'][$field]['values']);

							foreach($listValues as $listValue)
							{
								//echo $value;
								if($listValue === $value)
								{
									echo '<option selected="selected" value="'.$listValue.'">'.$listValue.'</option>';
								}
								else
								{
									echo '<option value="'.$listValue.'">'.$listValue.'</option>';
								}
							}

							echo '</select>';

							foreach ($template['fields'][$field]['field'] as $child)
							{
								if(isset($record[$field][$child]))
								{
									foreach($record[$field][$child][$valueCount] as $value)
									{
										echo '<textarea class="form-control" name="record['.$field.']['.$child.']['.$valueCount.'][]">'.$value.'</textarea>';
									}
								}
							}
						}
						elseif($template['fields'][$field]['inputType']==='radiobutton' )
						{
							if( !empty($values) && !empty($values[0]))
							{
								echo ' <br><input type="radio" name="record['.$field.'][]" value="'.$value.'">  &nbsp; <b>URL:</b> &nbsp; <a href="'.$value.'">'.$value.'</a>,  &nbsp; ' ;

								if(isset($record[$field][$template['fields'][$field]['field'][0]]))
								{	
									echo '<b>Version:</b>  &nbsp;'.$record[$field][$template['fields'][$field]['field'][0]][$valueCount];
								}
								echo '</input><br>';
							}
							elseif( $valueCount == 0) 
							{
								echo '<b>No Unpaywall results returned</b>';
							}
						}
						echo '</div>';
					}
					$valueCount++;
				}
			}
			echo '</div>';
		}
		else
		{
			if(empty($values))
			{
				$values = array(0=>'');
			}

			foreach($values as $key => $value)
			{
				if(is_int($key))
				{
					echo '<input type="hidden" name="record['.$field.'][]" value="'.htmlentities($value).'">';
				}
				else
				{
					$child = $key;
					foreach($value as $key => $values)
					{
						foreach($values as $value)
						{
							echo '<input type="hidden" name="record['.$field.']['.$child.']['.$key.'][]" value="'.($value).'">';							
						}
					}
				}
			}
		}
	}

<?php
	//Define function to prepare a form template for a given item type
	function prepareTemplate($itemType, $reviewStep = NULL)
	{
		global $irts, $report;

		$template = array();
		$fields = array();
		
		$typeHasTemplate = getValues($irts, "SELECT DISTINCT `idInSource` FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = 'itemType_$itemType' AND `deleted` IS NULL", array('idInSource'), 'singleValue');
		
		if(empty($typeHasTemplate))
		{
			//There is no template for ETDs
			if(in_array($itemType, array('Thesis','Dissertation')))
			{
				$templateNames = array();
			}
			else
			{
				$templateNames[] = 'itemType_Publication';
			}
		}
		else
		{
			$templateNames = getValues($irts, setSourceMetadataQuery('irts', 'itemType_'.$itemType, NULL, 'irts.form.template'), array('value'));
			$templateNames[] = 'itemType_'.$itemType;

			$templateNames = array_filter($templateNames);
		}

		foreach($templateNames as $templateName)
		{
			if(is_null($reviewStep))
			{
				$steps = getValues($irts, "SELECT `rowID`, `value` FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = '".$templateName."' AND `field` = 'irts.form.step' AND `deleted` IS NULL ORDER BY `place` ASC", array('rowID', 'value'));
			}
			else
			{
				$steps = getValues($irts, "SELECT `rowID`, `value` FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = '$templateName' AND `field` = 'irts.form.step' AND value IN ('$reviewStep') AND `deleted` IS NULL", array('rowID', 'value'));

				//print_r($steps);
			}

			foreach($steps as $step)
			{
				//get parent template fields for the step
				if(!isset($template['steps'][$step['value']]))
				{
					$template['steps'][$step['value']] = explode(',', getValues($irts, setSourceMetadataQuery('irts', $templateName, $step['rowID'], 'irts.form.fields'), array('value'), 'singleValue'));
				}
				//add child template field for the step
				else
				{
					$template['steps'][$step['value']] = array_merge($template['steps'][$step['value']], explode(',', getValues($irts, setSourceMetadataQuery('irts', $templateName, $step['rowID'], 'irts.form.fields'), array('value'), 'singleValue')));
				}
			}

			//parent fields
			$fields = getValues($irts, setSourceMetadataQuery('irts', $templateName, NULL, 'irts.form.field'), array('value'));

			//child fields
			$fields = array_merge($fields, getValues($irts, setSourceMetadataQuery('irts', $templateName, TRUE, 'irts.form.field'), array('value')));

			$fieldAttributes = array('label','note','baseURL','inputType','values','field');

			foreach($fields as $field)
			{
				foreach($fieldAttributes as $attribute)
				{
					if($attribute === 'field')
					{
						$template['fields'][$field][$attribute] = getValues($irts, setSourceMetadataQuery('irts', $templateName, array('parentField'=>'irts.form.field','parentValue'=>$field), 'irts.form.'.$attribute), array('value'), 'arrayOfValues');
					}
					else
					{
						$template['fields'][$field][$attribute] = getValues($irts, setSourceMetadataQuery('irts', $templateName, array('parentField'=>'irts.form.field','parentValue'=>$field), 'irts.form.'.$attribute), array('value'), 'singleValue');
					}
				}
			}
		}
		return $template;
	}

<?php
	//Define function to process arXiv results
	function processArxivRecord($item)
	{
		global $irts, $report;

		$source = 'arxiv';

		$arxivURL = $item->id;
		$arxivID = substr(str_replace("http://arxiv.org/abs/", "", $arxivURL), 0, -2);

		//Save copy of item XML
		$xml = $item->asXML();

		$recordType = saveSourceData($report, $source, $arxivID, $xml, 'XML');

		//Set dc.type as "Preprint" for all arxiv records
		$field = 'dc.type';
		$rowID = mapTransformSave($source, $arxivID, '', $field, '', 1, 'Preprint', NULL);

		//Set dc.publisher as "arXiv" for all arxiv records
		$field = 'dc.publisher';
		$rowID = mapTransformSave($source, $arxivID, '', $field, '', 1, 'arXiv', NULL);

		$fieldPlace = array();
		foreach($item->children() as $element)
		{
			$field = $element->getName();
			$value = trim((string)$element);
			if(isset($fieldPlace[$field]))
			{
				$fieldPlace[$field]++;
			}
			else
			{
				$fieldPlace[$field] = 1;
			}
			$parentField = '';
			$rowID = '';
			$parentID = NULL;

			$rowID = mapTransformSave('arxiv', $arxivID, $element, $field, $parentField, $fieldPlace[$field], $value, $parentID);
			
			//Set values in case there are children
			$parentID = $rowID;
			$parentField = $element->getName();

			if(count($element->children())!==0)
			{
				foreach($element->children() as $element)
				{
					$field = $element->getName();
					$value = trim((string)$element);
					if(isset($fieldPlace[$field]))
					{
						$fieldPlace[$field]++;
					}
					else
					{
						$fieldPlace[$field] = 1;
					}
					$rowID = mapTransformSave('arxiv', $arxivID, $element, $field, $parentField, $fieldPlace[$field], $value, $parentID);
				}
			}

			if(count($element->attributes())!==0)
			{
				foreach($element->attributes() as $field => $value)
				{
					$attributesToIgnore = array('scheme','term');
					if(!in_array($field, $attributesToIgnore))
					{
						if(isset($fieldPlace[$field]))
						{
							$fieldPlace[$field]++;
						}
						else
						{
							$fieldPlace[$field] = 1;
						}

						$rowID = mapTransformSave('arxiv', $arxivID, $element, $field, $parentField, $fieldPlace[$field], $value, $parentID);
					}
				}
			}
		}

		return array('idInSource'=>$arxivID,'recordType'=>$recordType);
	}

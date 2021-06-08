<?php
	//Define function to check if a record already exists in the repository with the given attribute
	function checkForExistingRecords($value, $type, &$report, $source = 'repository')
	{
		global $irts;

		$singleFields = array('dc.title','dc.identifier.arxivid','dc.identifier.doi','dc.identifier.uri');

		if($type === 'number')
		{
			/*
			if(strpos($value, ' ')!==FALSE)
			{
				$value1 = $value;
				$value2 = str_replace(' ', '', $value);
				$value3 = explode(' ', $value)[1];
			}
			elseif(strpos($value, '-')!==FALSE)
			{
				$value1 = str_replace('-', ' ', $value);
				$value2 = str_replace('-', '', $value);
				$value3 = explode('-', $value)[1];
			}
			else
			{
				$value1 = googlePatentsToUniversal($value);
				$value2 = $value;
				$value3 = str_replace('-', ' ', $value1);
			}

			$query = "SELECT DISTINCT `idInSource` FROM `metadata` WHERE source LIKE 'repository' AND (field LIKE 'dc.identifier.patentnumber' OR field LIKE 'dc.identifier.applicationnumber') AND (value LIKE '$value1' OR `value` LIKE '$value2' OR `value` LIKE '%$value3%') AND deleted IS NULL";
			*/
		}
		elseif(in_array($type, $singleFields))
		{
			$existingRecords = getValues($irts, setSourceMetadataQuery($source, NULL, NULL, $type, $value), array('idInSource'), 'arrayOfValues');
			
			if(!isset($_GET['ignoreVariantTitles']))
			{
				if($type === 'dc.title')
				{
					$titles = getValues($irts, "SELECT `idInSource`, `value`FROM `metadata` WHERE `source` = '$source' AND `parentRowID` IS NULL AND `field` = '$type' AND `deleted` IS NULL ORDER BY `idInSource` ASC", array('idInSource', 'value'), 'arrayOfValues');
					
					foreach($titles as $title)
					{
						if(similar_text(strtolower($value), strtolower($title['value']), $percentSimilar) > 10)
						{
							if($percentSimilar > 90)
							{
								$existingRecords[] = $title['idInSource'];
							}
						}
					}
				}
			}
		}

		if(isset($existingRecords))
		{
			$existingRecords = array_unique($existingRecords);
			//$report .= ' - Existing Records: '.implode($existingRecords).PHP_EOL;
			return $existingRecords;
		}
		else
		{
			//$report .= '<br> - No Existing Records';
			return array();
		}
	}

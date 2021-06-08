<?php

$source = 'irts';

$record = $_POST['record'];
//print_r($record);

//compress relation fields before saving to the database
foreach($record as $field=>$values)
{
	if($field === 'dc.relationType')
	{
		foreach($values as $key => $relationType)
		{
			if(is_int($key)&&!empty($relationType))
			{
				foreach($record['dc.relationType']['dc.relatedIdentifier'][$key] as $relatedIdentifier)
				{
					if(!empty($value))
					{
						$record['dc.relation.'.$relationType][] = $relatedIdentifier;
					}
				}
			}
		}
	}
}
unset($record['dc.relationType']);

foreach($record as $field=>$values)
{
	if(!empty($values))
	{
		$valueCount = 0;
		$place = 1;
		foreach($values as $key => $value)
		{
			//subfields will have strings (field names) as keys
			if(is_int($key)&&!empty($value))
			{
				$result = saveValue('irts', $idInIRTS, $field, $place, $value, NULL);
		
				$parentRowID =  $result['rowID'];

				if(!empty($template['fields'][$field]['field']))
				{
					foreach($template['fields'][$field]['field'] as $child)
					{
						if(isset($record[$field][$child][$valueCount]))
						{
							$childPlace = 1;

							foreach($record[$field][$child][$valueCount] as $value)
							{
								if(!empty($value))
								{
									$result = saveValue('irts', $idInIRTS, $child, $childPlace, $value, $parentRowID);

									$childPlace++;
								}
							}
						}
					}
				}
				$place++;
				$valueCount++;
			}
		}
	}
}

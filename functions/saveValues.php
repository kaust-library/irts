<?php
	/*

	**** This function recursively iterates over an array to save values as well as the values of child fields.

	** Parameters :
		$source : name of the source system.
		$idInSource : id of this record in the source system.
		$input : the record or subrecord as an array of field names and metadata values.
		$parentRowID : if the input is the subrecord of children of a value, this will be the parent row's rowID.
		
	** Output : returns a report.

	** Created by : Daryl Grenz
	** institute : King Abdullah University of Science and Technology | KAUST
	** Date : 10 June 2019 - 1:30 PM 

	*/
	//------------------------------------------------------------------------------------------------------------
	
	function saveValues($source, $idInSource, $input, $parentRowID)
	{
		global $irts, $errors;
		
		$report = '';

		foreach($input as $field=>$values)
		{
			//Previous database used 1 as the first place, we need to retain this even though the default arrays start with 0
			if(isset($values[0]))
			{
				array_unshift($values,"");
				unset($values[0]);
			}
			
			foreach($values as $place => $value)
			{
				if(!empty($value['value']))
				{		
					$result = saveValue($source, $idInSource, $field, $place, $value['value'], $parentRowID);
		
					$rowID = $result['rowID'];
					
					$report .= $source.' '.$idInSource.': '.$field.' '.$place.' child of '.$parentRowID.' - '.$result['status'].PHP_EOL;
					
					if(!empty($value['children']))
					{
						$report .= saveValues($source, $idInSource, $value['children'], $rowID);
					}
				}
			}
		}		

		return $report;
	}

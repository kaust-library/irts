<?php
	//Define function to get a single value or an array
	function getValues($database, $query, $fields, $request = 'arrayOfValues')
	{
		$result = $database->query($query);

		//set values variable
		if($request === 'singleValue')
		{
			$values = '';
		}
		elseif($request === 'arrayOfValues')
		{
			$values = array();
		}

		//check for success of query
		if(is_bool($result))
		{
			print_r('Failed query: '.$query.PHP_EOL);
		}
		else
		{
			if($request === 'singleValue')
			{
				$row = $result->fetch_assoc();
				$values = $row[$fields[0]];
			}
			elseif($request === 'arrayOfValues')
			{
				while($row = $result->fetch_assoc())
				{
					if(count($fields)===1)
					{
						if(!empty($fields[0]))
							array_push($values, $row[$fields[0]]);
						else
							array_push($values, null);
					}
					else
					{
						array_push($values, $row);
					}
				}
			}
		}

		return $values;
	}

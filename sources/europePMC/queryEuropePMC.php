<?php
	//Define function to query the Europe PMC API (documentation at: https://europepmc.org/RestfulWebService )
	function queryEuropePMC($queryType, $value, $nextCursorMark = NULL)
	{
		if($queryType === 'affiliation')
		{
			//Add first name in list to query
			$query = 'query=(aff:"'.INSTITUTION_NAME.'")';
			
			if(!empty(INSTITUTION_ABBREVIATION))
			{
				$query .= ' OR (aff:"'.INSTITUTION_ABBREVIATION.'")';
			}
			
			if(!empty(INSTITUTION_CITY))
			{
				$query .= ' OR (aff:"'.INSTITUTION_CITY.'")';
			}
			
			$query .= ' sort_date:y';
		}
		else
		{	
			$query = "query=$queryType:$value";
		}			
	
		$url = EUROPEPMC_API_URL.'search?'.str_replace(' ', '+', $query).'&resulttype=core&pageSize=50&format=json';
		
		if(!is_null($nextCursorMark))
		{
			$url .= '&cursorMark='.$nextCursorMark;
		}

		echo $url.PHP_EOL;

		return json_decode(file_get_contents($url), TRUE);		
	}

<?php
	//Define function to query the Scopus API
	function queryScopus($type, $value, $start = 0, $count = 10)
	{
		if($type === 'affiliation')
		{
			if(is_null($value))
			{
				$query = 'AF-ID('.SCOPUS_AF_ID.')';

				if(!empty(INSTITUTION_ABBREVIATION))
				{
					$query .= ' OR AFFIL("'.INSTITUTION_ABBREVIATION.'")';
				}

				if(!empty(INSTITUTION_CITY))
				{
					$query .= ' OR AFFIL("'.INSTITUTION_CITY.'")';
				}
			}
		}

		if($type === 'doi')
		{
			$query = 'DOI("'.$value.'")';
		}

		if($type === 'eid')
		{
			$query = 'EID("'.$value.'")';
		}

		if($type === 'authorID')
		{
			$query = 'AU-ID("'.$value.'") AND NOT AF-ID('.SCOPUS_AF_ID.')';
		}

		$query = urlencode($query);

		//Sorting by original load date allows us to exit when the first item is met that is already in the scopus harvest table
		$sort = '&sort=-orig-load-date';

		$url = ELSEVIER_API_URL."search/scopus?start=$start&count=$count&query=$query$sort";

		$opts = array(
		  'http'=>array(
			'method'=>"GET",
			'header'=>array("Accept: application/xml", "X-ELS-APIKey: ".ELSEVIER_API_KEY)
			)
		);

		$context = stream_context_create($opts);

		$xml = file_get_contents($url, false, $context);

		return $xml;
	}

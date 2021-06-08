<?php
	//The Sherpa Romeo API v2 documentation is at: https://v2.sherpa.ac.uk/romeo/api.html
	
	function querySherpaRomeo($type, $filter, $offset = NULL)
	{
		$url = SHERPA_ROMEO_API_URL."item-type=$type&api-key=".SHERPA_ROMEO_API_KEY.'&format=Json';
		
		if(!empty($filter))
		{
			$url .= '&filter=[["'.$filter['field'].'","'.$filter['operator'].'","'.urlencode($filter['value']).'"]]';
		}
		
		if(!empty($offset))
		{
			$url .= "&offset=$offset";
		}
		
		$json = json_decode(file_get_contents($url), TRUE);

		return $json;
	}

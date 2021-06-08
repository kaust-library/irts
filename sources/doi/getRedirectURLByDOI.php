<?php
	//Define function to get the URL that a DOI redirects to
	function getRedirectURLByDOI($doi)
	{
		$options = array(
		  CURLOPT_URL => DOI_BASE_URL.$doi,
		  CURLOPT_HEADER => TRUE,
		  CURLOPT_FOLLOWLOCATION => FALSE
		);

		$response = makeCurlRequest($options, 'HTTP/1.1 302', 'headers');
		
		//print_r($response);
		if(is_string($response))
		{
			if (preg_match('~Location: (.*)~i', $response, $match)) {
			   $location = trim($match[1]);
			}
		}
		else
		{
			$location = '';
		}
		
		//echo $location;

		return $location;
	}
<?php
//Define function to get citation via DOI content negotiation (https://crosscite.org/docs.html)
function getCitationByDOI($doi)
{
	$options = array(
		CURLOPT_URL => DOI_BASE_URL.$doi,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_FOLLOWLOCATION => TRUE,
		CURLOPT_HTTPHEADER => array(
			"Accept: text/x-bibliography; style=apa",			
			"Cache-Control: no-cache"			
		)
	);

	$response = makeCurlRequest($options);

	if(is_string($response))
	{
		if(strpos($response, '<!DOCTYPE html') === 0 )
		{
			$response = array();
		}
	}
	return $response;
}
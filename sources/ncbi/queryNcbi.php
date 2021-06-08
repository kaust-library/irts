<?php

/*

**** This file responsible for requesting XML data from NCBI.

** Parameters :
	$search: stirng to specify the search method ( efetch.fcgi, esummary.fcgi or esearch.fcgi).

	$db: the databse name in ncbi as string.
	$id: unique identifier.
	$label: the id for esearch.fcgi called term otherwise id.

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 7 May 2020 - 1:30 PM

*/

//--------------------------------------------------------------------------------------------



function queryNcbi($search, $db, $id, $label='id')
{
	// if the search is esearch we need only to request the XML
	if(strpos($search, 'esearch.fcgi') !== FALSE)
	{
		$successHeader = 'HTTP/1.1 200 OK';
		$successResponsePortionNeeded = 'response';

		$options = array(
		  CURLOPT_URL => NCBI_API_URL.$search.'?db='.$db.'&'.$label.'='.$id,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
			"Accept: */*",
			"accept-encoding: gzip, deflate",
			"cache-control: no-cache"
			),
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);
	}
	else
	{
		// else we have to load the whole page as xml
		$response = new DOMDocument;
		$response->load(NCBI_API_URL.$search.'?db='.$db.'&'.$label.'='.$id);
		$response =  $response->saveXml();
	}

	return $response;
}

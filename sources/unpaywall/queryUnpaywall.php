<?php

/*

**** This file is responsible of Sending request to unpaywall.

** Parameters :
	$doi : 
	

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 6 November 2019 - 10:18 AM 

*/

//-----------------------------------------------------------------------------------------------------------

function queryUnpaywall($doi){

	$successHeader = 'HTTP/1.1 200 OK';
	$successResponsePortionNeeded = 'response';

	$options = array(
	  CURLOPT_URL => UNPAYWALL_API_URL.$doi.'?email='.IR_EMAIL,
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
	return $response;
}

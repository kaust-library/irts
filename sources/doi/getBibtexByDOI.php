<?php
	//Define function to get Bibtex via DOI content negotiation (https://crosscite.org/docs.html)
	function getBibtexByDOI($doi)
	{
		$options = array(
			CURLOPT_URL => DOI_BASE_URL.$doi,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_FOLLOWLOCATION => TRUE,
			CURLOPT_HTTPHEADER => array(
				"Accept: application/x-bibtex",
				"Cache-Control: no-cache"
			)
		);

		$response = makeCurlRequest($options);

		return $response;
	}
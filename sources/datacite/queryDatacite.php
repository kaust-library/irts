<?php
	//Define function to query DataCite by a related identifier, or to retrieve a Datacite record by a known DataCite DOI
	function queryDatacite($identifier, $queryType)
	{
		$successHeader = 'HTTP/1.1 200 OK';
		$successResponsePortionNeeded = 'response';

		if($queryType === 'relations')
		{
			$url = DATACITE_API."works?query=relatedIdentifiers.relatedIdentifier:".$identifier;

		}
		elseif($queryType === 'title')
		{
			$url = DATACITE_API."works?query=titles.title:".$identifier;
		}
		elseif($queryType === 'metadata')
		{
			$url = DATACITE_API."dois/".$identifier;
		}

		$options = array(
			CURLOPT_URL =>$url,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_FOLLOWLOCATION => TRUE,
			CURLOPT_HTTPHEADER => array(
				"Accept: application/json",
				"Accept-Encoding: gzip, deflate"
			)
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);
		return $response;
	}

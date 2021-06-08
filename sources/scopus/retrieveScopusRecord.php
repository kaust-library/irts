<?php
	//Define function to query the Scopus API for a single identifier of the following types: abstract, author, affiliation
	function retrieveScopusRecord($recordType, $idType, $identifier)
	{
		global $irts;		
		
		//Full view appears to be the default response, no need to request
		//$view = '&view=FULL';

		$url = ELSEVIER_API_URL.$recordType.'/'.$idType.'/'.$identifier;

		$opts = array(
		  'http'=>array(
			'method'=>"GET",
			'header'=>array("Accept: application/xml", "X-ELS-APIKey: ".ELSEVIER_API_KEY),
			'ignore_errors' => true
			)
		);

		$context = stream_context_create($opts);
		
		$xml = file_get_contents($url, false, $context);		
		
		return $xml;		
	}

<?php

/*

**** This file responsible for requesting json data from github.

** Parameters :
	$githubURL: stirng.

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 23 August 2020 - 3:11 PM

*/

//--------------------------------------------------------------------------------------------

function queryGithub($githubID, $getReadMe = FALSE) 
{
	$successHeader = 'HTTP/1.1 200 OK';
	$successResponsePortionNeeded = 'response';

	if($getReadMe)
	{
		$url = GITHUB_API.$githubID.'/readme';
	}
	else
	{
		$url = GITHUB_API.$githubID;
	}

	$options = array(
		CURLOPT_URL => GITHUB_API.$githubID,
		CURLOPT_USERAGENT => INSTITUTION_ABBREVIATION.'_Repository',
		CURLOPT_CUSTOMREQUEST => "GET"
	);

	$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

	return $response;
}

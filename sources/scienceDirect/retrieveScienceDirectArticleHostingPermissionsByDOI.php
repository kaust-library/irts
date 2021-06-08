<?php



/**** This function is responsible of getting the right embargo for elsevier .

** Parameters :
	$text: abstarct or title. 

** Created by : Daryl Grenz and Yasmeen ALsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 23 January 2020 - 3:24 PM

*/

//--------------------------------------------------------------------------------------------


function retrieveScienceDirectArticleHostingPermissionsByDOI($doi)
{
	global $pubs, $today, $message;		
	
	$embargo = '';
	$jav = '';
	$license = '';
	$audience = '';
	
	sleep(2);
	$url = ELSEVIER_API_URL.'article/hostingpermission/doi/'.$doi;
	$opts = array(
		  CURLOPT_URL => $url,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"X-ELS-APIKey: ".ELSEVIER_API_KEY,
			"Content-Type: application/json",
		  )
		);

echo $doi.'<br>';
	$response = makeCurlRequest($opts);

	sleep(2);
	// if there is an error return null result
	if(!is_string($response))
		return $response;

	$output = json_decode($response, true);

	
	if(!empty($output))
	{
		$originalJSON = $output;

		if($originalJSON['hosting-permission-response']['document-hosting-permission']['hosting-platform']['@type'] == 'institutional_repository'){
		

			if(isset($originalJSON['hosting-permission-response']['document-hosting-permission']['hosting-platform']['document-version'])){

				$documentVersion = $originalJSON['hosting-permission-response']['document-hosting-permission']['hosting-platform']['document-version'];


				if(isset($documentVersion[0]['hosting-allowed'])){

					foreach ($documentVersion[0]['hosting-allowed'] as $value) {
						
						if(isset($value['@start_date'])){

							$embargo = $value['@start_date'];
						}
					}
					

				}

				// get license
				if(isset($documentVersion[0]['required-license'])){

					$license = $documentVersion[0]['required-license'];
				}

				// get jornual version 
				if(isset($documentVersion[1]['jav:journal_article_version'])){

					$jav = $documentVersion[1]['jav:journal_article_version'];
				
				}

				// if it is an OA article @audience should be public 
				if(isset($documentVersion[0]['hosting-allowed']['@audience'])){

					$audience = $documentVersion[0]['hosting-allowed']['@audience'];
				}


			}


		}
		
	}

	return array('embargo' => $embargo, 'jav' => $jav, 'license' => $license, 'audience' => $audience);
}
?>
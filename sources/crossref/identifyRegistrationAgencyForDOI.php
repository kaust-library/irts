<?php
	//The Crossref API documentation is at: https://github.com/CrossRef/rest-api-doc/blob/master/rest_api.md
	function identifyRegistrationAgencyForDOI($doi, &$sourceReport)
	{
		global $irts;
		
		$json = '';
		$source = 'doi';
		$doiStatus = '';
		$agencyID = '';
		$agencyLabel = '';
		
		$url = CROSSREF_API."works/" . urlencode($doi) . "/agency?mailto=".urlencode(IR_EMAIL);
		//echo $url;
		
		$headers = get_headers($url, 1);
		//print_r($headers);
		
		if($headers[0] == 'HTTP/1.1 404 Not Found')
		{
			$sourceReport .= ' - No agency result for: '.$url.' - DOI may be invalid...';

			$doiStatus = 'unknown';
		}
		else
		{
			$json = file_get_contents($url);
			if(empty($json))
			{
				$sourceReport .= ' - Empty agency result for: '.$url.' - DOI may be invalid...';

				$doiStatus = 'unknown';
			}
			else
			{
				$json = json_decode($json);		

				if(array_key_exists('id', $json->{'message'}->{'agency'}))
				{
					$agencyID = $json->{'message'}->{'agency'}->{'id'};
				}
				
				if(array_key_exists('label', $json->{'message'}->{'agency'}))
				{
					$agencyLabel = $json->{'message'}->{'agency'}->{'label'};
				}

				if(empty($agencyID)&&empty($agencyLabel))
				{
					$sourceReport .= ' - Empty agency id and label result for: '.$url.' - DOI may be invalid...';

					$doiStatus = 'unknown';
				}
				else
				{
					$doiStatus = 'registered';
					
					$result = saveValue('doi', $doi, 'doi.agency.id', 1, $agencyID, NULL);

					$result = saveValue('doi', $doi, 'doi.agency.label', 1, $agencyLabel, NULL);
					
					$url = getRedirectURLByDOI($doi);
					$result = saveValue('doi', $doi, 'dc.relation.url', 1, $url, NULL);
					$sourceReport .= ' - URL: '.$url.PHP_EOL;
					
					$bibtex = getBibtexByDOI($doi);
					if(is_string($bibtex))
					{
						$result = saveValue('doi', $doi, 'dc.identifier.bibtex', 1, $bibtex, NULL);
					}
					sleep(1);

					$citation = getCitationByDOI($doi);
					if(is_string($citation))
					{
						$result = saveValue('doi', $doi, 'dc.identifier.citation', 1, $citation, NULL);
					}
				}
			}
		}
		
		$sourceReport .= '- DOI Status: '.$doiStatus.PHP_EOL;
		
		$result = saveValue('doi', $doi, 'doi.status', 1, $doiStatus, NULL);
		
		return $agencyID;
	}

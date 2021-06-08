<?php
	//The Crossref API documentation is at: https://github.com/CrossRef/rest-api-doc/blob/master/rest_api.md

	function retrieveCrossrefMetadataByDOI($doi, &$sourceReport)
	{
		//select all elements except reference, is-referenced-by-count, and references-count as the reference list is long and unneeded
		$select = 'abstract,URL,member,posted,score,created,degree,update-policy,short-title,license,ISSN,container-title,issued,update-to,issue,prefix,approved,indexed,article-number,clinical-trial-number,accepted,author,group-title,DOI,updated-by,event,chair,standards-body,original-title,funder,translator,archive,published-print,alternative-id,subject,subtitle,published-online,publisher-location,content-domain,title,link,type,publisher,volume,ISBN,issn-type,assertion,deposited,page,content-created,short-container-title,relation,editor';

		$url = CROSSREF_API."works?filter=doi:".urlencode($doi)."&select=$select&mailto=".urlencode(IR_EMAIL);
		//echo $url;

		$sourceData = file_get_contents($url);

		$sourceData = json_decode($sourceData, TRUE);

		if($sourceData['message']['total-results'] === 1)
		{
			$sourceData = $sourceData['message']['items'][0];
		}
		else
		{
			$sourceReport .= ' - Unexpected result count for: '.$url.' - DOI may be invalid...';

			//empty out result to return
			$sourceData = array();
		}

		return $sourceData;
	}

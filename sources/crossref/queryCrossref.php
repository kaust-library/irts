<?php
	//The Crossref API documentation is at: https://github.com/CrossRef/rest-api-doc/blob/master/rest_api.md

	function queryCrossref($field, $value, $type = NULL)
	{
		//select all elements except reference, is-referenced-by-count, and references-count as the reference list is long and unneeded
		$select = 'abstract,URL,member,posted,score,created,degree,update-policy,short-title,license,ISSN,container-title,issued,update-to,issue,prefix,approved,indexed,article-number,clinical-trial-number,accepted,author,group-title,DOI,updated-by,event,chair,standards-body,original-title,funder,translator,archive,published-print,alternative-id,subject,subtitle,published-online,publisher-location,content-domain,title,link,type,publisher,volume,ISBN,issn-type,assertion,deposited,page,content-created,short-container-title,relation,editor';

		$url = CROSSREF_API."works?rows=50&filter=$field:".urlencode($value);
		
		if(!is_null($type))
		{
			$url .= ",type:$type";
		}
		
		$url .= "&select=$select&mailto=".urlencode(IR_EMAIL)."&sort=published&order=desc";
		//echo $url;

		$sourceData = json_decode(file_get_contents($url), TRUE);

		if($sourceData['message']['total-results'] !== 0)
		{
			$sourceData = $sourceData['message']['items'];
		}
		else
		{
			//empty out result to return
			$sourceData = array();
		}

		return $sourceData;
	}

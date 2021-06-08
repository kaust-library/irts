<?php
	//The Crossref API documentation is at: https://github.com/CrossRef/rest-api-doc/blob/master/rest_api.md

	function getCrossrefMemberByID($idInSource, &$report)
	{
		$source = 'crossref';
		
		$url = CROSSREF_API."members/$idInSource?mailto=".urlencode(IR_EMAIL);

		$sourceData = file_get_contents($url);

		$sourceData = json_decode($sourceData, TRUE);

		if(isset($sourceData['message']['primary-name']))
		{
			$idInSource = 'member_'.$idInSource;
			
			$field = 'crossref.member.name';
			$value = $sourceData['message']['primary-name'];
			
			$result = saveValue($source, $idInSource, $field, 1, $value, NULL);
			$report .= $value.PHP_EOL;
		}
		else
		{
			$report .= ' - Unexpected result for: '.$url.' - id may be invalid...';

			//empty result to return
			$sourceData = array();
		}

		return $sourceData;
	}

<?php
	//Define function to convert the DSpace JSON metadata to an array for updating
	function dSpaceJSONtoMetadataArray($json)
	{
		$json = json_decode($json, TRUE);
		
		$metadata = array();
		
		foreach($json as $entry)
		{
		  $metadata[$entry['key']][] = array('value'=>$entry['value'],'language'=>$entry['language']);
		}
		
		return $metadata;
	}

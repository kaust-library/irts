<?php
	//Define function to convert a DSpace metadata array to a simpler array
	function dSpaceMetadataToArray($input)
	{
		$output = array();
		
		foreach($input as $entry)
		{
		  $output[$entry['key']][] = $entry['value'];
		}
		
		return $output;
	}

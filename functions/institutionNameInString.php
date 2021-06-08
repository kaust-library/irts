<?php
	//Define function to check if an institutional name variant is in a string
	function institutionNameInString($string)
	{
		$ignore = '';
		foreach(AFFILIATION_STRINGS_TO_IGNORE as $affiliationStringToIgnore)
		{
			if($string === $affiliationStringToIgnore)
			{
				$ignore = 'yes';
			}
		}
		
		$matchFound = '';
		if(empty($ignore))
		{
			foreach(INSTITUTION_NAME_VARIANTS as $institutionName)
			{						
				if(stripos($string, $institutionName)!==FALSE)
				{
					$matchFound = 'yes';														
				}						
			}
		}		
		
		if(!empty($matchFound))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}	

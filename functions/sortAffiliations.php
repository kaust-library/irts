<?php
	//Define function to identify Local departments and add appropriate collection mapping
	function sortAffiliations(&$affiliations, &$originalLocalAffs, &$externalAffs)
	{
		global $institutionNames;
		
		foreach($affiliations as $affiliation)
		{
			$localAff = '';
			foreach($institutionNames as $institutionNameString)
			{
				if(strpos($affiliation, $institutionNameString)!==FALSE)
				{
					$localAff = $affiliation;					
				}
			}
			
			if(!empty($localAff))
			{
				array_push($originalLocalAffs, $affiliation);
			}
			else
			{
				array_push($externalAffs, $affiliation);
			}
		}
	}	

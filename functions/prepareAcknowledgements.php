<?php
	//Define function to extract additional metadata from the provided acknowledgement field 
	function prepareAcknowledgements($record)
	{
		global $irts;
		
		$ack = $record['dc.description.sponsorship'][0];
		
		$record['local.acknowledgement.type'] = array();		
	
		$knownAcknowledgedUnits = array_filter(getValues($irts, "SELECT DISTINCT value FROM metadata WHERE field = 'local.acknowledged.supportUnit' ORDER BY `value` ASC", array('value'), 'arrayOfValues'));
			
		$knownAcknowledgedGrants = array_filter(getValues($irts, "SELECT DISTINCT value FROM metadata WHERE field = 'local.grant.number' ORDER BY `value` ASC", array('value'), 'arrayOfValues'));
		
		if(strpos($ack, INSTITUTION_ABBREVIATION)!==FALSE)
		{
			$matches = array();
			foreach($knownAcknowledgedUnits as $unit)
			{
				if(strpos($ack, $unit)!==FALSE)
				{
					$matches[] = $unit;
				}
			}
			
			foreach($matches as $checkMatch)
			{
				foreach($matches as $match)
				{
					if($match !== $checkMatch)
					{
						if(strpos($match,$checkMatch)!==FALSE)
						{
							if (($key = array_search($checkMatch, $matches)) !== false) 
							{
								unset($matches[$key]);
							}
						}
					}
				}
			}
			$record['local.acknowledged.supportUnit'] = array_unique($matches);
			
			$matches = array();
			foreach($knownAcknowledgedGrants as $grant)
			{
				if (!ctype_digit($grant))
				{
					if(strpos($ack, $grant)!==FALSE)
					{
						$matches[] = $grant;
					}
				}
			}

			foreach($matches as $checkMatch)
			{
				foreach($matches as $match)
				{
					if($match !== $checkMatch)
					{
						if(strpos($match,$checkMatch)!==FALSE)
						{
							if (($key = array_search($checkMatch, $matches)) !== false) 
							{
								unset($matches[$key]);
							}
						}
					}
				}
			}
			$record['local.grant.number'] = array_unique($matches);
		}
		
		if(!isset($record['local.acknowledged.supportUnit']))
		{
			$record['local.acknowledged.supportUnit'] = array();	
		}
		
		if(!isset($record['local.grant.number']))
		{
			$record['local.grant.number'] = array();	
		}

		$record['local.acknowledged.person'] = array();		

		return $record;
	}

<?php
	//Define function to generate and save possible name variants for a given person
	function generateNameVariants($localID, $controlName)
	{
		global $irts;
		
		$variantsAdded = array();
		
		//Count number of existing variants for this person
		$place = count(getValues($irts, "SELECT rowID FROM `metadata` WHERE source='local' AND idInSource = '$localID' AND field='local.name.variant' AND deleted IS NULL", array('rowID'), 'arrayOfValues'));
		
		$surnamePrefixes = Array("Al", "El", "Bin", "Abdul", "Abdel");	
		
		$nameParts = explode(', ', $controlName);
		$familyName = $nameParts[0];
		$givenName = $nameParts[1];
		
		$familyNameVariants = Array($familyName);
		if(strpos($familyName, ' ')!==FALSE)
		{	
			$familyNameParts = explode(' ', $familyName);
			$familyNameVariants[] = implode('-', $familyNameParts);
			$familyNameVariants[] = implode('', $familyNameParts);
		}
		elseif(strpos($familyName, '-')!==FALSE)
		{	
			$familyNameParts = explode('-', $familyName);
			$familyNameVariants[] = implode(' ', $familyNameParts);
			$familyNameVariants[] = implode('', $familyNameParts);
		}
		else
		{
			foreach($surnamePrefixes as $surnamePrefix)
			{
				if(substr($familyName, 0, strlen($surnamePrefix))===$surnamePrefix)
				{
					$familyNameParts = Array();
					$familyNameParts[] = substr($familyName, 0, strlen($surnamePrefix));
					$familyNameParts[] = substr($familyName, strlen($surnamePrefix));
					$familyNameVariants[] = implode(' ', $familyNameParts);
					$familyNameVariants[] = implode('-', $familyNameParts);
				}
			}
		}
		
		$givenNameVariants = Array($givenName);
		$givenNameVariants[] = $givenName[0];
		$givenNameVariants[] = $givenName[0].'.';
		
		if(strpos($givenName, ' ')!==FALSE)
		{	
			$givenNameParts = explode(' ', $givenName);
			$givenNameVariants[] = $givenNameParts[0];
			
			$givenNameInitials = Array();
			foreach($givenNameParts as $givenNamePart)
			{
				if(!empty($givenNamePart))
				{
					$givenNameInitials[] = $givenNamePart[0];
				}
			}
			
			if(strpos($givenNameParts[1], '.')===FALSE)
			{
				$givenNameVariants[] = implode('-', $givenNameParts);
				$givenNameVariants[] = implode('', $givenNameParts);
			}
		}
		elseif(strpos($givenName, '-')!==FALSE)
		{	
			$givenNameParts = explode('-', $givenName);
			$givenNameVariants[] = $givenNameParts[0];
			
			$givenNameInitials = Array();
			foreach($givenNameParts as $givenNamePart)
			{
				$givenNameInitials[] = $givenNamePart[0];
			}
			
			if(strpos($givenNameParts[1], '.')===FALSE)
			{
				$givenNameVariants[] = implode(' ', $givenNameParts);
				$givenNameVariants[] = implode('', $givenNameParts);
			}
		}
		else
		{
			$givenNameParts = Array();
			$givenNameInitials = Array($givenName[0]);
			$givenNameVariants[] = $givenName[0];
			$givenNameVariants[] = $givenName[0].'.';
		}
		
		if(count($givenNameInitials)>1)
		{
			if(count($givenNameParts)>2)
			{
				$givenNameVariants[] = $givenNameParts[0].' '.implode('. ', $givenNameInitials).'.';
				$givenNameVariants[] = $givenNameParts[0].' '.implode('. ', $givenNameInitials);
				$givenNameVariants[] = $givenNameParts[0].' '.$givenNameParts[1];
			}
			
			$givenNameVariants[] = $givenNameParts[0].' '.$givenNameInitials[1].'.';
			$givenNameVariants[] = $givenNameParts[0].' '.$givenNameInitials[1];			
			$givenNameVariants[] = implode('. ', $givenNameInitials).'.';
			$givenNameVariants[] = implode('.-', $givenNameInitials).'.';
			$givenNameVariants[] = implode('.', $givenNameInitials).'.';
			$givenNameVariants[] = implode(' ', $givenNameInitials);
			$givenNameVariants[] = implode('-', $givenNameInitials);
			$givenNameVariants[] = implode('', $givenNameInitials);
		}
		
		array_unique($givenNameVariants);
		array_unique($familyNameVariants);
		
		foreach($familyNameVariants as $familyNameVariant)
		{
			foreach($givenNameVariants as $givenNameVariant)
			{
				$nameVariant = $familyNameVariant.', '.$givenNameVariant;
				
				if($nameVariant !== $controlName)
				{
					$escapedNameVariant = $irts->real_escape_string($nameVariant);
					
					//Check for existing nameVariant entry for this localID
					$existing = getValues($irts, "SELECT rowID FROM `metadata` WHERE source='local' AND idInSource = '$localID' AND (field='local.name.variant' OR field='local.person.name') AND value = '$escapedNameVariant' AND deleted IS NULL", array('rowID'), 'arrayOfValues');
					if(empty($existing))
					{
						$place++;
						$field = 'local.name.variant';
						$rowID = mapTransformSave('local', $localID, '', $field, '', $place, $nameVariant, NULL);
						$variantsAdded[]=$nameVariant;
					}
				}
			}
		}
		return $variantsAdded;
	}

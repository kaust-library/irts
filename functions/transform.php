<?php	
	//Define function to transform metadata based on transformations table entries
	function transform($source, $field, $element, $value)
	{			
		global $irts;
		
		//check if transformation is required
		$transformations = select($irts, "SELECT * FROM `transformations` WHERE `source` LIKE ? AND `field` LIKE ? ORDER BY `place` ASC", array($source, $field));
		
		//if matched, transform
		if(mysqli_num_rows($transformations) !== 0)
		{
			while($transformation = $transformations->fetch_assoc())
			{
				$value = runTransformation($transformation, $element, $value);
			}
		}
		else
		{
			$transformations = select($irts, "SELECT * FROM `transformations` WHERE `source` LIKE ? ORDER BY `place` ASC", array($source));
			
			if(mysqli_num_rows($transformations) !== 0)
			{
				while($transformation = $transformations->fetch_assoc())
				{
					//This allows us to match on a namespace.element that matches multiple namespace.element.qualifier form field names
					if(strpos($field, $transformation['field'])!==FALSE)
					{
						$value = runTransformation($transformation, $element, $value);
					}
				}
			}
		}
		
		return $value;
	}	
	
	//function to be used if there are matching transformations
	function runTransformation($transformation, $element, $value)
	{
		$type = $transformation['type'];
	
		if($type === 'replacePartOfString')
		{
			$parts = explode('::with::', $transformation['transformation']);
			$value = str_replace($parts[0], $parts[1], $value);
		}
		elseif($type === 'pregReplacePartOfString')
		{
			$parts = explode('::with::', $transformation['transformation']);
			$value = preg_replace($parts[0], $parts[1], $value);
		}
		elseif($type === 'prependString')
		{
			$value = $transformation['transformation'].$value;
		}
		elseif($type === 'reorderPartsOfString')
		{
			$orders = explode('::to::', $transformation['transformation']);
			if($orders[0]==='firstName lastName')
			{
				$parts = explode(' ', $value);
			}
			
			if($orders[1]==='lastName, firstName')
			{
				$value = array_pop($parts).', '.implode(' ', $parts);	
			}
		}
		elseif($type === 'getPartOfString')
		{
			$characterPlaces = explode('::to::', $transformation['transformation']);
			$value = substr($value, $characterPlaces[0], $characterPlaces[1]); 
		}
		elseif($type === 'useValueOfChildElement')
		{
			$value = (string)$element->{$transformation['transformation']};
		}
		elseif($type === 'useValueOfAttribute')
		{
			$value = (string)$element[$transformation['transformation']];
		}
		elseif($type === 'runFunction')
		{
			$value = $transformation['transformation']($value);
		}
		
		return $value;
	}

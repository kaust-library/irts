<?php
	//Define function to map from source field names to standard field names
	function mapField($source, $field, $parentField)
	{			
		global $irts;
		
		//check if element is mapped
		$mappings = select($irts, "SELECT `standardField` FROM `mappings` WHERE `source` LIKE ? AND `parentFieldInSource` LIKE ? AND `sourceField` LIKE ?", array($source, $parentField, $field));
		
		//if matched			
		if(mysqli_num_rows($mappings) !== 0)
		{
			while($mapping = $mappings->fetch_assoc())
			{
				$field = $mapping['standardField'];				
			}
		}
		elseif(strpos($field, '.')===FALSE)
		{
			//For non-standard field names, prepend the source as the namespace
			$field = $source.'.'.$field;
		}
		return $field;
	}
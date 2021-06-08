<?php
	//Define function to identify the parts of a name
	function explodeName($name)
	{
		$nameParts = array();
		if(strpos($name, ', ') !== FALSE)
		{
			$parts = explode(', ', $name);
			$nameParts['lastName'] = $parts[0];
			$nameParts['firstName'] = $parts[1];
			$nameParts['fullName'] = $parts[1].' '.$parts[0];
		}
		else
		{
			$nameParts['fullName'] = $name;
			$nameParts['lastName'] = '';
			$nameParts['firstName'] = '';
		}
		return $nameParts;
	}	

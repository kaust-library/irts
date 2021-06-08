<?php
	//Define function to look for an internal person
	function checkPerson($person)
	{
		global $irts;

		//Also needs to check name variants
		$fields = array('localID'=>'local.person.id','orcid'=>'dc.identifier.orcid','scopusid'=>'dc.identifier.scopusAuthorID','name'=>array('local.person.name','local.name.variant'),'controlName'=>'local.person.name','email'=>'local.person.email','orgUnitNumber'=>'local.org.id','studentNumber'=>'local.person.studentNumber','personnelNumber'=>'local.person.personnelNumber');

		$matchfound = FALSE;
		foreach($person as $key => $value)
		{
			$check = array_unique(getValues($irts, setSourceMetadataQuery('local', NULL, NULL, $fields[$key], $value), array('idInSource'), 'arrayOfValues'));

			//accept result and leave loop if unique match found
			if(count($check) === 1)
			{
				$matchfound = TRUE;

				foreach($fields as $label => $field)
				{
					if(is_string($field))
					{
						$person[$label]=getValues($irts, setSourceMetadataQuery('local', $check[0], NULL, $field), array('value'), 'singleValue');
					}
				}

				break 1;
			}
		}

		if(!$matchfound)
		{
			if(isset($person['name']))
			{
				$person = array("controlName" => $person['name']);
			}
		}

		return $person;
	}

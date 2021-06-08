<?php
	//Define function to check additional sources for values for a given DOI and field
	function checkAdditionalSources($doi, $field, $sources = NULL)
	{
		global $irts;

		$value = '';

		if(is_null($sources))
		{
			$sources = array('crossref'=>$doi, 'doi'=>$doi);

			foreach(array('scopus','ieee','europePMC','datacite') as $source)
			{
				$idInSource = getValues($irts, setSourceMetadataQuery($source, NULL, NULL, "dc.identifier.doi", $doi), array('idInSource'), 'singleValue');

				if(!empty($idInSource))
				{
					$sources[$source] = $idInSource;
				}
			}
		}

		foreach($sources as $source => $idInSource)
		{
			if($field === 'dc.date.issued')
			{
				$alternativeFields = array('crossref.date.published-online', 'crossref.date.published-print', 'crossref.date.created');

				foreach($alternativeFields as $alternate)
				{
					$value = getValues($irts, setSourceMetadataQuery($source, $doi, NULL, $alternate), array('value'), 'singleValue');

					if(!empty($value))
					{
						if($value <= TODAY)
						{
							$value = array($value);
							
							break 1;
						}
					}
				}
			}
			else
			{
				$value = getValues($irts, setSourceMetadataQuery($source, $idInSource, NULL, $field), array('value'));
			}

			//end loop if value found
			if(!empty($value))
			{
				break 1;
			}
		}

		return $value;
	}

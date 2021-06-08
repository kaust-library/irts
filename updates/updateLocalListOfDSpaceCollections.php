<?php
	//Define function to update local list of DSpace collections with their ids, handles, and names

	function updateLocalListOfDSpaceCollections($report, $errors, $recordTypeCounts)
	{
		global $irts;
		$source = 'dspace';

		$dSpaceAuthHeader = loginToDSpaceRESTAPI();

		$list = json_decode(getListOfCollectionsFromDSpaceRESTAPI($dSpaceAuthHeader), TRUE);

		$recordTypeCounts['collections'] = 0;

		foreach($list as $collection)
		{
			$recordTypeCounts['collections']++;

			$idInSource = 'collection_'.$collection[DSPACE_INTERNAL_ID_KEY_NAME];

			foreach($collection as $key => $value)
			{
				if(is_string($value)&&!empty($value))
				{
					$recordTypeCounts['all']++;

					$field = 'dspace.collection.'.$key;
					$result = saveValue($source, $idInSource, $field, 1, $value, NULL);

					$recordTypeCounts[$result['status']]++;
				}
			}

			//print_r($collection);
			//break;
		}

		$summary = saveReport(__FUNCTION__, $report, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$summary);
	}

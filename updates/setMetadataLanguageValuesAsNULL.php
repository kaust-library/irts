<?php
	//Define function to update language values on metadata to null
	function setMetadataLanguageValuesAsNULL($report, $errors, $recordTypeCounts)
	{
		global $irts;

		$dSpaceAuthHeader = loginToDSpaceRESTAPI();

		$result = $irts->query("SELECT DISTINCT(`idInSource`) FROM `metadata` WHERE `source` LIKE 'dspace' AND `field` LIKE 'dspace.metadata.language' AND `deleted` IS NULL AND added > (SELECT timestamp FROM messages WHERE process = 'setMetadataLanguageValuesAsNULL' ORDER BY timestamp DESC LIMIT 1)");

		//$result = $irts->query("SELECT DISTINCT(`idInSource`) FROM `metadata` WHERE `source` LIKE 'dspace' AND `field` LIKE 'dspace.metadata.language' AND `deleted` IS NULL");

		//$result = $irts->query("SELECT DISTINCT(`idInSource`) FROM `metadata` WHERE `source` LIKE 'dspace' AND `field` LIKE 'dspace.metadata.language' AND `deleted` IS NULL  ORDER BY `metadata`.`idInSource` DESC LIMIT 50");

		while($row = $result->fetch_assoc())
		{
			$idInSource = $row['idInSource'];

			$recordTypeCounts['all']++;

			$json = getItemMetadataFromDSpaceRESTAPI($idInSource, $dSpaceAuthHeader);

			$metadata = dSpaceJSONtoMetadataArray($json);

			$recordTypeCounts['modified']++;

			$metadata = appendProvenanceToMetadata($idInSource, $metadata, 'setMetadataLanguageValuesAsNULL');

			$json = prepareItemMetadataAsDSpaceJSON($metadata);

			$response = putItemMetadataToDSpaceRESTAPI($idInSource, $json, $dSpaceAuthHeader);

			sleep(5);
			set_time_limit(0);
		}

		$summary = saveReport(__FUNCTION__, $report, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$summary);
	}

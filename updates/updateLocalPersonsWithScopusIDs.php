<?php
	//Define function to add Scopus IDs as local person data based on name matches
	function updateLocalPersonsWithScopusIDs($report, $errors, $recordTypeCounts)
	{
		global $irts;

    $recordTypeCounts['updated'] = 0;

    $scopusIDsAndNames = getValues($irts, "SELECT DISTINCT authorid.value scopusid, authorname.value name FROM `metadata` authorid LEFT JOIN `metadata` authorname ON authorid.`parentRowID` = authorname.`rowID`
		WHERE authorid.`source` LIKE 'scopus'
		AND authorid.`parentRowID` IN (
		    SELECT `rowID` FROM `metadata`
		    WHERE `source` LIKE 'scopus'
			AND `rowID` IN (
		        SELECT `parentRowID` FROM `metadata`
		        WHERE `source` LIKE 'scopus'
		        AND `rowID` IN (
		            SELECT `parentRowID` FROM `metadata`
		            WHERE `source` LIKE 'scopus'
		            AND `field` LIKE 'dc.identifier.scopusid'
		            AND `value` LIKE '".SCOPUS_AF_ID."'
		            AND `deleted` IS NULL
		        )
		        AND `field` LIKE 'dc.contributor.affiliation'
		        AND `deleted` IS NULL
		    )
		    AND `field` LIKE 'dc.contributor.author'
		    AND `deleted` IS NULL
		)
		AND authorid.`field` LIKE 'dc.identifier.scopusid'
		AND authorid.`deleted` IS NULL", array('scopusid','name'), 'arrayOfValues');

		$itemCounts = array();

		$scopusIDsAndItemCounts = getValues($irts, "SELECT authorid.value scopusid, COUNT(*) AS `ItemCount` FROM `metadata` authorid LEFT JOIN `metadata` authorname ON authorid.`parentRowID` = authorname.`rowID`
		WHERE authorid.`source` LIKE 'scopus'
		AND authorid.`parentRowID` IN (
		    SELECT `rowID` FROM `metadata`
		    WHERE `source` LIKE 'scopus'
			AND `rowID` IN (
		        SELECT `parentRowID` FROM `metadata`
		        WHERE `source` LIKE 'scopus'
		        AND `rowID` IN (
		            SELECT `parentRowID` FROM `metadata`
		            WHERE `source` LIKE 'scopus'
		            AND `field` LIKE 'dc.identifier.scopusid'
		            AND `value` LIKE '".SCOPUS_AF_ID."'
		            AND `deleted` IS NULL
		        )
		        AND `field` LIKE 'dc.contributor.affiliation'
		        AND `deleted` IS NULL
		    )
		    AND `field` LIKE 'dc.contributor.author'
		    AND `deleted` IS NULL
		)
		AND authorid.`field` LIKE 'dc.identifier.scopusid'
		AND authorid.`deleted` IS NULL
    GROUP BY `scopusid`
    ORDER BY ItemCount DESC", array('scopusid','ItemCount'), 'arrayOfValues');

		foreach($scopusIDsAndItemCounts as $scopusIDAndItemCount)
		{
			$itemCounts[$scopusIDAndItemCount['scopusid']] = $scopusIDAndItemCount['ItemCount'];
		}

		$professors = getValues($irts, "SELECT id.idInSource, id.value localPersonID, name.value name
		FROM metadata id LEFT JOIN metadata name ON id.idInSource = name.idInSource
		WHERE id.source = 'local'
		AND id.field = 'local.person.id'
		AND id.idInSource IN (
		    SELECT idInSource FROM metadata
				WHERE source = 'local'
		    AND field = 'local.person.title'
		    AND (value LIKE '%prof %' OR value LIKE '%prof.%' OR value LIKE '%professor%' )
				AND deleted IS NULL
		)
		AND name.source = 'local'
		AND name.field = 'local.person.name'
		AND id.deleted IS NULL
		AND name.deleted IS NULL", array('idInSource', 'localPersonID', 'name'), 'arrayOfValues');

		foreach($professors as $professor)
		{
			$names = array();

			$recordTypeCounts['all']++;

			$idInSource = $professor['idInSource'];

			$nameVariants = getValues($irts, setSourceMetadataQuery('local', $idInSource, NULL, 'local.name.variant'), array('value'));

			print_r($nameVariants);

			foreach($nameVariants as $nameVariant)
			{
				$checkUnique = getValues($irts, "SELECT COUNT(DISTINCT idInSource) AS count FROM `metadata` WHERE `source` LIKE 'local' AND `field` IN ('local.person.name','local.name.variant') AND value LIKE '$nameVariant'", array('count'), 'singleValue');

				if((int)$checkUnique === 1)
				{
				  $names[] = $nameVariant;
				}
			}

			$names[] = $professor['name'];

			print_r($names);

			$scopusids = array();

			foreach($scopusIDsAndNames as $scopusIDandName)
			{
				if(in_array($scopusIDandName['name'], $names))
				{
					$matches = array_unique(getValues($irts, setSourceMetadataQuery('local', NULL, NULL, array('local.person.name','local.name.variant'), $scopusIDandName['name']), array('idInSource'), 'arrayOfValues'));

					//accept result and leave loop if unique match found
					if(count($matches) === 1)
					{
						$scopusids[$scopusIDandName['scopusid']] = $itemCounts[$scopusIDandName['scopusid']];
					}
			 	}
			}
			arsort($scopusids);

			print_r($scopusids);

			if(!empty($scopusids))
			{
				$result = saveValue('local', $idInSource, 'dc.identifier.scopusid', 1, array_keys($scopusids)[0], NULL);

				$recordTypeCounts[$result['status']]++;
			}
		}
		$summary = saveReport(__FUNCTION__, $report, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$summary);
	}
?>

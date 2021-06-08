<?php
	//Define function to set a query statement used for getting a list of repository handles for where an individual is an author
	function setIndividualPublicationListQuery($orcid, $controlName, $year, $types)
	{
		global $irts;

		if(!empty($orcid))
		{
			$query = "SELECT m.`idInSource` FROM `metadata` m
			LEFT JOIN metadata m2 ON m.rowID = m2.parentRowID
			WHERE m.`source` LIKE 'repository'
			AND m.`field` LIKE 'dc.contributor.author'
			AND m.deleted IS NULL
			AND m2.`source` LIKE 'repository'
			AND m2.`field` LIKE 'dc.identifier.orcid'
			AND m2.value LIKE '$orcid'
			AND m2.deleted IS NULL ";
		}
		else
		{
			$query = "SELECT m.`idInSource` FROM `metadata` m
			WHERE m.`source` LIKE 'repository'
			AND m.`field` LIKE 'dc.contributor.author'
			AND m.deleted IS NULL
			AND m.value LIKE '$controlName' ";
		}

		if(!is_null($year))
		{
			$query .= "AND m.idInSource IN (
					SELECT `idInSource` FROM metadata
					WHERE `source` LIKE 'repository'
					AND `field` IN ('dc.date.issued','dc.date.published-online','dc.date.published-print','dc.date.posted')
					AND `value` LIKE '$year%'
					AND `deleted` IS NULL) ";
		}

		if(!is_null($types))
		{
			$query .= "AND m.idInSource IN (
					SELECT `idInSource` FROM metadata
					WHERE `source` LIKE 'repository'
					AND `field` LIKE 'dc.type'
					AND `value` IN ($types)
					AND `deleted` IS NULL)";
		}

		echo $query.PHP_EOL;

		return $query;
	}

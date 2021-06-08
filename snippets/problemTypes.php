<?php
	$problemTypes = array(
		//Marked as completed, but no matching record in DSpace
		'notTransferred'=>
			array(
				'description' => 'Completed, not transferred',
				'typeCountQuery' =>
					"SELECT COUNT(*) AS `typeCount`, m.`value` itemType 
					FROM `metadata` m 
					LEFT JOIN metadata m2 
					USING(idInSource) 
					WHERE m.`source` LIKE 'irts' 
					AND m.`field` LIKE 'dc.type' 
					AND m2.field LIKE 'irts.status' 
					AND m2.value LIKE 'completed' 
					AND m2.deleted IS NULL 
					AND m2.idInSource IN (
						SELECT idInSource FROM metadata WHERE `source` LIKE 'irts' AND `field` LIKE 'dc.identifier.doi' AND deleted IS NULL
						AND value NOT IN (
							SELECT value FROM metadata WHERE `source` IN ('dspace','repository') AND `field` LIKE 'dc.identifier.doi' AND deleted IS NULL
						)
					)
					GROUP BY m.`value` ORDER BY `typeCount` DESC",
				'itemListQuery' => 
					"SELECT DISTINCT idInSource, m2.added, m2.rowID FROM `metadata` m LEFT JOIN metadata m2 USING(idInSource) 
					WHERE m.`source` LIKE 'irts' 
					AND m.`field` LIKE 'dc.type' 
					AND m.`value` LIKE '{itemType}' 
					AND m2.field LIKE 'irts.status' 
					AND m2.value LIKE 'completed' 
					AND m2.deleted IS NULL
					AND m2.idInSource IN (
						SELECT idInSource FROM metadata WHERE `source` LIKE 'irts' AND `field` LIKE 'dc.identifier.doi' AND deleted IS NULL
						AND value NOT IN (
							SELECT value FROM metadata WHERE `source` IN ('dspace','repository') AND `field` LIKE 'dc.identifier.doi' AND deleted IS NULL
						)
					)
					ORDER BY m2.added DESC"
				),
		'localAuthorsNotIdentified'=>
			array(
				'description'=>'Completed, transferred to DSpace, no local person entries in DSpace record',
				'typeCountQuery'=>"SELECT COUNT(*) AS `typeCount`, `value` itemType 
					FROM `metadata`
					WHERE field LIKE 'dc.type' 
					AND deleted IS NULL
					AND idInSource IN (
						SELECT idInSource FROM metadata WHERE source = 'irts'
						AND field = 'dc.identifier.doi'
						AND value IN (
							SELECT value FROM `metadata` WHERE `source` LIKE 'dspace' 
                            AND `field` LIKE 'dc.identifier.doi' 
                            AND `deleted` IS NULL
							AND idInSource NOT IN (
								SELECT idInSource FROM `metadata` WHERE `source` LIKE 'dspace' 
                                AND `field` LIKE 'local.person' 
                                AND `deleted` IS NULL
							) 
						)
						AND deleted IS NULL
					)
					GROUP BY `value` ORDER BY `typeCount` DESC",
				'itemListQuery' => "SELECT DISTINCT idInSource, added 
								FROM `metadata`
								WHERE field LIKE 'dc.type' 
								AND value LIKE '{itemType}'
								AND deleted IS NULL
								AND idInSource IN (
									SELECT idInSource FROM metadata WHERE source = 'irts'
									AND field = 'dc.identifier.doi'
									AND value IN (
										SELECT value FROM `metadata` WHERE `source` LIKE 'dspace' 
										AND `field` LIKE 'dc.identifier.doi' 
										AND `deleted` IS NULL
										AND idInSource NOT IN (
											SELECT idInSource FROM `metadata` WHERE `source` LIKE 'dspace' 
											AND `field` LIKE 'local.person' 
											AND `deleted` IS NULL
										) 
										AND value NOT LIKE '10.25781%'
									)
									AND deleted IS NULL
								)
								ORDER BY added DESC"
			)		
		);
?>

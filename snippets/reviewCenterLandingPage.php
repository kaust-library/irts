<?php
	echo '<div class="container"><div class="row"><div class="col border border-dark rounded m-2 p-4"><h4><b>Old records needing edits by type:</b></h4><table><tr><th>Type</th><th>Count</th></tr>';
	
	echo '<tr><th><h5 style="text-align:center"><b>Status: Unpaywall Result</b></h5></th></tr>';
	
	$unpaywallItems = getValues($irts, "SELECT count(`idInSource`) AS count FROM `metadata` WHERE `source` = 'irts' AND `field` = 'irts.check.unpaywall' AND value = 'inProcess' AND deleted IS NULL", array('count'), 'singleValue');

	echo '<tr><td>Unpaywall</td><td><a type="button" class="btn btn-primary rounded" href="reviewCenter.php?formType=reviewStep&itemType=Unpaywall">'.$unpaywallItems.'</a></td></tr>';
	
	foreach($problemTypes as $problemType => $problemDetails)
	{
		echo '<tr><th><h5 style="text-align:center"><b>Status: '.$problemDetails['description'].'</b></h5></th></tr>';
	
		$reviewItemTypes = getValues($irts, $problemDetails['typeCountQuery'], array('typeCount', 'itemType'));
		
		foreach($reviewItemTypes as $reviewItemType)
		{
			echo '<tr><td>'.$reviewItemType['itemType'].'</td><td><a type="button" class="btn btn-primary rounded" href="reviewCenter.php?formType=review&problemType='.$problemType.'&itemType='.$reviewItemType['itemType'].'">'.$reviewItemType['typeCount'].'</a></td></tr>';
		}
	}

	echo '</table></div>';

	echo '<div class="col border border-dark rounded m-2 p-4"><h4><b>New items to process by type:</b></h4><a href="reviewCenter.php?formType=addNewItem" type="button" class="btn btn-primary rounded">Add New Item</a>';

	echo '<a href="reviewCenter.php?formType=uploadFile" type="button" class="btn btn-primary rounded" style="margin-left: 10px;">Upload a File</a><table><tr><th>Type</th><th>Count</th></tr>';

	$newItemTypes = getValues($irts, "SELECT COUNT(*) AS `typeCount`, m.`value` itemType FROM `metadata` m LEFT JOIN metadata m2 USING(idInSource) 
		WHERE m.`source` LIKE 'irts' 
		AND m.`field` LIKE 'dc.type' 
		AND m2.field LIKE 'irts.status' 
		AND m2.value LIKE 'inProcess' 
		AND m2.deleted IS NULL 
		GROUP BY m.`value` 
		ORDER BY `typeCount` DESC", array('typeCount', 'itemType'));

	foreach($newItemTypes as $newItemType)
	{
		echo '<tr><td>'.$newItemType['itemType'].'</td><td><a type="button" class="btn btn-primary rounded" href="reviewCenter.php?formType=processNew&itemType='.str_replace(' ', '+',$newItemType['itemType']).'&page=0">'.$newItemType['typeCount'].'</a></td></tr>';
	}

	echo '</table></div></div><hr>';

	echo '<div class="row"><div class="col border border-dark rounded m-2 p-4"><h4><b>Unmatched variants:</b></h4><table><tr><th>Type</th><th>Count</th></tr>';

	$unmatchedVariantTypes[0]['itemType'] = 'Org Unit Name';

	$unmatchedVariantTypes[0]['typeCount'] = getValues($irts, "SELECT COUNT(DISTINCT value) AS count FROM `metadata` WHERE `field` = 'local.acknowledged.supportUnit' AND value NOT IN (SELECT value FROM metadata WHERE source = 'local' AND (field = 'local.org.name' OR field = 'local.name.variant'))", array('count'), 'singleValue');

	$unmatchedVariantTypes[1]['itemType'] = 'Affiliation';

	$unmatchedVariantTypes[1]['typeCount'] = getValues($irts, "SELECT COUNT(DISTINCT value) AS count FROM `metadata` WHERE `field` = 'irts.unmatched.affiliation' AND value NOT IN (SELECT value FROM metadata WHERE source = 'local' AND (field = 'local.org.name' OR field = 'local.name.variant') AND deleted IS NULL)", array('count'), 'singleValue');

	$unmatchedVariantTypes[2]['itemType'] = 'Person Name';

	$unmatchedVariantTypes[2]['typeCount'] = getValues($irts, "SELECT COUNT(DISTINCT value) AS count FROM `metadata` WHERE `field` = 'irts.unmatched.person' AND value NOT IN (SELECT value FROM metadata WHERE source = 'local' AND field = 'local.name.variant' AND deleted IS NULL)", array('count'), 'singleValue');

	foreach($unmatchedVariantTypes as $unmatchedVariantType)
	{
		echo '<tr><td>'.$unmatchedVariantType['itemType'].'</td><td><a type="button" class="btn btn-primary rounded" href="reviewCenter.php?formType=variantMatching&itemType='.$unmatchedVariantType['itemType'].'">'.$unmatchedVariantType['typeCount'].'</a></td></tr>';
	}

	echo '</table></div>';

	echo '<div class="col border border-dark rounded m-2 p-4"><h4><b>Steps needing review:</b></h4><table><tr><th>Type</th><th>Count</th></tr>';

	$stepsToReview[1]['itemType'] = 'Dataset Relationships';

	$stepsToReview[1]['typeCount'] = getValues($irts, "SELECT COUNT(DISTINCT idInSource) as count 
		FROM `metadata`
		WHERE source = 'irts'
		AND field IN ('dc.related.accessionNumber','dc.related.datasetDOI','dc.related.datasetURL','dc.related.codeURL') 
		AND deleted IS NULL
		AND (
			idInSource IN (
				SELECT idInSource FROM metadata WHERE source = 'irts'
				AND field = 'dc.identifier.doi'
				AND value IN (
					SELECT value FROM metadata WHERE source = 'repository'
					AND field = 'dc.identifier.doi'
					AND deleted IS NULL
				)
				AND deleted IS NULL
			)
			OR
			idInSource IN (
				SELECT idInSource FROM metadata WHERE source = 'irts'
				AND field = 'dc.identifier.arxivid'
				AND value IN (
					SELECT value FROM metadata WHERE source = 'repository'
					AND field = 'dc.identifier.arxivid'
					AND deleted IS NULL
				)
				AND deleted IS NULL
			)
		)", array('count'), 'singleValue');

	foreach($stepsToReview as $stepToReview)
	{
		echo '<tr><td>'.$stepToReview['itemType'].'</td><td><a type="button" class="btn btn-primary rounded" href="reviewCenter.php?formType=reviewStep&itemType='.$stepToReview['itemType'].'">'.$stepToReview['typeCount'].'</a></td></tr>';
	}

	echo '</table></div>';

	echo '</div><hr></div>';
?>

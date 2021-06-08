<?php
	//Define function to return all relevant departmental ids for a given person
	function checkDeptIDs($localPersonID, $pubdate)
	{
		global $irts, $message;
		
		$programIDs = getValues($irts, "SELECT value FROM `metadata` WHERE `source` LIKE 'local'
			AND `idInSource` LIKE 'org_%'
			AND `field` LIKE 'local.org.id' 
			AND deleted IS NULL
			AND idInSource IN (
				SELECT `idInSource` FROM `metadata` WHERE `source` LIKE 'local' 
				AND `field` LIKE 'local.org.type' 
				AND `value` LIKE 'program' 
				AND `deleted` IS NULL
			)", array('value'), 'arrayOfValues');
			
		$alternateProgramIDs = getValues($irts, "SELECT value FROM `metadata` WHERE `source` LIKE 'local'
			AND `idInSource` LIKE 'org_%'
			AND `field` LIKE 'local.org.id' 
			AND deleted IS NULL
			AND idInSource IN (
				SELECT `idInSource` FROM `metadata` WHERE `source` LIKE 'local' 
				AND `field` LIKE 'local.org.type' 
				AND `value` LIKE 'researchcenter' 
				AND `deleted` IS NULL
			)
			AND idInSource IN (
				SELECT `idInSource` FROM `metadata` WHERE `source` LIKE 'local' 
				AND `field` LIKE 'local.org.type' 
				AND `value` LIKE 'program' 
				AND `deleted` IS NOT NULL
			)", array('value'), 'arrayOfValues');

		$thisAuthorDeptIds = getValues($irts, "SELECT target.`rowID`, target.value FROM `metadata` target LEFT JOIN metadata sibling USING(parentRowID)
		WHERE target.`source` = 'local'
		AND target.`idInSource` = 'person_$localPersonID'
		AND target.field = 'local.org.id'
		AND target.deleted IS NULL
		AND sibling.field = 'local.date.start'
		AND sibling.value < '$pubdate'
		AND sibling.deleted IS NULL
		AND target.rowID NOT IN (
			SELECT target.`rowID` FROM `metadata` target LEFT JOIN metadata sibling USING(parentRowID) WHERE target.`source` = 'local'
			AND target.`idInSource` = 'person_$localPersonID'
			AND target.field = 'local.org.id'
			AND target.deleted IS NULL
			AND sibling.field = 'local.date.end'
			AND sibling.value < '$pubdate'
			AND sibling.deleted IS NULL)", array('value'), 'arrayOfValues');

		if(empty($thisAuthorDeptIds))
		{
			$thisAuthorDeptIds = getValues($irts, "SELECT target.`rowID`, target.value FROM `metadata` target 
				WHERE target.`source` = 'local' 
				AND target.`idInSource` = 'person_$localPersonID' 
				AND target.field = 'local.org.id' 
				AND target.deleted IS NULL", array('value'), 'arrayOfValues');

			//$message .= '<br> - Pub date may not fall within departmental affiliation range - any dept id added that we could find!!!';
		}

		$thisAuthorProgramIds = array();
		$thisAuthorAlternateProgramIds = array();
		foreach($thisAuthorDeptIds as $deptId)
		{
			if(in_array($deptId, $programIDs))
			{
				$thisAuthorProgramIds[] = $deptId;
			}
			elseif(in_array($deptId, $alternateProgramIDs))
			{
				$thisAuthorAlternateProgramIds[] = $deptId;
			}
		}
		
		if(!empty($thisAuthorProgramIds) && !empty($thisAuthorAlternateProgramIds))
		{
			$thisAuthorDeptIds = array_diff($thisAuthorDeptIds, $thisAuthorAlternateProgramIds);
		}

		$thisAuthorDeptIds = array_unique($thisAuthorDeptIds);
		$thisAuthorDeptIds = array_filter($thisAuthorDeptIds);

		$facultyID = getValues($irts, "SELECT DISTINCT idInSource FROM `metadata`
				WHERE `source` LIKE 'local'
				AND `idInSource` = 'person_$localPersonID'
				AND `field` LIKE 'local.person.title'
				AND (`value` LIKE '%Prof %' OR `value` LIKE '%Professor%' OR `value` LIKE '%Prof.%')
				AND value NOT LIKE '%Former%'
				AND value NOT LIKE '%Visiting%'
				AND value NOT LIKE '%Emeritus%'
				AND value NOT LIKE '%Courtesy%'
				AND `deleted` IS NULL", array('idInSource'), 'singleValue');
		
		//Skip check for parent org units for faculty because they will have a direct affiliation to a division
		if(empty($facultyID))
		{
			//check for divisions if only program or research center is given
			$thisAuthorDeptIds = checkParentOrgUnit($thisAuthorDeptIds);
		}

		return $thisAuthorDeptIds;
	}

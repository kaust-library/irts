<?php
	//check for divisions if only program or research center is given
	function checkParentOrgUnit($deptIDs)
	{
		global $irts, $message;

		foreach($deptIDs as $deptID)
		{
			$parentDeptID = getValues($irts, "SELECT value FROM `metadata` WHERE `source` LIKE 'local' AND `idInSource` LIKE 'org_$deptID' AND field = 'local.org.parent' AND deleted IS NULL", array('value'), 'singleValue');

			//if matched
			if(!empty($parentDeptID))
			{
				$parentType = getValues($irts, "SELECT value FROM `metadata` WHERE `source` LIKE 'local' AND `idInSource` LIKE 'org_$parentDeptID' AND field = 'local.org.type' AND deleted IS NULL", array('value'), 'singleValue');

				if($parentType === 'division')
				{
					if(!in_array($parentDeptID, $deptIDs))
					{
						$deptIDs[] = $parentDeptID;
					}
				}
			}
		}

		return $deptIDs;
	}

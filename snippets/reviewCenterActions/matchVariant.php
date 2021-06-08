<?php
	if(!isset($_POST['action']))
	{
		if($_GET['itemType']==='Org Unit Name')
		{
			$unmatchedVariant = getValues($irts, "SELECT DISTINCT value FROM `metadata` WHERE `field` = 'local.acknowledged.supportUnit' AND value NOT IN (SELECT value FROM metadata WHERE source = 'local' AND (field = 'local.org.name' OR field = 'local.name.variant')) ORDER BY value ASC LIMIT $page, 1", array('value'), 'singleValue');
		}
		elseif($_GET['itemType']==='Affiliation')
		{
			$unmatchedVariant = getValues($irts, "SELECT value FROM `metadata` WHERE `field` = 'irts.unmatched.affiliation' AND value NOT IN (SELECT value FROM metadata WHERE source = 'local' AND (field = 'local.org.name' OR field = 'local.name.variant')) AND deleted IS NULL ORDER BY place ASC LIMIT $page, 1", array('value'), 'singleValue');
		}
		elseif($_GET['itemType']==='Person Name')
		{
			$unmatchedVariant = getValues($irts, "SELECT value FROM `metadata` WHERE `field` = 'irts.unmatched.person' AND value NOT IN (SELECT value FROM metadata WHERE source = 'local' AND field = 'local.name.variant') AND deleted IS NULL ORDER BY value ASC LIMIT $page, 1", array('value'), 'singleValue');
			
			$handles = getValues($irts, "SELECT idInSource FROM `metadata` WHERE source = 'repository' AND `field` = 'local.person' AND value = '$unmatchedVariant' AND deleted IS NULL", array('idInSource'), 'arrayOfValues');
		}
		
		if($_GET['itemType']==='Person Name')
		{
			echo '<br><br><div class="row"><div class="col-lg-6"><form method="post" action="reviewCenter.php?'.$selections.'">
			'.$itemType.' To Match: <br><br><b>'.$unmatchedVariant.'</b><br><br>-- Listed as '.INSTITUTION_ABBREVIATION.' person on below publications:<br>';
			
			foreach($handles as $handle)
			{
				$title = getValues($irts, "SELECT value FROM `metadata` WHERE source = 'repository' AND idInSource = '$handle' AND `field` = 'dc.title' AND deleted IS NULL", array('value'), 'singleValue');
				
				echo '<li>'.$title.' -- record at: <a href="http://hdl.handle.net/'.$handle.'" target="_blank">'.$handle.'</a></li><br>';
			}
			
			echo '<input type="hidden" name="unmatchedVariant" value="'.$unmatchedVariant.'">
			<input type="hidden" name="page" value="'.($page).'">
			</div>
			<div class="col-lg-6">
				<select name="selected" size="10">';
				
				$listValues = getValues($irts, "SELECT `rowID`,`idInSource`, value FROM metadata WHERE source = 'local' AND field = 'local.person.name' AND deleted IS NULL ORDER BY value ", array('rowID', 'idInSource', 'added'));

				foreach($listValues as $listValue)
				{
					echo '<option value="'.htmlspecialchars(json_encode($listValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT)).'">'.$listValue['value'].'</option>';
					
					
				}
				
				echo '</select></div><br><br>';
			
			echo '<button class="btn btn-block btn-success" type="submit" name="action" value="matched">-- Matched: Match Selected From List --</button>
			<button class="btn btn-block btn-primary" type="submit" name="action" value="skip">-- Skip: Take No Action and Move to Next Item --</button>
			</form>
			</div>';
		}
		else
		{
			echo '<br><br><div class="col-lg-6"><form method="post" action="reviewCenter.php?'.$selections.'">
			'.$itemType.' To Match: <br><br><b>'.$unmatchedVariant.'</b><br><br>
			<input type="hidden" name="unmatchedVariant" value="'.$unmatchedVariant.'">
			<input type="hidden" name="page" value="'.($page).'">
			<select name="selected" size="10">';
			
			$listValues = getValues($irts, "SELECT `rowID`,`idInSource`, value FROM metadata WHERE source = 'local' AND field = 'local.org.name' AND deleted IS NULL ORDER BY value ", array('rowID', 'idInSource', 'added'));

			foreach($listValues as $listValue)
			{
				echo '<option value="'.htmlspecialchars(json_encode($listValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT)).'">'.$listValue['value'].'</option>';
			}
			
			echo '</select><br><br>';
			
			if($_GET['itemType']==='Org Unit Name')
			{
				echo '<button class="btn btn-block btn-warning" type="submit" name="action" value="isPerson">-- Person, Not an Org Unit: Mark as Person in Database --</button>';
			}
			
			if($_GET['itemType']==='Affiliation')
			{
				echo '<button class="btn btn-block btn-danger" type="submit" name="action" value="external">-- Not a '.INSTITUTION_ABBREVIATION.' Unit or Address: Mark as External Affiliation --</button>
				<button class="btn btn-block btn-warning" type="submit" name="action" value="addressOnly">-- No Org Unit Match, Address Only: Add as Recognized Generic '.INSTITUTION_ABBREVIATION.' Address --</button>';
			}
			
			echo '<button class="btn btn-block btn-success" type="submit" name="action" value="matched">-- Matched: Match Selected From List --</button>
			<button class="btn btn-block btn-primary" type="submit" name="action" value="skip">-- Skip: Take No Action and Move to Next Item --</button>
			</form>
			</div>';
		}
	}
	else
	{
		$action = $_POST['action'];
		
		if($action==='skip')
		{
			// continue to the main form
			$page++;
			header("Location: reviewCenter.php?formType=$formType&itemType=$itemType&page=$page");
			exit();
		}
		elseif($action === 'matched')
		{
			//print_r($_POST['selected']);
			
			$unmatchedVariant = $_POST['unmatchedVariant'];
			
			$selected = json_decode($_POST['selected'], TRUE);
			
			$idInSource = $selected['idInSource'];
			
			if($_GET['itemType']==='Person Name')
			{
				$fields = array('direct'=>array('local.person.name' => 'Person Name','dc.identifier.orcid' => 'ORCID','local.name.variant' => 'Already Accepted Person Name Variants'),'indirect'=>array('local.person.title' => 'All Job Titles','local.org.id' => 'All Related Org Units'));
				
				$labels = array();
				
				$details = array();
				foreach($fields['direct'] as $field => $label)
				{
					$labels[$field] = $label;
					
					$details[$field] = getValues($irts, "SELECT value FROM `metadata` WHERE source = 'local' AND parentRowID IS NULL AND idInSource = '$idInSource' AND `field` = '$field'", array('value'), 'arrayOfValues');
				}
				
				foreach($fields['indirect'] as $field => $label)
				{
					$labels[$field] = $label;
					
					$details[$field] = getValues($irts, "SELECT value FROM `metadata` WHERE source = 'local' AND parentRowID IS NOT NULL AND idInSource = '$idInSource' AND `field` = '$field'", array('value'), 'arrayOfValues');
				}
				
				$countOfExistingVariants = count($details['local.name.variant']);
			
				echo '<div class="col border border-dark rounded m-2 p-4"><b>Existing Person Record:</b><br><br>';
				foreach($details as $field => $values)
				{
					if(!empty($values))
					{
						if($field === 'dc.identifier.orcid')
						{
							echo '-- <b>'.$labels[$field].': </b><a href="https://orcid.org/'.$values[0].'" target="_blank">'.$values[0].'</a><br>';
						}
						elseif($field === 'local.org.id')
						{
							$orgNames = array();
							foreach($values as $orgID)
							{
								$orgNames[] = getValues($irts, "SELECT value FROM `metadata` WHERE source = 'local' AND idInSource = 'org_$orgID' AND `field` = 'local.org.name'", array('value'), 'singleValue');
							}
							
							echo '-- <b>'.$labels[$field].':</b> '.implode('; ', $orgNames).'<br>';
						}
						else
						{
							echo '-- <b>'.$labels[$field].':</b> '.implode('; ', $values).'<br>';
						}
					}
				}
				echo '</div>';
			}
			else
			{
				$fields = array('local.org.name' => 'Org Unit Name','local.org.url' => 'Org Unit Web Page Link','local.org.type' => 'Org Unit Type','local.name.variant' => 'Already Accepted Name Variants');
				
				$details = array();
				if(strpos($idInSource, 'org_')!==FALSE)
				{
					foreach($fields as $field => $label)
					{
						$details[$field] = getValues($irts, "SELECT value FROM `metadata` WHERE source = 'local' AND idInSource = '$idInSource' AND `field` = '$field'", array('value'), 'arrayOfValues');
					}
				}
				elseif(strpos($idInSource, 'person_')!==FALSE)
				{
					$parentRowID = $selected['rowID'];
					foreach($fields as $field => $label)
					{
						$details[$field] = getValues($irts, "SELECT value FROM `metadata` WHERE source = 'local' AND idInSource = '$idInSource' AND parentRowID = '$parentRowID' AND `field` = '$field'", array('value'), 'arrayOfValues');
					}
				}
				
				$countOfExistingVariants = count($details['local.name.variant']);
			
				echo '<div class="col border border-dark rounded m-2 p-4"><b>Existing Org Unit Record:</b><br><br>';
				foreach($details as $field => $values)
				{
					if(!empty($values))
					{
						if($field === 'local.org.url')
						{
							echo '-- <b>'.$fields[$field].': </b><a href="'.$values[0].'" target="_blank">'.$values[0].'</a><br>';
						}
						else
						{
							echo '-- <b>'.$fields[$field].':</b> '.implode('; ', $values).'<br>';
						}
					}				
				}
				echo '</div>';
			}			
			
			echo 'Are you sure you want to add the new variant "<b>'.$unmatchedVariant.'</b>" to the above record? <br>Please confirm below.<br><form method="post" action="reviewCenter.php?'.$selections.'">
				<input type="hidden" name="page" value="'.($page).'">
				<input type="hidden" name="countOfExistingVariants" value="'.$countOfExistingVariants.'">
				<input type="hidden" name="selected" value="'.htmlspecialchars($_POST['selected']).'">
				<input type="hidden" name="unmatchedVariant" value="'.$unmatchedVariant.'">
				<button class="btn btn-block btn-success" type="submit" name="action" value="confirmed">-- Confirm --</button>
			</form>';
		}
		elseif($action === 'addressOnly')
		{
			$countOfExistingAddresses = getValues($irts, "SELECT `place` FROM metadata WHERE source = 'local' AND field = 'local.address.variant' AND deleted IS NULL ORDER BY place DESC LIMIT 1", array('place'), 'singleValue');
			
			$field = 'local.address.variant';
			$rowID = mapTransformSave('local', 'org_30000085', '', $field, '', $countOfExistingAddresses+1, $unmatchedVariant, NULL);
			
			$rowID = getValues($irts, "SELECT rowID FROM `metadata` WHERE source='irts' AND field='irts.unmatched.affiliation' AND value = '$unmatchedVariant'", array('rowID'), 'singleValue');
			
			update($irts, 'metadata', array("deleted"), array(date("Y-m-d H:i:s"), $rowID), 'rowID');
			
			echo $unmatchedVariant.' successfully saved as generic '.INSTITUTION_ABBREVIATION.' address';
			
			echo '<hr>
			<div class="col-lg-6">	
			
			<form method="post" action="reviewCenter.php?'.$selections.'">
				<input class="btn btn-lg btn-primary" type="submit" name="next" value="-- Start Next Item --"></input>
				</form>
			</div>';
		}
		elseif($action === 'external')
		{
			$rowID = getValues($irts, "SELECT rowID FROM `metadata` WHERE source='irts' AND field='irts.unmatched.affiliation' AND value = '$unmatchedVariant'", array('rowID'), 'singleValue');
			
			update($irts, 'metadata', array("field"), array('irts.external.affiliation', $rowID), 'rowID');
			
			echo $unmatchedVariant.' successfully saved as external address';
			
			echo '<hr>
			<div class="col-lg-6">	
			
			<form method="post" action="reviewCenter.php?'.$selections.'">
				<input class="btn btn-lg btn-primary" type="submit" name="next" value="-- Start Next Item --"></input>
				</form>
			</div>';
		}
		elseif($action === 'isPerson')
		{
			$rowIDs = getValues($irts, "SELECT rowID FROM `metadata` WHERE source='irts' AND field='local.acknowledged.supportUnit' AND value = '$unmatchedVariant'", array('rowID'), 'arrayOfValues');
			
			foreach($rowIDs as $rowID)
			{
				update($irts, 'metadata', array("field"), array('local.acknowledged.person', $rowID), 'rowID');
			}
			
			echo $unmatchedVariant.' successfully marked as person';
			
			echo '<hr>
			<div class="col-lg-6">	
			
			<form method="post" action="reviewCenter.php?'.$selections.'">
				<input class="btn btn-lg btn-primary" type="submit" name="next" value="-- Start Next Item --"></input>
				</form>
			</div>';
		}
		elseif($action === 'confirmed')
		{
			//print_r($_POST['selected']);
			//print_r($_POST['unmatchedVariant']);
			//print_r($_POST['countOfExistingVariants']);
			
			$countOfExistingVariants = $_POST['countOfExistingVariants'];
			
			$unmatchedVariant = $_POST['unmatchedVariant'];
			
			$selected = json_decode($_POST['selected'], TRUE);
			
			$idInSource = $selected['idInSource'];
			
			if(strpos($idInSource, 'org_')!==FALSE)
			{
				$field = 'local.name.variant';
				$rowID = mapTransformSave('local', $idInSource, '', $field, '', $countOfExistingVariants+1, $unmatchedVariant, NULL);
			}
			elseif(strpos($idInSource, 'person_')!==FALSE)
			{
				if($_GET['itemType']==='Person Name')
				{
					$field = 'local.name.variant';
					$rowID = mapTransformSave('local', $idInSource, '', $field, '', $countOfExistingVariants+1, $unmatchedVariant, NULL);
					
					$rowID = getValues($irts, "SELECT rowID FROM `metadata` WHERE source='irts' AND field='irts.unmatched.person' AND value = '$unmatchedVariant'", array('rowID'), 'singleValue');
			
					update($irts, 'metadata', array("deleted"), array(date("Y-m-d H:i:s"), $rowID), 'rowID');
				}
				else
				{
					$parentRowID = $selected['rowID'];
					$field = 'local.name.variant';
					$rowID = mapTransformSave('local', $idInSource, '', $field, '', $countOfExistingVariants+1, $unmatchedVariant, $parentRowID);
				}
			}
			
			if($_GET['itemType']==='Affiliation')
			{
				$rowID = getValues($irts, "SELECT rowID FROM `metadata` WHERE source='irts' AND field='irts.unmatched.affiliation' AND value = '$unmatchedVariant'", array('rowID'), 'singleValue');
			
				update($irts, 'metadata', array("deleted"), array(date("Y-m-d H:i:s"), $rowID), 'rowID');
			}
			
			echo $unmatchedVariant.' successfully saved as '.$_GET['itemType'].' variant';
			
			echo '<hr>
			<div class="col-lg-6">	
			
			<form method="post" action="reviewCenter.php?'.$selections.'">
				<input class="btn btn-lg btn-primary" type="submit" name="next" value="-- Start Next Item --"></input>
				</form>
			</div>';
		}		
	}

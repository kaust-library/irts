<?php
	if(!isset($_POST['action']))
	{
		//Set startTime for this item
		$_SESSION["variables"]["startTime"]=date("Y-m-d H:i:s");

		if($_GET['itemType']==='Acknowledgements')
		{
			$record = array();

			$idInIRTS = getValues($irts, "SELECT DISTINCT i.`idInSource`
			FROM `metadata` m
			LEFT JOIN metadata m2 USING(idInSource)
			LEFT JOIN metadata i ON i.value = m2.value
			WHERE m.`source` LIKE 'repository'
			AND m.`field` LIKE 'dc.description.sponsorship'
			AND m.deleted IS NULL
			AND m2.`source` LIKE 'repository'
			AND m2.`field` LIKE 'dc.identifier.doi'
			AND m2.deleted IS NULL
			AND i.`source` LIKE 'irts'
			AND i.`field` LIKE 'dc.identifier.doi'
			AND i.deleted IS NULL
			AND i.idInSource NOT IN (
			SELECT `idInSource` FROM metadata WHERE field IN ('local.acknowledged.person', 'local.acknowledged.supportUnit', 'local.grant.number', 'local.acknowledgement.type', 'local.acknowledgement.type'))
            AND m.idInSource NOT IN (
			SELECT `idInSource` FROM metadata WHERE field IN ('local.acknowledged.supportUnit', 'local.grant.number', 'local.acknowledgement.type', 'local.acknowledgement.type'))
            AND i.idInSource IN (
			SELECT DISTINCT idInSource FROM `metadata` WHERE `field` = 'irts.check.acknowledgement' AND value = 'yes' AND deleted IS NULL) ORDER BY i.idInSource ASC LIMIT $page, 1", array('idInSource'), 'singleValue');

			$step = 'acknowledgementsPlus';

			$template = prepareTemplate('Publication', "'$step'");

			$item = getValues($irts, "SELECT DISTINCT m.`idInSource` handle, m.value ack, m2.value doi
			FROM `metadata` m
			LEFT JOIN metadata m2 USING(idInSource)
			LEFT JOIN metadata i ON i.value = m2.value
			WHERE m.`source` LIKE 'repository'
			AND m.`field` LIKE 'dc.description.sponsorship'
			AND m.deleted IS NULL
			AND m2.`source` LIKE 'repository'
			AND m2.`field` LIKE 'dc.identifier.doi'
			AND m2.deleted IS NULL
			AND i.`source` LIKE 'irts'
			AND i.`field` LIKE 'dc.identifier.doi'
			AND i.deleted IS NULL
			AND i.idInSource = '$idInIRTS'", array('handle','ack','doi'));

			$record['dc.description.sponsorship'][0] = $item[0]['ack'];

			$handle = $item[0]['handle'];

			$doi = $item[0]['doi'];

			$record = prepareAcknowledgements($record);

			echo 'Review and complete the acknowledgements metadata for the item with IRTS ID '.$idInIRTS. '. <br>-- Handle: <a href="http://hdl.handle.net/'.$handle.'">'.$handle.'</a><br>-- DOI: <a href="http://doi.org/'.$doi.'">'.$doi.'</a><hr><br>';

			echo '<form method="post" action="reviewCenter.php?'.$selections.'">';

			include_once "snippets/displayForm.php";

			echo '<input type="hidden" name="page" value="'.($page).'">
			<input type="hidden" name="handle" value="'.$handle.'">
			<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
			<button class="btn btn-block btn-success" type="submit" name="action" value="save">-- Save Completed Metadata --</button>
			<button class="btn btn-block btn-primary" type="submit" name="action" value="skip">-- Skip: Take No Action and Move to Next Item --</button>
			</form>
			</div>';
		}
		elseif($_GET['itemType']==='Dataset Relationships')
		{
			$record = array();

			$idInIRTS = getValues($irts, "SELECT DISTINCT idInSource, added 
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
			)
			ORDER BY added DESC LIMIT $page, 1", array('idInSource'), 'singleValue');

			$step = 'dataRelations';

			$template = prepareTemplate('Publication', "'$step'");

			//print_r($template);
			
			$existingHandles = array();

			$fieldsToCheck = array('dc.identifier.arxivid'=>array('label'=>'arXiv ID','prefix'=>'https://arxiv.org/abs/','values'=>array()), 'dc.identifier.doi'=>array('label'=>'DOI','prefix'=>'http://doi.org/','values'=>array()));
			
			foreach($fieldsToCheck as $fieldToCheck => $details)
			{
				$fieldsToCheck[$fieldToCheck]['values'] = getValues($irts, "SELECT value
					FROM `metadata`
					WHERE source = 'irts'
					AND idInSource = '$idInIRTS'
					AND `field` LIKE '$fieldToCheck'
					AND deleted IS NULL", array('value'));
					
				foreach($fieldsToCheck[$fieldToCheck]['values'] as $idToCheck)
				{
					$existingHandles = array_merge($existingHandles, checkForExistingRecords($idToCheck, $fieldToCheck, $report, 'repository'));
				}
			}

			echo 'Review and complete the dataset relations metadata for the item with IRTS ID '.$idInIRTS. '. <br>';

			foreach($existingHandles as $handle)
			{
				$title = getValues($irts, "SELECT value
					FROM `metadata`
					WHERE source = 'repository'
					AND idInSource = '$handle'
					AND `field` LIKE 'dc.title'
					AND deleted IS NULL", array('value'), 'singleValue');
					
				$authors = implode('; ', getValues($irts, "SELECT value
					FROM `metadata`
					WHERE source = 'repository'
					AND idInSource = '$handle'
					AND `field` LIKE 'dc.contributor.author'
					AND deleted IS NULL", array('value')));

				$type = getValues($irts, "SELECT value
					FROM `metadata`
					WHERE source = 'repository'
					AND idInSource = '$handle'
					AND `field` LIKE 'dc.type'
					AND deleted IS NULL", array('value'), 'singleValue');
					
				$doi = getValues($irts, "SELECT value
					FROM `metadata`
					WHERE source = 'repository'
					AND idInSource = '$handle'
					AND `field` LIKE 'dc.identifier.doi'
					AND deleted IS NULL", array('value'), 'singleValue');
				
				echo '-- <b>Title: </b>'.$title.'<br>-- <b>Authors: </b>'.$authors.'<br>-- <b>Handle: </b><a href="http://hdl.handle.net/'.$handle.'">'.$handle.'</a><br>';
				
				foreach($fieldsToCheck as $fieldToCheck => $details)
				{
					foreach($details['values'] as $idToCheck)
					{
						echo '-- <b>'.$details['label'].': </b><a href="'.$details['prefix'].$idToCheck.'">'.$idToCheck.'</a><br>';
					}
				}
				echo '<hr>';
			}

			$dataFields = array('dc.description.dataAvailability','dc.related.datasetDOI', 'dc.related.datasetURL', 'dc.related.codeURL', 'dc.related.accessionNumber');

			foreach($dataFields as $dataField)
			{
				$record[$dataField] = getValues($irts, "SELECT value
				FROM `metadata`
				WHERE source = 'irts'
				AND idInSource = '$idInIRTS'
				AND `field` LIKE '$dataField'
				AND deleted IS NULL", array('value'));
			}

			echo '<div class="col-sm-12 alert-warning border border-dark rounded"><b>Notes on relationships:</b>
			<br><br>-- <b>issupplementedby:</b> Assign this relationship if the authors of the original publication and the related item are the same people or if we have other reason to think that <b>the related item was released by the '.INSTITUTION_ABBREVIATION.' authors together with and in support of the publication</b> (items with this relationship will be treated as new items and cataloged in the repository).
			<br><br>-- <b>references:</b> Assign this relationship if the authors of the original publication and the related item are <b>not</b> the same people or if we have other reason to think that the related item was released separately from the publication (items with this relationship will only be linked to the original publication but not cataloged).</div><br><hr><br>';

			echo '<form method="post" action="reviewCenter.php?'.$selections.'">';

			include_once "snippets/displayForm.php";

			echo '<input type="hidden" name="page" value="'.($page).'">
			<input type="hidden" name="handle" value="'.$handle.'">
			<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
			<button class="btn btn-block btn-success" type="submit" name="action" value="save">-- Save Completed Metadata --</button>
			<button class="btn btn-block btn-primary" type="submit" name="action" value="skip">-- Skip: Take No Action and Move to Next Item --</button>
			</form>
			</div>';
		}
		elseif($_GET['itemType']==='Unpaywall')
		{
			if(isset($_GET['Message']))
			{
				//print_r($record);

				echo '<div class="alert alert-danger" id="message"><p><b>Warning</b></p><p>
				Please select a file <p></div>';
			}

			$record = array();

			// make the step = rights to show all the rights
			$step = 'rights';
			
			$template = prepareTemplate('Publication', "'$step'");

			if(isset($_GET['idInIRTS']))
			{
				$idInIRTS = $_GET['idInIRTS'];
			}
			else
			{
				$idInIRTS = getValues($irts, "SELECT `idInSource` FROM `metadata` WHERE `source` = 'irts' AND `field` = 'irts.check.unpaywall' AND value = 'inProcess' AND deleted IS NULL ORDER BY added ASC LIMIT $page, 1", array('idInSource'), 'singleValue');
			}

			if(!empty($idInIRTS))
			{
				// get the DOI
				$doi = getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, NULL, 'dc.identifier.doi'), array('value'), 'singleValue');

				// Get the handle
				$handle	= getValues($irts, setSourceMetadataQuery('repository', NULL, NULL, 'dc.identifier.doi', $doi), array('idInSource'), 'singleValue');

				echo 'Review and complete the Unpaywall metadata for the item with IRTS ID '.$idInIRTS. '. <br>-- Handle: <a href="http://hdl.handle.net/'.$handle.'">'.$handle.'</a><br>-- DOI: <a href="http://doi.org/'.$doi.'">'.$doi.'</a><hr><br>';

				echo '<form method="post" action="reviewCenter.php?'.$selections.'">';

				echo '<input type="hidden" name="page" value="'.($page).'">
				<input type="hidden" name="handle" value="'.$handle.'">
				<input type="hidden" name="doi" value="'.$doi.'">
				<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">';

				if(!empty($handle))
				{
					// get the data ready to display
					$record = getRecord('irts', $idInIRTS, $template);

					$record['dc.identifier.doi'][0] = $doi;
					
					//For development - uncomment to see the contents of the record
					//print_r($record);
					
					//For development - uncomment to see the contents of the template
					//print_r($template);

					// get the source data JSON string (so rights step does not need to query Unpaywall again)
					$unpaywallSourceData = getValues($irts, "SELECT `sourceData` FROM `sourceData` WHERE `source` = 'unpaywall' AND `idInSource` = '$doi' AND `deleted` IS NULL", array('sourceData'), 'singleValue');

					include_once "snippets/forMetadataEntry/rights.php";
					include_once "snippets/displayForm.php";
				}
				else
				{
					echo '<b>No matching repository item...</b><br><br><br>';
				}

				// send record in submission and show process option buttons
				echo '
				<button class="btn btn-block btn-success" type="submit" name="action" value="save">-- Save Completed Metadata And Add A File --</button>
			<button class="btn btn-block btn-warning" type="submit" name="action" value="reject">-- Linked Files CANNOT Be Uploaded, Mark Review as Complete --</button>
				<button class="btn btn-block btn-primary" type="submit" name="action" value="skip">-- Skip: Take No Action and Move to Next Item --</button>
				</form>
				</div>';
			}
			else
			{
				echo '<b>No Unpaywall results</b><br><br>';
			}
		}// end of unpaywall		
		else
		{
			// if no items exist and the page number is 1 that means there are no items left
			if($page == 1)
				echo '<b>There are no items to process</b><br><br>';
			else
			{
				// if the page number = the last number and select skip it will return you to the first page
				header("Location: reviewCenter.php?formType=$formType&itemType=$itemType&page=1");
				exit();
			}
		}
	}
	else
	{
		$action = $_POST['action'];

		if($action==='skip')
		{
			$page++;
			header("Location: reviewCenter.php?formType=$formType&itemType=$itemType&page=$page");
			exit();
		}
		elseif($action === 'save')
		{
			$dSpaceAuthHeader = loginToDSpaceRESTAPI();

			$idInIRTS = $_POST['idInIRTS'];

			$handle = $_POST['handle'];

			if($_GET['itemType']==='Acknowledgements')
			{
				include_once "snippets/saveValues.php";

				$rowID = getValues($irts, "SELECT rowID FROM `metadata` WHERE source='irts' AND idInSource='$idInIRTS' AND field = 'irts.check.acknowledgement' AND value = 'yes'", array('rowID'), 'singleValue');

				update($irts, 'metadata', array("deleted"), array(date("Y-m-d H:i:s"), $rowID), 'rowID');

				echo $itemType.' step review completed for '.$idInIRTS;

				//Also try to update DSpace record immediately
				unset($record['local.acknowledgement.type']);
				unset($record['local.acknowledged.person']);

				$itemID = getValues($irts, setSourceMetadataQuery('repository', $handle, NULL, 'dc.internalItemId'), array('value'), 'singleValue');

				$record['dc.description.provenance'] = getValues($irts, setSourceMetadataQuery('repository', $handle, NULL, 'dc.description.provenance'), array('value'));

				$record['dc.description.provenance'][] = 'Record metadata updated via REST API by '.$_SESSION['displayname'].' using the '.IR_EMAIL.' user account on '.TODAY.' as part of the acknowledgements review process.';

				$metadata = prepareItemMetadataAsDSpaceJSON($record);

				$response = putItemMetadataToDSpaceRESTAPI($itemID, $metadata, $dSpaceAuthHeader);

				//echo ' - Update Metadata Response: '.$response.PHP_EOL;

				echo '<br>DSpace metadata directly updated in DSpace for: <br>- Handle: <a href="http://hdl.handle.net/'.$handle.'">'.$handle.'</a>';
			}
			elseif($_GET['itemType']==='Dataset Relationships')
			{
				unset($record['dc.description.dataAvailability']);

				//get relation fields
				$relationFields = getValues($irts, "SELECT field FROM `metadata` WHERE `source` LIKE 'irts' AND `idInSource` LIKE '$idInIRTS' AND `field` LIKE 'dc.relation.%' AND `field` NOT LIKE 'dc.relation.url' AND `deleted` IS NULL", array('field'), 'arrayOfValues');

				foreach($relationFields as $relationField)
				{
					$record[$relationField] = getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, NULL, $relationField), array('value'));
				}
				
				$dataFields = array('dc.related.datasetDOI', 'dc.related.datasetURL', 'dc.related.codeURL', 'dc.related.accessionNumber');

				//print_r($record).'<br>';

				foreach($dataFields as $dataField)
				{
					if(isset($record[$dataField]))
					{
						foreach($record[$dataField] as $key => $value)
						{
							//subfields will have strings (field names) as keys
							if(is_int($key)&&!empty($value))
							{
								if(isset($record[$dataField]['dc.relation.type'][$key]))
								{
									if(in_array($record[$dataField]['dc.relation.type'][$key],array('issupplementedby','references')))
									{
										$relationship = $record[$dataField]['dc.relation.type'][$key];

										if($dataField === 'dc.related.datasetDOI')
										{
											$record['dc.relation.'.$relationship][] = 'DOI:'.$value;
										}
										elseif(in_array($dataField,array('dc.related.codeURL','dc.related.datasetURL')))
										{
											$record['dc.relation.'.$relationship][] = 'URL:'.$value;
										}
										elseif($dataField === 'dc.related.accessionNumber')
										{
											$record['dc.relation.'.$relationship][] = 'accessionNumber:'.$value;
										}
									}
								}
							}
						}
						
						//Mark all old entries for this field as deleted (including those with no relation marked or with the relation "ignore"
						$rowIDs = getValues($irts, "SELECT rowID FROM `metadata` WHERE source='irts' AND idInSource='$idInIRTS' AND field = '$dataField' AND deleted IS NULL", array('rowID'));

						foreach($rowIDs as $rowID)
						{
							update($irts, 'metadata', array("deleted"), array(date("Y-m-d H:i:s"), $rowID), 'rowID');
						}
						
						unset($record[$dataField]);
					}
				}

				//print_r($record).'<br>';

				$_POST['record'] = $record;

				include_once "snippets/saveValues.php";

				echo $itemType.' step review completed for '.$idInIRTS;
			}
			elseif($_GET['itemType']==='Unpaywall')
			{
				//If a file URL was selected
				if(!empty($record['unpaywall.relation.url'][0]))
				{
					$_POST['record']['dc.rights'][] = 'This file is an open access version redistributed from: '.$record['unpaywall.relation.url'][0];

					// if the embargo date has been deleted mark the dc.rights.embargodate as empty
					if(empty($record['dc.rights.embargodate'][0]))
					{
						// mark the dc.rights.embargodate as deleted
						$update = $irts->query("UPDATE `metadata` SET `deleted` = '".date("Y-m-d H:i:s")."' WHERE  `source` = 'irts' AND `idInSource` = '$idInIRTS' and `field` = 'dc.rights.embargodate'");
					}

					echo '<div class="alert alert-success" id="message"><p><b>Message</b></p><p>Unpaywall linked file <a href='.$record['unpaywall.relation.url'][0].' >'.$record['unpaywall.relation.url'][0].'</a> has been selected to be added to the repository. Update the item metadata below before transferring or uploading the file.<p></div>';

					//save the data
					include_once "snippets/reviewCenterActions/save.php";
				}
				else
				{
					// Return message when no file URL was selected
					header("Location: reviewCenter.php?formType=$formType&itemType=$itemType&page=$page&Message=SelectAFile");
					exit();
				}
			} //end of Unpaywall section

			if($_GET['itemType']!='Unpaywall')
				echo '<hr>
				<div class="col-lg-6">

				<form method="post" action="reviewCenter.php?'.$selections.'">
					<input class="btn btn-lg btn-primary" type="submit" name="next" value="-- Start Next Item --"></input>
					</form>
				</div>';
		}	// end of the save action
		elseif($action=='reject')
		{
			//show this message only if the itemType=Unpaywall and no file is selected
			echo  '<div class="alert alert-warning" id="message"><p><b>Message</b></p><p>Review of Unpaywall links for item with DOI: <a href="https://doi.org/'.$doi.'">'.$doi.'</a> <br><b>No files were selected to add to the repository.</b><p></div>
			<hr>';

			include_once "snippets/reviewCenterActions/reject.php";
		}
		elseif($action === 'transfer')
		{
			if($_GET['itemType']==='Unpaywall')
			{
				if($_POST['transferType']==='updateAllMetadata')
				{
					echo '<div class="alert alert-success" id="message"><p><b>Message</b></p><p>The rights metadata for this item has been updated in DSpace, but the selected Unpaywall file <a href='.$record['unpaywall.relation.url'][0].' >'.$record['unpaywall.relation.url'][0].'</a> has not yet been added to the repository. Select an option below to add the file.<p></div>';
				}

				$template = prepareTemplate('Publication', 'rights');
			}
			include_once "snippets/reviewCenterActions/transfer.php";
		}
	}//end of the action handling section

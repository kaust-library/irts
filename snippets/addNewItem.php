<?php
	echo '<div class="container">';
	if(isset($_POST['addItem']))
	{
		$report = '';
		if(!empty($_POST['doi']))
		{
			$dois = explode(',', $_POST['doi']);

			foreach($dois as $doi)
			{
				$report .= '<br>addNewItem - DOI: '.$doi.'<br>';

				if(identifyRegistrationAgencyForDOI($doi, $report)==='crossref')
				{
					$sourceData = retrieveCrossrefMetadataByDOI($doi, $report);

					if(!empty($sourceData))
					{
						$recordType = processCrossrefRecord($sourceData, $report);

						$report .= ' - '.$recordType.'<br>';

						$existingRecords = checkForExistingRecords($doi, 'dc.identifier.doi', $report);

						if(empty($existingRecords))
						{
							//Check for existing IRTS entry
							$irtsID = 'crossref_'.$doi;

							$existingRecords = getValues($irts, "SELECT DISTINCT `idInSource` FROM `metadata` WHERE source LIKE 'irts' AND (idInSource LIKE '$irtsID' OR (field = 'dc.identifier.doi' AND value = '$doi'))", array('idInSource'), 'arrayOfValues');

							if(empty($existingRecords))
							{
								$field = 'dc.type';

								$type = ucfirst(getValues($irts, ucfirst(setSourceMetadataQuery('crossref', $doi, NULL, $field)), array('value'), 'singleValue'));

								$rowID = mapTransformSave('irts', $irtsID, '', $field, '', 1, $type, NULL);

								$field = 'status';

								$rowID = mapTransformSave('irts', $irtsID, '', $field, '', 1, 'inProcess', NULL);

								$field = 'dc.identifier.doi';

								$rowID = mapTransformSave('irts', $irtsID, '', $field, '', 1, $doi, NULL);
								
								$report .= " - New entry made in IRTS at: <a href=reviewCenter.php?formType=processNew&itemType=".str_replace(' ', '+', $type)."&page=0&idInIRTS=$irtsID>$irtsID</a><br>";
							}
							else
							{
								$report .= ' - Matching record(s) exist in IRTS: <br>';
								
								foreach($existingRecords as $existingID)
								{
									$doi = getValues($irts, setSourceMetadataQuery('irts', $existingID, NULL, 'dc.identifier.doi'), array('value'), 'singleValue');
									
									$report .= '<div class="col-sm-12 border border-dark rounded">
										<br>Type: '.getValues($irts, setSourceMetadataQuery('irts', $existingID, NULL, 'dc.type'), array('value'), 'singleValue').'
										<br>Title: '.getValues($irts, "SELECT  `value`FROM `metadata` WHERE `idInSource` = '$doi' AND `parentRowID` IS NULL AND `field` = 'dc.title' AND `deleted` IS NULL" , array('value'), 'singleValue')."
										<br>IRTS ID: <a href=reviewCenter.php?formType=processNew&itemType=".str_replace(' ', '+',getValues($irts, setSourceMetadataQuery('irts', $existingID, NULL, 'dc.type'), array('value'), 'singleValue'))."&page=0&idInIRTS=$existingID>$existingID</a><br><br></div>";
								}
							}
						}
						else
						{
							$report .= ' - Matching record(s) exist in the repository: <br>';
							foreach($existingRecords as $existingID)
							{

								$report .= '<div class="col-sm-12 border border-dark rounded">
								<br>Type: '.getValues($irts, setSourceMetadataQuery('repository', $existingID, NULL, 'dc.type'), array('value'), 'singleValue').'
								<br>Title: '.getValues($irts, setSourceMetadataQuery('repository', $existingID, NULL, 'dc.title'), array('value'), 'singleValue').'
								<br>Handle: <a href="'.getValues($irts, setSourceMetadataQuery('repository', $existingID, NULL, 'dc.identifier.uri'), array('value'), 'singleValue').'">'.getValues($irts, setSourceMetadataQuery('repository', $existingID, NULL, 'dc.identifier.uri'), array('value'), 'singleValue').'</a><br><br></div>';
							}
						}
					}
				}
			}
		}
		elseif(!empty($_POST['arxivID']))
		{
			$arxivIDs = explode(',', $_POST['arxivID']);

			foreach($arxivIDs as $arxivID)
			{
				$report .= '<br>addNewItem - arxivID: '.$arxivID.'<br>';

				$xml = retrieveArxivMetadata('arxivID',$arxivID);

				foreach($xml->entry as $item)
				{
					$result = processArxivRecord($item);
					
					$recordType = $result['recordType'];

					$report .= '<br> - '.$recordType.'<br>';

					$status = addToProcess('arxiv', $arxivID, $arxivID, 'dc.identifier.arxivid', 'Preprint');
					
					if($status === 'inProcess')
					{
						$irtsID = 'arxiv_'.$arxivID;
						
						$report .= " - New entry made in IRTS at: <a href=reviewCenter.php?formType=processNew&itemType=Preprint&page=0&idInIRTS=$irtsID>$irtsID</a><br>";
					}
				}
			}
		}
		elseif(!empty($_POST['handle']))
		{
			$dSpaceAuthHeader = loginToDSpaceRESTAPI();
			
			$handles = explode(',', $_POST['handle']);

			foreach($handles as $handle)
			{
				$report .= '<br>addNewItem - Handle: '.$handle.'<br>';

				$dspaceObject = getObjectByHandleFromDSpaceRESTAPI($handle, $dSpaceAuthHeader, 'metadata');

				if(is_string($dspaceObject))
				{
					$dspaceObject = json_decode($dspaceObject, TRUE);
				
					$itemID = $dspaceObject[DSPACE_INTERNAL_ID_KEY_NAME];
					
					//process item
					$recordType = processDspaceRecord($itemID, $dspaceObject['metadata'], $report);
					
					/* $doi = getValues($irts, setSourceMetadataQuery('dspace', $itemID, NULL, 'dc.identifier.doi'), array('value'), 'singleValue');
					
					$existingRecords = checkForExistingRecords($doi, 'dc.identifier.doi', $report);

					if(empty($existingRecords))
					{
						
					} */
					
					$report .= '<br> - '.$recordType.'<br>';
					
					$type = getValues($irts, setSourceMetadataQuery('dspace', $itemID, NULL, 'dc.type'), array('value'), 'singleValue');

					$status = addToProcess('dspace', $itemID, $handle, 'dc.identifier.uri', $type);
					
					if($status === 'inProcess')
					{
						$irtsID = 'dspace_'.$itemID;
						
						$report .= " - New entry made in IRTS at: <a href=reviewCenter.php?formType=processNew&itemType=".str_replace(' ', '+', $type)."&page=0&idInIRTS=$irtsID>$irtsID</a><br>";
					}
				}
				else
				{
					print_r($dspaceObject);
				}
			}
		}

		if(isset($report))
		{
			echo $report;
		}
		else
		{
			echo 'no IDs were entered';
		}

		echo '<hr><br><a href="reviewCenter.php?formType=addNewItem" type="button" class="btn btn-primary">Add Another Item</a><a href="reviewCenter.php" type="button" class="btn btn-primary">Return to Review Center</a>';
	}
	else
	{
		echo 'Retrieve metadata and add record to process for a given ID (multiple IDs can be entered as a comma separated list).<br><hr><form method="post" action="reviewCenter.php?formType=addNewItem">
				<div class="form-group">
				  <label for="doi">DOI:</label>
				  <textarea class="form-control" rows="1" name="doi"></textarea>
				</div>
				<div class="form-group">
				  <label for="arxivID">arXiv ID:</label>
				  <textarea class="form-control" rows="1" name="arxivID"></textarea>
				</div>
				<div class="form-group">
				  <label for="handle">Handle:</label>
				  <textarea class="form-control" rows="1" name="handle"></textarea>
				</div>
				<input class="btn btn-primary" type="submit" name="addItem" value="Add Item Record for Processing"></input>
			</form>';
	}
	echo '</div>';
?>

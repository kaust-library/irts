<?php
	$dSpaceAuthHeader = loginToDSpaceRESTAPI();

	//Inserting sleep as it seems that sometimes posting too quickly after logging in can return a 500 internal server error from DSpace
	sleep(3);

	$itemID = NULL;

	if(isset($_POST['handle']) )
	{
		$handle = $_POST['handle'];

		$handleURL = 'http://hdl.handle.net/'.$handle;
		
		if(!isset($_POST['itemID']))
		{
			$dspaceObject = getObjectByHandleFromDSpaceRESTAPI($handle, $dSpaceAuthHeader);

			if(is_string($dspaceObject))
			{
				$dspaceObject = json_decode($dspaceObject, TRUE);

				$itemID = $dspaceObject[DSPACE_INTERNAL_ID_KEY_NAME];
			}
			else
			{
				print_r($dspaceObject);
			}
		}
		else
		{
			$itemID = $_POST['itemID'];
		}
	}

	// if this is the update process from Unpaywall
	if($_GET['itemType']==='Unpaywall')
	{
		$type = getValues($irts, "SELECT value FROM `metadata` WHERE source='repository' AND idInSource='$handle' AND field = 'dc.type' AND deleted IS NULL", array('value'), 'singleValue');
	}
 
	if(isset($_POST['transferType']))
	{
		if($_POST['transferType'] === 'addFileByURL')
		{
			if(empty($_POST['fileURLs']))
			{
				if($_GET['itemType']==='Unpaywall')
				{
					$urls = getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, NULL, array('unpaywall.relation.url')), array('value'));
				}
				else
				{
					$urls = getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, NULL, array('dc.relation.url','unpaywall.relation.url')), array('value'));
				}

				echo '<b>Select the URL for the PDF file that you would like to transfer:</b>
				<br><br> -- NOTE: The selected link must point directly to a PDF document file without passing through redirects or requiring cookies, etc. -- <br><br>

				<form method="post" action="reviewCenter.php?'.$selections.'">
					<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
					<input type="hidden" name="itemID" value="'.$itemID.'">';

				foreach($urls as $url)
				{
					echo '<input type="checkbox" name="fileURLs[]" value="'.$url.'"> '.$url.'</input><br>';
				}

				echo '<br>OR<br><br>
					<b>Enter the URL for the PDF file that you would like to transfer:</b>
					<br><br> -- NOTE: The entered link must point directly to a PDF document file without passing through redirects or requiring cookies, etc. -- 	<br><br>
					<textarea class="form-control" rows="1" name="fileURLs[]"></textarea>
					<input type="hidden" name="transferType" value="addFileByURL">
					<input type="hidden" name="handle" value="'.$handle.'">
					<button class="btn btn-lg btn-warning" type="submit" name="action" value="transfer">Add PDF file to item based on file URL</button>
				</form>';
			}
			else
			{
				$type = getValues($irts, setSourceMetadataQuery('dspace', $itemID, NULL, 'dc.type'), array('value'), 'singleValue');

				$version = getValues($irts, setSourceMetadataQuery('dspace', $itemID, NULL, 'dc.eprint.version'), array('value'), 'singleValue');

				$count = 1;

				if(count($_POST['fileURLs']) === 1 && empty($_POST['fileURLs'][0]))
				{
					echo "<br><br> -- You didn't select any file <br><br>";
				}

				foreach($_POST['fileURLs'] as $fileURL)
				{
					$fileURL = str_replace(' ', '', $fileURL);

					if(!empty($fileURL))
					{
						$fileURL = str_replace('http://','https://',$fileURL);

						$name = $type.'file'.$count.'.pdf';

						if(empty($version))
						{
							$description = $type;
						}
						else
						{
							$description = $version;
						}

						$bundleName = 'ORIGINAL';

						$response = postBitstreamToDSpaceRESTAPI($itemID, $fileURL, $name, $description, $bundleName, $dSpaceAuthHeader);

						$responseAsArray = json_decode($response, TRUE);

						if(empty($responseAsArray[DSPACE_INTERNAL_ID_KEY_NAME]))
						{
							$message .= ' - Failed to post the file at <a href="'.$fileURL.'">'.$fileURL.'</a> to the repository item at <a href="'.$handleURL.'">'.$handleURL.'</a><br><br>Please load the file directly via the DSpace interface.';
						}
						else
						{
							// add Provenance
							$json = getItemMetadataFromDSpaceRESTAPI($itemID, $dSpaceAuthHeader);

							$metadata = dSpaceJSONtoMetadataArray($json);

							$metadata = appendProvenanceToMetadata($itemID, $metadata, 'fileUpload', $name);

							$json = prepareItemMetadataAsDSpaceJSON($metadata);

							$response = putItemMetadataToDSpaceRESTAPI($itemID, $json, $dSpaceAuthHeader);

							// check if the record has embargo date
							if(isset($record['dc.rights.embargodate'][0]))
							{
								// get the bitstream metadata
								$bitstreamResponse = getBitstreamFromDSpaceRESTAPI($responseAsArray[DSPACE_INTERNAL_ID_KEY_NAME], $dSpaceAuthHeader, '?expand=all');

								//convert json to array
								$bitstreamResponse = json_decode($bitstreamResponse, TRUE);

								// change the date
								foreach ($bitstreamResponse['policies'] as &$policy)
								{
									$policy['startDate'] = $record['dc.rights.embargodate'][0];
								}

								// reconvert the array to json
								$bitstreamResponse = json_encode($bitstreamResponse);

								// put the metadata
								putBitstreamMetadataToDSpaceRESTAPI($responseAsArray[DSPACE_INTERNAL_ID_KEY_NAME], $bitstreamResponse, $dSpaceAuthHeader);
							}

							$message .= ' - The file from: <br><a href="'.$fileURL.'">'.$fileURL.'</a><br>was successfully posted to the repository item at:<br><a href="'.$handleURL.'">'.$handleURL.'</a><br><br>Please visit the repository item to confirm successful transfer <b>by opening the file</b> and to add additional information or an embargo.
							
							<hr>';
						}
						$count++;
					}
				}

				if(isset($selections) && $_GET['formType'] !== 'uploadFile')
				{
					$message .= '<hr>
						<div class="col-lg-6">
						<form method="post" action="reviewCenter.php?'.$selections.'">
							<input class="btn btn-lg btn-primary" type="submit" name="next" value="-- Start Next Item --"></input>
							</form>
						</div>';
				}
			}
		}
		else
		{
			$fields = array_unique(array_keys($template['fields']));

			$fieldsToIgnore = array('dc.contributor.affiliation','local.acknowledgement.type','local.acknowledged.person','dc.description.dataAvailability','dc.related.accessionNumber','dc.related.codeURL','dc.related.datasetDOI','dc.related.datasetURL','dc.rights.embargolength', 'dc.relationType', 'dc.relatedIdentifier', 'dc.identifier.handle', 'dc.creator');
			
			$source = 'irts';
			$record = array();

			foreach($fields as $field)
			{
				if(!in_array($field, $fieldsToIgnore))
				{
					if( $field == 'dc.version')
					{
						//dc.version has non-null parent row id, so setSourceMetadataQuery does not work
						$record[$field] = getValues($irts, "SELECT `value` FROM `metadata` WHERE `source` = '$source' AND `idInSource` = '$idInIRTS' AND `field` = 'dc.version' AND `deleted` IS NULL ", array('value'));
					}
					else
					{
						$record[$field] = getValues($irts, setSourceMetadataQuery($source, $idInIRTS, NULL, $field), array('value'));
					}					
				}
			}
			
			//get relation fields
			$relationFields = getValues($irts, "SELECT field FROM `metadata` WHERE `source` LIKE '$source' AND `idInSource` LIKE '$idInIRTS' AND `field` LIKE 'dc.relation.%' AND `field` NOT LIKE 'dc.relation.url' AND `deleted` IS NULL", array('field'), 'arrayOfValues');
			
			//if a relation field is not in the DSpace metadata registry, it will cause an error on transfer.
			$relationsToSendToDspace = array('dc.relation.issupplementto', 'dc.relation.issupplementedby', 'dc.relation.haspart', 'dc.relation.ispartof', 'dc.relation.isreferencedby', 'dc.relation.references');

			foreach($relationFields as $relationField)
			{
				if(in_array($relationField, $relationsToSendToDspace))
				{
					$record[$relationField] = getValues($irts, setSourceMetadataQuery($source, $idInIRTS, NULL, $relationField), array('value'));
				}
			}
			
			//Check for relation fields and set the display.relations field as needed
			$result = setDisplayRelationsField($record);
			
			$record = $result['record'];

			//For patents
			/* if(isset($record['googlePatents.citation_pdf_url']))
			{
				$filesToTransfer = $record['googlePatents.citation_pdf_url'];

				unset($record['googlePatents.citation_pdf_url']);
			}

			if(!isset($record['dc.description.status']))
			{
				if(!empty($record['dc.identifier.patentnumber']))
				{
					$record['dc.description.status'][] = 'Granted Patent';
				}
				else
				{
					$record['dc.description.status'][] = 'Published Application';
				}
			} */

			// because Unpaywall processing does not need to update the author field we can ignore the below section
			if($_GET['itemType']!='Unpaywall')
			{
				$deptCollectionIDs = array();
				$record['dc.contributor.institution'] = [];
				$record['dc.contributor.department'] = [];
				foreach($record['dc.contributor.author'] as $authorPlace=>$author)
				{
					//echo $author.'<br>';

					$affiliations = getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, array('parentField'=>'dc.contributor.author', 'parentValue'=>$author), 'dc.contributor.affiliation'), array('value'), 'arrayOfValues');

					$orcid = getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, array('parentField'=>'dc.contributor.author', 'parentValue'=>$author), 'dc.identifier.orcid'), array('value'), 'singleValue');

					//print_r($affiliations);

					foreach($affiliations as $affiliation)
					{
						//echo $affiliation.'<br>';
						if(institutionNameInString($affiliation))
						{
							$match = checkPerson(array('name'=>$author));
							if(!empty($match['localID']))
							{
								//echo 'Person Matched<br>';
								$record['local.person'][] = $match['controlName'];

								//Respect ORCID received from Crossref, otherwise add ORCID from local person match
								if(!empty($orcid))
								{
									$record['dc.contributor.author'][$authorPlace] = $match['controlName'] . '::' . $orcid;
								}
								elseif(!empty($match['orcid']))
								{
									$record['dc.contributor.author'][$authorPlace] = $match['controlName'] . '::' . $match['orcid'];
								}
								else
								{
									$record['dc.contributor.author'][$authorPlace] = $match['controlName'];
								}

								if(isset($record['dc.date.issued'][0]))
									$deptIDs = checkDeptIDs($match['localID'], $record['dc.date.issued'][0]);
								else
									$deptIDs = checkDeptIDs($match['localID'], null);

								foreach($deptIDs as $deptID)
								{
									//echo $deptID.'<br>';

									$deptHandle = getValues($irts, setSourceMetadataQuery('local', 'org_'.$deptID, NULL, 'dspace.collection.handle'), array('value'), 'singleValue');

									//echo $deptHandle.'<br>';

									if(!empty($deptHandle))
									{
										$collectionID = getValues($irts, setSourceMetadataQuery('dspace', NULL, NULL, 'dspace.collection.handle', $deptHandle), array('idInSource'), 'singleValue');

										$deptCollectionIDs[] = str_replace('collection_', '', $collectionID);

										//echo $collectionID.'<br>';

										$record['dc.contributor.department'][] = getValues($irts, setSourceMetadataQuery('dspace', $collectionID, NULL, 'dspace.collection.name'), array('value'), 'singleValue');
										//print_r($record['dc.contributor.department']);
									}
									else
									{
										$record['dc.contributor.department'][] = getValues($irts, setSourceMetadataQuery('local', 'org_'.$deptID, NULL, 'local.org.name'), array('value'), 'singleValue');
										//print_r($record['dc.contributor.department']);
									}
								}
							}
							else
							{
								if(!empty($orcid))
								{
									$record['dc.contributor.author'][$authorPlace] = $author . '::' . $orcid;
								}

								$record['dc.contributor.department'][] = $affiliation;
								$record['local.person'][] = $author;
							}
						}
						else
						{
							if(!empty($orcid))
							{
								$record['dc.contributor.author'][$authorPlace] = $author . '::' . $orcid;
							}

							$record['dc.contributor.institution'][] = $affiliation;
						}
					}
				}
				$record['dc.contributor.institution'] = array_unique($record['dc.contributor.institution']);
				$record['dc.contributor.institution'] = array_filter($record['dc.contributor.institution']);

				$record['dc.contributor.department'] = array_unique($record['dc.contributor.department']);
				$record['dc.contributor.department'] = array_filter($record['dc.contributor.department']);

				$deptCollectionIDs = array_unique($deptCollectionIDs);
				$deptCollectionIDs = array_filter($deptCollectionIDs);
			}
			//echo '<br>Record:<br>'.print_r($record, TRUE);
			//echo '<br>Metadata:<br>'.print_r($metadata, TRUE);
			
			if(in_array($_POST['transferType'],array('createNewItem','updateAllMetadata')))
			{
				//Automated submissions collection id for posting new item with default public read permissions and no workflow
				$collectionID = SUBMISSIONS_COLLECTION_ID;
				
				$metadata = appendProvenanceToMetadata($itemID, $record);
				
				if($_POST['transferType']==='updateAllMetadata')
				{
					$metadata = prepareItemMetadataAsDSpaceJSON($record, TRUE);
					
					$response = putItemMetadataToDSpaceRESTAPI($itemID, $metadata, $dSpaceAuthHeader);
				}
				elseif($_POST['transferType']==='createNewItem')
				{
					$metadata = prepareItemMetadataAsDSpaceJSON($record, FALSE);

					$response = postItemToDSpaceRESTAPI($collectionID, $metadata, $dSpaceAuthHeader);
				}

				if(is_array($response))
				{
					$message .= 'FAILURE: <br> -- Response received was: '.print_r($response, TRUE).'<br> -- Posted JSON was: '.$metadata;
				}
				else
				{
					if($_POST['transferType']==='updateAllMetadata')
					{
						$message .= PHP_EOL.'Metadata updated for: '.PHP_EOL.'- Handle: <a href="'.$handleURL.'">'.$handleURL.'</a>'.PHP_EOL;
					}
					elseif($_POST['transferType']==='createNewItem')
					{
						$responseAsArray = json_decode($response, TRUE);

						if(!empty($responseAsArray[DSPACE_INTERNAL_ID_KEY_NAME]))
						{
							$itemID = $responseAsArray[DSPACE_INTERNAL_ID_KEY_NAME];
							$handle = $responseAsArray['handle'];
							
							$message .= ' - New DSpace Item ID: '.$itemID.PHP_EOL;
							$message .= ' - Handle: <a href=http://hdl.handle.net/'.$handle.'>http://hdl.handle.net/'.$handle.'</a>'.PHP_EOL;
						}
						else
						{
							$message .= ' - Post Item Failure Response: '.$response.PHP_EOL;
						}
					}
					
					if(DSPACE_COLLECTION_MAPPING_SUPPORTED)
					{
						$newCollections = array();
						$newCollections[DSPACE_INTERNAL_ID_KEY_NAME] = $itemID;
						
						if(empty($record['dc.contributor.department'])&&empty($record['local.person'])&&institutionNameInString($record['dc.description.sponsorship'][0]))
						{
							$acknowledgementOnlyCollectionID = ACKNOWLEDGEMENT_ONLY_COLLECTION_ID;
							
							$newCollections['parentCollection'][DSPACE_INTERNAL_ID_KEY_NAME] = $acknowledgementOnlyCollectionID;

							$newCollections['parentCollectionList'][][DSPACE_INTERNAL_ID_KEY_NAME] = $acknowledgementOnlyCollectionID;
						}
						else
						{
							//move to type collection
							$typeCollectionIDs = TYPE_COLLECTION_IDS;
								
							$typeCollectionID = $typeCollectionIDs[$record['dc.type'][0]];
							
							$newCollections['parentCollection'][DSPACE_INTERNAL_ID_KEY_NAME] = $typeCollectionID;

							$newCollections['parentCollectionList'][][DSPACE_INTERNAL_ID_KEY_NAME] = $typeCollectionID;

							//map to departmental collections
							foreach($deptCollectionIDs as $deptCollectionID)
							{
								$newCollections['parentCollectionList'][][DSPACE_INTERNAL_ID_KEY_NAME] = $deptCollectionID;
							}
						}

						$newCollections = json_encode($newCollections, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT);

						$response = mapItemToCollections($newCollections, $collectionID, $dSpaceAuthHeader);
						if(is_array($response))
						{
							$message .= 'FAILURE: <br> -- Response received to map request was: '.print_r($response,TRUE).'<br> -- Failed to map to: '.$newCollections.'<br> -- Check the item and move it to the correct collection if needed.';
						}
					}

					//get the metadata for the item to save in the local database
					$metadataToBeSavedInDB = getItemMetadataFromDSpaceRESTAPI($itemID, $dSpaceAuthHeader);
					
					//Inserting pause as it seems that too many requests to the API too quickly can return a 500 internal server error from DSpace
					sleep(2);

					//save it in the database
					$recordType = processDspaceRecord($itemID, $metadataToBeSavedInDB, $report);
					
					//set inverse relationships on any related items
					$result = setInverseRelations($itemID);
					
					if(!empty($result))
					{
						$message .= PHP_EOL.' - Set Inverse Relations Result: '.$result.PHP_EOL;
					}
				}
			}

			// to show the add file if there is already file otherwise just show next item button
			if($_GET['itemType']!='Unpaywall' || isset($record['unpaywall.relation.url'][0]))
			{
				$message .= '<br><b> -- If the file is not available via URL, please add it via direct upload to the DSpace item record.
				<br> -- If a manuscript request needs to be sent to authors, select that option below.</b>

				<hr>
				<div class="col-lg-6">

				<form method="post" action="reviewCenter.php?'.$selections.'">
					<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
					<input type="hidden" name="itemID" value="'.$itemID.'">
					<input type="hidden" name="handle" value="'.$handle.'">
					<input type="hidden" name="transferType" value="addFileByURL">
					<button class="btn btn-lg btn-warning" type="submit" name="action" value="transfer">-- Add PDF file to item based on file URL --</button>
				</form>

				<form method="post" action="reviewCenter.php?'.$selections.'">
					<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
					<input type="hidden" name="itemID" value="'.$itemID.'">
					<input type="hidden" name="handle" value="'.$handle.'">
					<input type="hidden" name="uploadSelections" value="'.$selections.'">
					<button class="btn btn-lg btn-secondary" type="submit" name="action" value="uploadFile" >-- Upload a file from desktop --</button>
				</form>
				';
			}
			else
			{
				$message .= '
				<hr>
				<div class="col-lg-6">
				<form method="post" action="reviewCenter.php?'.$selections.'">

					<input class="btn btn-lg btn-primary" type="submit" name="next" value="-- Start Next Item --"></input>
					</form>
				</div>';
			}

			if($_GET['itemType']!='Unpaywall')
			{
				$message .= '
			<form method="post" action="reviewCenter.php?'.$selections.'">
				<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
				<input type="hidden" name="handle" value="'.$handle.'">
				<input type="hidden" name="itemID" value="'.$itemID.'">
				<button class="btn btn-lg btn-success" type="submit" name="action" value="request">-- Send Manuscript Request -- </button>
			</form>
			<form method="post" action="reviewCenter.php?'.$selections.'">
				<input class="btn btn-lg btn-primary" type="submit" name="next" value="-- Start Next Item --"></input>
				</form>
			</div>';
			}
		}
	}

if($_GET['formType'] !== 'uploadFile')
	echo $message;

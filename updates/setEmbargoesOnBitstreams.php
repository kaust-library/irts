<?php
	//Define function to add embargoes to files if it is recorded in the metadata but not showing on the bitstream
	function setEmbargoesOnBitstreams($report, $errors, $recordTypeCounts)
	{
		global $irts;

		$dSpaceAuthHeader = loginToDSpaceRESTAPI();

		$recordTypeCounts['existingEmbargo'] = 0;

		$recordTypeCounts['embargoSetOnBitstreams'] = 0;

		$recordTypeCounts['provenanceAdded'] = 0;

		$result = $irts->query("SELECT e.idInSource, e.value, b.added
			FROM `metadata` e
			LEFT JOIN `metadata` b
			USING(idInSource)
			WHERE e.`source` LIKE 'repository'
			AND e.`field` LIKE 'dc.rights.embargodate'
			AND e.value > '".TODAY."'
	    AND e.deleted IS NULL
			AND b.`field` LIKE 'dspace.bitstream.url'
			AND b.deleted IS NULL
	    AND e.idInSource NOT IN (
	        SELECT idInSource FROM `metadata` WHERE `source` LIKE 'repository'
	        AND `field` LIKE 'dspace.bitstream.embargo'
	        AND deleted IS NULL
	    )
	    AND e.idInSource IN (
	        SELECT idInSource FROM `metadata` WHERE `source` LIKE 'repository'
	        AND `field` LIKE 'dc.type'
	        AND value LIKE 'Article'
	        AND deleted IS NULL
	    )
	    ORDER BY e.`value` ASC");

		while($row = $result->fetch_assoc())
		{
			$embargoSet = FALSE;

			$handle = $row['idInSource'];

			$itemReport = 'Handle: '.$handle.PHP_EOL;

			$embargoEndDate = $row['value'];

			if(!isset($_GET['allDays']))
			{
				if(strpos($row['added'], TODAY)=== FALSE)
				{
					$itemReport .= '- bitstream not added today'.PHP_EOL;

					continue;
				}
			}

			$dspaceObject = getObjectByHandleFromDSpaceRESTAPI($handle, $dSpaceAuthHeader);

			if(is_string($dspaceObject))
			{
				$dspaceObject = json_decode($dspaceObject, TRUE);

				$itemID = $dspaceObject[DSPACE_INTERNAL_ID_KEY_NAME];

				$itemReport .= '- item ID: '.$itemID.PHP_EOL;

				$recordTypeCounts['all']++;

				sleep(1);

				//get the bitsreams for the item
				$bitstreams = getBitstreamListForItemFromDSpaceRESTAPI($itemID, $dSpaceAuthHeader);

				if(is_string($bitstreams))
				{
					// convert the json to array
					$bitstreams = json_decode($bitstreams, TRUE);

					foreach ($bitstreams as $bitstream)
					{
						//check if the bitstream is ORIGINAL
						if($bitstream['bundleName'] == 'ORIGINAL')
						{
							sleep(1);

							$itemReport .= '-- bitstream ID: '.$bitstream[DSPACE_INTERNAL_ID_KEY_NAME].PHP_EOL;

							// get the bitstream metadata
							$bitstreamResponse = getBitstreamFromDSpaceRESTAPI($bitstream[DSPACE_INTERNAL_ID_KEY_NAME], $dSpaceAuthHeader, '?expand=all');

							if(is_string($bitstreamResponse))
							{
								//convert json to array
								$bitstreamResponse = json_decode($bitstreamResponse, TRUE);

								// change the date
								foreach ($bitstreamResponse['policies'] as &$policy)
								{
									if($policy['groupId'] == 0)
									{
										if(empty($policy['startDate']))
										{
											$policy['startDate'] = $embargoEndDate;

											$itemReport .= '-- embargo added'.PHP_EOL;

											$embargoSet = TRUE;
										}
										else
										{
											$itemReport .= '-- existing embargo: '.$policy['startDate'].PHP_EOL;

											$recordTypeCounts['existingEmbargo']++;
										}
									}
								}

								if($embargoSet)
								{
									// reconvert the array to json
									$bitstreamResponse = json_encode($bitstreamResponse);

									sleep(1);

									$recordTypeCounts['embargoSetOnBitstreams']++;

									// put the metadata
									putBitstreamMetadataToDSpaceRESTAPI($bitstream[DSPACE_INTERNAL_ID_KEY_NAME], $bitstreamResponse, $dSpaceAuthHeader);
								}
							}
						}
					}

					if($embargoSet)
					{
						sleep(1);

						$json = getItemMetadataFromDSpaceRESTAPI($itemID, $dSpaceAuthHeader);

						if(is_string($json))
						{
							$metadata = dSpaceJSONtoMetadataArray($json);

							$recordTypeCounts['modified']++;

							$metadata = appendProvenanceToMetadata($itemID, $metadata, __FUNCTION__);

							$json = prepareItemMetadataAsDSpaceJSON($metadata);

								sleep(1);

							$response = putItemMetadataToDSpaceRESTAPI($itemID, $json, $dSpaceAuthHeader);

							if(is_string($json))
							{
								$recordTypeCounts['provenanceAdded']++;
							}
						}
					}
				}

				sleep(5);
				set_time_limit(0);
			}
			echo $itemReport;
			ob_flush();
		}

		$report .= $itemReport;

		$summary = saveReport(__FUNCTION__, $itemReport, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$summary);
	}

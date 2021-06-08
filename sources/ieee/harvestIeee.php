<?php
	//Define function to harvest IEEE metadata records
	function harvestIeee($source)
	{
		global $irts, $newInProcess, $errors;

		$sourceReport = '';

		$errors = array();

		//Record count variable
		$recordTypeCounts = array('all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'unchanged'=>0);

		$iterationsOfIeeeUpdate = 1;

		$resultPlace = 1;

		//Change this to decide how many of the available updates to run
		while($iterationsOfIeeeUpdate <= 2)
		{
			$url = IEEE_API.'apikey='.IEEE_API_KEY.'&format=json&max_records=50&start_record='.$resultPlace.'&sort_order=desc&sort_field=article_number&affiliation=';

			if($iterationsOfIeeeUpdate === 1)
			{
				$url .= INSTITUTION_ABBREVIATION;
			}
			elseif($iterationsOfIeeeUpdate === 2)
			{
				$url .= urlencode(INSTITUTION_NAME);
			}

			$results = json_decode(file_get_contents($url), TRUE);

			$total = $results['total_records'];

			foreach($results['articles'] as $sourceData)
			{
				$resultPlace++;
				$recordTypeCounts['all']++;

				//print_r($sourceData);

				$recordType = processIeeeRecord($sourceData);

				$recordTypeCounts[$recordType]++;

				//During the nightly update, if we meet an existing item we will not continue harvesting for that iteration
				if($recordType !== 'new')
				{
					$iterationsOfIeeeUpdate++;

					$resultPlace = 1;
				}

				$title = $sourceData['title'];

				if(isset($sourceData['abstract_url']))
				{
					$abstractURL = $sourceData['abstract_url'];
				}
				elseif(isset($sourceData['html_url']))
				{
					$abstractURL = $sourceData['html_url'];
				}

				$articleNumber = $sourceData['article_number'];

				if(isset($sourceData['doi']))
				{
					$doi = $sourceData['doi'];

					$existingRecords = checkForExistingRecords($doi, 'dc.identifier.doi', $sourceReport);
				}
				else
				{
					$doi = '';

					$existingRecords = checkForExistingRecords($title, 'dc.title', $sourceReport);
				}

				if(empty($existingRecords))
				{
					$sourceReport .= $recordTypeCounts['all'].') '.$title.' - '.$doi.' - '.$abstractURL.PHP_EOL;

					$sourceReport .= ' - '.$recordType.PHP_EOL;

					//Check for existing IRTS entry
					$irtsID = $source.'_'.$articleNumber;

					$query = "SELECT `idInSource` FROM `metadata` WHERE source LIKE 'irts' AND (idInSource LIKE '$irtsID' OR (field = 'dc.identifier.doi' AND value LIKE '$doi'))";

					$check = $irts->query($query);

					if($check->num_rows === 0)
					{
						$field = 'dc.type';

						if($sourceData['content_type']==='Conferences')
						{
							$type = "Conference Paper";
						}
						else
						{
							$type = "Article";
						}

						$rowID = mapTransformSave('irts', $irtsID, '', $field, '', 1, $type, NULL);

						$field = 'status';

						$rowID = mapTransformSave('irts', $irtsID, '', $field, '', 1, 'inProcess', NULL);

						if(isset($sourceData['doi']))
						{
							$field = 'dc.identifier.doi';

							$rowID = mapTransformSave('irts', $irtsID, '', $field, '', 1, $doi, NULL);
						}

						$newInProcess++;
					}
				}
			}

			//Once all items for the query have been processed, advance to the next query
			if($resultPlace >= $total)
			{
				$iterationsOfIeeeUpdate++;

				$resultPlace = 1;
			}
			//$iterationsOfIeeeUpdate++;
		}

		$sourceSummary = saveReport($source, $sourceReport, $recordTypeCounts, $errors);

		return array('changedCount'=>$recordTypeCounts['all']-$recordTypeCounts['unchanged'],'summary'=>$sourceSummary);
	}

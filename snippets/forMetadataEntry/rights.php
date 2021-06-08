<?php
	$source = explode('_', $idInIRTS)[0];
	$idInSource = explode('_', $idInIRTS)[1];

	//Insert Sherpa Romeo policy information if available
	$issn = getValues($irts, setSourceMetadataQuery($source, $idInSource, NULL, 'dc.identifier.issn'), array('value'), 'singleValue');

	if(empty($issn)&&$source!=='crossref')
	{
		if(empty($doi))
		{
			$doi = getValues($irts, setSourceMetadataQuery($source, $idInSource, NULL, 'dc.identifier.doi'), array('value'), 'singleValue');
		}

		if(!empty($doi))
		{
			$issn = getValues($irts, setSourceMetadataQuery('crossref', $doi, NULL, 'dc.identifier.issn'), array('value'), 'singleValue');
		}
	}

	if(!empty($issn))
	{
		$romeoResults = querySherpaRomeo('publication', array('field'=>'issn', 'operator'=>'equals', 'value'=>$issn));

		if(empty($romeoResults['items']))
		{
			if($record['dc.publisher'][0] === 'IEEE')
			{
				$publisher = 'Institute of Electrical and Electronics Engineers';

				$romeoResults = querySherpaRomeo('publisher', array('field'=>'name', 'operator'=>'equals', 'value'=>$publisher));
			}
		}

		if(empty($romeoResults['items']))
		{
			$romeoResults = querySherpaRomeo('publisher', array('field'=>'name', 'operator'=>'equals', 'value'=>$record['dc.publisher'][0]));
		}

		if(count($romeoResults['items'])===0)
		{
			echo 'No Sherpa Romeo results returned, please search Sherpa Romeo directly at: <a href="https://v2.sherpa.ac.uk/romeo/search.html" target="_blank">https://v2.sherpa.ac.uk/romeo/search.html</a>, or check the journal or publisher website directly to find the relevant policy.';
		}
		else
		{
			$policyLink = $romeoResults['items'][0]["system_metadata"]["uri"];

			if(isset($romeoResults['items'][0]['publisher_policy']))
			{
				$policies = $romeoResults['items'][0]['publisher_policy'][0];
			}
			elseif(isset($romeoResults['items'][0]['policies']))
			{
				//Assume that first listed publisher policy listed is the default policy
				$policies = $romeoResults['items'][0]['policies'][0];
			}

			if(!isset($policies))
			{
				echo 'No publisher policy listed in the Sherpa Romeo results, please search Sherpa Romeo directly at: <a href="https://v2.sherpa.ac.uk/romeo/search.html" target="_blank">https://v2.sherpa.ac.uk/romeo/search.html</a>, or check the journal or publisher website directly to find the relevant policy.';
			}
			else
			{
				$acceptableLocations = array("any_repository","institutional_repository","institutional_website","non_commercial_repository","non_commercial_institutional_repository");

				$policiesFound = array();
				foreach($policies['permitted_oa'] as $policy)
				{
					//print_r($policy);
					$desiredVersions = array('published','accepted');

					if(isset($policy['article_version'][0])) {

						if(in_array($policy['article_version'][0], $desiredVersions))
						{
							foreach($policy['location']['location'] as $location)
							{
								//echo $location;
								if(in_array($location,$acceptableLocations))
								{
									if($policy['additional_oa_fee']==='no')
									{
										$policiesFound[$policy['article_version'][0]][] = $policy;
									}
								}
							}
						}
					}
				}

				$policySelected = array();

				if(isset($policiesFound['published']))
				{
					$record['dc.eprint.version'][0] = "Publisher's Version/PDF";
					$policySelected = $policiesFound['published'][0];
				}
				elseif(isset($policiesFound['accepted']))
				{
					$record['dc.eprint.version'][0] = "Post-print";
					$policySelected = $policiesFound['accepted'][0];
				}

				if(!empty($policySelected))
				{
					echo '<b>Relevant policy details:</b><br>';

					$relevantFields = array("article_version","copyright_owner","additional_oa_fee","location","embargo","license");

					//,"conditions","prerequisites","public_notes"

					ksort($policySelected);

					foreach($policySelected as $policyField => $policyValue)
					{
						if(in_array($policyField, $relevantFields))
						{
							echo "<br>".ucfirst(str_replace('_', ' ', $policyField)).": ";

							if(is_array($policyValue))
							{
								if($policyField === 'embargo')
								{
									echo $policyValue['amount'].' '.$policyValue['units'];
								}
								elseif($policyField === 'license')
								{
									echo $policyValue[0]['license_phrases'][0]['phrase'];
								}
								elseif($policyField === 'location')
								{
									echo '<ul>';
									foreach($policyValue['location_phrases'] as $location)
									{
										echo '<li>'.$location['phrase'].'</li>';
									}
									echo '</ul>';
								}
								else
								{
									echo $policyValue[0];
								}
							}
							else
							{
								echo $policyValue;
							}
						}
					}

					if(isset($policySelected['embargo']) )
					{
						$record['dc.rights.embargolength'][0] = $policySelected['embargo']['amount'];
						$date = new DateTime($record['dc.date.issued'][0]);
						$date->add(new DateInterval('P'.$record['dc.rights.embargolength'][0].'M'));
						$record['dc.rights.embargodate'][0] = $date->format('Y-m-d');
					}

					if(strpos($record['dc.publisher'][0], 'Elsevier') !==FALSE)
					{
						$embargo = retrieveScienceDirectArticleHostingPermissionsByDOI($record['dc.identifier.doi'][0])['embargo'];

						if(!empty($embargo))
							$record['dc.rights.embargodate'][0] = $embargo;
					}
				}

				echo '<br>Full policy record in Sherpa Romeo at: <a href="'.$policyLink.'" target="_blank">'.$policyLink.'</a>.<br><hr><b>NOTE: Please check if a separate license (such as a CC license) has been applied at the article level. If so, that license should be used in place of any publisher or journal default policies.</b><hr>';
			}
		}

		if(isset($record['dc.identifier.doi'][0]))
		{
			$publisher = getValues($irts, setSourceMetadataQuery('crossref', $record['dc.identifier.doi'][0], NULL, 'dc.publisher'), array('value'), 'singleValue');

			//if matched in crossref table
			if(!empty($publisher))
			{
				//echo $publisher;

				$publisherID = getValues($irts, setSourceMetadataQuery('sherpaRomeo', NULL, NULL, 'crossref.publisher.name', $publisher), array('idInSource'), 'singleValue');

				if(!empty($publisherID))
				{
					//echo $publisherID;

					$setStatement = getValues($irts, setSourceMetadataQuery('sherpaRomeo', $publisherID, NULL, 'irts.publisher.setStatement'), array('value'), 'singleValue');

					if(!empty($setStatement))
					{
						//echo $setStatement;

						$placeHolders = array('[JournalTitle]'=>'dc.identifier.journal','[DOI]'=>'dc.identifier.doi','[ArticleLink]'=>'dc.relation.url','[pubDate]'=>'dc.date.issued','[Volume]'=>'dc.identifier.volume','[Issue]'=>'dc.identifier.issue');

						foreach($placeHolders as $placeHolder=>$field)
						{
							if(isset($record[$field][0]))
							{
								$setStatement = str_replace($placeHolder, $record[$field][0], $setStatement);
							}
						}

						$setStatement = str_replace('[year]', substr($record['dc.date.issued'][0], 0, 4), $setStatement);

						$record['dc.rights'][0] = $setStatement;
					}
				}
			}
		}
	}

	if(empty($record['dc.rights'][0]))
	{
		if(!empty($record['dc.identifier.journal'][0]))
		{
			$record['dc.rights'][0] = 'Archived with thanks to '.$record['dc.identifier.journal'][0];
		}
		elseif(!empty($record['dc.publisher'][0]))
		{
			$record['dc.rights'][0] = 'Archived with thanks to '.$record['dc.publisher'][0];
		}
	}

	if(isset($record['dc.identifier.doi'][0]))
	{
		$doi = $record['dc.identifier.doi'][0];

		// check for license info from Crossref
		$record['dc.rights.uri'][0] = getValues($irts,  "SELECT value FROM `metadata` where `source` = 'crossref' and `field` = 'crossref.license.URL' AND (`value` Like '%creativecommons.org%' or `value` Like '%ccby%') AND `idInSource` = '$doi' AND deleted IS NULL ", array('value'), 'singleValue');

		// check for OA links from unpaywall
		if(isset($unpaywallSourceData))
			$responseJson = $unpaywallSourceData;
		else
			$responseJson = queryUnpaywall($doi);

		if(is_string($responseJson))
		{
			// convert it to array
			$response = json_decode($responseJson, TRUE);

			// if there is a result
			if(!is_null($response['best_oa_location']) && !empty($response['best_oa_location']) && !empty($response['oa_locations']) &&  !is_null($response['oa_locations']))
			{
				$record['unpaywall.relation.url'] = array();
				$record['dc.identifier.arxivid'] = array();
				$record['unpaywall.relation.url']['unpaywall.version'] = array();

				//echo 'OA location url:<ul>';
				$oaLocations = $response['oa_locations'];
				foreach ($oaLocations as $oaLocation)
				{
					if(!empty($oaLocation['pmh_id']) && strpos($oaLocation['pmh_id'], 'arXiv') !== false)
					{
						// Example of unpaywall arxiv id : oai:arXiv.org:1803.04951
						
						$record['dc.identifier.arxivid'][] = substr($oaLocation['pmh_id'], (strripos( $oaLocation['pmh_id'],':') + 1 ), strlen($oaLocation['pmh_id']));
					}

					array_push($record['unpaywall.relation.url'], $oaLocation['url']);
					array_push($record['unpaywall.relation.url']['unpaywall.version'], $oaLocation['version']);
				}
			}
		}
	}
?>

<?php
	//define function to process each item returned from Google Scholar
	function processGoogleScholarRecord($item)
	{
		global $irts, $errors, $report;

		$output = array();

		$h3element = $item->getElementsByTagName('h3')->item(0);
		$aElement = $h3element->getElementsByTagName('a')->item(0);
		if(!empty($aElement))
		{
			$output['dc.relation.url'][]['value'] = $aElement->getAttribute('href');

			if(preg_match('/\d{16}/', $aElement->getAttribute('data-clk'), $matches))
			{
				$output['googleScholar.cluster.id'][]['value'] = $matches[0];
			}
		}
		$output['dc.title'][]['value'] = str_replace(array('[HTML][HTML] ','[PDF][PDF] '), '', $h3element->nodeValue);

		foreach($item->getElementsByTagName('div') as $div)
		{
			$class = $div->getAttribute('class');

			if($class === 'gs_a')
			{
				//split on nonword character followed by dash as dash may also be included in author id strings
				$divparts = preg_split("/[\W]-/", $div->ownerDocument->saveHTML($div));

				//!!Also need to remove line breaks!!

				$report .= print_r($divparts, TRUE);

				$authors = $divparts[0];
				if(strpos($divparts[0], ', ')!==FALSE)
				{
					$authors = explode(', ', $divparts[0]);

					$authorSequence = 0;
					foreach($authors as $author)
					{
						if(preg_match('/user=[-_0-9A-Za-z]{12}/', $author, $matches))
						{
							$output['dc.contributor.author'][$authorSequence]['children']['googleScholar.author.id'][]['value'] = str_replace('user=', '', $matches[0]);
						}

						$parts = explode(' ', strip_tags($author));

						if(preg_match('/[A-Za-z]*/', array_pop($parts), $matches))
						{
							$output['dc.contributor.author'][$authorSequence]['value'] = $matches[0].', '.str_replace(array("\r", "\n"), '', implode(' ', $parts));
						}

						$authorSequence++;
					}
				}

				$divparts[1] = strip_tags($divparts[1]);
				if(strpos($divparts[1], ', ')!==FALSE)
				{
					$output['dc.identifier.journal'][]['value'] = str_replace(array("\r", "\n"), '', trim(explode(', ', $divparts[1])[0]));
					$output['dc.date.issued'][]['value'] = substr(trim(explode(', ', $divparts[1])[1]), 0, 4);
				}
				else
				{
					$output['dc.identifier.journal'][]['value'] = str_replace(array("\r", "\n"), '', trim($divparts[1]));
				}

				if(isset($divparts[2]))
				{
					$divparts[2] = strip_tags($divparts[2]);

					$output['dc.publisher'][]['value'] = str_replace('</div>', '', trim($divparts[2]));
				}
			}
			elseif($class === 'gs_rs')
			{
				$excerpt = strip_tags($div->ownerDocument->saveHTML($div));
			}
			elseif($class === 'gs_or_ggsm')
			{
				$aElement = $div->getElementsByTagName('a')->item(0);
				if(!empty($aElement))
				{
					$spanElement = $aElement->getElementsByTagName('span')->item(0);
					if(is_object($spanElement))
					{
						if($spanElement->nodeValue === '[PDF]')
						{
							$output['dc.relation.url'][]['value'] = $aElement->getAttribute('href');
						}
					}
				}
			}
		}

		//Try extracting the ID from the URL
		$id = idFromUrl($output['dc.relation.url'][0]['value']);

		//See if an arXivID or DOI has been extracted
		if(isset($id['arxivID']))
		{
			$output['dc.identifier.arxivid'][]['value'] = $id['arxivID'];
		}
		elseif(isset($id['doi']))
		{
			$output['dc.identifier.doi'][]['value'] = $id['doi'];
		}

		//If still no DOI, try retrieving it from Crossref based on title and first author
		if(!isset($id['doi']))
		{
			if(!empty($output['dc.title'][0]['value'])&&!empty($output['dc.contributor.author'][0]['value']))
			{
				$output['dc.identifier.doi'][]['value'] = retrieveCrossrefDOIByCitation($output['dc.title'][0]['value'], $output['dc.contributor.author'][0]['value']);
			}
		}

		return $output;
	}

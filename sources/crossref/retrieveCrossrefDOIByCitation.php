<?php
	//Define function to check for crossref DOI by title
	function retrieveCrossrefDOIByCitation($sourceTitle, $sourceAuthors)
	{		
		global $report;
		
		$doi = '';
		$sourceTitle = str_replace('…', '', $sourceTitle);
		
		$json = file_get_contents(CROSSREF_API."works?query=".urlencode($sourceTitle)."&rows=1&mailto=".urlencode(IR_EMAIL));
		$json=json_decode($json);
		//print_r($json);
		
		if(empty($json))
		{
			$report .= 'No CrossRef Result!'.PHP_EOL;
		}
		else
		{
			$authors = '';
			$autnames = array();
			$autname = '';
			$crossrefTitle = '';
			$crossrefAuthors = '';
			$firstCrossrefAuthorSurname = '';
			
			$crossrefItem = $json->{'message'}->{'items'}[0];			
			
			if(array_key_exists('author', $crossrefItem))
			{
				$authors = $crossrefItem->{'author'};
				
				if(isset($authors[0]->{'family'}))
				{
					$firstCrossrefAuthorSurname = $authors[0]->{'family'};
				}
				
				foreach($authors as $author)
				{
					if(isset($author->{'family'})&&isset($author->{'given'}))
					{
						$autname = $author->{'family'} . ', ' . $author->{'given'};
						array_push($autnames, $autname);
					}
				}
				
				$crossrefAuthors = implode('; ', $autnames);
			}

			if(isset($crossrefItem->{'title'}[0]))
			{
				$crossrefTitle = $crossrefItem->{'title'}[0];
							
				$report .= 'Source Title: '.$sourceTitle.PHP_EOL.' -- Source Authors: '.$sourceAuthors.' -- Possible DOI: '.$crossrefItem->{'DOI'}.PHP_EOL.' -- Crossref Result Title: '.$crossrefTitle.PHP_EOL.' -- Crossref Result Authors: '.$crossrefAuthors.PHP_EOL;
				
				$sourceTitleToTest = cleanString($sourceTitle);
				
				$crossrefTitleToTest = cleanString($crossrefTitle);
				
				if($sourceTitleToTest !== $crossrefTitleToTest)
				{
					$report .= ' -- No Title Match!'.PHP_EOL;
					
					//$report .= '<br> -- Source title (' .$sourceTitle.' ('.$sourceTitleToTest.') ) and Crossref title (' .$crossrefTitle.' ('.$crossrefTitleToTest.') ) do not match!';
				}
				else
				{
					if(strpos($sourceAuthors, $firstCrossrefAuthorSurname)===FALSE)
					{
						$report .= ' -- No Author Match!'.PHP_EOL;
						//$report .= '<br> -- Crossref first author surname('.$firstCrossrefAuthorSurname.', from Crossref authors: '.$crossrefAuthors.') is not in source authors list (' .$sourceAuthors.' )!';
					}
					else
					{
						$doi = $crossrefItem->{'DOI'};
						$report .= ' -- Match found for DOI: '.$doi.PHP_EOL;
					}
				}	
			}
			else
			{
				$report .= PHP_EOL.' -- No title in Crossref record?'.PHP_EOL;
			}
		}
		return $doi;
	}

<?php
	//Define function to prepare chapters list
	function prepareChapters($record)
	{
		global $irts;
		
		//$isbn = $record['dc.identifier.isbn'][0];
		$doi = $record['dc.identifier.doi'][0];
		$isbn = getValues($irts, setSourceMetadataQuery('crossref', $doi, NULL, 'dc.identifier.isbn'), array('value'), 'singleValue');
		
		if(!empty($isbn))
		{
			if(!in_array($isbn, $record['dc.identifier.isbn']))
			{
				$record['dc.identifier.isbn'][] = $isbn;
			}
		}

		$chapters = queryCrossref('isbn', $isbn, 'book-chapter');
		
		foreach($chapters as $chapter)
		{
			$record['dc.relation.haspart'][] = 'DOI:'.$chapter['DOI'];
		}
		
		natsort($record['dc.relation.haspart']);
		
		$record['dc.relation.haspart'] = array_filter($record['dc.relation.haspart']);

		return $record;
	}

<?php
	//The arXiv API documentation is at: https://arxiv.org/help/api/index
	function retrieveArxivMetadata($type, $value)
	{
		global $report;
		
		$xml = '';
		
		if($type === 'arxivID')
		{
			$url = ARXIV_API_URL."id:" . $value . "&start=0&max_results=1";
		}
		elseif($type === 'name')
		{
			$nameParts = explode(', ', $value);
		
			$value = $nameParts[1].'+'.$nameParts[0];
			
			$value = str_replace(' ', '+', $value);

			$url = ARXIV_API_URL.'au:"'.$value.'"&sortBy=submittedDate&sortOrder=descending&start=0&max_results=30';
		}
		
		$report .= '-- '.$url.PHP_EOL;
				
		$xml = file_get_contents($url);
		$xml = str_replace("arxiv:", "", $xml);
		$xml = simplexml_load_string($xml);

		return $xml;
	}

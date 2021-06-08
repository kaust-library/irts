<?php
	//define function to retrieve results from Google Scholar
	function queryGoogleScholar($count)
	{
		$query = urlencode(INSTITUTION_ABBREVIATION.' OR "'.INSTITUTION_NAME. '"');
		
		$url = GOOGLE_SCHOLAR_URL."?start=".$count."&q=".$query."&hl=en&scisbd=2&as_sdt=0,5";
		
		$html = new DOMDocument();
		libxml_use_internal_errors(true);
		$html->loadHTML(file_get_contents($url, false));
		
		foreach($html->getElementsByTagName('div') as $div)
		{
			$id = $div->getAttribute('id');

			if($id === 'gs_res_ccl_mid')
			{
				$result = $div;				
			}
		}
		return array('url'=>$url,'result'=>$result);
	}
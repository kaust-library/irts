<?php
	function idFromUrl($url)
	{
		$id = array();
		if(strpos($url, 'https://arxiv.org/abs/')!==FALSE)
		{
			$id['arxivID'] = str_replace('https://arxiv.org/abs/', '', $url);
		}
		elseif(strpos($url, 'https://pubs.acs.org/doi/abs/')!==FALSE)
		{
			$id['doi'] = str_replace('https://pubs.acs.org/doi/abs/', '', $url);
		}
		elseif(strpos($url, 'http://pubs.rsc.org/en/content/')!==FALSE)
		{
			$suburl = str_replace('http://pubs.rsc.org/en/content/', '', $url);
			$urlparts = explode('/', $suburl);
			$id['doi'] = '10.1039/'.$urlparts[3];
		}
		elseif(strpos($url, 'tandfonline.com/doi/')!==FALSE)
		{
			$urlparts = explode('tandfonline.com/doi/', $url);
			$idparts = explode('/', $urlparts[1]);
			$id['doi'] = $idparts[1].'/'.$idparts[2];
		}
		elseif(strpos($url, 'onlinelibrary.wiley.com/doi/abs/')!==FALSE)
		{
			$urlparts = explode('onlinelibrary.wiley.com/doi/abs/', $url);
			$id['doi'] = $urlparts[1];
		}
		elseif(strpos($url, 'http://link.springer.com/')!==FALSE)
		{
			$url = str_replace('http://link.springer.com/', '', $url);
			$urlparts = explode('/', $url);
			if(strpos($url, 'content/pdf/')!==FALSE)
			{
				$pdfparts = explode('.', $urlparts[3]);
				$id['doi'] = $urlparts[2].'/'.$pdfparts[0];
			}
			elseif($urlparts[0]=='10.1007')
			{
				$id['doi'] = $urlparts[0].'/'.$urlparts[1];
			}
			elseif($urlparts[0]=='10.1140')
			{
				$id['doi'] = $urlparts[0].'/'.$urlparts[1].'/'.$urlparts[2];
			}
			elseif($urlparts[1]=='10.1140')
			{
				$id['doi'] = $urlparts[1].'/'.$urlparts[2].'/'.$urlparts[3];
			}
			else
			{
				$id['doi'] = $urlparts[1].'/'.$urlparts[2];
			}
		}
		/* elseif(strpos($url, 'http://www.sciencedirect.com/science/article/pii/')!==FALSE)
		{
			$pii = str_replace('http://www.sciencedirect.com/science/article/pii/', '', $url);

			$query = 'pii('.$pii.')';

			$query = urlencode($query);

			$view = '&view=STANDARD';

			$url = ELSEVIER_API_URL.'search/scidir?query='.$query.$view;

			$opts = array(
			  'http'=>array(
				'method'=>"GET",
				'header'=>array("Accept: application/xml", "X-ELS-APIKey: ".ELSEVIER_API_KEY)
				)
			);

			$context = stream_context_create($opts);

			$xml = file_get_contents($url, false, $context);

			//Strip namespaces due to problems in accessing elements with namespaces even with xpath, temporary solution?
			$xml = str_replace('dc:', '', $xml);
			$xml = str_replace('opensearch:', '', $xml);
			$xml = str_replace('prism:', '', $xml);

			$xml = simplexml_load_string($xml);

			$total = $xml->totalResults;

			foreach($xml->entry as $item)
			{
				$id['doi'] = '';

				$id['doi'] = $item->doi;
			}
		}
		elseif(strpos($url, 'http://ieeexplore.ieee.org/xpls/abs_all.jsp?arnumber=')!==FALSE)
		{
			$articlenuumber = str_replace('http://ieeexplore.ieee.org/xpls/abs_all.jsp?arnumber=', '', $url);

			$query = 'an='.$articlenuumber;

			$query = urlencode($query);

			$url = 'http://ieeexplore.ieee.org/gateway/ipsSearch.jsp?'.$query;

			$xml = file_get_contents($url, false);

			$xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

			$total = $xml->totalfound;

			foreach($xml->document as $item)
			{
				$id['doi'] = '';

				$id['doi'] = $item->doi;
			}
		} */
		elseif(strpos($url, 'http://epubs.siam.org/doi/')!==FALSE)
		{
			$url = str_replace('http://epubs.siam.org/doi/', '', $url);
			$urlparts = explode('/', $url);
			$id['doi'] = $urlparts[1].'/'.$urlparts[2];
		}
		elseif(strpos($url, 'http://scitation.aip.org/content/aip/journal/')!==FALSE)
		{
			$url = str_replace('http://scitation.aip.org/content/aip/journal/', '', $url);
			$urlparts = explode('/', $url);
			$id['doi'] = $urlparts[3].'/'.$urlparts[4];
		}
		elseif(strpos($url, 'http://journals.aps.org/')!==FALSE)
		{
			$url = str_replace('http://journals.aps.org/', '', $url);
			$urlparts = explode('/', $url);
			$id['doi'] = $urlparts[2].'/'.$urlparts[3];
		}
		elseif(strpos($url, 'biomedcentral.com/articles/')!==FALSE)
		{
			$urlparts = explode('biomedcentral.com/articles/', $url);
			$id['doi'] = $urlparts[1];
		}
		return $id;
	}

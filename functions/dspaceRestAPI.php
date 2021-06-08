<?php
	//Define functions to interact with the DSpace REST API
	function loginToDSpaceRESTAPI()
	{
		if(DSPACE_VERSION === '5')
		{
			$options = array(
			  CURLOPT_URL => REPOSITORY_API_URL."login",
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => '{"email": "'.REPOSITORY_USER.'", "password": "'.REPOSITORY_PW.'"}',
			  CURLOPT_HTTPHEADER => array(
				"Accept: application/json",
				"Cache-Control: no-cache",
				"Content-Type: application/json",
			  )
			);

			$response = makeCurlRequest($options, 'HTTP/1.1 200', 'response');
			
			if(is_string($response))
			{
				$response = 'rest-dspace-token: '.$response;
			}
		}
		elseif(DSPACE_VERSION === '6')
		{
			$options = array(
			  CURLOPT_URL => REPOSITORY_API_URL."login",
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => 'email='.urlencode(REPOSITORY_USER).'&password='.REPOSITORY_PW,
			  CURLOPT_HTTPHEADER => array(
				"Content-Type: application/x-www-form-urlencoded",
			  )
			);

			$response = makeCurlRequest($options, 'HTTP/1.1 200', 'headers');
			
			if(is_string($response))
			{
				$headers = explode('||', $response);
				
				foreach($headers as $header)
				{
					if(strpos($header, 'Set-Cookie: JSESSIONID=')!==FALSE)
					{
						$header = str_replace('Set-Cookie: ', '', $header);
						
						$headerParts = explode('; ', $header);
						
						$response = 'Cookie: '.$headerParts[0];
					}
				}
			}
		}

		return $response;
	}

	function logoutFromDSpaceRESTAPI($dSpaceAuthHeader)
	{
		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL."logout",
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_HTTPHEADER => array(
			"Cache-Control: no-cache",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options);

		return $response;
	}

	function statusOfTokenForDSpaceRESTAPI($dSpaceAuthHeader)
	{
		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL."status",
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options);

		return $response;
	}

	function getCollectionMetadataFromDSpaceRESTAPI($collectionID, $dSpaceAuthHeader)
	{
		$successHeader = 'HTTP/1.1 200';
		$successResponsePortionNeeded = 'response';

		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'collections/'.$collectionID,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;
	}

	function getItemMetadataFromDSpaceRESTAPI($itemID, $dSpaceAuthHeader)
	{
		$successHeader = 'HTTP/1.1 200';
		$successResponsePortionNeeded = 'response';

		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'items/'.$itemID.'/metadata',
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;
	}

	function getObjectByHandleFromDSpaceRESTAPI($handle, $dSpaceAuthHeader, $expand = NULL)
	{
		if(is_null($expand))
		{
			$url = REPOSITORY_API_URL.'handle/'.$handle;
		}
		else
		{
			$url = REPOSITORY_API_URL.'handle/'.$handle.'?expand='.$expand;
		}

		$successHeader = 'HTTP/1.1 200';
		$successResponsePortionNeeded = 'response';

		$options = array(
		  CURLOPT_URL => $url,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;
	}

	function getItemListForCollectionFromDSpaceRESTAPI($collectionID, $offset, $limit, $dSpaceAuthHeader)
	{
		$successHeader = 'HTTP/1.1 200';
		$successResponsePortionNeeded = 'response';

		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'collections/'.$collectionID.'/items?offset='.$offset.'&limit='.$limit,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;
	}

	function getBitstreamListForItemFromDSpaceRESTAPI($itemID, $dSpaceAuthHeader)
	{
		$successHeader = 'HTTP/1.1 200';
		$successResponsePortionNeeded = 'response';

		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'items/'.$itemID.'/bitstreams',
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;
	}

	function getBitstreamFromDSpaceRESTAPI($bitstreamID, $dSpaceAuthHeader, $params = null)
	{
		$successHeader = 'HTTP/1.1 200';
		$successResponsePortionNeeded = 'response';

		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'/bitstreams/'.$bitstreamID.$params,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;
	}

	function postItemToDSpaceRESTAPI($collectionID, $item, $dSpaceAuthHeader)
	{
		$successHeader = 'HTTP/1.1 200';
		$successResponsePortionNeeded = 'response';

		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'collections/'.$collectionID.'/items',
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "$item",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;
	}

	function putItemMetadataToDSpaceRESTAPI($itemID, $item, $dSpaceAuthHeader)
	{
		$successHeader = 'HTTP/1.1 200';
		$successResponsePortionNeeded = 'response';

		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'items/'.$itemID.'/metadata',
		  CURLOPT_CUSTOMREQUEST => "PUT",
		  CURLOPT_POSTFIELDS => "$item",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;
	}

	function mapItemToCollections($newCollections, $collectionID, $dSpaceAuthHeader)
	{
		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'collections/'.$collectionID.'/items',
		  CURLOPT_CUSTOMREQUEST => "PUT",
		  CURLOPT_POSTFIELDS => "$newCollections",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options);

		return $response;
	}

	function deleteItemFromDSpaceRESTAPI($itemID, $dSpaceAuthHeader)
	{
		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'items/'.$itemID,
		  CURLOPT_CUSTOMREQUEST => "DELETE",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options);

		return $response;
	}

	function deleteBitstreamFromDSpaceRESTAPI($bitstreamID, $dSpaceAuthHeader)
	{
		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'bitstreams/'.$bitstreamID,
		  CURLOPT_CUSTOMREQUEST => "DELETE",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options);

		return $response;
	}

	function postBitstreamToDSpaceRESTAPI($itemID, $file, $name, $description, $bundleName, $dSpaceAuthHeader)
	{
		$context = stream_context_create(
		    array(
		        "http" => array(
		            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
		        )
		    )
		);

		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'items/'.$itemID.'/bitstreams?name='.urlencode($name).'&description='.urlencode($description).'&bundleName='.$bundleName,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: multipart/form-data",
			$dSpaceAuthHeader
		  ),
		  CURLOPT_POSTFIELDS => @file_get_contents($file, FALSE, $context)
		);

		//return $options;

		$response = makeCurlRequest($options);

		return $response;
	}

	function putBitstreamToDSpaceRESTAPI($bitstreamID, $file, $dSpaceAuthHeader)
	{
		$successHeader = 'HTTP/1.1 200';
		$successResponsePortionNeeded = 'headers';

		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'bitstreams/'.$bitstreamID.'/data',
		  CURLOPT_CUSTOMREQUEST => "PUT",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: multipart/form-data",
			$dSpaceAuthHeader
		  ),
		  CURLOPT_POSTFIELDS => file_get_contents($file)
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;
	}

	function putBitstreamMetadataToDSpaceRESTAPI($bitstreamID, $metadata, $dSpaceAuthHeader)
	{
		$successHeader = 'HTTP/1.1 200';
		$successResponsePortionNeeded = 'headers';

		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'bitstreams/'.$bitstreamID,
		  CURLOPT_CUSTOMREQUEST => "PUT",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  ),
		  CURLOPT_POSTFIELDS => "$metadata"
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;
	}

	function putCollectionToDSpaceRESTAPI($collectionID, $collection, $dSpaceAuthHeader)
	{
		$successHeader = 'HTTP/1.1 200';
		$successResponsePortionNeeded = 'headers';

		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'collections/'.$collectionID,
		  CURLOPT_CUSTOMREQUEST => "PUT",
			CURLOPT_POSTFIELDS => "$collection",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;
	}

	function putCommunityToDSpaceRESTAPI($communityID, $community, $dSpaceAuthHeader)
	{
		$successHeader = 'HTTP/1.1 200';
		$successResponsePortionNeeded = 'headers';

		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'communities/'.$communityID,
		  CURLOPT_CUSTOMREQUEST => "PUT",
			CURLOPT_POSTFIELDS => "$community",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;
	}

	function getListOfCollectionsFromDSpaceRESTAPI($dSpaceAuthHeader)
	{
		$successHeader = 'HTTP/1.1 200';
		$successResponsePortionNeeded = 'response';

		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'collections/',
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;
	}

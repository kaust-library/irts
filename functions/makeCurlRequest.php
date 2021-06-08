<?php
	//Define base function with default options for making Curl requests
	function makeCurlRequest($customOptions, $successHeader ='HTTP/1.1 200 OK', $successResponsePortionNeeded = 'response')
	{
		$curl = curl_init();
		$headers = [];
		
		$defaultOptions = array(
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_HEADERFUNCTION => function($curl, $header) use (&$headers)
			{
				$len = strlen($header);
				
				$headers[]=$header;

				return $len;
			}
		);
		
		$options = $customOptions + $defaultOptions;
		
		//print_r($options);
		
		curl_setopt_array($curl, $options);

		$response = curl_exec($curl);
		$error = curl_error($curl);
		$headers = implode('||',$headers);

		curl_close($curl);
		
		//print_r($response);

		if($error) 
		{
			return array('error'=>"cURL Error #:" . $error,'response'=>$response,'headers'=>$headers);
		}
		elseif(strpos($headers, $successHeader)!==FALSE)
		{
			if($successResponsePortionNeeded === 'headers')
			{
				return $headers;
			}
			elseif($successResponsePortionNeeded === 'response')
			{
				return $response;
			}				
		}
		else
		{
			return array('response'=>$response,'headers'=>$headers);
		}
	}

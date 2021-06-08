<?php
	//Define function to prepare the item metadata as JSON for transmission to the DSpace REST API
	function prepareItemMetadataAsDSpaceJSON($metadata, $put = TRUE)
	{
		$json = array('metadata'=>array());
		
		foreach($metadata as $key => $value)
		{		  
			if(!empty($value))
			{
				if(is_string($value))
				{
					$value = preg_replace('/\x{2010}/u','-', $value);
					
					$value = preg_replace('/\x{2009}/u',' ', trim($value));
					
					//$value = preg_replace('/[\n]+/','\\n', trim($value));
					
					$json['metadata'][] = array('key'=>$key,'language'=>null,'value'=>$value);
				}
				elseif(is_array($value))
				{
					foreach($value as $value)
					{
						if(is_array($value))
						{
							$value = $value['value'];
						}
						
						$value = preg_replace('/\x{2010}/u','-', $value);
						
						$value = preg_replace('/\x{2009}/u',' ', trim($value));
						
						//$value = preg_replace('/[\n]+/','\\n', trim($value));
						
						if(!empty($value))
						{					
							$json['metadata'][] = array('key'=>$key,'language'=>null,'value'=>$value);
						}
					}
				}
			}	  		  
		}
		
		if($put)
		{	
			return json_encode($json['metadata'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT);
		}
		else
		{

			return json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT);
		}
	}

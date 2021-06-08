<?php

// function to convert XML to array
function xml2array($xmlObject, $out = array())
{
    foreach ((array)$xmlObject as $index => $node )
	{
        if(is_object($node))
		{
			//var_dump($node);
			
			$out[$index] = xml2array($node);
		}
		elseif(is_array($node))
		{
			$out[$index] = xml2array($node);
		}
		else
		{
			//var_dump($node);
			
			$out[$index] = $node;
		}
	}

    return $out;
}

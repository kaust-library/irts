<?php
	//Define functions to standardize a string so that it can by compared with other strings (like titles)
	function cleanString($string)
	{
		$string = strip_tags($string);
		$string = strtolower($string);
		$string = str_replace(' ', '', $string);
		$string = str_replace('-', '', $string);
		$string = str_replace('–', '', $string);
		$string = str_replace('‐', '', $string);
		$string = str_replace('.', '', $string);
		$string = str_replace(',', '', $string);
		$string = str_replace('/', '', $string);
		$string = str_replace('?', '', $string);
		$string = str_replace('’', '', $string);	
		$string = str_replace("'", "", $string);
		$string = preg_replace('/\s+/', '', $string);
		$string = preg_replace("/(\t|\n|\v|\f|\r| |\xC2\x85|\xc2\xa0|\xe1\xa0\x8e|\xe2\x80[\x80-\x8D]|\xe2\x80\xa8|\xe2\x80\xa9|\xe2\x80\xaF|\xe2\x81\x9f|\xe2\x81\xa0|\xe3\x80\x80|\xef\xbb\xbf)+/", "_", $string);
		$string = substr($string, 0, 120);
		return $string;
	}

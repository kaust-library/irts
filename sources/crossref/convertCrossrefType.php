<?php
	function convertCrossrefType($type)
	{
		//Convert crossref item types to repository equivalent
		if($type==='journal-article')
		{
			$type = 'Article';
		}
		elseif($type==='book')
		{
			$type = 'Book';
		}
		elseif($type==='monograph')
		{
			$type = 'Book';
		}
		elseif($type==='book-chapter')
		{
			$type = 'Book Chapter';
		}
		elseif($type==='posted-content')
		{
			$type = 'Preprint';
		}
		elseif($type==='proceedings-article')
		{
			$type = 'Conference Paper';
		}
		
		return $type;
	}

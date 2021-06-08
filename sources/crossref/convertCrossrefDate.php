<?php
	function convertCrossrefDate($dateToChange)
	{
		//Convert crossref date format to repository equivalent by adding 0s before single integers
		$changedDate = array();
		foreach($dateToChange as $integer)
		{
			$string = (string)$integer;
			if(strlen($string) === 1)
			{
				$string = '0'.$string;
			}
			
			$changedDate[] = $string;
		}
		$changedDate = implode('-', $changedDate);
		
		//original method of doing this
		/* $changedDate = implode('-', $dateToChange);
		$date = new DateTime($changedDate);
		if(strlen($changedDate)==4)
		{
		}
		elseif(strlen($changedDate)>7)
		{
			$changedDate = $date->format('Y-m-d');
		}
		elseif(strlen($changedDate)<7)
		{
			$changedDate = $date->format('Y-m');
		} */
		
		return $changedDate;
	}

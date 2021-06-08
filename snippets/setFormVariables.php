<?php
	//store get variables in the session
	//all variables are unset after one item is processed, but selections are kept
	foreach ($_GET as $key => $value) 
	{
		if($key === 'formType'||$key === 'itemType'||$key === 'problemType'||$key === 'sort'||$key==='ignoreVariantTitles')
		{
			$_SESSION['selections'][$key] = $value;
		}		
		else
		{
			$_SESSION['variables'][$key] = $value;
		}
	}
	
	//store posted variables in the session
	foreach ($_POST as $key => $value) 
	{
		$_SESSION['variables'][$key] = $value;
	}
	
	//add all session selections to the current symbol table
	if(isset($_SESSION['selections']))
	{
		extract($_SESSION['selections']);
	}
	
	//add all session variables to the current symbol table
	if(isset($_SESSION['variables']))
	{
		extract($_SESSION['variables']);
	}
	
	if(!isset($page))
	{
		$page = 0;
	}
	
	//Default sort is "newestFirst"
	if(!isset($sort))
	{
		$sort = 'newestFirst';
	}
	
	//Translate sorting instructions for SQL query
	if($sort === 'newestFirst')
	{
		$sort = 'DESC';
	}
	elseif($sort === 'oldestFirst')
	{
		$sort = 'ASC';
	}	
?>
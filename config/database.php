<?php
	$irts = new mysqli("localhost", MYSQL_USER, MYSQL_PW, "irts");
	
	$ioi = new mysqli("localhost", MYSQL_USER, MYSQL_PW, "ioi");
	
	ini_set('mbstring.internal_encoding','UTF-8');
	ini_set('mbstring.func_overload',7);
	ini_set('default_charset', 'UTF-8');
	
	$irts->set_charset("utf8");
	$ioi->set_charset("utf8");

<?php
	$loginForm =
		'<!DOCTYPE html>
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		
		<link rel="stylesheet" href="../css/loginForm.css">
		
		<title>'.$pageTitle.'</title>
		</head>
		<body>
		<div class="logo">
		<img src="../images/logo.png" alt="'.INSTITUTION_ABBREVIATION.' logo" title="'.INSTITUTION_ABBREVIATION.' logo" />
		</div>

		<div class="title">
		<span>'.$pageTitle.'</span>
		</div>

		<div class="login">
		<form action="" method="post">
		<input type="text" placeholder="'.INSTITUTION_ABBREVIATION.' username" name="username" id="username" value="'.$username.'">
		<div class="help-tip">
				<p>Use your '.INSTITUTION_ABBREVIATION.' username</p>
		</div>
		<input type="password" placeholder="password" name="password" id="password">';
		
	if ($error_msg != '')
	{
		$loginForm .= '<div class="alert-box error"><span>error: </span>'.$error_msg.'</div>';
	}
			  
	$loginForm .= '<input type="submit" value="Sign In">
		</form>
		</div>
		<div class="shadow"></div>		

		<script>
		document.getElementById( "username" ).focus();
		</script>

		</body>
		</html>';
		
	echo $loginForm;	
?>	
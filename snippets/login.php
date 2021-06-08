<?php
	$error_msg = '';
	$username = '';
	$password = '';

	//define functions
	function error_connection($username='')
	{
		// display error message on the form
		$error_msg = 'Unable to establish connection for authentication. Please contact <a href="mailto:'.IR_EMAIL.'">'.IR_EMAIL.'</a> to report this issue.';
		include_once 'html/loginForm.php';
	  
		// send email to repository manager
		$to      = IR_EMAIL;
		$from    = IR_EMAIL;
		$subject = 'ERROR - Process form login: critical connection error';
		$message = "Unable to establish connection for authentication: \n" . print_r(error_get_last(), true);
		$headers = 'From: ' . $from . "\r\n" .
			'Reply-To: ' . $from . "\r\n" .
			'X-Mailer: PHP/' . phpversion();

		mail($to, $subject, $message, $headers);
		
		exit;
	}

	function error_credentials($username='')
	{
	  $error_msg = 'Invalid credentials';
	  include_once 'html/loginForm.php';
	  exit;
	}
	
	// if no credentials submitted
	if(!isset($_POST['username']) and !isset($_POST['password']))
	{
	  include_once 'html/loginForm.php';
	}
	else
	{
		// check submitted credentials
		if (isset($_POST['username']))
		{
		  $username = trim($_POST['username']);
		  // don't allow blank username
		  if (strlen($username)==0)
			error_credentials();
		}

		/*
		* To avoid LDAP injection: when constructing LDAP filters you must ensure that filter values are handled according to RFC2254:
		* "Any control characters with an ACII code < 32 as well as the characters with special meaning in LDAP filters "*", "(", ")", and "\" (the
		*  backslash) are converted into the representation of a backslash followed by two hex digits representing the hexadecimal value of the character."
		*/
		$username = str_replace(array('\\', '*', '(', ')'), array('\5c', '\2a', '\28', '\29'), $username);
		for ($i = 0; $i<strlen($username); $i++) {
			$char = substr($username, $i, 1);
			if (ord($char)<32) {
				$hex = dechex(ord($char));
				if (strlen($hex) == 1) $hex = '0' . $hex;
				$username = str_replace($char, '\\' . $hex, $username);
			}
		}

		if (isset($_POST['password']))
		  $password = trim($_POST['password']);

		// search parameters
		$filter_prefix = "CN=";
		$justthese = array(LDAP_PERSON_ID_ATTRIBUTE, LDAP_EMAIL_ATTRIBUTE, LDAP_NAME_ATTRIBUTE, LDAP_TITLE_ATTRIBUTE);

		// establish connection
		/* NOTE: When OpenLDAP 2.x.x is used, ldap_connect() will always return a resource as it does not actually
		 * connect but just initializes the connecting parameters. The actual connect happens with the next calls
		 * to ldap_* funcs, usually with ldap_bind().
		*/
		$ldapconn =  ldap_connect(LDAP_HOSTNAME_SSL);
		if (!is_resource($ldapconn))
		  // but just in case...
		  error_connection($username);

		// options from http://www.php.net/manual/en/ref.ldap.php#73191
		if (!ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3))
		  error_connection($username);

		ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

		// bind
		// IMPORTANT! works with empty password (must be some kind of anonymous binding allowed by AD)
		if (!ldap_bind($ldapconn, $username . LDAP_ACCOUNT_SUFFIX, $password))
		{
		  // check if it's a connection or credentials error
		  $last_error = error_get_last();
		  $pos = strpos($last_error['message'], "Can't contact LDAP server");

		  if ($pos === false)
		  {
			error_credentials($username);
		  }
		  else
		  {
			error_connection($username);
		  }
		}

		// define search for username
		$filter = $filter_prefix . $username;
		// search user information
		$result = ldap_search($ldapconn, LDAP_BASE_DN, $filter, $justthese);

		if (!$result)
		  error_credentials($username);

		// Successful login

		// retrieve user information
		$data = ldap_get_entries($ldapconn, $result);

		// create new session id to avoid session fixation
		session_regenerate_id(true);
		// unset all of the session variables.
		$_SESSION = array();

		// assign basic values
		$_SESSION['username'] = $username;
		$_SESSION['dn'] = $data[0]["dn"];

		// assign remaining values
		foreach ($justthese as $attr)
		{
		  // the server returns lc attribute names
		  $attr = strtolower($attr);
		  if(isset($data[0][$attr][0]))
			$_SESSION[$attr] = $data[0][$attr][0];
		}

		ldap_close($ldapconn);
		
		// continue to the main form
		header("Location: ".$location);
		exit();
	}	
?>
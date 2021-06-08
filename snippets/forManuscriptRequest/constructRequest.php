<?php
	$request = 'Dear '.implode(', ', $recipients['givenNames']).',
	
	This email is to request deposit of the accepted manuscript of the below publication in the '.INSTITUTION_ABBREVIATION.' repository in accordance with the '.INSTITUTION_ABBREVIATION.' open access policy:
	
	'.$citation.'
	
	The policy of this publisher only allows the accepted version (not the publisher’s final version) to be placed in the repository. This is defined as the author’s manuscript with any changes made as a result of the peer-review process, but prior to publisher’s copy-editing or formatting. Please attach a copy of the appropriate file in a reply to this email.
		
	For more information on the open access policy please visit: '.OAPOLICY_URL.'
		
	If you have any questions or concerns please contact us at '.IR_EMAIL.'.
	
	Sincerely, 
	'.$_SESSION['displayname'].'
	on behalf of The University Library Repository Team';

	$request = preg_replace('/\t+/', '', $request);
	
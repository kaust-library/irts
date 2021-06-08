<?php
	$doi = getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, NULL, 'dc.identifier.doi'), array('value'), 'singleValue');
	
	if(!isset($_POST['emailStep']))
	{
		$authors = getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, NULL, 'dc.contributor.author'), array('rowID', 'value'));
		
		$localCorresponding = array();
		$localNonCorresponding = array();
		
		foreach($authors as $author)
		{
			//echo $author['value'].'<br>';

			$affiliations = getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, $author['rowID'], 'dc.contributor.affiliation'), array('value'), 'arrayOfValues');

			//print_r($affiliations).'<br>';

			$emails = getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, $author['rowID'], 'irts.author.correspondingEmail'), array('value'), 'arrayOfValues');


			# lower case all the emails
			$emails = array_map('strtolower', $emails);
			
			//print_r($emails).'<br>';

			foreach($affiliations as $affiliation)
			{
				//echo $affiliation.'<br>';
				if(institutionNameInString($affiliation))
				{
					//echo 'locally affiliated<br>';
					$match = checkPerson(array('name'=>$author['value']));
					if(!empty($match['localID']))
					{
						//echo 'Person Matched<br>';
						if(!empty($emails))
						{
							$localCorresponding['authors'][] = $match['controlName'];
							$localCorresponding['givenNames'][] = explode(' ', explode(', ', $match['controlName'])[1])[0];
							
							if(!empty($match['email']))
							{
								if(!in_array(strtolower($match['email']), $emails))
								{
									$localCorresponding['emails'][] = $match['email'];
									
									$localCorresponding['emails'] = array_merge($localCorresponding['emails'] , $emails);
								}
								elseif(isset($localCorresponding['emails']))
								{
									$localCorresponding['emails'] = array_merge($localCorresponding['emails'] , $emails);
								}
								else
								{
									$localCorresponding['emails'] = $emails;
								}
							}
						}
						else
						{
							$localNonCorresponding['authors'][] = $match['controlName'];
							$localNonCorresponding['givenNames'][] = explode(' ', explode(', ', $match['controlName'])[1])[0];
							if(!empty($match['email']))
							{
								$localNonCorresponding['emails'][] = $match['email'];
							}
						}
					}
					else
					{
						if(!empty($emails))
						{
							$localCorresponding['authors'][] = $author['value'];
							$localCorresponding['givenNames'][] = explode(' ', explode(', ', $author['value'])[1])[0];
							if(isset($localCorresponding['emails']))
							{
								$localCorresponding['emails'] = array_merge($localCorresponding['emails'] , $emails);
							}
							else
							{
								$localCorresponding['emails'] = $emails;
							}
						}
						else
						{
							$localNonCorresponding['authors'][] = $author['value'];
							$localNonCorresponding['givenNames'][] = explode(' ', explode(', ', $author['value'])[1])[0];
							$localNonCorresponding['emails'][] = '';
						}
						//print_r($author);
					}
				}
			}
		}
		
		//print_r($localNonCorresponding);
		
		if(!empty($localCorresponding))
		{
			//print_r($localCorresponding);
			$recipients = $localCorresponding;
		}
		else
		{
			$recipients = $localNonCorresponding;	
		}
		
		$recipients['authors'] = array_unique($recipients['authors']);
		
		$recipients['givenNames'] = array_unique($recipients['givenNames']);
		
		$recipients['emails'] = array_unique($recipients['emails']);
		
		if(empty($recipients))
		{
			$recipients = array('authors'=>array(),'givenNames'=>array(),'emails'=>array());
			
			echo '<b>No local persons were matched. Please enter their names and emails manually or update the database and reload the page.</b>';
		}
		
		include 'snippets/forManuscriptRequest/prepareCitation.php';
		
		include 'snippets/forManuscriptRequest/constructRequest.php';
		
		$textareaRows = (int)round(strlen($request)/100);

		if($textareaRows===0)
		{
			$textareaRows = 1;
		}

		// get the note for each person in the corresponding authors
		$recipients['notes'] = array();

		foreach($recipients['emails'] as $email) {

		$personNote = getValues($irts, "SELECT `value`  FROM `metadata` WHERE `field` = 'local.person.note' and  `idInSource` IN (select `idInSource` from metadata where `value` = '".$email."' and `field` = 'local.person.email' )", array('value'), 'singleValue');
		
			if(!empty($personNote))
				array_push($recipients['notes'], $personNote);
		}
	
		if(!empty($recipients['notes']))
		{
			echo '<div class="col-sm-12 alert-warning border border-dark rounded"><b> -- Important notes : <br></b>* '.implode('<br> * ', $recipients['notes']).' </div>';
		}		

		echo '
		<div class="col-lg-12">
		<form method="post" action="reviewCenter.php?'.$selections.'">
			<br>'.INSTITUTION_ABBREVIATION.' affiliated author(s) to receive email: '.implode('||', $recipients['authors']).'
			<br>Identified emails: <textarea class="form-control" rows="2" name="emails">'.implode(', ', $recipients['emails']).'</textarea>
			<br>Subject: <textarea class="form-control" rows="2" name="subject">Deposit of the manuscript for your recent publication in the '.INSTITUTION_ABBREVIATION.' Repository</textarea>
			<hr>			
			<b>Draft Message:</b>
			<textarea class="form-control" rows="'.$textareaRows.'" name="request">'.$request.'</textarea>
			<input type="hidden" name="page" value="'.($page).'">
			<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
			<input type="hidden" name="emailStep" value="reviewDraft">
			<button class="btn btn-lg btn-success" type="submit" name="action" value="request">Review the drafted email</button>
		</form>
		</div>';
	}
	elseif($_POST['emailStep']==='reviewDraft')
	{
		echo 'Below is the draft email for the item with DOI: '.$doi.' for your confirmation.<br><hr>';
		
		echo '<br>Emails: '.$_POST['emails'].'<hr>';
		
		echo preg_replace('/\r|\n/', '<br>', $_POST['request']).'<hr>';		
		
		echo '<div class="col-lg-12">
		<form method="post" action="reviewCenter.php?'.$selections.'">
			<input type="hidden" name="emails" value="'.$_POST['emails'].'">
			<input type="hidden" name="request" value="'.$_POST['request'].'">
			<input type="hidden" name="subject" value="'.$_POST['subject'].'">
			<input type="hidden" name="page" value="'.($page).'">
			<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
			<input type="hidden" name="emailStep" value="send">
			<button class="btn btn-lg btn-success" type="submit" name="action" value="request">Send the manuscript request email (copy will be sent to the repository email)</button>
		</form>
		</div>';
	}
	elseif($_POST['emailStep']==='send')
	{
		$emails = $_POST['emails'];
		
		//Headers
		$headers = 'From: '.INSTITUTION_ABBREVIATION.' Repository<'.IR_EMAIL.'>' . "\r\n";
		$headers .= 'Cc: <'.IR_EMAIL.'>' . "\r\n";

		if(mail($emails,$_POST['subject'],$_POST['request'],$headers))
		{
			echo 'Manuscript request email successfully sent to '.$emails.' for item with DOI: '.$doi.'. Record will be marked accordingly.';
			
			$field = 'irts.date.manuscriptRequestSent';

			$rowID = mapTransformSave('irts', $idInIRTS, '', $field, '', 1, TODAY, NULL);
		}
		else
		{
			echo 'Error! -- Manuscript request email failed to send to '.$emails.' for item with ID# '.$idInIRTS.' and DOI: '.$doi.'.';
		}
		
		echo	'<hr><div><form method="post" action="reviewCenter.php?'.$selections.'">
			<input class="btn btn-lg btn-primary" type="submit" name="next" value="-- Start Next Item --"></input>
			</form>
		</div>';
	}
	

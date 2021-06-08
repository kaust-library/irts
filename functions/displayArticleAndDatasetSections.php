<?php
/*

**** This file is responsible of display the atricle details that associated with the dataset.

** Parameters :
	$articleDOI:  unique identifier for the article

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 15 April 2019 - 12:03 PM

*/

//--------------------------------------------------------------------------------------------



function displayArticleAndDatasetSections($record, $template)
{	
	// init 
	global $irts;

	//copy the creator/relation array and remove the extra arrays inside them
	$creators = $record['dc.creator'];
	unset($creators['dc.contributor.affiliation']);
	unset($creators['dc.identifier.orcid']);

	//section 1 : Dataset 
	$sections = '<br> <b>Type:</b> '.$record['dc.type'][0];

	$sections .='<br><br><div class="card w-75" >
		<div class="card-header">'.$record['dc.type'][0].' Information</div>
		<div class="card-body text-dark">
			<p class="card-text" style="display: inline;"><b>'.$record['dc.type'][0].' Title: </b>'.$record['dc.title'][0];

    if(!empty($creators))
	{
		$sections .= '<br> <b>'.$record['dc.type'][0].' Creators: </b>'.implode('; ', $creators);
	}

	foreach($record as $field => $values)
	{
		if(strpos($field, 'dc.identifier') !== FALSE && $field !== 'dc.identifier.handle')
		{
			foreach($values as $value)
			{
				$sections .= '<br><b>'.$template['fields'][$field]['label'].': </b><a href="'.$template['fields'][$field]['baseURL'].$value.'" target="_blank">'.$value.'</a>';
			}
		}
	}

	if(isset($record['dc.date.issued'][0]))
	{
		 $sections .= '<br> <b>Publication Date: </b>'.$record['dc.date.issued'][0];
	}

	$sections .= '<br><b>Relation(s):</b> <div style="padding:0px 0px 0px 30px;">';
	
	foreach ($record as $field => $values)
	{
		if(strpos($field, 'dc.relation.') !== FALSE && $field != 'dc.relation.url')
		{
			foreach($values as $value)
			{
				if(strpos($value, 'DOI:') !== FALSE)
				{
					$sections .= '<b> - '. str_replace('dc.relation.','',$field) . ': </b><a href="'.$template['fields']['dc.identifier.doi']['baseURL'].str_replace('DOI:', '', $value).'" target="_blank">'.str_replace('DOI:', '', $value).'</a><br>';
				}
				elseif(strpos($value, 'URL:') !== FALSE)
				{
					$sections .= '<b> - '. str_replace('dc.relation.','',$field) . ': </b><a href="'.str_replace('URL:', '', $value).'" target="_blank">'.str_replace('URL:', '', $value).'</a><br>';
				}
				else
				{
					$sections .= '<b> - '. str_replace('dc.relation.','',$field) . ': </b>'.$value.'</a><br>';
				}
			}
		}
	}

	$sections .= '</div></p>
		  </div>
		</div>'
		;
	
	######################################################################################################
	
	//section 2 : Article

	//get the article DOI and Title from the handle
	if(isset($record['dc.identifier.handle'][0]))
	{
		foreach ($record['dc.identifier.handle'] as $handle)
		{
			$articleTitle = getValues($irts, "SELECT value FROM `metadata` WHERE `source` LIKE 'repository' AND `idInSource` LIKE '$handle' AND `field` LIKE 'dc.title' AND `deleted` IS NULL", array('value'), 'singleValue');
			
			$articleDOI = getValues($irts, "SELECT value FROM `metadata` WHERE `source` LIKE 'repository' AND `idInSource` LIKE '$handle' AND `field` LIKE 'dc.identifier.doi' AND `deleted` IS NULL", array('value'), 'singleValue');
			
			$articleAuthors = getValues($irts, "SELECT value FROM `metadata` WHERE `source` LIKE 'repository' AND `idInSource` LIKE '$handle' AND `field` LIKE 'dc.contributor.author' AND `deleted` IS NULL", array('value'));

			$sections .='<br> <div class="card w-75" >
			  <div class="card-header">Article Information</div>
			  <div class="card-body text-dark">
				<p class="card-text">'.
					'<b>Article Title: </b>'.$articleTitle.
					'<br><b> Article Authors: </b>'.implode('; ', $articleAuthors);

			$sections .= '<br><b> Article DOI: </b>'.'<a href="'.$template['fields']['dc.identifier.doi']['baseURL'].$articleDOI.'" target="_blank">'.$articleDOI.'</a><br><b> Article Handle: </b> <a href="'.$template['fields']['dc.identifier.handle']['baseURL'].$handle.'" target="_blank">'.$handle.'</a> </p></div></div>';
		}
	}

	return $sections;
}
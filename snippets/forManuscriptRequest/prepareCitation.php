<?php
	if(!empty($doi))
	{
		$citation = getCitationByDOI($doi);
	}
	else
	{
		$citation = '';
	}
	
	if(empty($citation)||is_array($citation))
	{
		$citation = 'Title: '.getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, NULL, 'dc.title'), array('value'), 'singleValue').'
		Authors: '.implode('; ', getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, NULL, 'dc.contributor.author'), array('value'))).'
		Journal: '.getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, NULL, 'dc.identifier.journal'), array('value'), 'singleValue').'
		Publication Date: '.getValues($irts, setSourceMetadataQuery('irts', $idInIRTS, NULL, 'dc.date.issued'), array('value'), 'singleValue').'
		DOI: '.$doi;
		//$citation = htmlentities($citation, ENT_QUOTES);		
	}
?>
<?php
	//Define function to process an item record retrieved from the Scopus Abstract API (http://api.elsevier.com/documentation/AbstractRetrievalAPI.wadl) and return standard metadata output
	function processScopusRecord($input)
	{
		global $irts, $errors, $report;
		
		$source = 'scopus';
		
		$output = array();

		//print_r($input);
		
		foreach($input as $field => $value)
		{
			if($field === 'coredata')
			{
				unset($value->creator);
				
				$isbns = array();
				foreach($value as $childField => $childValue)
				{
					//print_r($childValue);
					$currentField = mapField($source, $source.'.'.$field.'.'.$childField, '');
					
					if($childField === 'description')
					{
						$currentField = 'dc.description.abstract';
						$abstract = $childValue->{"abstract"}->para->asXML();
						
						$tags = array('<inf>','</inf>','<sup>','</sup>');
						foreach($tags as $tag)
						{
							$abstract = str_replace($tag, '', $abstract);
						}
						$abstract = simplexml_load_string($abstract)[0];

						$output[$currentField][]['value'] = (string)$abstract;
					}
					elseif($childField === 'isbn')
					{
						//print_r($childValue);
						$isbns[] = (string)$childValue[0];
					}
					elseif($childField === 'issn')
					{
						//print_r($childValue);
						$issns = explode(' ', (string)$childValue[0]);

						$currentField = 'dc.identifier.issn';
						foreach($issns as $issn)
						{
							$issn = substr($issn,0,4).'-'.substr($issn,-4);
							$output[$currentField][]['value'] = $issn;
						}
					}
					elseif(!empty((string)$childValue[0]))
					{
						if($childField==='title')
						{
							$childValue = $childValue->asXML();
							$childValue = preg_replace('!\s+!', ' ', $childValue);
								
							$tags = array('<inf>','</inf>','<sup>','</sup>');
							foreach($tags as $tag)
							{
								$childValue = str_replace($tag, '', $childValue);
							}
							$childValue = simplexml_load_string($childValue);
						}
						elseif($childField==='publisher')
						{
							$childValue = $childValue->asXML();
							
							$childValue = explode("\r\n", $childValue)[0];
							
							$childValue = simplexml_load_string($childValue);
						}
						
						$output[$currentField][]['value'] = (string)$childValue[0];
					}
				}
				
				$currentField = 'dc.identifier.isbn';
				foreach($isbns as $isbn)
				{
					if(!empty($isbn))
					{
						$output[$currentField][]['value'] = $isbn;
					}
				}
			}

			if($field === 'item')
			{
				$authors = array();
				foreach($input->xpath('//author-group') as $authorGroup)
				{
					$affiliation = '';
					foreach($authorGroup->affiliation as $affiliation)
					{
						$afid = (string)$affiliation->attributes()->afid;
						
						if(isset($affiliation->{'source-text'}))
						{
							$affiliation = (string)$affiliation->{'source-text'};
							
							$affparts = explode(', ', $affiliation);
						}
						else
						{
							unset($affiliation->{'affiliation-id'});
							$affparts = array();
							foreach($affiliation as $partName => $partValue)
							{
								$affparts[] = (string)$partValue;
							}							
						}
						
						if(strpos('@', end($affparts))!==FALSE)
						{
							array_pop($affparts);
						}
					
						$affiliation = implode(', ', $affparts);
					}
					
					foreach($authorGroup->author as $author)
					{
						//print_r($author).PHP_EOL;
						
						$seq = (int)$author->attributes()->seq;
						
						//echo $seq.PHP_EOL;
						
						$authors[$seq]['value'] = (string)$author->{'preferred-name'}->surname.', '.(string)$author->{'preferred-name'}->{'given-name'};
						
						//echo $authors[$seq].PHP_EOL;
						
						$authors[$seq]['children']['dc.identifier.scopusid'][]['value'] = (string)$author->attributes()->auid;

						//echo $authors['dc.identifier.scopusid'][$seq].PHP_EOL;
						
						if(isset($author->{'e-address'}))
						{
							$authors[$seq]['children']['irts.author.correspondingEmail'][]['value'] = (string)$author->{'e-address'};
						}
						
						if(isset($author->attributes()->orcid))
						{
							$authors[$seq]['children']['dc.identifier.orcid'][]['value'] = (string)$author->attributes()->orcid;
						}
						
						$authors[$seq]['children']['dc.contributor.affiliation'][]['value'] = $affiliation;
						
						if(!empty($afid))
						{
							$afkeys = array_keys($authors[$seq]['children']['dc.contributor.affiliation']);
							
							$afkey = array_pop($afkeys);
							
							$authors[$seq]['children']['dc.contributor.affiliation'][$afkey]['children']['dc.identifier.scopusid'][]['value'] = $afid;
						}
					}
				}
				
				ksort($authors);
				//print_r($authors).PHP_EOL;
				$output['dc.contributor.author'] = $authors;
				
				foreach($input->xpath('//grantlist') as $grantlist)
				{
					if(isset($grantlist->{'grant-text'}))
					{
						$currentField = 'dc.description.sponsorship';
						$acknowledgements = $grantlist->{'grant-text'};

						$output[$currentField][]['value'] = (string)$acknowledgements;
					}
					
					/* if(!empty($grant->{'grant-id'}))
					{
						array_push($fundingDetails, $grant->{'grant-agency'}.'('.$grant->{'grant-acronym'}.')::grantNumber::'.$grant->{'grant-id'});
					}
					else
					{
						array_push($fundingDetails, $grant->{'grant-agency'}.'('.$grant->{'grant-acronym'}.')');					
					} */		
				}
				
				foreach($input->xpath('//confevent') as $confevent)
				{
					if(isset($confevent->confname))
					{
						$currentField = 'dc.conference.name';
						$value = (string)$confevent->confname;

						$output[$currentField][]['value'] = $value;
					}

					if(isset($confevent->confdate))
					{
						$currentField = 'dc.conference.date';

						$startDate = $confevent->confdate->startdate->attributes()->year.'-'.$confevent->confdate->startdate->attributes()->month.'-'.$confevent->confdate->startdate->attributes()->day;
						
						if(isset($confevent->confdate->enddate))
						{
							$endDate = $confevent->confdate->enddate->attributes()->year.'-'.$confevent->confdate->enddate->attributes()->month.'-'.$confevent->confdate->enddate->attributes()->day;
						}
						else
						{
							$endDate = '';
						}
						
						if(!empty($endDate))
						{
							$conferenceDate = $startDate.' to '.$endDate;
						}
						else
						{
							$conferenceDate = $startDate;
						}
						$output[$currentField][]['value'] = $conferenceDate;
					}
					
					$conferenceLocationParts = array();					
					if(isset($confevent->conflocation))
					{
						$currentField = 'dc.conference.location';
						
						array_push($conferenceLocationParts, $confevent->conflocation->{'city-group'});
						array_push($conferenceLocationParts, $confevent->conflocation->city);
						array_push($conferenceLocationParts, $confevent->conflocation->state);
						
						if(isset($confevent->conflocation->attributes()->country))
						{
							array_push($conferenceLocationParts, strtoupper($confevent->conflocation->attributes()->country));
						}
						
						$conferenceLocation = implode(', ', array_filter($conferenceLocationParts));
						
						$output[$currentField][]['value'] = $conferenceLocation;
					}
				}
			}
		}
		
		return $output;
	}

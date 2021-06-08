<?php
//Define function to add a provenance statement to DSpace metadata
function appendProvenanceToMetadata($itemID, $metadata, $process = NULL, $fileName = NULL)
{
	if(!is_null($itemID))
	{
		if(!is_null($process))
		{		
			if(isset($_SESSION['displayname']))
			{	
				if(in_array($process, array('fileUpload','addGraphicalAbstractAsThumbnail')))
				{
					$metadata['dc.description.provenance'][] = 'File named '.$fileName.' added by '.$_SESSION['displayname'].' via REST API using the '.IR_EMAIL.' user account on '.TODAY.' through the '.$process.' process.';
				}
				else
				{
					$metadata['dc.description.provenance'][] = 'Record metadata updated via REST API by the '.$process.' process by '.$_SESSION['displayname'].' using the '.IR_EMAIL.' user account on '.TODAY.'.';
				}
			}
			else
			{
				$metadata['dc.description.provenance'][] = 'Record metadata updated via REST API by the '.$process.' process using the '.IR_EMAIL.' user account on '.TODAY.'.';
			}
		}
		elseif(isset($_SESSION['displayname']))
		{
			$metadata['dc.description.provenance'][] = 'Record metadata updated via REST API by '.$_SESSION['displayname'].' using the '.IR_EMAIL.' user account on '.TODAY.'.';
		}
	}
	else
	{
		if(isset($_SESSION['displayname']))
		{
			$metadata['dc.description.provenance'][] = 'Record created via REST API by '.$_SESSION['displayname'].' using the '.IR_EMAIL.' user account on '.TODAY.'.';
		}
		else
		{			
			$metadata['dc.description.provenance'][] = 'Record created via REST API using the '.IR_EMAIL.' user account on '.TODAY.'.';
		}
	}

	return $metadata;
}

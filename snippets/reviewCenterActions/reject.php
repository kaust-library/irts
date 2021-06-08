<?php
	if(!isset($_POST['reasons'])&&!isset($_POST['other']))
	{
		echo 'Item with ID# '.$idInIRTS. ' will be moved to the rejected table.<br> - Please select the reason(s) for rejection:<br><hr><form method="post" action="reviewCenter.php?'.$selections.'">';
		
		if($_GET['itemType'] == 'Unpaywall')
		{
			$reasonArray = array("The link refers to a supplemental file.","The linked version is a final publisher formatted PDF and can not be deposited under the journal policy.","The link refers to the publisher landing page and no file is available that can be deposited under the journal policy.","The link is referring to a related item of a different type.");
		}
		elseif($_GET['itemType'] == 'Dataset')
		{
			$reasonArray = array("None of the dataset creators are ".INSTITUTION_ABBREVIATION." affiliated authors on the related article.","The dataset was cited by or references the article, but was not created as part of the research for the article");
		}
		else
		{
			$reasonArray = array(INSTITUTION_ABBREVIATION." is mentioned in the text of the item", "Only an editor or reviewer of the item is ".INSTITUTION_ABBREVIATION."-affiliated", INSTITUTION_ABBREVIATION." is mentioned in the references", INSTITUTION_ABBREVIATION." is mentioned in an advertisement accompanying the item", INSTITUTION_ABBREVIATION." is mentioned in the biography of an author, but their affiliation with ".INSTITUTION_ABBREVIATION." is not current", "Record is for book with some book chapters by ".INSTITUTION_ABBREVIATION."-affiliated authors, the chapters have their own DOIs and will be handled separately", "File stamped with indication that download was by or access was provided by ".INSTITUTION_ABBREVIATION, "Totally erroneous search result, can not find any mention of ".INSTITUTION_ABBREVIATION);
		}

		foreach($reasonArray as $reason)
		{
			echo '<label class="checkbox"><input type="checkbox" name="reasons[]" value="'.$reason.'">'.$reason.'</label><br>';
		}

		echo '<div class="form-group">
			  <label for="other">Other reason for rejection:</label>
			  <textarea class="form-control" rows="1" name="other"></textarea>
			</div>';

		if(isset($_POST['doi']))
		{
			echo '<input type="hidden" name="doi" value="'.$_POST['doi'].'">';
		}

		echo '<input type="hidden" name="page" value="'.($page).'">
			<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
			<input type="hidden" name="action" value="reject">
			<input class="btn btn-lg btn-success" type="submit" name="reject" value="Reason Identified: Move To Rejected Table"></input>
		</form>';
	}
	else
	{
		//echo 'rejected';
		$reasons = array();
		if(isset($_POST['reasons']))
		{
			$reasons = $_POST['reasons'];
		}

		if(!empty($_POST['other']))
		{
			$reasons[] = $_POST['other'];
		}
		$reasons = implode('; ', $reasons);

		if(!empty($reasons))
		{
			echo 'Marking item with ID: '.$idInIRTS.' as rejected for the below reasons: <br>-- '.$reasons;

			if( $_GET['itemType'] == 'Unpaywall')
			{
				// check if there is more than one ID in source
				$irtsIDs =  getValues($irts, "SELECT `idInSource` FROM `metadata` WHERE `source` = 'irts' AND `field` = 'dc.identifier.doi' AND `value` = '".$doi."' AND `deleted` IS NULL ORDER BY `added` ASC", array('idInSource'), 'arrayOfValues');

				foreach ($irtsIDs as $idInIRTS)
				{
					$result = saveValue('irts', $idInIRTS, 'irts.check.unpaywall', 1, 'rejected' , NULL);
					
					$parentRowID = $result['rowID'];

					$result = saveValue('irts', $idInIRTS, 'irts.processedBy', 1, $reviewer, $parentRowID);

					$result = saveValue('irts', $idInIRTS, 'irts.rejectedReason', 1, $reasons, $parentRowID);
				}
			}
			else
			{
				$result = saveValue('irts', $idInIRTS, 'irts.processedBy', 1, $reviewer, NULL);

				$result = saveValue('irts', $idInIRTS, 'irts.status', 1, 'rejected' , NULL);
				
				$parentRowID = $result['rowID'];

				$result = saveValue('irts', $idInIRTS, 'irts.rejectedReason', 1, $reasons, $parentRowID);
			}
		}

		echo '<hr><div><form method="post" action="reviewCenter.php?'.$selections.'">
				<input class="btn btn-lg btn-primary" type="submit" name="next" value="-- Start Next Item --"></input>
				</form>
			</div>';
	}

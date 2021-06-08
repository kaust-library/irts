<?php
	if($formType === 'processNew')
	{
		echo '<br><br><div class="col-lg-6"><form method="post" action="reviewCenter.php?'.$selections.'">
		<input type="hidden" name="page" value="'.($page).'">
		<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
		<input type="hidden" name="step" value="initial">
		<input type="hidden" name="record" value="'.htmlspecialchars(json_encode($record, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT)).'">';


	// -------------------- Handling the non-doi articles and conference papers ---------

		if(!isset($record['dc.identifier.doi'][0]) && in_array( $record['dc.type'][0], array('Article', 'Book', 'Book Chapter', 'Conference Paper' )))
		{
				echo '
				<button class="btn btn-block btn-info" type="submit" name="action" value="addItemDOIManually">-- Add: Item\'s DOI Manually --</button>';
		}

	// -----------------------------------------------------------------------------------

		echo '
		<button class="btn btn-block btn-danger" type="submit" name="action" value="reject">-- Reject: Not a '.INSTITUTION_ABBREVIATION.'-affiliated or '.INSTITUTION_ABBREVIATION.'-funded Item --</button>
		<button class="btn btn-block btn-success" type="submit" name="action" value="deposit">-- Deposit: '.INSTITUTION_ABBREVIATION.' Affiliated or Funded Item --</button>
		<button class="btn btn-block btn-warning" type="submit" name="action" value="addNote">-- Problem Item: Add Note For Admin Review --</button>
		<button class="btn btn-block btn-primary" type="submit" name="action" value="skip">-- Skip: Take No Action and Move to Next Item --</button>
		</form>
		</div>';
	}
	elseif($formType === 'review')
	{
		echo '<br><br><div class="col-lg-6">
		<form method="post" action="reviewCenter.php?'.$selections.'">
			<input type="hidden" name="transferType" value="createNewItem">
			<input type="hidden" name="page" value="'.($page).'">
			<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
			<button class="btn btn-block btn-warning" type="submit" name="action" value="transfer">-- Directly add as new item in DSpace : Skip metadata editing --</button>
		</form>
		<br>
		<form method="post" action="reviewCenter.php?'.$selections.'">
			<input type="hidden" name="page" value="'.($page).'">
			<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
			<input type="hidden" name="step" value="review">
			<input type="hidden" name="record" value="'.htmlspecialchars(json_encode($record, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT)).'">
			<button class="btn btn-block btn-success" type="submit" name="action" value="deposit">-- Reprocess Item : Jump To Final Review Step --</button>
		</form>
		<br>
		<form method="post" action="reviewCenter.php?'.$selections.'">
			<input type="hidden" name="page" value="'.($page).'">
			<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
			<input type="hidden" name="step" value="initial">
			<input type="hidden" name="record" value="'.htmlspecialchars(json_encode($record, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT)).'">
			<button class="btn btn-block btn-success" type="submit" name="action" value="deposit">-- Reprocess Item : All Steps --</button>
			<button class="btn btn-block btn-warning" type="submit" name="action" value="addNote">-- Problem Item: Add Note For Later Review --</button>
			<button class="btn btn-block btn-danger" type="submit" name="action" value="reject">-- Ignore Permanently --</button>
			<button class="btn btn-block btn-primary" type="submit" name="action" value="skip">-- Skip: Take No Action and Move to Next Item --</button>
		</form>
		</div>';
	}

?>

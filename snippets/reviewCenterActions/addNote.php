<?php
	if(isset($_POST['note']))
	{
		$note = $_POST['note'];

		$message .= 'Processing item with ID# '.$idInIRTS. ', adding note for admin to check';
		
		$result = saveValue('irts', $idInIRTS, 'irts.processedBy', 1, $reviewer, NULL);

		$result = saveValue('irts', $idInIRTS, 'irts.status', 1, 'problem' , NULL);
		
		$parentRowID = $result['rowID'];

		$result = saveValue('irts', $idInIRTS, 'irts.note', 1, $note, $parentRowID);

		echo '<hr><div><form method="post" action="reviewCenter.php?'.$selections.'">
				<input class="btn btn-lg btn-primary" type="submit" name="next" value="-- Start Next Item --"></input>
				</form>
			</div>';
	}
	else
	{
		echo 'Item with ID# '.$idInIRTS.' needs to have problem note added.<br><hr><form method="post" action="reviewCenter.php?'.$selections.'">
			<div class="form-group">
			  <label for="note">Note:</label>
			  <textarea class="form-control" rows="5" name="note"></textarea>
			</div>
			<input type="hidden" name="page" value="'.($page).'">
			<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
			<input class="btn btn-lg btn-success" type="submit" name="action" value="addNote"></input>
		</form>';
	}

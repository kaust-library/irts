<?php
	echo 'This item, with IRTS ID '.$idInIRTS. ', will be marked as depositable.<hr><br>';

	//print_r($_POST);

	//print_r($_POST['record']);

	if(is_string($_POST['record']))
	{
		$record = json_decode(htmlspecialchars_decode($_POST['record']), TRUE);
	}
	else
	{
		$record = $_POST['record'];
	}

	//print_r($record);

	if($step === 'rights' || $step === 'DatasetRights')
	{
		include_once "snippets/forMetadataEntry/rights.php";
	}
	elseif($step === 'acknowledgementsPlus')
	{
		$record = prepareAcknowledgements($record);
	}
	elseif($step === 'chapters')
	{
		$record = prepareChapters($record);
	}
	elseif($step === 'review')
	{
		if(isset($record['irts.contributor.type']))
		{
			if($record['irts.contributor.type'][0] === 'Editors')
			{
				$record['dc.contributor.editor'] = $record['dc.contributor.author'];
				unset($record['dc.contributor.author']);
			}
			elseif($record['irts.contributor.type'][0] === 'Authors')
			{
				unset($record['dc.contributor.editor']);
			}
			unset($record['irts.contributor.type']);
			unset($record['dc.contributor.affiliation']);
		}
	}

	echo '<form method="post" action="reviewCenter.php?'.$selections.'">';

	$step = $_POST['step'];

	while(!in_array(key($template['steps']), [$step, NULL]))
	{
		next($template['steps']);
	}

	if(next($template['steps']))
	{
		$nextStep = key($template['steps']);

		// ignore the unpaywallstep
		if($nextStep  == 'UnpaywallStep')
			$nextStep = 'review';
	}
	else
	{
		$nextStep = 'review';
	}

	//print_r($template);

	include_once "snippets/displayForm.php";

	if($step !== 'review')
	{
		
		echo '<input type="hidden" name="page" value="'.($page).'">
			<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
			<input type="hidden" name="step" value="'.$nextStep.'">
			<button class="btn btn-lg btn-success" type="submit" name="action" value="deposit">Proceed to Next Step</button>';
			
		if($formType === 'reviewStep')
		{
			echo '<button class="btn btn-block btn-primary" type="submit" name="action" value="skip">-- Skip: Take No Action and Move to Next Item --</button>';
		}
			
		echo '</form>';
	}
	else
	{
		echo '<input type="hidden" name="page" value="'.($page).'">
			<input type="hidden" name="idInIRTS" value="'.$idInIRTS.'">
			<button class="btn btn-lg btn-success" type="submit" name="action" value="save">Save Updated Metadata</button>
		</form>';
	}


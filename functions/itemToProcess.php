<?php
	//define function to show full item info for processing
	function itemToProcess($formType, $template, $idInIRTS)
	{
		global $irts, $page;

		//print_r($_SESSION["variables"]);

		unset($_SESSION['variables']);

		//Set startTime for this item
		$_SESSION["variables"]["startTime"]=date("Y-m-d H:i:s");

		foreach($_SESSION['selections'] as $selection=>$value)
		{
			$selections[]=$selection.'='.$value;
		}
		if(isset($page))
		{
			$selections[]='page='.$page;
		}
		$selections = implode('&', $selections);

		if($formType === 'processNew')
		{
			$source = explode('_', $idInIRTS)[0];

			$idInSource = explode('_', $idInIRTS, 2)[1];
		}
		elseif($formType === 'review')
		{
			$source = 'irts';
			$idInSource = $idInIRTS;
		}

		echo '<div class="container">';
		include 'snippets/displayItemDetails.php';
		include 'snippets/html/processButtons.php';
		echo '</div>';
	}

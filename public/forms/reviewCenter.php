<?php

	header('Content-Type: text/html; charset=UTF-8');

	//This allows users to use the back button smoothly without receiving a warning from the browser
	header('Cache-Control: no cache');
	session_cache_limiter('private_no_expire');

	ini_set('display_errors', 1);

	//set application home directory as the include path
	set_include_path('../../');

	//include core configuration and common function files
	include_once 'include.php';

	$pageTitle = 'Metadata Review Center';
	$pageLink = 'reviewCenter.php';

	//initialize the session.
	session_start();

	// check for authenticated user
	if(!isset($_SESSION['username']))
    {
		$location = 'reviewCenter.php';

		include_once 'snippets/login.php';
	}
	elseif(!in_array($_SESSION['mail'], AUTHORIZED_PROCESSORS))
	{
		include_once 'snippets/html/header.php';

		include_once 'snippets/html/startBody.php';

		echo '<div class="text">
		Your email address ('.$_SESSION['mail'].') is not in the list of authorized processors for the new items form. If you believe you should have access to this form, please email <a href="mailto:'.IR_EMAIL.'">'.IR_EMAIL.'</a> for access.</a>.
		</div>';
	}
	else
	{
		include_once 'snippets/html/header.php';

		include_once 'snippets/html/startBody.php';
		
		include_once 'snippets/problemTypes.php';

		include 'snippets/setFormVariables.php';

		$reviewer = $_SESSION['mail'];

		//$form = 'processNewItems';
		$message = '';

		if(!isset($_GET['formType']))
		{
			unset($_SESSION['variables']);

			unset($idInIRTS);

			$_SESSION['variables']['page'] = 0;

			echo '<div class="container">';

			// if the users is admin show the button
			if(in_array($reviewer, ADMINS))
			{				
				echo '<div class="btn-group">
						  <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						   Template
						  </button>
						  <div class="dropdown-menu">
						    <a class="dropdown-item" href="templateForm.php?selected=New">Create A New Template</a>';

				$templates = getValues($irts, "SELECT DISTINCT `idInSource`  FROM `metadata` WHERE `source` LIKE 'irts' AND `idInSource` LIKE 'itemType_%' AND `deleted` IS NULL", array('idInSource'));
				
				foreach ($templates as $template)
				{
					$template = str_replace('itemType_', '', $template);
					echo '<a  class="dropdown-item" href="templateForm.php?selected='.$template.'">Edit '.$template.' Template</a>';					
				}
				echo '</div></div>';
			}
			
			//Links to dashboards
			echo ' <a href="../dashboards/irtsAdmin.php" type="button" style="margin: 0px 5px 0px 0px;" class="btn btn-primary rounded">IRTS Admin Dashboard</a>
			<a href="../dashboards/openAccess.php" type="button" style="margin: 0px 5px 0px 0px;" class="btn btn-primary rounded">Open Access Dashboard</a>
			';
			echo '</div>';

			include_once 'snippets/reviewCenterLandingPage.php';
		}
		elseif($_GET['formType']==='addNewItem')
		{
			include_once 'snippets/addNewItem.php';
		}
		elseif($_GET['formType']==='uploadFile' || isset($_POST['uploadSelections'])){
			include_once 'snippets/reviewCenterActions/uploadFile.php';
		}
		else
		{
			foreach($_SESSION['selections'] as $selection=>$value)
			{
				$selections[]=$selection.'='.$value;
			}

			if(isset($page))
			{
				$selections[]='page='.$page;
			}

			$selections = implode('&', $selections);

			if(isset($itemType))
			{
				if($formType === 'variantMatching')
				{
					$formHeader = $itemType.' Unmatched Variants';

					echo '<div class="container"><h3 class="text-center"><b>'.$formHeader.'</b></h3><hr></div>';

					echo '<div class="container">';

					include_once "snippets/reviewCenterActions/matchVariant.php";
				}
				elseif($formType === 'reviewStep')
				{
					$formHeader = 'Review '.$itemType.' Step';

					echo '<div class="container"><h3 class="text-center"><b>'.$formHeader.'</b></h3><hr></div>';

					echo '<div class="container">';

					include_once "snippets/reviewCenterActions/reviewStep.php";

					echo '</div>';
				}
				else
				{
					//print_r($_POST);
					
					$template = prepareTemplate($itemType);

					if($formType === 'processNew')
					{
						$formHeader = 'New '.$itemType.' Records';
						
						$items = getValues($irts, "SELECT DISTINCT idInSource, m2.added, m2.rowID FROM `metadata` m LEFT JOIN metadata m2 USING(idInSource) 
						WHERE m.`source` LIKE 'irts' 
						AND m.`field` LIKE 'dc.type' 
						AND m.`value` LIKE '$itemType' 
						AND m2.field LIKE 'irts.status' 
						AND m2.value LIKE 'inProcess' 
						AND m2.deleted IS NULL
						ORDER BY m2.added DESC", array('idInSource', 'added'));
					}
					elseif($formType === 'review')
					{
						$formHeader = 'Old '.$itemType.' Records to Review';
						
						$problemTypeQuery = str_replace('{itemType}', $itemType, $problemTypes[$problemType]['itemListQuery']);
						
						$items = getValues($irts, $problemTypeQuery, array('idInSource', 'added'));
					}

					echo '<div class="container"><h3 class="text-center"><b>'.$formHeader.': '.count($items).'</b></h3><hr></div>';

					/* //Display reason for status, if available
					if(count($items)!==0)
					{
						$statusRowID = $items[$page]['rowID'];

						$reason = getValues($irts, "SELECT value FROM `metadata` WHERE `source` LIKE 'irts' AND parentRowID LIKE '$statusRowID' AND `field` LIKE 'irts.status.reason' AND deleted IS NULL", array('value'), 'singleValue');

						if(!empty($reason))
						{
							echo '<p><b>'.ucwords($formType).' reason:</b> '.$reason.'</p><hr>';
						}
					} */

					//If user is ready to process a new item
					if(!isset($_POST['action']))
					{
						unset($idInIRTS);
						unset($_SESSION['variables']['idInIRTS']);

						
						if(isset($_GET['idInIRTS']))
						{
							$idInIRTS = $_GET['idInIRTS'];
						}
						elseif(count($items)!==0)
						{
							if(isset($items[$page]['idInSource']))
							{
								$idInIRTS = $items[$page]['idInSource'];
							}
							else
							{
								$idInIRTS = $items[$page];
							}
						}

						if(isset($idInIRTS))
						{
							itemToProcess($formType, $template, $idInIRTS);
						}
						else
						{
							echo '<br> -- No more records to process --';
						}
					}
					elseif($_POST['action']==='skip')
					{
						// continue to the main form
						$page++;
						
						//reset selections to include new page number
						$selections = array();
						
						foreach($_SESSION['selections'] as $selection=>$value)
						{
							$selections[]=$selection.'='.$value;
						}

						if(isset($page))
						{
							$selections[]='page='.$page;
						}

						$selections = implode('&', $selections);
						
						header("Location: reviewCenter.php?$selections");
						exit();
					}
					//if form submission received with action to perform
					else
					{
						$action = $_POST['action'];

						echo '<div class="container">';

						include_once "snippets/reviewCenterActions/$action.php";

						echo '</div>';
					}
				}
			}
		}
	}
	include_once 'snippets/html/footer.php';

	//For development - uncomment to see the contents of the session
	//print_r($_SESSION);
	
	//For development - uncomment to see the contents of the record
	//print_r($record);
	
	//For development - uncomment to see the contents of the template
	//print_r($template);
	
	//For development - uncomment to see the selections
	//print_r($selections);
?>

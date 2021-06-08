<?php
	/*

**** This file is responsible of deleting items from the database.

** Parameters :
	$deletedItems:  array contains the deleted items as string.
	template: template label.
	isTemplate: flag ( T or F ) by default it will be false

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 4 January 2020 - 11:37 AM

*/

//--------------------------------------------------------------------------------------------

function deleteFromTheTemplate($deletedItems, $template, $isTemplate=false)
{
	
	
	//init
	global $irts;
	$source = 'irts';
	
	if($isTemplate){
		
		// get all the steps for this template
		$stepRowIDs = getValues($irts, "SELECT  rowID, value FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = 'itemType_".$template."' AND `field` ='irts.form.step' AND `deleted` IS NULL", array('rowID', 'value'), 'arrayOfValues');
		
		foreach( $stepRowIDs as $stepRowID ) {
			
			// add the step to the array 
			$deletedItems[$stepRowID['value']][] = $stepRowID['value'];
				
		}
			
		// delete clone from row 
		$cloneFromRowID = getValues($irts, "SELECT  rowID FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = 'itemType_".$template."' AND `field` ='irts.form.template' AND `deleted` IS NULL", array('rowID'), 'singleValue');
		
		if(!empty($cloneFromRowID)){
		
			update($irts, 'metadata', array("deleted"), array(date("Y-m-d H:i:s"), $cloneFromRowID), 'rowID');
			
		}
	
	}
	

	
	// for each item check if it is a field or a step 
	foreach($deletedItems as $key => $items){
		
		foreach($items as $item){
		
			$data = getValues($irts, "SELECT `field`, rowID FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = 'itemType_".$template."' AND `value` ='".$item."' AND `deleted` IS NULL", array('field', 'rowID'), 'arrayOfValues');
			
			if(strpos($data[0]['field'], 'irts.form.step') !== FALSE){
				
				//get all the fields 
				$fieldsValue = getValues($irts, "SELECT  value, rowID FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = 'itemType_".$template."' AND `field` ='irts.form.fields' AND parentRowID  ='".$data[0]['rowID']."' AND `deleted` IS NULL", array('value', 'rowID'), 'arrayOfValues');
							
				if(!empty($fieldsValue)) {
					// convert the fields to array
					$fields = explode(',', $fieldsValue[0]['value']);
						
					
					// resend the fields to the
					foreach( $fields as $field ) {
						
						$deletedFields[] = array($item=>$field);
						deleteFromTheTemplate($deletedFields, $template);
						
					}
					
					
					// delete the step ( set the deleted field to today's date )
					update($irts, 'metadata', array("deleted"), array(date("Y-m-d H:i:s"), $data[0]['rowID']), 'rowID');
					
					// delete the irts.form.fields for the step
					update($irts, 'metadata', array("deleted"), array(date("Y-m-d H:i:s"), $fieldsValue[0]['rowID']), 'rowID'); 
				}
				
				
			} else {
				
				// if the item is not step
				
				// update the deleted for the field 
				update($irts, 'metadata', array("deleted"), array(date("Y-m-d H:i:s"), $data[0]['rowID']), 'rowID');
				
				// update the deleted for the child
				update($irts, 'metadata', array("deleted"), array(date("Y-m-d H:i:s"),$data[0]['rowID']), 'parentRowID');
				
				
				// step rowID
				$stepRowID = getValues($irts, "SELECT  rowID FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = 'itemType_".$template."' AND `field` ='irts.form.step' AND value ='".$key."' AND `deleted` IS NULL", array('rowID'), 'singleValue');
							
				
				// get the step fields
				$stepData = getValues($irts, "SELECT `value`, rowID FROM `metadata` WHERE `source` = 'irts' AND `idInSource` = 'itemType_".$template."' AND `field` ='irts.form.fields' AND parentRowID = '".$stepRowID."' AND `deleted` IS NULL", array('value', 'rowID'), 'arrayOfValues');
				
				
				if(!empty($stepData)) {
					// convert the fields to array
					$fields = explode(',', $stepData[0]['value']);
					
					
					// remove the deleted field
					$fields = array_diff($fields, array($item));
					
				
					// convert the fields to string
					$fields = implode(',', $fields);
				
					//save the new fields 
					$rowID =  saveValue($source, 'itemType_'.$template, 'irts.form.fields' , 1, $fields, $stepRowID);
					
				
					
					//  set the deleted field 
					update($irts, 'metadata', array("deleted"), array(date("Y-m-d H:i:s"),$stepData[0]['rowID']), 'rowID');
					
					
					
				
				}
				
			}
			
		}
		
	}
	

	//return to the main page it's the template that was deleted
	if($isTemplate){
		header("Location: /irts/forms/reviewCenter.php" );		
		die();	
	}
	
	
	
}
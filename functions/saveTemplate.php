<?php
/*

**** This function  responsible of saving the template values to the database.

** Parameters :
	$record : array of template values

** Created by : Yasmeen alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 03 December 2020 - 10:45 AM

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------


function saveTemplate($record, $idInSource){
		
		
		//init 
		global $irts;
		$source = 'irts';
		$inputFields = array('field'=> 'irts.form.field','label' => 'irts.form.label', 'baseURL' => 'irts.form.baseURL', 'note' => 'irts.form.note','inputType' => 'irts.form.inputType', 'inputValue'=> 'irts.form.values');
		$fieldRowID = null;
		$fieldPlace = 1;
		
		
		
		foreach($record as $key => $values){
			
			//init for each step
			$fields = array();
			
			foreach($values as $keyValue => $value){
				
				if(is_string($value)){
					
					
					//save th step and save the 
					$stepData = saveValue($source, $idInSource, 'irts.form.step', $value, $key, NULL);
					//echo 'step: '.$key.' place: '.$value.'<br>';
					
				
					
					
				} else {
					
					
					
					foreach($value as $keyField => $valuesField){
						
						//add the field to the database 
						$inputField = $inputFields[$keyField];
					
						
						
						foreach($valuesField as $field => $inputFieldValue){
							
														
							if(is_string($inputFieldValue)){
								
								if(!empty($inputFieldValue)){
									
									
									
									if(strpos($inputField , 'irts.form.field') !== false){
							
										$fields[] = $inputFieldValue;
									
										
										$rowID =  saveValue($source, $idInSource, $inputField , $fieldPlace, $inputFieldValue, null);
										
										$fieldRowID = $rowID['rowID'];
										
										// increase the $fieldPlace by 1
										$fieldPlace++;
										
										// echo 'fieldRowID:  '.$fieldRowID.'<br>';
										// echo $fieldRowID.'    '.$inputFieldValue.'<br>';
										
									} else {
										
										//save the other ( label, BaseURL, note, inputType )
										saveValue($source, $idInSource, $inputField , 1, $inputFieldValue, $fieldRowID);
										
										// echo $fieldRowID.'    '.$inputFieldValue;
										
									}
									
								}
								
							} else {
								
								
								// save values
								$inputFieldValues = implode(',', $inputFieldValue);
								
								if(!empty($inputFieldValues)){
									
									saveValue($source, $idInSource, $inputField , 1, $inputFieldValues, $fieldRowID);
									
									// echo $field .'  '.$inputFieldValues.'<br>';
									
								}
								
								
							}
						
						}
						
						
					 }
					 
					
					 // set the rowID for the field to null 
					 $fieldRowID = null;
					 
					
				}
				
			}
			
			
			//add all the fields to the step 
			$fields = implode(',', $fields);
			
			if(!empty($fields)) {
				saveValue($source, $idInSource, 'irts.form.fields', 1, $fields , $stepData['rowID']);
			}
			//echo 'fields: '.implode(',', $fields).'<br>';
			// echo '<br> ----------------- <br>';
	
		}
		
		//save the clone from template
	
}
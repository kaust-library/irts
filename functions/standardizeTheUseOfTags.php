<?php


/*

**** This function is responsible for standardizing the use of dollar signs and representation of formulas in abstracts and titles.

** Parameters :
	$text: abstract or title. 
	$remove: boolean variable to remove the tags completely.

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 20 January 2020 - 3:52 PM

*/

//--------------------------------------------------------------------------------------------


function standardizeTheUseOfTags($text, $remove=False) {

	// -------------------------------------------- Replace the tags with the appropriate replacement ---------------------------------------------
	//tags arrays
	$tags = array('<sub>', '<sup>', '<italic>', '<i>', '<jats:sup>');
	$tagsReplacement = array('$_{', '$^{', '$\textit{', '$\textit{', '$^{');
	$closeTags = array('</sub>', '</sup>', '</italic>', '</i>', '</jats:sup>');
	$itemsID = array();

	foreach ($tags as $index => $tag) {

		// replace the tag
		if(!($remove)){
			
			$text = str_replace($tag,$tagsReplacement[$index],$text);

			// get the close tag and replace it with }$
			if($closeTags[$index] != '')
				$text = str_replace($closeTags[$index],'}$',$text);
			else
				$text = str_replace($closeTags[$index],'',$text);
				
		} else {
			
			$text = str_replace($tag,' ',$text);
			
		}
	}

	// removing all the html tags that not transfer to math tags
	$text = strip_tags($text);

	return $text;
}
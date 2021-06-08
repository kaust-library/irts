<?php

/*

**** This function is responsible of calculating the median for an array.

** Parameters :
	$a: array of int or float. 

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 20 January 2020 - 3:52 PM

*/

//--------------------------------------------------------------------------------------------



function findMedian($a) 
{ 
    // First we sort the array 
    sort($a); 

    $n = sizeof($a);
  
    // check for even case 
    if ($n % 2 != 0) 
    return (double)$a[$n / 2]; 
      
    return (double)($a[($n - 1) / 2] + 
                    $a[$n / 2]) / 2.0; 
} 
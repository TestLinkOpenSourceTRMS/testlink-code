<?

////////////////////////////////////////////////////////////////////////////////
//File:     orderArray.php
//Author:   Chad Rosen
//Purpose:  PHP maps arrays as name/value pairs instead of numerically. This
//          function takes those name value pairs and converts them to
//          number/value.
////////////////////////////////////////////////////////////////////////////////

function orderArray($array)
{

	foreach ($array as $key)
    {
	
		$newArray[$i] = $key;
		$i++;

	}

	return $newArray;

}

?>

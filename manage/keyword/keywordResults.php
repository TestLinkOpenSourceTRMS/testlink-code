<?php

////////////////////////////////////////////////////////////////////////////////
//File:     keywordResults.php
//Author:   Chad Rosen
//Purpose:  This page manages the keyword results.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();

$keywordArray = $_POST['keywords'];
$overwrite = $_POST['overwrite'];

echo $overwrite;

if($_GET['type'] == 'COM') //editing a component
{
	//print_r($keywordArray);
	
	iterateOverCat($_GET['ID']);

}else if($_GET['type'] == 'CAT')
{
	iterateOverTC($_GET['ID']);

}else if($_GET['type'] == 'TC')
{
	//echo "tc";
	updateTC($_GET['ID']);
}

function returnKeywordCSV($newKeywordArray, $existingKeywords)
{
	/*
	
		This method is used only if the user is adding keywords and not overwriting
	
		The method looks at the two arrays (existing and new), finds the intersection, throws away the dups in the new array, and adds the new keywords (if any) to the existing array
	*/

	//if the new array is empty then do nothing
	if(count($newKeywordArray) > 0)
	{


		echo "new: ";
		print_r($newKeywordArray);

		echo "<br><br>existing: ";
		print_r($existingKeywords);
		
		if($existingKeywords != null)
		{
			$comma_separated = explode(",", $existingKeywords);
		}
		
		var_dump(array_intersect($newKeywordArray, $comma_separated));
		
		echo "<br><br>";

		var_dump(array_diff($newKeywordArray, $comma_separated));

		$mergedArray = array_merge($newKeywordArray, $comma_separated);

		echo "<br><br>";

		print_r($mergedArray);

		echo "<br><br>unique";
		$uniqueArray = (array_unique($mergedArray));

		echo "<br><br>implode";
		$imploded = implode(",", $uniqueArray);

		echo "<Br><Br>" . $imploded;
	}else
	{
		$imploded = $existingKeywords;
	}


	return $imploded;
}

function iterateOverCat($id)
{
	$sqlCAT = "select id from mgtcategory where compid='" . $id . "'";

	$resultCAT = mysql_query($sqlCAT);

	while($rowCAT = mysql_fetch_array($resultCAT)) //Display all Categories
	{
		iterateOverTC($rowCAT[0]);
	}

}

function iterateOverTC($id)
{
	$sqlTC = "select id from mgttestcase where catid='" . $id . "'";

	$resultTC = mysql_query($sqlTC);

	while($rowTC = mysql_fetch_array($resultTC)) //Display all Categories
	{
		updateTC($rowTC[0]);
	}


}

function updateTC($id)
{
	//first get the test case's keyword CSV

	global $keywordArray;
	global $overwrite;

		//did the user choose to overwrite the keys?
		if($overwrite == "true")
		{
			echo "ow <br>";
			//did the user pass in key words?
			if(count($keywordArray) > 0)
			{
				//if yes then put them in csv format
				$newKeywordCSV = implode(",", $keywordArray);
			}else
			{
				//otherwise set the values to null
				$newKeywordCSV = null;
			}

		}else
		{
			//the user chose to add keys instead of overwrite
			echo "no ow<br><br>";

			//get the keywords from the db
			$sqlGetKeywordCSV = "select keywords from mgttestcase where id=" . $id;
			$getKeywordCSVResult = mysql_query($sqlGetKeywordCSV);
	
			$rowKeyword = mysql_fetch_array($getKeywordCSVResult);

			echo "kwRow: " + $rowKeyword[0] . "::<br><br>";

			//if the user actually passed in values for the keyword array
			if(count($keywordArray) > 0)
			{
				//call the return kw CSV function
				$newKeywordCSV = returnKeywordCSV($keywordArray, $rowKeyword[0]);
			}else
			{	
				//otherwise set the new keywords to the values in the db
				$newKeywordCSV = $rowKeyword[0];
			}
			
		}

		

		echo "<br><br>" . $newKeywordCSV . "<br><br>";

		//update the db
		$sqlUpdate = "update mgttestcase set keywords='" . $newKeywordCSV . "' where id='" . $id . "'";
		
		echo "sql: " . $sqlUpdate;
		$resultUpdate = mysql_query($sqlUpdate);

}
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

$comma_separated = implode(",", $keywordArray);
echo "cs:" . $comma_seperated . "::";


//echo "foo" . $keywordArray . "<br>";
//echo "foo2" . $overwrite;

if($_GET['type'] == 'COM') //editing a component
{
	print_r($keywordArray);
	
	//iterateOverCat($_GET['ID']);

}else if($_GET['type'] == 'CAT')
{
	//iterateOverTC($_GET['ID']);

}else if($_GET['type'] == 'TC')
{
	//echo "tc";
	updateTC($_GET['ID']);
}

function returnKeywordCSV($newKeywordArray, $existingKeywords)
{

	//first check if the existing keywords are empty

	if($existingKeywords == "")
	{
		$i=0; //counter

		foreach($newKeywordArray as $bob) //for each of the array values
		{
			$keywords .= $newKeywordArray[$i] . ","; //Build this string	
			$i++; //increment
		}

		return $keywords;

	}
	
	echo "existingKeywords: " . $existingKeywords . "<br>";


	$existingKeywordArray = explode(",", $existingKeywords);

	$newKeywordCSV = array_diff($existingKeywordArray, $newKeywordArray);


	print_r($newKeywordCSV);

	foreach($newKeywordCSV as $bob) //for each of the array values
	{
		$keywords .= $bob . ","; //Build this string	
		echo "next word: " . $bob . "<br>";
		
		//$i++; //increment
	}

	echo "<br><br>new array: " . $keywords . "<br><br>";

	return $keywords;
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

	//echo "<br><br>" . count($keywordArray) . "<br><br>";

	//print_r($keywordArray) . "<br><br>";
	//echo $overwrite . "<br><br>";

	if(count($keywordArray) > 0) //if there actually are values passed in
	{
	//	echo "true";

		if($overwrite != "overwrite")
		{
	//		echo "foooooo";

			$sqlGetKeywordCSV = "select keywords from mgttestcase where id=" . $id;
			$getKeywordCSVResult = mysql_query($sqlGetKeywordCSV);
	
			$rowKeyword = mysql_fetch_array($getKeywordCSVResult);

			echo "kwRow: " + $rowKeyword[0] . "::<br><br>";

			$newKeywordCSV = returnKeywordCSV($keywordArray, $rowKeyword[0]);

			//echo $newKeywordCSV;
		}else
		{
		//	echo "no ow<br>";
			$i=0; //counter

			foreach($keywordArray as $bob) //for each of the array values
			{
				$newKeywordCSV .= $keywordArray[$i] . ","; //Build this string	
				$i++; //increment
			}
			
		}

		echo "<br><br>" . $newKeywordCSV . "<br><br>";

		$sqlUpdate = "update mgttestcase set keywords='" . $newKeywordCSV . "' where id='" . $id . "'";
		
		echo "sql: " . $sqlUpdate;
		$resultUpdate = mysql_query($sqlUpdate);

	}

	
}

//This page displays the result of the user picking to edit something
/*
if(count($_POST['keywords']) > 0) //if there actually are values passed in
{

	$i=0; //counter

	foreach($_POST['keywords'] as $bob) //for each of the array values
	{
		$keywords .= $_POST['keywords'][$i] . ","; //Build this string	
			$i++; //increment
	}

}

/*
if($_GET['type'] == 'COM') //editing a component
{

	$sqlCAT = "select id from mgtcategory where compid='" . $_GET['ID'] . "'";

	$resultCAT = mysql_query($sqlCAT);

	while($rowCAT = mysql_fetch_array($resultCAT)) //Display all Categories
		{	
			$sqlTC = "select id from mgttestcase where catid='" . $rowCAT[0] . "'";

			$resultTC = mysql_query($sqlTC);

			while($rowTC = mysql_fetch_array($resultTC)) //Display all Categories
			{

				//echo $rowTC[0] . " " . $rowTC[1] . "<br>";

				$sqlUpdate = "update mgttestcase set keywords='" . $keywords . "' where id='" . $rowTC[0] . "'";

				$resultUpdate = mysql_query($sqlUpdate);

				//echo $sqlUpdate . "<br>";

			}
	

		}
	

	//echo $keywords . "<br><br>";

	echo "All Test Cases In This Component Have Been Edited";

		

}elseif($_GET['type'] =='CAT') //Editing a category
{

//print_r($_POST);
	
	if(count($_POST['keywords']) > 0) //if there actually are values passed in
	{

		$i=0; //counter

		foreach($_POST['keywords'] as $bob) //for each of the array values
		{
			$keywords .= $_POST['keywords'][$i] . ","; //Build this string
				
				$i++; //increment
		}

	}

			
			$sqlTC = "select id from mgttestcase where catid='" . $_GET['ID'] . "'";

			$resultTC = mysql_query($sqlTC);

			while($rowTC = mysql_fetch_array($resultTC)) //Display all Categories
			{

				//echo $rowTC[0] . " " . $rowTC[1] . "<br>";

				$sqlUpdate = "update mgttestcase set keywords='" . $keywords . "' where id='" . $rowTC[0] . "'";
				
				$resultUpdate = mysql_query($sqlUpdate);

				
				//echo $sqlUpdate . "<br>";

			}
	
	

	//echo $keywords . "<br><br>";

	echo "All Test Cases In This Category Have Edited";


}elseif($_GET['type'] == 'TC') //saving a test case but not archiving it
{

	//print_r($_POST);
	
	if(count($_POST['keywords']) > 0) //if there actually are values passed in
	{

		$i=0; //counter

		foreach($_POST['keywords'] as $bob) //for each of the array values
		{
			$keywords .= $_POST['keywords'][$i] . ","; //Build this string
				
				$i++; //increment
		}

	}

	$sqlUpdate = "update mgttestcase set keywords='" . $keywords . "' where id='" . $_GET['ID'] . "'";

	$resultUpdate = mysql_query($sqlUpdate);


	//echo $sqlUpdate . "<br>";

	echo "Test Case's Keyword Has Been Edited";
	
}*/
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

//This page displays the result of the user picking to edit something

if($_GET['type'] == 'COM') //editing a component
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
	





}




<?php

////////////////////////////////////////////////////////////////////////////////
//File:     searchDataResults.php
//Author:   Chad Rosen
//Purpose:  This page has something to do with the results of the search.has something to do with the results of the search.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();

if($_POST['submit'])
{

	//print_r($_POST);

	//Loop to put the $_POST variable into order

	foreach ($_POST as $key)
    {
	
		$newArray[$i] = $key;
		$i++;

	}

	//Ignore the 1st value because it's the submit button

	$i = 1;

	while ($i < count($newArray)) //Loop for the entire size of the array
	{

		//I had to do some tricky stuff to get the keywords working in the search pages
		//The problem was how to check if there was a keywords array being passed in
		//If no keywords were selected there is no array passed in.
		//The way I got around the problem was to insert a hidden field named with the value of break
		//If the hidden field is the 6th value then I write null in the keywords
		//If the hidden field is the 7th value then I know that an array was passed in
		
		if($newArray[$i + 6] == 'break') //If there was no keyword array passed in
		{
			
			$id = $newArray[$i];
			$version = $newArray[$i + 1];
			$title = $newArray[$i + 2];
			$summary = $newArray[$i + 3];
			$steps = $newArray[$i + 4];
			$exResult = $newArray[$i + 5];

			$version = 1 + $version; //everytime a test case is saved I update its version

			//SQL Code to update the testcase with its new values

			$sql = "UPDATE mgttestcase set keywords ='', version='" . $version . "', title='" . $title . "', author ='" . $_SESSION['user'] . "', summary='" . $summary . "', steps='" . $steps . "', exresult='" . $exResult . "' where id='" . $id . "'";		

			$result = mysql_query($sql); //Execute query

			$i = $i + 7;

		}else //If there was a keyword array passed in
		{

			$id = $newArray[$i];
			$version = $newArray[$i + 1];
			$title = $newArray[$i + 2];
			$summary = $newArray[$i + 3];
			$steps = $newArray[$i + 4];
			$exResult = $newArray[$i + 5];
			$keywordsArray = $newArray[$i + 6];

			$version = 1 + $version; //everytime a test case is saved I update its version

			//Loop through the keyword array and build the string

			$j=0; //counter

			foreach($keywordsArray as $bob) //for each of the array values
			{
				$keywords .= $keywordsArray[$j] . ","; //Build this string
				$j++; //increment
			}

			//SQL code to update the test case table

			$sql = "UPDATE mgttestcase set keywords='" . $keywords . "', version='" . $version . "', title='" . $title . "', author ='" . $_SESSION['user'] . "', summary='" . $summary . "', steps='" . $steps . "', exresult='" . $exResult . "' where id='" . $id . "'";

			$result = mysql_query($sql); //Execute query

			unset($keywords); //destroy keywords variable

			$i = $i + 8; //increment i value by 8 to the next set of data

		}

	}

	echo "Test Case(s) Changes Have Been Saved";


}elseif($_POST['archive'])
{

	//Loop to put the $_POST variable into order

	foreach ($_POST as $key)
    {
	
		$newArray[$i] = $key;
		$i++;

	}

	//Ignore the 1st value because it's the submit button

	$i = 1;

	while ($i < count($newArray)) //Loop for the entire size of the array
	{
		
		if($newArray[$i + 6] == 'break') //If there was no keyword array passed in
		{

			$id = $newArray[$i];
			$version = $newArray[$i + 1];
			$title = $newArray[$i + 2];
			$summary = $newArray[$i + 3];
			$steps = $newArray[$i + 4];
			$exResult = $newArray[$i + 5];

			$version = 1 + $version; //everytime a test case is saved I update its version

			//SQL Code to update the testcase with its new values

			$sql = "UPDATE mgttestcase set keywords='', version='" . $version . "', title='" . $title . "', author ='" . $_SESSION['user'] . "', summary='" . $summary . "', steps='" . $steps . "', exresult='" . $exResult . "' where id='" . $id . "'";

			//echo $sql . "<br><br>";


			$result = mysql_query($sql); //Execute query

			$sqlArchive = "insert into mgttcarchive (title,author,summary,steps,exresult,version,id,keywords) values ('" . $title . "','" . $_SESSION['user'] . "','" . $summary . "','" . $steps . "','" . $exResult . "','" . $version . "','" . $id . "','')";

			//echo $sqlArchive;

			$result = mysql_query($sqlArchive); //Execute query

			$i = $i + 7;

		}else

		{

			$id = $newArray[$i];
			$version = $newArray[$i + 1];
			$title = $newArray[$i + 2];
			$summary = $newArray[$i + 3];
			$steps = $newArray[$i + 4];
			$exResult = $newArray[$i + 5];
			$keywordsArray = $newArray[$i + 6];

			$version = 1 + $version; //everytime a test case is saved I update its version

			//Loop through the keyword array and build the string

			$j=0; //counter

			foreach($keywordsArray as $bob) //for each of the array values
			{
				$keywords .= $keywordsArray[$j] . ","; //Build this string
				$j++; //increment
			}

			//SQL code to update the test case table

			$sql = "UPDATE mgttestcase set keywords='" . $keywords . "', version='" . $version . "', title='" . $title . "', author ='" . $_SESSION['user'] . "', summary='" . $summary . "', steps='" . $steps . "', exresult='" . $exResult . "' where id='" . $id . "'";

			$result = mysql_query($sql); //Execute query

			$sqlArchive = "insert into mgttcarchive (title,author,summary,steps,exresult,version,id,keywords) values ('" . $title . "','" . $_SESSION['user'] . "','" . $summary . "','" . $steps . "','" . $exResult . "','" . $version . "','" . $id . "','" . $keywords . "')";

			//echo $sqlArchive;

			$result = mysql_query($sqlArchive); //Execute query

			unset($keywords); //destroy keywords variable


			$i = $i + 8;

		}




	}

	echo "Test Case(s) Changes Have Been Saved and Archived";


}

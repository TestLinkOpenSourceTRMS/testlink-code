<?

////////////////////////////////////////////////////////////////////////////////
//File:     updateModified.php
//Author:   Ken Mamitsuka
//Purpose:  This file manages the editing of components, categories, and test
//          cases within a project.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();
  doNavBar();

echo "<br>";

$project = $_GET['project']; //store the project number in a variable so that i can fresh the left frame later

if($_POST['updateSelected'])

{
	$i = 0; //start a counter

	$oneEdited = 0; // used for a message if no testcases are edited
	
	foreach ($_POST as $key)
		{
		
		$newArray[$i] = $key;
		$i++;

		}

	$i = 1; //Start the counter at 3 because the first three variable is a submit box

	while ($i < count($newArray)) //Loop for the entire size of the array
	{
		
		$tcID = $newArray[$i]; //Then the first value is the ID
		$tcUpdate = $newArray[$i + 1];

		if($tcUpdate != 'break') {
			//Grab the mgtTCID from the current test case
			
			$idSQL = "select mgttcid from testcase where id=" . $tcID;
			$idResult = mysql_query($idSQL); //Run the query
			$idRow= mysql_fetch_row($idResult);
			
			//Grab the relevent data from the mgtTestCase table

			$mgtSQL = "select title, steps, exresult, keywords, catid, version, summary,TCorder from mgttestcase where id=" . $idRow[0];
			$mgtResult = mysql_query($mgtSQL); //Run the query
			$mgtRow= mysql_fetch_row($mgtResult);

			$mgtTitle=mysql_escape_string($mgtRow[0]);
			$mgtSteps=mysql_escape_string($mgtRow[1]);
			$mgtExresult=mysql_escape_string($mgtRow[2]);
			$mgtKeywords=$mgtRow[3];
			$mgtCatid=$mgtRow[4];
			$mgtVersion=$mgtRow[5];
			$mgtSummary=mysql_escape_string($mgtRow[6]);
			$mgtTCorder = $mgtRow[7];
			
			if($mgtVersion == "")
			{
				$SQLDeleteTC = "delete from testcase where id=" . $tcID;

				$updateResult = mysql_query($SQLDeleteTC); //Run the query

				$SQLdeleteResults = "delete from results where tcid=" . $tcID;
				
				$updateResult2 = mysql_query($SQLdeleteResults); //Run the query

				$SQLdeleteBugs = "delete from bugs where tcid=" . $tcID;

				$updateResult3 = mysql_query($SQLdeleteBugs); //Run the query	

				echo "Test Case <b>" . $tcID . "</b>:" . $mgtTitle . " has been deleted<br>";


			}else
			{
			
			//Update the testcase with the new data

			$updateSQL = 'update testcase set TCorder=' . $mgtTCorder . ',title="' . $mgtTitle . '", steps="' . $mgtSteps . '", exresult="' . $mgtExresult . '", keywords="' . $mgtKeywords . '", version="' . $mgtVersion . '", summary="' . $mgtSummary . '" where id=' . $tcID;
			
			
			$updateResult = mysql_query($updateSQL); //Run the query
			
			
			echo "Test Case <b>" . $tcID . "</b>:" . $mgtTitle . " has been updated<br>";
			}

			
			$i = $i + 3; // go to the next result - skip the break

			$oneEdited = 1;
		} else {
			$i = $i + 2; //go to the next set of results
		}			
			
	}//end while

}

if (!$oneEdited) {
	echo "<b>No test cases were updated.</b>\n";
}


?>

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

			$mgtSQL = "select title, steps, exresult, keywords, catid, version, summary,TCorder from mgtTestCase where id=" . $idRow[0];
			$mgtResult = mysql_query($mgtSQL); //Run the query
			$mgtRow= mysql_fetch_row($mgtResult);

			$mgtTitle=stripTree($mgtRow[0]);
			$mgtSteps=stripTree($mgtRow[1]);
			$mgtExresult=stripTree($mgtRow[2]);
			$mgtKeywords=$mgtRow[3];
			$mgtCatid=$mgtRow[4];
			$mgtVersion=$mgtRow[5];
			$mgtSummary=stripTree($mgtRow[6]);
			$mgtTCorder = $mgtRow[7];
			
			//Update the testcase with the new data

			$updateSQL = 'update testcase set TCorder=' . $mgtTCorder . ',title="' . $mgtTitle . '", steps="' . $mgtSteps . '", exresult="' . $mgtExresult . '", keywords="' . $mgtKeywords . '", version="' . $mgtVersion . '", summary="' . $mgtSummary . '" where id=' . $tcID;	
			
			$updateResult = mysql_query($updateSQL); //Run the query
			
			echo "Test Case <b>" . $tcID . "</b>:" . $mgtTitle . " has been updated<br>";

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

function stripTree($name)
{

$name = str_replace ( "'", "", $name); //remove apostraphy
$name = str_replace ( '"', '', $name); //remove apostraphy

return $name;

}

?>

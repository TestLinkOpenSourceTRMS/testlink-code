<?php

////////////////////////////////////////////////////////////////////////////////
//File:     ExecutionResults.php
//Author:   Chad Rosen
//Purpose:  This incredibly importatnt page takes the data submitted from 
//          the user from the execution page and adds it to the database.
////////////////////////////////////////////////////////////////////////////////


require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

require_once("../functions/csvSplit.php");
require_once("../functions/refreshLeft.php"); //This adds the function that refreshes the left hand frame

?>

Your results have been submitted

<hr/>

<table width="100%" border="1">
<tr>
	<th width="5%">
		Test Case ID
	</th>
	<th width="20%">
		Test Case
	</th>
	<th width="5%">
		Build
	</th>
	<th width="5%">
		Result
	</th>
	<th width="50%">
		Notes
	</th>
	<th width="25%">
		Bugs
	</th>
</tr>

<?
	
$i = 0; //start a counter

//It is necessary to turn the $_POST map into a number valued array

foreach ($_POST as $key)
{	
	$newArray[$i] = $key;
	$i++;
}


//Grab the build and date values from the last form

$date = $newArray[0];
$build = $newArray[1];

$i = 3; //Start the counter at 3 because the first three variables are build,date, and submit

// Get all of the platform info

while ($i < count($newArray)) //Loop for the entire size of the array
{

	$tcId = $newArray[$i]; //Then the first value is the ID
	$tcNotes = $newArray[$i + 1]; //The second value is the notes
	$tcStatus = $newArray[$i + 2]; //The 3rd value is the status
	
	if($bugzillaOn == true)
	{
		$tcBugs = $newArray[$i + 3]; //The 4th value is the CSV of bugs
		$i = $i + 4; //Increment 3 values to the next tcID
	}else
	{
		$i = $i + 3;
	}
		
	//SQL statement to look for the same record (tcid, build = tcid, build)

	$sql = "select tcid, build, notes, status, title, mgttcid from results,testcase where tcid='" . $tcId . "' and build='" . $build . "' and results.tcid=testcase.id";
	
	$result = mysql_query($sql); //Run the query
	$num = mysql_num_rows($result); //How many results

	$myrow = mysql_fetch_row($result);
		

	if($num == 1) //If we find a matching record
	{					
		//Grabbing the values from the query above
		$queryNotes		= $myrow[2];
		$queryStatus	= $myrow[3];
		$tcTitle		= $myrow[4];
		$mgttcid		= $myrow[5];
				
		//If the (notes, status) information is the same.. Do nothing
			
		if($queryNotes == $tcNotes && $queryStatus == $tcStatus)
		{
			updateBugs($tcId, $build, $tcBugs);

			//Don't display anything if there are no changes			
		}
		else if($tcStatus == 'n')
		{
			//I think that from now on it may just be easier to delete the result row in the db if the status is
			//not run

			$sql = "delete from results where tcid=" . $tcId . " and build=" . $build;
			$result = mysql_query($sql);
		}
		else
		{
			//update the old result
	
			$sql = "UPDATE results set runby ='" . $_SESSION['user'] . "', status ='" .  $tcStatus . "', notes='" . $tcNotes . "' where tcid='" . $tcId . "' and build='" . $build . "'";
			
			$result = mysql_query($sql); //Execute query

			updateBugs($tcId, $build, $tcBugs);

		}
		
	//If the (notes, status) information is different.. then update the record

	}
	else //If there is no entry for the build or the build is different
	{

		$sqlNoResult = "select title, mgttcid from testcase where id=" . $tcId;

		$resultNoResult = mysql_query($sqlNoResult); //Run the query
		$myrowNoResult = mysql_fetch_row($resultNoResult);
		
		//Grabbing the values from the query above
		$tcTitle		= $myrowNoResult[0];
		$mgttcid		= $myrowNoResult[1];
		
		if($tcNotes == "" && $tcStatus == "n") //If the notes are blank and the status is n then do nothing
		{
			updateBugs($tcId, $build, $tcBugs);
			
			//I dont want to display anything if no data was submitted
			
		}
		else //Else enter a new row
		{
			
			$sql = "insert into results (build,daterun,status,tcid,notes,runby) values ('" . $build . "','" . $date . "','" . $tcStatus . "','" . $tcId . "','" . $tcNotes . "','" . $_SESSION['user'] . "')";

			$result = mysql_query($sql);

			updateBugs($tcId, $build, $tcBugs);

		}
	}

	displayResult($tcId, $build, $tcStatus, $tcNotes, $tcBugs,$tcTitle,$mgttcid);
		

}//end while

echo "</table>";
//refresh the page
$page =  _BASE_HREF . "execution/executionFrameLeft.php";

refreshFrame($page); //call the function below to refresh the left frame


function updateBugs($tcId, $build, $bugs)
{
	$sqlDelete = "DELETE from bugs where tcid=" . $tcId . " and build=" . $build;

	$result = mysql_query($sqlDelete); //Execute query

	//Grabbing the bug info from the results table

	$bugArray = csv_split($bugs);

	$counter = 0;

	while($counter < count($bugArray))
	{

		$sqlBugs = "insert into bugs (tcid,build,bug) values ('" . $tcId . "','" . $build . "','" . $bugArray[$counter] . "')";
	
		$result = mysql_query($sqlBugs); //Execute query

		$counter++;
	}

}

function displayResult($tcId, $build, $tcStatus, $tcNotes, $tcBugs, $tcTitle, $mgttcid)
{
	//get the test case's name

	if($result == "n")
	{
	?>
		<tr>
			<td align="center"><? echo $mgttcid ?></td>
			<td>Test Case Name</td>
			<td align="center"><? echo $build ?></td>
			<td align="center">Not Run</td>
			<td>None</td>
			<td align="center">None</td>
		</tr>

	<?

	}
	else
	{

	?>
		<tr>
			<td align="center"><? echo $mgttcid ?></td>
			<td><? echo $tcTitle ?></td>
			<td align="center">
				<a href='execution/execution.php?edit=testcase&data=<? echo $tcId ?>&build=<? echo $build[0] ?>'>
					<? echo $build ?>
				</a>
			</td>
			<td align="center"><? echo $tcStatus ?></td>
			<td ><? echo $tcNotes ?>&nbsp</td>
			<td ><? echo $tcBugs ?>&nbsp</td>
		</tr>
	<?
	}
}

?>

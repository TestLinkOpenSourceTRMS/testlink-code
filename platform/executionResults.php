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
	<th>
		Platforms
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

//set $i the array index counter to 3 which is after the build,submit button,date,etc
$i = 3;
//declare platformArray
$platformArray;

//loop through the post values until break.. these are the platforms
while ($newArray[$i] != "break")
{
	$platformArray[] = $newArray[$i];
	$i++;
}

//sort platforms
sort($platformArray);
reset($platformArray);

//Turn the platform array into a CSV. No need if it's one or less values
if(count($platformArray > 1))
{
	$platformCSV = implode(",", $platformArray);
}

//increment over the break
$i++;

//Grab the build and date values from the last form

$date = $newArray[0];
$build = $newArray[1];

while ($i < count($newArray)) //Loop for the entire size of the array
{
	$tcId = $newArray[$i]; //Then the first value is the ID
	$tcNotes = "foo";//$newArray[$i + 1]; //The second value is the notes
	$tcStatus = $newArray[$i + 1]; //The 3rd value is the status

	$i = $i + 2;
			
	//SQL statement to look for the same record (tcid, build = tcid, build)

//	$sql = "select tcid, buildId, notes, result, title, mgttcid, platformList from platformResults,testcase where tcid='" . $tcId . "' and buildId='" . $build . "' and platformResults.tcid=testcase.id and platformList='" . $platformCSV . "'";

	$sql = "select tcid, buildId, notes, result, platformList from platformResults where tcid='" . $tcId . "' and buildId='" . $build . "' and platformList='" . $platformCSV . "'";
	
	$result			= mysql_query($sql); //Run the query
	$num			= mysql_num_rows($result); //How many results

	$myrow			= mysql_fetch_row($result);

	$sqlTestCase	= "select title,mgttcid from testcase where id=" . $tcId;

	$myrowTestCase = mysql_fetch_row(mysql_query($sqlTestCase)); //Run the query
		
	$tcTitle		= $myrowTestCase[0];
	$mgttcid		= $myrowTestCase[1];

	//Grabbing the values from the query above
			
	if($num == 1) //If we find a matching record
	{		
		$queryNotes		= $myrow[2];
		$queryStatus	= $myrow[3];
	
		//If the (notes, status) information is the same.. Do nothing
			
		if($queryNotes == $tcNotes && $queryStatus == $tcStatus)
		{
			//updateBugs($tcId, $build, $tcBugs);

			//Don't display anything if there are no changes
	
		}
		else if($tcStatus == 'n')
		{
			//I think that from now on it may just be easier to delete the result row in the db if the status is
			//not run

			$sql = "delete from platformResults where tcid=" . $tcId . " and buildId=" . $build . " and platformList='" . $platformCSV . "'";
			
			$result = mysql_query($sql);
		}
		else
		{
			//update the old result
	
			$sql = "UPDATE platformResults set runBy ='" . $_SESSION['user'] . "', result ='" .  $tcStatus . "', notes='" . $tcNotes . "' where tcid='" . $tcId . "' and buildId='" . $build . "' and platformList='" . $platformCSV . "'";
			
			$result = mysql_query($sql); //Execute query
		}
		
	//If the (notes, status) information is different.. then update the record

	}
	else //If there is no entry for the build or the build is different
	{
		
		if($tcStatus != "n") //Else enter a new row
		{
			$sqlInsert = "insert into platformResults (buildId,dateRun,result,tcId,notes,runBy,platformList) values (" . $build . ",'" . $date . "','" . $tcStatus . "'," . $tcId . ",'" . $tcNotes . "','" . $_SESSION['user'] . "','" . $platformCSV  . "')";

			$result = mysql_query($sqlInsert);

		}
	}

	displayResult($tcId, $build, $tcStatus, $tcNotes, $tcTitle,$mgttcid,$platformCSV);
		
}//end while

echo "</table>";
//refresh the page
$page =  _BASE_HREF . "platform/executionLeft.php";

refreshFrame($page); //call the function below to refresh the left frame

function displayResult($tcId, $build, $tcStatus, $tcNotes, $tcTitle, $mgttcid,$platformCSV)
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
				<a href='platform/executionData.php?edit=testcase&data=<? echo $tcId ?>&build=<? echo $build[0] ?>'>
					<? echo $build ?>
				</a>
			</td>
			<td align="center"><? echo $tcStatus ?></td>
			<td><? echo $tcNotes ?>&nbsp</td>
			<td><? echo $platformCSV ?>&nbsp</td>
		</tr>
	<?
	}
}

?>

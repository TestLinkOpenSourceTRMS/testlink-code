<?

////////////////////////////////////////////////////////////////////////////////
//File:     editResults.php
//Author:   Chad Rosen
//Purpose:  This file manages the editing of components, categories, and test
//          cases within a project.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();


require_once("../../functions/refreshLeft.php"); //This adds the function that refreshes the left hand frame

$project = $_GET['project']; //store the project number in a variable so that i can fresh the left frame later

if($_POST['TCEditSubmit'])

{

	$i = 0; //start a counter
	
	foreach ($_POST as $key)
		{
		
		$newArray[$i] = $key;
		$i++;

		}

		//print_r($newArray);

	$i = 1; //Start the counter at 3 because the first three variable is a submit box

	while ($i < count($newArray)) //Loop for the entire size of the array
	{
		

		$tcID = $newArray[$i]; //Then the first value is the ID
		
		if($newArray[$i + 1] == 'break')
		{

			//do nothing

			$i = $i + 2;


		}else
		{

			$sqlMGT = "select mgttcid,title from testcase where id='" . $tcID . "'";
			$resultMGT = mysql_query($sqlMGT);
			$mgtID = mysql_fetch_row($resultMGT);


			//Delete the test case as well as its results and bugs
		
			$sqlTCDel = "delete from testcase where id='" . $tcID . "'";

			$sqlRESDel = "delete from results where tcid='" . $tcID . "'";

			$sqlBUGDel = "delete from bugs where tcid='" . $tcID . "'";

			$result = mysql_query($sqlTCDel); //Execute query
					
			$result = mysql_query($sqlRESDel); //Execute query

			$result = mysql_query($sqlBUGDel); //Execute query

			//delete all results

			echo "Test Case <b>" . $mgtID[0] . "</b>: " . $mgtID[1] . " has been deleted<br>";

			$i = $i + 3;
		
		}
		
		
				
			
	}//end while

	//Add in code that refreshes the left frame..


	$page = "editLeft.php?project=" . $project;

	refreshFrame($page); //call the function below to refresh the left frame

}


/*
if($_POST['TCEditSubmit'])

{

	$i = 0; //start a counter
	
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

		if($tcUpdate == 'none')
		{

		}elseif($tcUpdate == 'update')
		{
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
			
			echo "Test Case " . $tcID . " has been updated<br>";



		}elseif($tcUpdate == 'delete')
		{

			//Delete the test case as well as its results and bugs
		
			$sqlTCDel = "delete from testcase where id='" . $tcID . "'";

			$sqlRESDel = "delete from results where tcid='" . $tcID . "'";

			$sqlBUGDel = "delete from bugs where tcid='" . $tcID . "'";

			$result = mysql_query($sqlTCDel); //Execute query
					
			$result = mysql_query($sqlRESDel); //Execute query

			$result = mysql_query($sqlBUGDel); //Execute query

			//echo "delete";

			//delete all results

			echo "Test Case " . $tcID . " has been deleted<br>";

		}
		
		$i = $i + 2; //go to the next set of results
				
			
	}//end while

	//Add in code that refreshes the left frame..


	$page = "editLeft.php?project=" . $project;

	refreshFrame($page); //call the function below to refresh the left frame

}

*/


if($_POST['deleteCOM'])

{

	//Select all of the categories from the component

	//echo $_GET['data'] . "<br>";

	$sqlCAT = "select category.id from category where compid='" . $_GET['data'] . "'";
	$resultCAT = mysql_query($sqlCAT); //Execute query

	while($rowCAT = mysql_fetch_array($resultCAT))
	{

		//Select all of the test cases from the categories
		//run the query

		$sqlTC = "select id from testcase where catid='" . $rowCAT[0] . "'";
		$resultTC = mysql_query($sqlTC); //Execute query

		while($rowTC = mysql_fetch_array($resultTC))
		{
			
			//delete each of the results and bugs from the selected test case
			
			$sqlTCDel = "delete from testcase where id='" . $rowTC[0] . "'";
			
			$sqlRESDel = "delete from results where tcid='" . $rowTC[0] . "'";

			$sqlBUGDel = "delete from bugs where tcid='" . $rowTC[0] . "'";

			$result = mysql_query($sqlTCDel); //Execute query
					
			$result = mysql_query($sqlRESDel); //Execute query

			$result = mysql_query($sqlBUGDel); //Execute query



		}

		//delete each category when you're done

		$sqlCATDel = "delete from category where id='" . $rowCAT[0] . "'";
		$resultCATDel = mysql_query($sqlCATDel); //Execute query

	}

	
	//Grab the component name

	$sqlComName = "select name from component where id='" . $_GET['data'] . "'";
	$comResult = mysql_query($sqlComName);
	$comRow = mysql_fetch_row($comResult);
	
	//finally delete the component

	$sqlCOMDel = "delete from component where id='" . $_GET['data'] . "'";
	$resultCOMDel = mysql_query($sqlCOMDel); //Execute query

	echo "<b>" . "Component:</b> " . $comRow[0] . " has been deleted";
	
	//Add in code that refreshes the left frame..

	$page = "editLeft.php?project=" . $project;

	refreshFrame($page); //call the function below to refresh the left frame



	

}

if($_POST['deleteCAT'])
{
	
		//Select all of the test cases from the categories
		//run the query

		$sqlTC = "select testcase.id from testcase where catid='" . $_GET['data'] . "'";
		$resultTC = mysql_query($sqlTC); //Execute query

		//echo $sqlTC;

		while($rowTC = mysql_fetch_array($resultTC))
		{
			
			//delete each of the results and bugs from the selected test case
			
			$sqlTCDel = "delete from testcase where id='" . $rowTC[0] . "'";
			
			$sqlRESDel = "delete from results where tcid='" . $rowTC[0] . "'";

			$sqlBUGDel = "delete from bugs where tcid='" . $rowTC[0] . "'";

			$result = mysql_query($sqlTCDel); //Execute query
					
			$result = mysql_query($sqlRESDel); //Execute query

			$result = mysql_query($sqlBUGDel); //Execute query



		}

		//Grab the category name

		$sqlCatName = "select name from category where id='" . $_GET['data'] . "'";
		$catResult = mysql_query($sqlCatName);
		$catRow = mysql_fetch_row($catResult);

		//delete the category when you're done

		$sqlCATDel = "delete from category where id='" . $_GET['data'] . "'";
		$resultCATDel = mysql_query($sqlCATDel); //Execute query

		//Add in code that refreshes the left frame..

		echo "<b>Category:</b> " . $catRow[0] . " has been deleted";

		$page = "editLeft.php?project=" . $project;

		refreshFrame($page); //call the function below to refresh the left frame


}

function stripTree($name)
{

$name = str_replace ( "'", "", $name); //remove apostraphy
$name = str_replace ( '"', '', $name); //remove apostraphy

return $name;

}


?>

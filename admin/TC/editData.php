<?php

////////////////////////////////////////////////////////////////////////////////
//File:     editData.php
//Author:   Chad Rosen
//Purpose:  This file manages the editing of components, categories, and
//          test cases within a project.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

?>

<LINK REL='stylesheet' TYPE='text/css' HREF='kenny.css'>

<?

$project = $_GET['project']; //store the project number in a variable so that i can fresh the left frame later

if($_GET['edit'] == 'info')
{
	echo "<table class=helptable width=100%>";
	
	echo "<tr><td class=helptabletitle><h2>Active/Inactive Test Case</td></tr></table>";

	echo "<table class=helptable width=100%>";

	echo "<tr><td class=helptablehdr><b>Purpose:</td><td class=helptable>This Page allows the user (a lead) to set testcases as either inactive or inactive. An inactive testcase will not show up on the test case execution page. Note: Old test case results will still be present in metrics</td></tr>";
	
	echo "<tr><td class=helptablehdr><b>Getting Started:</td><td class=helptable>Click on a category to see all of its test cases. Set the test case to either active or inactive. Submit the page.</td></tr>";

	echo "</table>";

}


if($_GET['level'] == 'com')
	{

	//Start the form

	echo "<Form Method='POST' ACTION='admin/TC/editResults.php?data=" . $_GET['data'] . "'>";

	echo "<input type='submit' Name='deleteCOM' Value='Delete Entire Component From Test Plan'>";	

	echo "<input type='submit' Name='TCEditSubmit' Value='Delete Test Case(s)'><br><br>";

	echo "<table width='100%' class=navtable >";
		
	echo "<tr><td width='15%' class=navtable><b>Component</td><td width='15%' class=navtable><b>Category</td><td width='60%' class=navtable><b>Test Case</td><td width='10%' class=navtable><b>Delete?</td>";

	$sqlCat = "select id from category where compid =" . $_GET['data'];

	$result = @mysql_query($sqlCat);

	while($row = mysql_fetch_array($result)){

		$sqlTC = "select id from testcase where catid=" . $row[0];

		$resultTC = @mysql_query($sqlTC);

		while($rowTC = mysql_fetch_array($resultTC)){

			displayTC($rowTC[0]);


		}

		


	}


	//Here we are going to show the button that allows users to delete categories


	echo "</form>";	
	
	echo "</table>";


}

if($_GET['level'] == 'cat')
{
	
		//Start the form

		echo "<Form Method='POST' ACTION='admin/TC/editResults.php?data=" . $_GET['data'] . "'>";

		//Here we are going to show the button that allows users to delete categories

		echo "<input type='submit' Name='deleteCAT' Value='Delete Entire Category From Test Plan'>";
		echo "<input type='submit' Name='TCEditSubmit' Value='Delete Test Case(s)'><br><br>";
		
		echo "<table width='100%' class=navtable >";
		
		echo "<tr><td width='15%' class=navtable><b>Component</td><td width='15%' class=navtable><b>Category</td><td width='60%' class=navtable><b>Test Case</td><td width='10%' class=navtable><b>Delete?</td>";


		$sqlCat ="select id from testcase where catid =" . $_GET['data'];

		$result = @mysql_query($sqlCat);

		while($row = mysql_fetch_array($result)){

			displayTC($row[0]);
	
		}

		echo "</table>";

		echo "</form>";
		


}

if($_GET['level'] == 'tc')
{
	//Start the form

	echo "<Form Method='POST' ACTION='admin/TC/editResults.php?data=" . $_GET['data'] . "'>";

	echo "<input type='submit' Name='TCEditSubmit' Value='Delete Test Case(s)'><br><br>";

	echo "<table width='100%' class=navtable >";
		
	echo "<tr><td width='15%' class=navtable><b>Component</td><td width='15%' class=navtable><b>Category</td><td width='60%' class=navtable><b>Test Case</td><td width='10%' class=navtable><b>Delete?</td>";

	displayTC($_GET['data']);
		

	echo "</form>";	

	echo "</table>";


}

function TCHeader($mgtID, $compName, $catName, $mgtCompName, $mgtCatName,$title, $mgtTitle)
{

	echo "<table width='100%' class=navtable >";
		
	echo "<tr><td width='25%' class=navtable><b>Component</td><td width='25%' class=navtable><b>Category</td><td width='25%' class=navtable><b>Test Case</td><td width='25%' class=navtable><b>Delete?</td>";

	//echo "<td width='50%' class=navtable><b><a href='manage/editData.php?editTC=tc&data=" . $mgtID . "' target='_blank'>Current</a></td>";

	
			
	//Display the management test case title
					
//	echo "</td><td class=tctablehdr>";
			
//	echo "<b>Component: </b>" . $mgtCompName . "<br>";
//	echo "<b>Category: </b>" . $mgtCatName . "<br>";
			
//	echo "<b>Title:</b> " . $mgtID ." " . htmlspecialchars($mgtTitle) . "</td></tr>";

	echo "</table>";

}

function TCResult($id)
{

	//Show the table that holds the update and delete checkboxes	
					
	echo "<table width='100%' class=navtable><tr><td>";

	echo "<input type='radio' name='update" . $id . "' CHECKED value='none'>None<br>";						

	echo "<input type='radio' name='update" . $id . "' value='update'>Update To The Latest Version<br>";
		
	echo "<input type='radio' name='update" . $id . "' value='delete'>Delete Test Case From Test Plan";
	
	echo "</td></tr></table><br>";


}

function displayTC($id)
{


	
	$sql = "select category.name, component.name, testcase.id, title, summary,steps,exresult, active, version, mgttcid,TCorder from testcase,component,category where testcase.id='" . $id . "' and component.id=category.compid and category.id=testcase.catid order by TCorder";


	$result = @mysql_query($sql);

	while($row = mysql_fetch_array($result)){

	//Assign values from the test case query

		$id = $row['id'];
		$title = $row['title'];
//		$summary = $row['summary'];
//		$steps = $row['steps'];
//		$exresult = $row['exresult'];
//		$active = $row['active'];
//		$version = $row['version'];
		$mgtID = $row['mgttcid'];
		$TCorder = $row['TCorder'];
		$compName = $row[0];
		$catName = $row[1];
/*
		$sqlMgt = "select mgtcomponent.name, mgtcategory.name,title,summary,steps,exresult,version,TCorder from mgttestcase,mgtcomponent,mgtcategory where mgttestcase.id='" . $mgtID . "' and mgtcomponent.id=mgtcategory.compid and mgtcategory.id=mgttestcase.catid";

		$mgtResult = @mysql_query($sqlMgt);

		$mgtRow = mysql_fetch_array($mgtResult);

		$mgtTitle = $mgtRow['title'];
		$mgtSummary = $mgtRow['summary'];
		$mgtSteps = $mgtRow['steps'];
		$mgtExresult = $mgtRow['exresult'];
		$mgtVersion = $mgtRow['version'];
		$mgtTCorder = $mgtRow['TCorder'];
		$mgtCompName = $row[0];
		$mgtCatName = $row[1];
		
		//TCHeader($mgtID, $compName, $catName, $mgtCompName, $mgtCatName,$title, $mgtTitle);*/

		echo "<input type=hidden name=tcid" . $id . " value=" . $id . ">";

		echo "<tr><td class=tctablehdr align=center>";
				
		echo $compName;

		echo "<td class=tctablehdr align=center>";

		echo $catName;

		echo "<td class=tctablehdr align=center>";
		
		echo "<b>" . $mgtID . "</b>: " . htmlspecialchars($title);

		echo "<td class=tctablehdr align=center>";

		echo "<input type=checkbox name=delete" . $id . ">";

		echo "<input type=hidden name=break" . $id . " value=break>";

		echo "</td></tr>";

/*
		echo "<table width='100%' class=navtable >";

		echo "<tr><td class=tctable><b>Summary:</b><br>" . htmlspecialchars(nl2br($summary)) . "</td>";

		//Display the management test case summary

		echo "<td class=tctable><b>Summary:</b><br>" . htmlspecialchars(nl2br($mgtSummary)) . "</td></tr>";

		//Display the test plan test case steps

		echo "<tr><td class=tctable><b>Steps:</b><br>" . nl2br($steps) . "</td>";

		//Display the management test case steps

		echo "<td class=tctable><b>Steps:</b><br>" . nl2br($mgtSteps) . "</td></tr>";

		//Display the test plan test case expected results

		echo "<tr><td class=tctable><b>Expected Result:</b><br>" . nl2br($exresult);

		////Display the management test case expected results

		echo "<td class=tctable><b>Expected Result:</b><br>" . nl2br($mgtExresult);

			
		echo "<input type='hidden' name='id" . $id . "' value=" . $id . "></td></tr>";

		//Show the test case version

		echo "<tr><td class=tctable><b>Order: </b>" . $TCorder . "<br>";

		//SQL query that grabs the latest version of the currently viewed test case
					
		echo "<td class=tctable><b>Order: </b>" . $mgtTCorder . "</td></tr>";

		echo "<tr><td bgcolor='#EEEEEE'><b>Version: </b>" . $version . "<br>";

		//SQL query that grabs the latest version of the currently viewed test case
				
		echo "<td bgcolor='#EEEEEE'><b>Version: </b>" . $mgtVersion . "</td></tr>";

		//Shows the order*/
					


				
		//Show the table that holds the update and delete checkboxes	
						
	//	echo "</table>";

		//display the results field
		
		//TCResult($id);

	}//end while




}


?>

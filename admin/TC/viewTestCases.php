<?php

////////////////////////////////////////////////////////////////////////////////
//File:     viewTestCases.php
//Author:   Chad Rosen
//Purpose:  This file allows viewing of project and product test cases.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

?>

<LINK REL='stylesheet' TYPE='text/css' HREF='kenny.css'>

<?

$project = $_GET['project']; //store the project number in a variable so that i can fresh the left frame later

//Start the form

displayTC($_GET['id']);
		
function TCHeader($mgtID, $compName, $catName, $mgtCompName, $mgtCatName,$title, $mgtTitle)
{

	echo "<table width='100%' class=navtable >";
		
	echo "<tr><td width='50%' class=navtable><b>Test Plan</td>";

	echo "<td width='50%' class=navtable><b><a href='manage/editData.php?editTC=tc&data=" . $mgtID . "' target='_blank'>Current</a></td>";

	echo "<tr><td class=tctablehdr>";
			
	echo "<b>Component: </b>" . $compName . "<br>";
	echo "<b>Category: </b>" . $catName . "<br>";
	
	echo "<b>Title: </b>" . $mgtID . " " . htmlspecialchars($title);
			
	//Display the management test case title
					
	echo "</td><td class=tctablehdr>";
			
	echo "<b>Component: </b>" . $mgtCompName . "<br>";
	echo "<b>Category: </b>" . $mgtCatName . "<br>";
			
	echo "<b>Title:</b> " . $mgtID ." " . htmlspecialchars($mgtTitle) . "</td></tr>";

	echo "</table>";

}

function TCResult($id)
{

	//Show the table that holds the update and delete checkboxes	
					
	echo "<table width='100%' class=navtable><tr><td>";

	echo "<input type='radio' name='update" . $id . "' CHECKED value='none'>None<br>";						

	//echo "<input type='radio' name='update" . $id . "' value='update'>Update To The Latest Version<br>";
		
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
		$summary = $row['summary'];
		$steps = $row['steps'];
		$exresult = $row['exresult'];
		$active = $row['active'];
		$version = $row['version'];
		$mgtID = $row['mgttcid'];
		$TCorder = $row['TCorder'];
		$compName = $row[0];
		$catName = $row[1];

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
		
		TCHeader($mgtID, $compName, $catName, $mgtCompName, $mgtCatName,$title, $mgtTitle);


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
				
		//Show the table that holds the update and delete checkboxes	
						
		echo "</table>";

	}//end while




}


?>

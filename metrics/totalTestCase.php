<?php

////////////////////////////////////////////////////////////////////////////////
//File:     totalTestCase.php
//Author:   Chad Rosen
//Purpose:  This page that views metrics by individual test case.
////////////////////////////////////////////////////////////////////////////////


require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

?>

<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">

<?

echo "<table class=userinfotable><tr><td bgcolor='#CCCCCC' width='15%'>Component</td><td bgcolor='#CCCCCC' width='30%'>Category</td><td bgcolor='#CCCCCC' width='30%'>Test Case</td>";
//echo "session = " . $_SESSION['project']. "\n";
$sql = "select build from build,project where project.id = '" . $_SESSION['project'] . "' and project.id=build.projid";

//echo $sql;

//Begin code to display the component

$result = mysql_query($sql);

while ($myrow = mysql_fetch_row($result)) 
{

	echo "<td bgcolor='#99CCFF'>" . $myrow[0] . "</td>";

}

echo "</tr>";

$sql = "select component.name,category.name, testcase.title, testcase.id,mgttcid from project,component,category,testcase where project.id='" . $_SESSION['project'] . "' and component.projid=project.id and category.compid=component.id and testcase.catid=category.id";


$result = mysql_query($sql);

while ($myrow = mysql_fetch_row($result)) //Cycle through all of the test cases
{

	echo "<tr><td bgcolor='#EEEEEE'>" . $myrow[0] . "</td><td bgcolor='#EEEEEE'>" . $myrow[1] . "</td><td bgcolor='#EEEEEE'><b>" . $myrow[4] . "</b>:" . htmlspecialchars($myrow[2]) . "</td>";
	
	$sqlBuild = "select build from build,project where project.id = '" . $_SESSION['project'] . "' and project.id=build.projid";

	$resultBuild = mysql_query($sqlBuild);

	while ($myrowBuild = mysql_fetch_row($resultBuild)) //Cycle through all the builds
	{
		
		$sqlStatus = "select status from testcase,results where results.tcid='" . $myrow[3] . "' and results.build='" . $myrowBuild[0]  . "' and testcase.id=results.tcid";

		//echo $sqlStatus;

		$resultStatus = mysql_query($sqlStatus);

		$myrowStatus = mysql_fetch_row($resultStatus);

	

		if($myrowStatus[0] == "p" || $myrowStatus[0] == "f" || $myrowStatus[0] == "b")
		{
			
			//This displays the pass,failed or blocked test case result
			//The hyperlink will take the user to the test case result in the execution page

			echo "<td>";

			
			if(has_rights("tp_execute"))
			{

				echo "<a href='execution/execution.php?edit=testcase&data=" . $myrow[3] . "&build=" . $myrowBuild[0] . "' target='_blank'>";

			}

			echo $myrowStatus[0] . "</a></td>";


		}
		else
		{
			echo "<td>-</td>";
		}
		


	}

	echo "</tr>";

}

?>

<a href="metrics/generateForm.php"><b>Generate Report(.xls format)</b></a><br>

<?php

////////////////////////////////////////////////////////////////////////////////
//File:     execution.php
//Author:   Chad Rosen
//Purpose:  The page builds the detailed test case execution frame
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();
?>

<h2>Platform Metrics By Component</h2>

<?

$sqlProject = "select name from project where id=" . $_SESSION['project'];
$resultProject = mysql_query($sqlProject);
$myrowProj = mysql_fetch_row($resultProject);
		
$sql = "select component.id, component.name from component,project where project.id = " . $_SESSION['project'] . " and component.projid = project.id order by name";

$comResult = mysql_query($sql);

while ($myrowCOM = mysql_fetch_row($comResult)) 
{ 
	echo "<b>Component:</b> " . $myrowCOM[1] . "<br>";
	//display all the components until we run out
			
	//Here I create a query that will grab every category depending on the component the user picked

	$catResult = mysql_query("select category.id, category.name from component,category where component.id = " . $myrowCOM[0] . " and component.id = category.compid order by CATorder,category.id",$db);
			
	while ($myrowCAT = mysql_fetch_row($catResult)) 
	{  
		echo "<blockquote><b>Category:</b> " . $myrowCAT[1];

		$sqlTC = "select testcase.id, title, mgttcid from testcase where catid=" . $myrowCAT[0] . " order by mgttcid";

		$resultTC = mysql_query($sqlTC);

		while ($myrowTC = mysql_fetch_row($resultTC)) 
		{  
			echo "<blockquote><b>Test Case:</b> " . $myrowTC[2] . " " . $myrowTC[1] . "</blockquote>";

			$sqlResult = "select buildId, platformList, result, dateRun from platformresults where tcid=" . $myrowTC[0] . " order by buildId";

			$resultResult = mysql_query($sqlResult);

			echo "<blockquote>";
			echo "<table width='100%' class='mainTable'>";
			

			if(mysql_num_rows($resultResult) > 0)
			{	
				echo "<tr><td class='userinfotable'><b>Build</td>";
				echo "<td class='userinfotable'><b>Platforms</td>";
				echo "<td class='userinfotable'><b>Result</td>";
				echo "<td class='userinfotable'><b>Date Ran</td>";
				
				if($bugzillaOn == "true")
				{
					echo "<td class='userinfotable'><b>Bugs</td>";
				}

				echo "</tr>";
			}
			else
			{
				echo "<tr><td class='userinfotable'><b>No Results For This Test Case</td></tr>";
			}

			while ($myrowResult = mysql_fetch_row($resultResult)) 
			{
				echo "<tr>";
				echo "<td>" . $myrowResult[0] . "</td>";
				echo "<td>";

				$platformNameArray = explode(",",$myrowResult[1]);
		
				foreach ($platformNameArray as $platformId)
				{	
					$sqlPlatformName = "select name from platform where id=" . $platformId;
					$platformNameRow = mysql_fetch_row(mysql_query($sqlPlatformName)); //Run the query
					
					echo $platformNameRow[0] . " ";
				}
				
				echo "</td>";
				echo "<td>" . $myrowResult[2] . "</td>";
				echo "<td>" . $myrowResult[3] . "</td>";

				if($bugzillaOn == "true")
				{
					$platformBugsSql = "select buglist from platformbugs where tcid='" . $myrowTC[0] . "' and buildid='" . $myrowResult[0] . "' and platformlist='" . $myrowResult[1] . "'";

					$bugsResult = mysql_fetch_row(mysql_query($platformBugsSql));

					echo "<td>" . $bugsResult[0] . "</td>";

				}

				echo "</tr>";
			}

			echo "</table>";

			echo "</blockquote>";

		}

		echo "</blockquote>";
			
	}

}


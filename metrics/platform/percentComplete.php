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

<h2>Platform Metrics By Platform Container</h2>

<?

$sqlTotalTestCases = "select count(testcase.id) from project,component,category,testcase where project.id=" . $_SESSION['project'] . " and component.projid=project.id and category.compid=component.id and testcase.catid=category.id";

$sqlTotalTestCases = mysql_fetch_row(mysql_query($sqlTotalTestCases));

$sqlPlatCon = "select id,name from platformcontainer where projId=" . $_SESSION['project'];
$resultPlatCon = mysql_query($sqlPlatCon);

while ($myrowPlatCon = mysql_fetch_row($resultPlatCon)) 
{ 
	
	$sqlPlatform = "select id,name from platform where containerId=" . $myrowPlatCon[0];
	$resultPlatform = mysql_query($sqlPlatform);

	if(mysql_num_rows($resultPlatform) > 0)
	{
		echo "<b>Container: </b>" . $myrowPlatCon[1] . "<br>";

		while ($myrowPlatform = mysql_fetch_row($resultPlatform)) 
		{ 	
			$sqlResults = "select count(tcid) from platformresults,testcase where (platformList like '%," . $myrowPlatform[0] . ",%' or platformList like '" . $myrowPlatform[0] . ",%' or platformList like '%," . $myrowPlatform[0] . "') and tcid=id group by buildId order by buildId";

			//echo $sqlResults;
			$resultPlatformResults = mysql_query($sqlResults);

			echo "<blockquote><b>Platform: </b>" . $myrowPlatform[1] . "<br><br>";
			echo "<table width='100%' class='mainTable'>";
			
			echo "<tr>";
			echo "<td width='40%' class='userinfotable'><b>Total completed  by platform</td>";
			echo "<td width='40%' class='userinfotable'><b>Total tcs in project</td>";
			echo "<td width='20%' class='userinfotable'><b>% complete</td>";
			echo "</tr>";

			if(mysql_num_rows($resultPlatformResults) > 0)
			{
				$myrowResults = mysql_fetch_row($resultPlatformResults); 
				$totalCompleted = $myrowResults[0];
			
			}else
			{
				$totalCompleted = 0;
			}
			
			echo "<tr>";
			echo "<td>" . $totalCompleted . "</td>";
			echo "<td>" . $sqlTotalTestCases[0] . "</td>";
			echo "<td>";

			if($totalCompleted == 0)
			{
				echo "0";
			}
			else
			{
				$completed = round(($totalCompleted / $sqlTotalTestCases[0]),4);
				echo $completed * 100;
			}

			echo "</td></tr></table></blockquote>";
			
			}
		}
	}
//}
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
			$sqlResults = "select tcid,buildId,platformList,result,dateRun,runBy,title from platformresults,testcase where (platformList like '%," . $myrowPlatform[0] . ",%' or platformList like '" . $myrowPlatform[0] . ",%' or platformList like '%," . $myrowPlatform[0] . "') and tcid=id order by buildId";

			//echo $sqlResults;
			$resultPlatformResults = mysql_query($sqlResults);

			if(mysql_num_rows($resultPlatformResults) > 0)
			{
				echo "<blockquote><b>Platform: </b>" . $myrowPlatform[1] . "<br><br>";
				echo "<table width='100%' class='mainTable'>";
				echo "<tr><td class='userinfotable'><b>tcid</td>";
				echo "<td class='userinfotable'><b>Build</td>";
				echo "<td class='userinfotable'><b>Result</td>";
				echo "<td class='userinfotable'><b>Date</td>";
				echo "<td class='userinfotable'><b>Run By</td></tr>";
				
				while ($myrowResults = mysql_fetch_row($resultPlatformResults)) 
				{ 
					echo "<tr>";
					echo "<td>" . $myrowResults[6] . "</td>";
					echo "<td>" . $myrowResults[1] . "</td>";
					echo "<td>" . $myrowResults[3] . "</td>";
					echo "<td>" . $myrowResults[4] . "</td>";
					echo "<td>" . $myrowResults[5] . "</td>";
					echo "</tr>";				
				}
				
				echo "</table>";
			}

			echo "</blockquote>";
		}
	}
}
<?php

////////////////////////////////////////////////////////////////////////////////
//File:     metricsSelection.php
//Author:   Chad Rosen
//Purpose:  This page shows the data from a build
////////////////////////////////////////////////////////////////////////////////


require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

?>

<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">

<?

//Display the most recent build

$sql="select build from build,project where build.projid='" . $_SESSION['project'] . "' order by build desc limit 1";

$result = mysql_query($sql); //Run the query

$myrow = mysql_fetch_row($result); //fetch the data

echo "<font size='4'>Most Recent Results As Of Build " . $myrow[0] . "</font><br>";


//require_once section. These require_onces will show the entire project information on the screen

require_once("priority.php"); //require_onces the priority information

//Display the priority table

echo "<br><table class=userinfotable width=100%><tr><td bgcolor='#99CCFF' class='subTitle'>Status By Priority</td></tr>";

//Displays the most current Milestone and Date

echo "<tr><td><b>Current Milestone: </b>" . $Mname . "<br><b>End Date: </b>" . $Mdate . "</td></tr></table>";

//I want a new table for the name of the display data

echo "<table width='100%' class=userinfotable>";

//Setting up the header row

echo "<tr><td width='11%' class=tctablehdrCenter >Priority</td><td width='11%' class=tctablehdrCenter>Total</td><td width='11%' class=tctablehdrCenter>Status</td><td width='11%' class=tctablehdrCenter>Pass</td><td width='11%' class=tctablehdrCenter>Fail</td><td width='11%' class=tctablehdrCenter>Blocked</td><td width='11%' class=tctablehdrCenter>Not Run</td><td width='11%' class=tctablehdrCenter>% Complete</td><td class=tctablehdrCenter>Milestone Goal</td></tr>";


//Finally, the display

echo "<tr><td bgcolor='#CCCCCC' class='boldFont' align='center'>A</td><td class='font' align='center'>" . $totalA . "</td><td class='font' align='center'>" . $AStatus . "</td><td class='font' align='center'>" . $passA . "</td><td class='font' align='center'>" . $failA . "</td><td class='font' align='center'>" . $blockedA . "</td><td class='font' align='center'>" . $notRunTCsA . "</td><td class='font' align='center'>" . $percentCompleteA . "%</td><td class='font' align='center'>" . $MA . "%</td></tr>";

echo "<tr><td bgcolor='#CCCCCC' class='boldFont' align='center'>B</td><td class='font' align='center'>" . $totalB . "</td><td class='font' align='center'>" . $BStatus . "</td><td class='font' align='center'>" . $passB . "</td><td class='font' align='center'>" . $failB . "</td><td class='font' align='center'>" . $blockedB . "</td><td class='font' align='center'>"  . $notRunTCsB . "</td><td class='font' align='center'>" . $percentCompleteB . "%</td><td class='font' align='center'>" . $MB . "%</td></tr>";

echo "<tr><td bgcolor='#CCCCCC' class='boldFont' align='center'>C</td><td class='font' align='center'>" . $totalC . "</td><td class='font' align='center'>" . $CStatus . "</td><td class='font' align='center'>" . $passC . "</td><td class='font' align='center'>" . $failC . "</td><td class='font' align='center'>" . $blockedC . "</td><td class='font' align='center'>"  . $notRunTCsC. "</td><td class='font' align='center'>" . $percentCompleteC . "%</td><td class='font' align='center'>" . $MC . "%</td></tr>";


echo "</table>";


require_once("totalComponent.php"); //require_onces the total percentages by component

require_once("owner.php"); //require_onces the total percentages by category owner

require_once("keywords.php"); //require_once the total percentages by keyword



?>

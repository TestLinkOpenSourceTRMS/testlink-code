<?
////////////////////////////////////////////////////////////////////////////////
//File:     buildSelect.php
//Author:   Chad Rosen
//Purpose:  The page is the intermediary page before the execution page. 
//          I needed this page because of new left frame way of doing things.
////////////////////////////////////////////////////////////////////////////////

require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();


	echo "<a href='mainPage.php' target='_parent'>Back To Main Page</a><br>";

	//Building the dropdown box of builds from the project the user picked on the previous page

		$result = mysql_query("select build from build,project where project.id = " . $_SESSION['project'] . " and build.projid = project.id",$db);
		
		//setting up the top table with the date and build selection

		echo "<table border = 1 width='100%' cellpadding='0' cellspacing='4' align='center'>";

		echo "<tr><td bgcolor='#FFCC99' width='25%' class='boldFont' align='center'>Selected Project</td><td bgcolor='#FFCC99' width='25%' class='boldFont' align='center'>Select build</td><td bgcolor='#FFCC99' width='25%' class='boldFont' align='center'>Click To Execute Cases</b></td></tr>";

		//Starting the form that will submit to this page

		echo "<form method='post' ACTION='execution/FrameSet.php?page=" . $_GET['page'] . "'>";

		echo "<tr>";

		//I need to do a query here and get the project name and not the number

		$projectNameResult = mysql_query("select name from project where project.id = " . $_SESSION['project'],$db);
		$projectName = mysql_fetch_row($projectNameResult);
		

		echo "<td bgcolor='#EEEEEE' align='center'>" . $projectName[0] . "</td>";

		//Code that displays all the available builds in a dropdown box		
		
		echo "<td bgcolor='#EEEEEE' align='center'><SELECT NAME='build'>";
		
		while ($myrow = mysql_fetch_row($result)) 
		{
			echo "<OPTION VALUE='" . $myrow[0] ."'>" . $myrow[0];

		}//END WHILE

		echo "</SELECT></td>";

		echo "<td bgcolor='#EEEEEE' align='center'><input type='submit' NAME='submitBuild' value='Submit'></td>";
				
		echo "</tr></table>";

		echo "</form>";




?>

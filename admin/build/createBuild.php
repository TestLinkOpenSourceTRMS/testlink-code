<?php

////////////////////////////////////////////////////////////////////////////////
//File:     createBuild.php
//Author:   Chad Rosen
//Purpose:  This file allows admins to create new builds for a project.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();
  doNavBar();

?>

<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">

<br>

<?

  
if(!$_POST['submit'])
 {

	newBuild();

	existingBuilds();
  
}

elseif($_POST['submit'])
{

	if($_POST['build'] == "")
	{
		echo "You have entered a blank build. Please try again<br>";
	}elseif($_POST['build'] == 0)
	{

		echo "I'm sorry, zero is not a valid build number. Please try again<br>";

	}else
	{


	$sql = "INSERT INTO build (build,projid) VALUES ('" . $_POST['build'] . "','". $_SESSION['project'] . "')";

	$result = mysql_query($sql) or die(mysql_error());

	}//end if $_POST['build']


	newBuild();

	existingBuilds();
  
}

function existingBuilds()
{

//Code to display the current project chosen and the builds already taken

  $sql = "select name from project where id = " . $_SESSION['project'];
  
  $result = mysql_query($sql) or die(mysql_error());

  $myrow = mysql_fetch_row($result);

  echo "<table class=userinfotable  width='50%'>";

  echo "<tr class='subTitle'><td bgcolor='#CCCCCC'><b>Existing Builds for project " . $myrow[0] . "</b></td></tr>";

  echo "</table>";

  echo "<table class=userinfotable  width='50%'>";

  $sql = "select build from build where projid = " . $_SESSION['project'];

  $result = mysql_query($sql) or die(mysql_error());

  $numRows = mysql_num_rows($result);

  if($numRows > 0)
	 {

	  while ($myrow = mysql_fetch_row($result)) 
		{
			echo "<tr><td>" . $myrow[0] . "</td></tr>";

		}//END WHILE

	 }//end if

	 else
	 {
		 echo "<tr><td>No Builds Created</td></tr>";


	 }

  echo "</table>";


}

function newBuild()
{

  echo "<form method='post' action='admin/build/createBuild.php'>\n\n";

  echo "<table class=userinfotable  width='50%'>\n\n";
  
  echo "<tr><td bgcolor='#CCCCCC' class='subTitle'><b>Enter New Build Identifier:</b></td></tr>";
  echo "<tr><td bgcolor='#99CCFF' class='boldFont'>Note: You cannot name a new build the same as an existing build</td></tr>";  
  echo "</table>";
  
  echo "<table class=userinfotable  width='50%'>\n\n";
  
  echo "<tr><td><input type='text' name='build' size='30'></td></tr>\n\n";
  
  echo "</table>";
  
  echo "<br><br><input type='Submit' name='submit' value='Enter information'>\n\n";

  echo "</form>";

  echo "</table>";


}

?>


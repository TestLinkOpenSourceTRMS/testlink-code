<?php

////////////////////////////////////////////////////////////////////////////////
//File:     newProject.php
//Author:   Chad Rosen
//Purpose:  This page manages the ability to add new projects.
////////////////////////////////////////////////////////////////////////////////

//session_start();

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();
  doNavBar();

?>

<br>

<?

	//Users have requested the functionality to copy an exisiting test plan into a new plan. Here I grab
	//all of the project which will go into a drop down below

	$sqlProj = "select id,name from project where active='y' order by id";
	$resultProj = mysql_query($sqlProj);

	while ($myrowProj = mysql_fetch_row($resultProj))
	{
		$option .= "<option value=" . $myrowProj[0] . ">" . $myrowProj[1] . "</option>";


	}

?>

	<FORM method='post' ACTION='admin/project/projectResult.php'>

	<table class=userinfotable width='65%'><tr><td bgcolor='#CCCCCC'><b>Enter New Test Plan</td></tr></table>

	<table class=userinfotable width='65%'>

	<tr><td>Name:</td><td><input type='text' name='name'></td></tr>
	<tr><td>Notes:</td><td><input type='text' name='notes' size='50'></td></tr>
	<tr><td>Create this test plan from an existing plan?</td><td><select name=copy><option value=noCopy>No</option><? echo $option ?></select>
	<tr><td>Would you like rights to this project?</td><td><input type=checkbox name=rights>Yes</td></tr>

	</table>

	<br><input type='submit' NAME='newProject' value='New'>

	</form>

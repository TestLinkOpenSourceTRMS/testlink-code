<?php

////////////////////////////////////////////////////////////////////////////////
//File:     doAuthorize.php
//Author:   Chad Rosen
//Purpose:  This is an extremeley important file. This file handles the 
//          initial login and creates all session variables.
////////////////////////////////////////////////////////////////////////////////

//Setting up cookies so that the user can automatically login next time

  require_once("functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

//Find all rows where the posted username and password are correct

$sql = "Select id,login,password,rightsid,email from user where login='" . $_POST[login] . "' and password='" . $_POST[password] . "'";

//Run the query

$result = mysql_query($sql);

//This variable will determine if the user is allowed in

$num = mysql_num_rows($result);

//If there are entries that match the username and password

if ($num !=0) {
		

		//This variable is used for the session information

		$userResult = mysql_fetch_row($result);

		//Getting the rights of the user
		
		$sql = "select role from rights,user where user.rightsid = rights.id and rights.id ='" . $userResult[3] . "'";
		
		
		$result = mysql_query($sql);
		$rightsResult = mysql_fetch_row($result);

		//Setting user's session information

		$_SESSION['valid'] = 'yes'; //Check to see if the user is valid
		$_SESSION['user'] = $userResult[1]; //Set the user name in a session variable
		$_SESSION['role'] = $rightsResult[0]; //Set the user's rights
		$_SESSION['email'] = $userResult[4]; //Set the user's email address for easy lookup later
		$_SESSION['userID'] = $userResult[0];


		//forwarding user to the mainpage
		
		echo "<script language='javascript'>";
	
		echo "location.href='mainPage.php';";

		echo "</script>";




		
} 	else {
	
		echo "<script language='javascript'>";
	
		echo "location.href='$loginurl';";

		echo "</script>";

	
}



?>

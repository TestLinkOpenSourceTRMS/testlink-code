<?php

////////////////////////////////////////////////////////////////////////////////
//File:     frameSet.php
//Author:   Chad Rosen
//Purpose:  This is the main frame page that displays the javascript 
//          tree on the left and the execution page in the center
////////////////////////////////////////////////////////////////////////////////

//Since I'm combining the project and product print pages I need to pass along a variable
//that tells us if we're looking at a project or product

$type = $_GET['type'];

//Require the valid user page which sets up the session

require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();


//Begin the code that displays the frame

	echo "<frameset rows='70,*' frameborder='NO' border='0' framespacing='0'>";

//Display the top frame

	echo "<frame src='navBar.php?type=" . $type . "&nav=" . $_GET['nav'] . "' name='topFrame' scrolling='NO' noresize MARGINWIDTH=1 MARGINHEIGHT=1>";

//Display the two bottom frames

	echo "<frameset cols='30%,*' frameborder='NO' border='0' framespacing='0'>";

//Left Frame

	echo "<frame src='print/printLeft.php?type=" . $type . "' scrolling='yes'>";

//Right Frame	

	echo "<frame src='print/printData.php' name='mainFrame'>";
	echo "</frameset>";


?>

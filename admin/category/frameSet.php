<?php

////////////////////////////////////////////////////////////////////////////////
//File:     frameSet.php
//Author:   Chad Rosen
//Purpose:  This manages the frameset for category management.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();


	echo "<frameset rows='70,*' frameborder='NO' border='0' framespacing='0'>";
  
	echo "<frame src='navBar.php?type=project&nav=" . $_GET['nav'] . "' name='topFrame' scrolling='NO' noresize MARGINWIDTH=1 MARGINHEIGHT=1>";
	
	echo "<frameset cols='20%,*,20%' frameborder='NO' border='0' framespacing='0'>";
	echo "<frame src='admin/category/categoryFrameLeft.php' name='leftFrame' scrolling='yes'>";
	echo "<frame src='admin/category/categorySelect.php?edit=info' name='mainFrame'>";
	echo "<frame src='admin/category/priorityDefinition.php' name='rightFrame' noresize>";
	echo "</frameset>'";


?>

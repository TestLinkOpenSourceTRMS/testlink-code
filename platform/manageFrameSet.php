<?php
////////////////////////////////////////////////////////////////////////////////
//File:     archiveFrameSet.php
//Author:   Chad Rosen
//Purpose:  This is the main frame page that displays the javascript tree 
//          on the left and the execution page in the center.
////////////////////////////////////////////////////////////////////////////////

require_once("../functions/header.php");

doDBConnect();
doHeader();

	echo "<frameset rows='70,*' frameborder='NO' border='0' framespacing='0'>";
  
	echo "<frame src='navBar.php?type=product&nav=" . $_GET['nav'] . "' name='topFrame' scrolling='NO' noresize MARGINWIDTH=1 MARGINHEIGHT=1>";

	echo "<frameset cols='33%,*' frameborder='NO' border='0' framespacing='0'>";
	echo "<frame src='platform/manageLeft.php' scrolling='yes' name='left'>";
	echo "<frame src='platform/manageData.php' name='mainFrame'>";
	echo "</frameset>'";

?>
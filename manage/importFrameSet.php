<?php

////////////////////////////////////////////////////////////////////////////////
//File:     importframeSet.php
//Author:   Chad Rosen
//Purpose:  This is the main frame page that displays the javascript tree 
//          on the left and the execution page in the center
////////////////////////////////////////////////////////////////////////////////

require_once("../functions/header.php");
doHeader();

	echo "<frameset rows='70,*' frameborder='NO' border='0' framespacing='0'>";
  
	echo "<frame src='navBar.php?type=project&nav=" . $_GET['nav'] . "' name='topFrame' scrolling='NO' noresize MARGINWIDTH=1 MARGINHEIGHT=1>";
	
	echo "<frameset cols='30%,*' frameborder='NO' border='0' framespacing='0'>";
	echo "<frame src='manage/importLeft.php' scrolling='yes'>";
	echo "<frame src='manage/importData.php?edit=info' name='mainFrame'>";
	echo "</frameset>'";


?>

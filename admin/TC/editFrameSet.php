<?php
////////////////////////////////////////////////////////////////////////////////
//File:     frameSet.php
//Author:   Chad Rosen
//Purpose:  This is the main frame page that displays the javascript tree on 
//          the left and the editData page on the right.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();


	echo "<frameset rows='70,*' frameborder='NO' border='0' framespacing='0'>";
  
	echo "<frame src='navBar.php?type=project&nav=" . $_GET['nav'] . "' name='topFrame' scrolling='NO' noresize MARGINWIDTH=1 MARGINHEIGHT=1>";
	
	echo "<frameset cols='35%,*' frameborder='NO' border='0' framespacing='0'>";
	echo "<frame src='admin/TC/editLeft.php' scrolling='yes' name='left'>";
	echo "<frame src='admin/TC/editData.php?edit=info&project=" . $_GET['project'] . "' name='mainFrame'>";
	echo "</frameset>'";


?>

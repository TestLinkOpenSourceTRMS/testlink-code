<?

////////////////////////////////////////////////////////////////////////////////
//File:     logout.php
//Author:   Chad Rosen
//Purpose:  This file handles logout by removing session variables and closing
//          the session.
////////////////////////////////////////////////////////////////////////////////

require_once("functions/header.php");

doDBConnect() or die("Could not connect to DB");
doSessionStart() or die("Could not start session");

// Unset all of the session variables.
session_unset();
// Finally, destroy the session.
session_destroy();

echo "<script language='javascript'>";
echo "location.href='" . $loginurl . "';";
echo "</script>";


?>

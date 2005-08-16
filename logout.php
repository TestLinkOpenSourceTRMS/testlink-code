<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: logout.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2005/08/16 17:57:41 $
 *
 * @author Martin Havlat
 *
 * 
**/
// Unset all of the session variables.
session_start();
session_unset();
session_destroy();

// redirect to login page
echo "<html><head>";
echo "<script type='text/javascript'>";
echo "location.href='login.php';";
echo "</script></head><body></body></html>";
?>

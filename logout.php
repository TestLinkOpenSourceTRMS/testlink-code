<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Filename $RCSfile: logout.php,v $
 *
 * @version $Revision: 1.13 $
 * @modified $Date: 2008/01/30 19:52:23 $
**/
require_once('config.inc.php');
require_once('common.php');

$userID = null;
if(!isset($_SESSION))
{ 
	session_start();
	$userID = $_SESSION['userID'] ?  $_SESSION['userID'] : null;
}
doDBConnect($db);
logAuditEvent(TLS("audit_user_logout"),"LOGOUT",$userID,"users");  
session_unset();
session_destroy();

redirect("login.php");
exit();
?>
<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Filename $RCSfile: logout.php,v $
 *
 * @version $Revision: 1.14 $
 * @modified $Date: 2008/01/30 20:37:43 $
**/
require_once('config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$userID = $_SESSION['userID'] ?  $_SESSION['userID'] : null;
$userName = null;
if ($userID)
{
	$userName = $_SESSION['currentUser']->getDisplayName();
	logAuditEvent(TLS("audit_user_logout",$userName),"LOGOUT",$userID,"users");  
}
session_unset();
session_destroy();

redirect("login.php");
exit();
?>
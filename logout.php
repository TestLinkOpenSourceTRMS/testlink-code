<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Filename $RCSfile: logout.php,v $
 *
 * @version $Revision: 1.18 $
 * @modified $Date: 2009/08/11 19:48:50 $
**/
require_once('config.inc.php');
require_once('common.php');
testlinkInitPage($db);
$args = init_args();
if ($args->userID)
{
	logAuditEvent(TLS("audit_user_logout",$args->userName),"LOGOUT",$args->userID,"users");  
}
session_unset();
session_destroy();

redirect("login.php");
exit();

function init_args()
{
	$args = new stdClass();
	
	$args->userID = isset($_SESSION['userID']) ?  $_SESSION['userID'] : null;
	$args->userName = $args->userID ? $_SESSION['currentUser']->getDisplayName() : "";
	
	return $args;
}
?>
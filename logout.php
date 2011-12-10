<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * @filesource	logout.php
 *
 * @internal revisions
 * @since 1.9.4
 *
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
redirect("login.php?note=logout");
exit();


function init_args()
{
	$args = new stdClass();
	
	$args->userID = isset($_SESSION['userID']) ?  $_SESSION['userID'] : null;
	$args->userName = $args->userID ? $_SESSION['currentUser']->getDisplayName() : "";
	
	return $args;
}
?>
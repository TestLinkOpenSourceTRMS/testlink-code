<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Filename $RCSfile: logout.php,v $
 *
 * @version $Revision: 1.11 $
 * @modified $Date: 2008/01/19 17:47:47 $
**/
require_once('config.inc.php');
require_once('common.php');
doDBConnect($db);

$userID = null;
if(!isset($_SESSION))
{ 
	session_start();
	$userID = $_SESSION['userID'] ?  $_SESSION['userID'] : null;
}
tLog(TLS("audit_user_logout"),'AUDIT',null,$userID,"users");  
session_unset();
session_destroy();

redirect("login.php");
exit();
?>
<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * @filesource	logout.php
 *
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

$authCfg = config_get('authentication');
if(isset($authCfg['SSO_enabled']) && $authCfg['SSO_enabled'] 
   && $args->ssodisable == FALSE)
{
  redirect($authCfg['SSO_logout_destination']);
}
else
{
  $std = "login.php?note=logout&viewer={$args->viewer}";
  $std .= $args->ssodisable ? "&ssodisable" : '';

  $xx = config_get('logoutUrl');
  $lo = is_null($xx) || trim($xx) == '' ? $std : $xx;
  redirect($lo);
}
exit();


/**
 *
 */
function init_args()
{
	$args = new stdClass();
	
	$args->userID = isset($_SESSION['userID']) ?  $_SESSION['userID'] : null;
	$args->userName = $args->userID ? $_SESSION['currentUser']->getDisplayName() : "";
	
	$args->viewer = isset($_GET['viewer']) ? $_GET['viewer'] : '';
    $args->ssodisable = getSSODisable();
	
    return $args;
}
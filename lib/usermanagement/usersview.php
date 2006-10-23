<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: usersview.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2006/10/23 20:11:28 $
 *
 * This page shows all users
**/
include('../../config.inc.php');
require_once("users.inc.php");
testlinkInitPage($db);

$sqlResult = null;
$action = null;
$update_title_bar = 0;
$reload = 0;

$bDelete = isset($_GET['delete']) ? $_GET['delete'] : 0;
$userID = isset($_GET['user']) ? $_GET['user'] : 0;
$sessionUserID = $_SESSION['userID'];

if ($bDelete && $userID)
{
	$sqlResult = userDelete($db,$userID);
	
	//if the users deletes itself then logout
	if ($userID == $sessionUserID)
	{
		header("Location: ../../logout.php");
		exit();
	}
	$action = "deleted";
}
	
$users = getAllUsers($db);
$roles = getAllRoles($db);

$smarty = new TLSmarty();
$smarty->assign('optRoles',$roles);
$smarty->assign('mgt_users',has_rights($db,"mgt_users"));
$smarty->assign('role_management',has_rights($db,"role_management"));
$smarty->assign('tp_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"user_role_assignment"));
$smarty->assign('tproject_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"user_role_assignment",null,-1));
$smarty->assign('update_title_bar',$update_title_bar);
$smarty->assign('reload',$reload);
$smarty->assign('users',$users);
$smarty->assign('result',$sqlResult);
$smarty->assign('action',$action);
$smarty->display($g_tpl['usersview']);
?>
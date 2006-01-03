<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: adminUsers.php,v $
 *
 * @version $Revision: 1.10 $
 * @modified $Date: 2006/01/03 21:19:02 $
 *
 * @author Martin Havlat
 *
 * This page shows all users
 *
 * 20053112 - scs - cleanup, due to removing bulk update of users
 * 20060103 - scs - ADOdb changes
**/
include('../../config.inc.php');
require_once("users.inc.php");
testlinkInitPage();

$sqlRes = null;
//delete
$id = isset($_GET['user']) ? intval($_GET['user']) : 0;
$bDelete = isset($_GET['delete']) ? intval($_GET['delete']) : 0;
//update
$_POST = strings_stripSlashes($_POST);
$first = isset($_POST['first']) ? $_POST['first'] : null;
$last = isset($_POST['last']) ? $_POST['last'] : null;
$email = isset($_POST['email']) ? $_POST['email'] : null;
$locale = isset($_POST['locale']) ? $_POST['locale'] : null;
$rights_id = isset($_POST['rights_id']) ? intval($_POST['rights_id']) : 5;
$user_is_active = isset($_POST['user_is_active']) ? 1 : 0;
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$bUpdate = isset($_POST['do_update']) ? 1 : 0;

$action = null;
$update_title_bar = 0;
$reload = 0;

if ($bUpdate)
{
	$sqlRes = userUpdate($db,$user_id,$first,$last,$email,null,$rights_id,$locale,$user_is_active);
	$action = "updated";
	if ($sqlRes == 'ok' && $user_id == $_SESSION['userID'])
	{
		//if the user has no longer the mgt_users right, reload the index.php page,
		//else we must update the titlebar
		//BUGID 0000103: Localization is changed but not strings
		if (!has_rights('mgt_users'))
			$reload = 1;
		else
			$update_title_bar = 1;
		if (!$user_is_active)
		{
			header("Location: ../../logout.php");
			exit();
		}
	}
}
else if ($bDelete && $id)
{
	$sqlRes = userDelete($db,$id);
	//if the users deletes itself then logout
	if ($id == $_SESSION['userID'])
	{
		header("Location: ../../logout.php");
		exit();
	}
	$action = "deleted";
}
	
$users = getAllUsers($db);
$rights = getListOfRights($db);

$smarty = new TLSmarty();
$smarty->assign('optRights',$rights);
$smarty->assign('update_title_bar',$update_title_bar);
$smarty->assign('reload',$reload);
$smarty->assign('users',$users);
$smarty->assign('result',$sqlRes);
$smarty->assign('action',$action);
$smarty->display($g_tpl['adminUsers']);
?>
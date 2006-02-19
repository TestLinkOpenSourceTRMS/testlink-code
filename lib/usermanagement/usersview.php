<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: usersview.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2006/02/19 13:08:05 $
 *
 * This page shows all users
 *
 * 20053112 - scs - cleanup, due to removing bulk update of users
 * 20060103 - scs - ADOdb changes
 * 20060107 - fm  - refactoring init_args()
 *
**/
include('../../config.inc.php');
require_once("users.inc.php");
testlinkInitPage($db);

$sqlResult = null;
$action = null;
$args = init_args($_GET,$_POST,TRUE);

if ($args->delete && $args->user)
{
	$sqlResult = userDelete($db,$args->user);
	
	//if the users deletes itself then logout
	if ($args->user == $_SESSION['userID'])
	{
		header("Location: ../../logout.php");
		exit();
	}
	$action = "deleted";
}
	
$users = getAllUsers($db);
$rights = getListOfRoles($db);

$smarty = new TLSmarty();
$smarty->assign('optRights',$rights);
$smarty->assign('update_title_bar',$update_title_bar);
$smarty->assign('reload',$reload);
$smarty->assign('users',$users);
$smarty->assign('result',$sqlResult);
$smarty->assign('action',$action);
$smarty->display($g_tpl['usersview']);

// 20060107 - fm
function init_args($get_hash, $post_hash, $do_post_strip=TRUE)
{
	if($do_post_strip)
		$post_hash = strings_stripSlashes($post_hash);

	$intval_keys = array('delete' => 0, 'user' => 0);
	foreach ($intval_keys as $key => $value)
	{
		$args->$key = isset($get_hash[$key]) ? intval($get_hash[$key]) : $value;
	}
  
	return $args;
}
?>
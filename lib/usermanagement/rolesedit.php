<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: rolesedit.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2006/02/19 13:08:05 $ by $Author: schlundus $
 *
**/
require_once("../../config.inc.php");
require_once("../functions/users.inc.php");
require_once("../functions/common.php");
testlinkInitPage($db);

$_POST = strings_stripSlashes($_POST);
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$postBack = sizeof($_POST) ? 1 : 0;

if ($postBack)
{
	$roleName = isset($_POST['rolename']) ? $_POST['rolename'] : null;
	$id = isset($_POST['id']) ? $_POST['id'] : 0;
	$bNew = ($id == 0);
	//remove all keys except the rights
	unset($_POST['id']);
	unset($_POST['editRole']);
	unset($_POST['newRole']);
	unset($_POST['rolename']);
	
	$rights = $_POST;
	$sqlResult = checkRole($db,$roleName,$rights,$id);
	if ($sqlResult == 'ok')
	{
		$rights = array_keys($rights);
		if ($bNew)
		{
			$id = createRole($db,$roleName,$rights);
			if (!$id)
				$sqlResult = lang_get('error_role_creation');
			$action = "added";
			//reset id if all was ok
			if ($sqlResult == "ok")
				$id = 0;
		}
		else
		{
			if (!updateRole($db,$id,$roleName,$rights))
				$sqlResult = lang_get('error_role_update');
			$action = "updated";
		}
	}
}

//get the role info
$roles = getRoles($db,$id);
$role = null;
$affectedUsers = null;
$allUsers = null;
if (sizeof($roles))
{
	$role = $roles[$id];
	if($role)
	{
		//build the checked attribute for the checkboxes
		$rights = explode(",",$role['rights']);
		for($i = 0;$i < sizeof($rights);$i++)
		{
			$roleRights[$rights[$i]] = "checked=\"checked\"";
		}
		//get all users which are affected by changing the role definition
		$allUsers = getAllUsers($db,null,'id');
		$affectedUsers = getAllUsersWithRole($db,$id);
	}
}

$smarty = new TLSmarty();
$smarty->assign('role',$role);
$smarty->assign('tpRights',$g_rights_tp);
$smarty->assign('tcRights',$g_rights_mgttc);
$smarty->assign('kwRights',$g_rights_kw);
$smarty->assign('pRights',$g_rights_product);
$smarty->assign('uRights',$g_rights_users);
$smarty->assign('reqRights',$g_rights_req);
$smarty->assign('roleRights',$roleRights);
$smarty->assign('sqlResult',$sqlResult);
$smarty->assign('allUsers',$allUsers);
$smarty->assign('affectedUsers',$affectedUsers);
$smarty->assign('action',$action);
$smarty->display('rolesedit.tpl');
?>
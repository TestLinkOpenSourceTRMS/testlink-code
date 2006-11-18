<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: rolesedit.php,v $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2006/11/18 21:33:23 $ by $Author: schlundus $
 *
**/
require_once("../../config.inc.php");
require_once("../functions/users.inc.php");
require_once("../functions/common.php");
require_once("../../third_party/fckeditor/fckeditor.php");
testlinkInitPage($db);

$_POST = strings_stripSlashes($_POST);
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$postBack = (sizeof($_POST) > 2) ? 1 : 0;

$of = new fckeditor('notes') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet = 'TL_Medium';


$roleRights = null;
$sqlResult = null;
$action = null;

if ($postBack && has_rights($db,"role_management"))
{
	$roleName = isset($_POST['rolename']) ? $_POST['rolename'] : null;
	$id = isset($_POST['id']) ? $_POST['id'] : 0;
	$notes = isset($_POST['notes']) ? strings_stripSlashes($_POST['notes']) : '';
	$bNew = ($id == 0);
	//remove all keys except the rights
	unset($_POST['id']);
	unset($_POST['editRole']);
	unset($_POST['newRole']);
	unset($_POST['rolename']);
	unset($_POST['notes']);
	
	$rights = $_POST;
	$sqlResult = checkRole($db,$roleName,$rights,$id);
	if ($sqlResult == 'ok')
	{
		$rights = array_keys($rights);
		if ($bNew)
		{
			$id = createRole($db,$roleName,$rights,$notes);
			if (!$id)
				$sqlResult = lang_get('error_role_creation');
			$action = "added";
			//reset id if all was ok
			if ($sqlResult == "ok")
				$id = 0;
		}
		else
		{
			if (!updateRole($db,$id,$roleName,$rights,$notes))
				$sqlResult = lang_get('error_role_update');
			$action = "updated";
		}
	}
}

//get the role info
$role = null;
$affectedUsers = null;
$allUsers = null;

$roles = getRoles($db,$id);
if (sizeof($roles) && $id)
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
		$of->Value = isset($role['notes']) ? $role['notes'] : '';
	}
}

$smarty = new TLSmarty();
$smarty->assign('role',$role);
$smarty->assign('role_management',has_rights($db,"role_management"));
$smarty->assign('mgt_users',has_rights($db,"mgt_users"));
$smarty->assign('tp_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"user_role_assignment"));
$smarty->assign('tproject_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"user_role_assignment",null,-1));
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
$smarty->assign('notes', $of->CreateHTML());
$smarty->assign('noRightsRole',TL_ROLES_NONE);
$smarty->display('rolesedit.tpl');
?>
<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: rolesEdit.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2007/12/22 12:26:46 $ by $Author: schlundus $
 *
 *
 * 20071201 - franciscom - new web editor code
 * 20070901 - franciscom - BUGID 1016 
 * 20070829 - jbarchibald - BUGID 1000 - Testplan role assignments
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once("web_editor.php");
testlinkInitPage($db);

$template_dir = 'usermanagement/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

// 20070901 - BUGID 1016
// lang_get() is used inside roles.inc.php to translate user right descriptionm and needs $_SESSION info.
// If no _SESSION info is found, then default locale is used.
// We need to be sure _SESSION info exists before using lang_get(); in any module.
//
init_global_rights_maps();

$_POST = strings_stripSlashes($_POST);
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$postBack = (sizeof($_POST) > 2) ? 1 : 0;

$of = web_editor('notes',$_SESSION['basehref']) ;
$of->Value = null;

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
	$rights = implode("','",array_keys($rights));
	$role = new tlRole();
	$role->rights = tlRight::getAll($db,"WHERE description IN ('{$rights}')");
	$role->name = $roleName;
	$role->description = $notes;
	$role->dbID = $id;
	
	$result = $role->writeToDB($db);
	$action = $bNew ? "do_add" : "updated";
	$sqlResult = getRoleErrorMessage($result);
}

$affectedUsers = null;
$allUsers = null;

//get the role info
$role = tlRole::getByID($db,$id);
if($role)
{
	//build the checked attribute for the checkboxes
	for($i = 0;$i < sizeof($role->rights);$i++)
	{
		$right = $role->rights[$i];
		$roleRights[$right->name] = "checked=\"checked\"";
	}
	//get all users which are affected by changing the role definition
	$allUsers = tlUser::getAll($db,null,"id");
	$affectedUsers = getAllUsersWithRole($db,$role->dbID);
	$of->Value = $role->description;
}

$smarty = new TLSmarty();
$smarty->assign('role',$role);
$smarty->assign('role_management',has_rights($db,"role_management"));
$smarty->assign('mgt_users',has_rights($db,"mgt_users"));
$smarty->assign('tp_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"testplan_user_role_assignment"));
$smarty->assign('tproject_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"user_role_assignment",null,-1));
$smarty->assign('tpRights',$g_rights_tp);
$smarty->assign('tcRights',$g_rights_mgttc);
$smarty->assign('kwRights',$g_rights_kw);
$smarty->assign('pRights',$g_rights_product);
$smarty->assign('uRights',$g_rights_users);
$smarty->assign('reqRights',$g_rights_req);
$smarty->assign('cfRights',$g_rights_cf);
$smarty->assign('roleRights',$roleRights);
$smarty->assign('sqlResult',$sqlResult);
$smarty->assign('allUsers',$allUsers);
$smarty->assign('affectedUsers',$affectedUsers);
$smarty->assign('action',$action);
$smarty->assign('notes', $of->CreateHTML());
$smarty->assign('noRightsRole',TL_ROLES_NONE);
$smarty->display($template_dir . $default_template);
?>
<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: rolesView.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2007/12/20 09:45:38 $ by $Author: franciscom $
 *
 *  20070901 - franciscom - BUGID
 *  20070829 - jbarchibald - BUGID 1000 - Testplan role assignments
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once("roles.inc.php");
testlinkInitPage($db);

$template_dir='usermanagement/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

// 20070901 - franciscom@gruppotesi.com -BUGID 1016
init_global_rights_maps();


$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$bDelete = isset($_GET['deleterole']) ? 1 : 0;
$bConfirmed = isset($_GET['confirmed']) ? 1 : 0;
$userID = $_SESSION['userID'];

$sqlResult = null;
$affectedUsers = null;
$allUsers = getAllUsers($db,null,'id');
$role_id_replacement=config_get('role_replace_for_deleted_roles');

if ($bDelete && $id)
{
	$sqlResult = "ok";
	//get all users which are affected by deleting the role if the user hasn't 
	//confirmed the deletion

	if (!$bConfirmed)
		$affectedUsers = getAllUsersWithRole($db,$id);
	
	if (!sizeof($affectedUsers))
	{
		if (!deleteRole($db,$id))
			$sqlResult = lang_get("error_role_deletion");
		else
		{
			//reset all affected users by replacing the deleted role with 
			// configured role
			resetUserRoles($db,$id,$role_id_replacement);
		}
	}
	else
		$sqlResult = null;
}
$roles = getRoles($db);


if ($bDelete && $id)
{
	//reload the roles of the current user
	$_SESSION['testprojectRoles'] = getUserTestProjectRoles($db,$userID);
	$_SESSION['testPlanRoles'] = getUserTestPlanRoles($db,$userID);
	if ($_SESSION['roleID'] == $id)
	{
		$_SESSION['roleID'] = TL_ROLES_NO_RIGHTS;
		$_SESSION['role'] = $roles[TL_ROLES_NO_RIGHTS]['role'];
	}
}

$smarty = new TLSmarty();
$smarty->assign('mgt_users',has_rights($db,"mgt_users"));
$smarty->assign('role_management',has_rights($db,"role_management"));
$smarty->assign('tp_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"testplan_user_role_assignment"));
$smarty->assign('tproject_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"user_role_assignment",null,-1));
$smarty->assign('roles',$roles);
$smarty->assign('id',$id);
$smarty->assign('sqlResult',$sqlResult);
$smarty->assign('allUsers',$allUsers);
$smarty->assign('affectedUsers',$affectedUsers);
$smarty->assign('role_id_replacement',$role_id_replacement);
$smarty->display($template_dir . $default_template);
?>
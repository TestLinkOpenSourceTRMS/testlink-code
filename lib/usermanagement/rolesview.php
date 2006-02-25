<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: rolesview.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2006/02/25 21:48:27 $ by $Author: schlundus $
 *
 * 20060224 - franciscom - changes in session product -> testproject
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("../functions/users.inc.php");
require_once("../functions/roles.inc.php");
testlinkInitPage($db);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$bDelete = isset($_GET['deleterole']) ? 1 : 0;
$bConfirmed = isset($_GET['confirmed']) ? 1 : 0;
$userID = $_SESSION['userID'];

$sqlResult = null;
$affectedUsers = null;
$allUsers = getAllUsers($db,null,'id');
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
			//reset all affected users by replacing the deleted role with the
			//<no rights> role
			resetUserRoles($db,$id);
		}
	}
	else
		$sqlResult = null;
}
$roles = getRoles($db);
if ($bDelete && $id)
{
	//reload the roles of the current user
	$_SESSION['testprojectRoles'] = getUserProductRoles($db,$userID);
	$_SESSION['testPlanRoles'] = getUserTestPlanRoles($db,$userID);
	if ($_SESSION['roleId'] == $id)
	{
		$_SESSION['roleId'] = TL_ROLES_NONE;
		$_SESSION['role'] = $roles[TL_ROLES_NONE]['role'];
	}
}

$smarty = new TLSmarty();
$smarty->assign('roles',$roles);
$smarty->assign('id',$id);
$smarty->assign('sqlResult',$sqlResult);
$smarty->assign('allUsers',$allUsers);
$smarty->assign('affectedUsers',$affectedUsers);
$smarty->display('rolesview.tpl');
?>
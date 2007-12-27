<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: rolesView.php,v $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2007/12/27 17:07:00 $ by $Author: franciscom $
 *
 *  20070829 - jbarchibald - BUGID 1000 - Testplan role assignments
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once("roles.inc.php");
testlinkInitPage($db);

$template_dir = 'usermanagement/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

// 20070901 - franciscom@gruppotesi.com -BUGID 1016
init_global_rights_maps();

$args=init_args();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$bDelete = isset($_GET['deleterole']) ? 1 : 0;
$bConfirmed = isset($_GET['confirmed']) ? 1 : 0;

$userFeedback = null;
$affectedUsers = null;
$allUsers = tlUser::getAll($db,null,"id");

switch ( $args->doAction )
{
   case 'delete':
   $affectedUsers = getAllUsersWithRole($db,$args->roleid);
   $doDelete=(sizeof($affectedUsers) == 0);
   break;  
 
   case 'confirmDelete':
   $doDelete=1;
   break;  
}

if( $doDelete )
{
    $userFeedback=deleteRole($db,$args->roleid);
    updateSessionRoles($db,$args->roleid,$args->userID);
}

$smarty = new TLSmarty();
$smarty->assign('mgt_users',has_rights($db,"mgt_users"));
$smarty->assign('role_management',has_rights($db,"role_management"));

$smarty->assign('tp_user_role_assignment', 
                has_rights($db,"mgt_users") ? "yes" : has_rights($db,"testplan_user_role_assignment"));
$smarty->assign('tproject_user_role_assignment', 
                has_rights($db,"mgt_users") ? "yes" : has_rights($db,"user_role_assignment",null,-1));
                
$smarty->assign('roles',tlRole::getAll($db,null,null,null,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM));
$smarty->assign('id',$args->roleid);
$smarty->assign('sqlResult',$userFeedback);
$smarty->assign('allUsers',$allUsers);
$smarty->assign('affectedUsers',$affectedUsers);
$smarty->assign('role_id_replacement',config_get('role_replace_for_deleted_roles'));
$smarty->display($template_dir . $default_template);
?>

<?php
/*
  function: 

  args:
  
  returns: 

*/
function init_args()
{
    $_REQUEST = strings_stripSlashes($_REQUEST);

    $args->roleid = isset($_REQUEST['roleid']) ? intval($_REQUEST['roleid']) : 0;
    $args->doAction = isset($_REQUEST['doAction']) ? intval($_REQUEST['doAction']) : 0;
    $args->userID = $_SESSION['userID'];

    return $args;  
}


/*
  function: 

  args:
  
  returns: 

*/
function updateSessionRoles(&$db,$roleID,$userID)
{
	//reload the roles of the current user
	$_SESSION['testprojectRoles'] = getUserTestProjectRoles($db,$userID);
	$_SESSION['testPlanRoles'] = getUserTestPlanRoles($db,$userID);
	
	if ($_SESSION['roleID'] == $roleID)
	{
		$_SESSION['roleID'] = TL_ROLES_NO_RIGHTS;
		//SCHLUNDUS: needs to be refactored
		$roles = getRoles($db);
		$_SESSION['role'] = $roles[TL_ROLES_NO_RIGHTS]['role'];
	}
}

/*
  function: 

  args:
  
  returns: 

*/
function deleteRole(&$db,$roleID)
{
    $userFeedback = 'ok';
		$role = tlRole::getByID($db,$roleID,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
		if ($role && $role->deleteFromDB($db) < tl::OK)
		{
			$userFeedback = lang_get("error_role_deletion");
    }
    return $userFeedback;
}
?>
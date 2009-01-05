<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: rolesView.php,v $
 *
 * @version $Revision: 1.24 $
 * @modified $Date: 2009/01/05 21:38:57 $ by $Author: schlundus $
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once("roles.inc.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
init_global_rights_maps();
$args = init_args();

$affectedUsers = null;
$doDelete = false;
$role = null;

switch ($args->doAction)
{
	case 'delete':
		$role = tlRole::getByID($db,$args->roleid,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
		if ($role)
		{
			$affectedUsers = $role->getAllUsersWithRole($db);
			$doDelete = (sizeof($affectedUsers) == 0);
		}
		break;

	case 'confirmDelete':
		$doDelete = 1;
		break;
}
$userFeedback = null;
if($doDelete)
{
	$userFeedback = deleteRole($db,$args->roleid);
	//refresh the current user
	checkSessionValid($db);
}

$roles = tlRole::getAll($db,null,null,null,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);

$highlight = initialize_tabsmenu();
$highlight->view_roles = 1;

$smarty = new TLSmarty();
$smarty->assign('highlight',$highlight);
$smarty->assign('grants',getGrantsForUserMgmt($db,$_SESSION['currentUser']));
$smarty->assign('roles',$roles);
$smarty->assign('id',$args->roleid);
$smarty->assign('sqlResult',$userFeedback);
$smarty->assign('affectedUsers',$affectedUsers);
$smarty->assign('role_id_replacement',config_get('role_replace_for_deleted_roles'));
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

function init_args()
{
    $_REQUEST = strings_stripSlashes($_REQUEST);
	
    $args = new stdClass();
    $args->roleid = isset($_REQUEST['roleid']) ? intval($_REQUEST['roleid']) : 0;
    $args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : '';
    $args->userID = $_SESSION['currentUser']->dbID;

    return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,"role_management");
}
?>

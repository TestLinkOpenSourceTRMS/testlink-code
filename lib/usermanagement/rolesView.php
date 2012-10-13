<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	rolesView.php
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once("roles.inc.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$args = init_args();
checkRights($db,$_SESSION['currentUser'],$args);
$gui = initializeGui($db,$args);

$doDelete = false;
$role = null;
$updateGui = false;
switch ($args->doAction)
{
	case 'delete':
		$role = tlRole::getByID($db,$args->roleid,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
		if ($role)
		{
			$gui->affectedUsers = $role->getAllUsersWithRole($db);
			$doDelete = (sizeof($gui->affectedUsers) == 0);
		}
		break;

	case 'confirmDelete':
		$doDelete = true;
		break;
}

if($doDelete)
{
  $role = new tlRole($args->roleid);
	$userFeedback = $role->delete($db);
  $gui = initializeGui($db,$args);
	$gui->userFeedback = $userFeedback;
	checkSessionValid($db); 	//refresh the current user
}


$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * @return object returns the arguments for the page
 */
function init_args()
{
	$iParams = array("roleid" => array(tlInputParameter::INT_N),
					         "tproject_id" => array(tlInputParameter::INT_N),
					         "doAction" => array(tlInputParameter::STRING_N,0,100));

	$args = new stdClass();
	R_PARAMS($iParams,$args);
    
	$args->currentUser = $_SESSION['currentUser'];
  return $args;
}


function initializeGui(&$dbHandler,&$argsObj)
{
	$guiObj = new stdClass();

	$guiObj->highlight = initialize_tabsmenu();
	$guiObj->highlight->view_roles = 1;

	$guiObj->roles = tlRole::getAll($dbHandler,null,null,null,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
	$guiObj->grants = getGrantsForUserMgmt($dbHandler,$argsObj->currentUser);
	$guiObj->id = $argsObj->roleid;
	$guiObj->role_id_replacement = config_get('role_replace_for_deleted_roles');

	$guiObj->affectedUsers = null;
	$guiObj->userFeedback = '';
	$guiObj->tproject_id = $argsObj->tproject_id;

	return $guiObj;
}

/**
 * 
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	// For this feature check must be done on Global Rights => those that belong to
	// role assigned to user when user was created (Global/Default Role)
	// => enviroment is ignored.
	// To instruct method to ignore enviromente, we need to set enviroment but with INEXISTENT ID 
	// (best option is negative value)
	$env['tproject_id'] = -1;
	$env['tplan_id'] = -1;
	checkSecurityClearance($db,$userObj,$env,array('role_management'),'and');
}
?>
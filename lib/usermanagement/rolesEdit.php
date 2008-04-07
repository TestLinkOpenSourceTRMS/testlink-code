<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: rolesEdit.php,v $
 *
 * @version $Revision: 1.18 $
 * @modified $Date: 2008/04/07 07:07:00 $ by $Author: franciscom $
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once("web_editor.php");
testlinkInitPage($db);
init_global_rights_maps();

$template_dir = 'usermanagement/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$args = init_args();

$of = web_editor('notes',$_SESSION['basehref']) ;
$of->Value = null;

$checkboxStatus = null;
$userFeedback = null;
$action = null;
$affectedUsers = null;
$role = null;
$action_type = 'edit';
$highlight = initialize_tabsmenu();


switch($args->doAction)
{
	case 'create':
    	$highlight->create_role=1;
		$action_type = 'doCreate';
		break;

	case 'edit':
	  	$highlight->edit_role=1;
		$action_type = 'doEdit';
		$role = tlRole::getByID($db,$args->roleid);
		break;

	case 'doCreate':
	case 'doEdit':
	    if($args->doAction == 'doCreate')
        	$highlight->create_role = 1;
	    else
	        $highlight->edit_role = 1;

		if(has_rights($db,"role_management"))
		{
			$op = doCreate($db,$args);
			$role = $op->role;
			if ($role->dbID)
				$action_type = 'doEdit';
			else
				$action_type = 'doCreate';
			$action = $op->action;
			$userFeedback = $op->userFeedback;
		}
		break;
}


if($role)
{
	// build checked attribute for checkboxes
	$checkboxStatus = null;
	if(sizeof($role->rights))
	{
	    foreach($role->rights as $key => $right)
	    {
	    	$checkboxStatus[$right->name] = "checked=\"checked\"";
	    }
	}
	//get all users which are affected by changing the role definition
	if ($role->dbID)
		$affectedUsers = $role->getAllUsersWithRole($db);
	$of->Value = $role->description;
}

$smarty = new TLSmarty();
$smarty->assign('highlight',$highlight);
$smarty->assign('action_type',$action_type);
$smarty->assign('role',$role);

$smarty->assign('grants',getGrantsForUserMgmt($db,$_SESSION['currentUser']));

// $smarty->assign('role_management',has_rights($db,"role_management"));
// $smarty->assign('mgt_users',has_rights($db,"mgt_users"));
// $smarty->assign('tp_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"testplan_user_role_assignment"));
// $smarty->assign('tproject_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"user_role_assignment",null,-1));

$smarty->assign('tpRights',$g_rights_tp);
$smarty->assign('tcRights',$g_rights_mgttc);
$smarty->assign('kwRights',$g_rights_kw);
$smarty->assign('pRights',$g_rights_product);
$smarty->assign('uRights',$g_rights_users);
$smarty->assign('reqRights',$g_rights_req);
$smarty->assign('cfRights',$g_rights_cf);
$smarty->assign('checkboxStatus',$checkboxStatus);
$smarty->assign('sqlResult',$userFeedback);
$smarty->assign('affectedUsers',$affectedUsers);
$smarty->assign('action',$action);
$smarty->assign('notes', $of->CreateHTML());
$smarty->assign('noRightsRole',TL_ROLES_NONE);
$smarty->display($template_dir . $default_template);

function init_args()
{
  $args = new stdClass();
	$_REQUEST = strings_stripSlashes($_REQUEST);

	$key2loop = array('doAction' => null,'rolename' => null , 'roleid' => 0, 'notes' => '', 'grant' => null);
	foreach($key2loop as $key => $value)
	{
		$args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
	}
	return $args;
}

/*
  function:

  args :

  returns:

*/
function doCreate(&$db,$args)
{
	$rights = implode("','",array_keys($args->grant));

  $op = new stdClass();
 	$op->role = new tlRole();
	$op->role->rights = tlRight::getAll($db,"WHERE description IN ('{$rights}')");
	$op->role->name = $args->rolename;
	$op->role->description = $args->notes;
	$op->role->dbID = $args->roleid;

	if ($args->roleid == 0)
	{
		$op->action =  "do_add";
		$auditMsg = "audit_role_created";
		$activity = "CREATE";
	}
	else
	{
		$op->action = "updated";
		$auditMsg = "audit_role_saved";
		$activity = "SAVE";
	}
	$result = $op->role->writeToDB($db);
	if ($result >= tl::OK)
		logAuditEvent(TLS($auditMsg,$args->rolename),$activity,$op->role->dbID,"roles");

	$op->userFeedback = getRoleErrorMessage($result);

	return $op;
}
?>

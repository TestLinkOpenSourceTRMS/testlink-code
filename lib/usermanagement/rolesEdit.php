<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: rolesEdit.php,v $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2007/12/27 17:07:00 $ by $Author: franciscom $
 *
 *
 * 20071227 - franciscom - refactoring
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

$args=init_args();

$of = web_editor('notes',$_SESSION['basehref']) ;
$of->Value = null;

$checkboxStatus = null;
$userFeedback = null;
$action = null;
$affectedUsers = null;
$allUsers = null;
$role=null;
$action_type='edit';


switch($args->doAction)
{
   case 'create':
   $action_type='doCreate';
   break;

   case 'edit':
   $action_type='doEdit';
   $role = tlRole::getByID($db,$args->roleid);
   break;
  
     
   case 'doCreate': 
   case 'doEdit': 
   if( has_rights($db,"role_management") )
   {
     $action_type='edit';
     $op = doCreate($db,$args); 
     $role=$op->role;
     $action=$op->action;
     $userFeedback=$op->userFeedback;
   }
   break;
}

if($role)
{
	// build checked attribute for checkboxes
	$checkboxStatus = null;
	if( sizeof($role->rights) > 0 )
	{
	    foreach($role->rights as $key => $right)
	    {
	    	$checkboxStatus[$right->name] = "checked=\"checked\"";
	    }
	}
	
	//get all users which are affected by changing the role definition
	$allUsers = tlUser::getAll($db,null,"id");
	$affectedUsers = getAllUsersWithRole($db,$role->dbID);
	$of->Value = $role->description;
}

$smarty = new TLSmarty();
$smarty->assign('action_type',$action_type);
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
$smarty->assign('checkboxStatus',$checkboxStatus);
$smarty->assign('sqlResult',$userFeedback);
$smarty->assign('allUsers',$allUsers);
$smarty->assign('affectedUsers',$affectedUsers);
$smarty->assign('action',$action);
$smarty->assign('notes', $of->CreateHTML());
$smarty->assign('noRightsRole',TL_ROLES_NONE);
$smarty->display($template_dir . $default_template);
?>

<?php
function init_args()
{
  $_REQUEST = strings_stripSlashes($_REQUEST);
  
  $key2loop=array('doAction' => null,'rolename' => null , 'roleid' => 0, 'notes' => '', 'grant' => null);
  foreach($key2loop as $key => $value)
  {
    $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
  }
  return $args;
}

/*
  function: 

  args:
  
  returns: 

*/
function doCreate(&$db,$args)
{
	$rights = implode("','",array_keys($args->grant));
  $op->role = new tlRole();
  $op->role->rights = tlRight::getAll($db,"WHERE description IN ('{$rights}')");
	$op->role->name = $args->rolename;
	$op->role->description = $args->notes;
	$op->role->dbID = $args->roleid;
	
	$result = $op->role->writeToDB($db);
	$op->userFeedback = getRoleErrorMessage($result);
	$op->action = ($args->roleid == 0) ? "do_add" : "updated";
 
  return $op;
}
 	
	

?>
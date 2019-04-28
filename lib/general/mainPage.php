<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	mainPage.php
 * 
 * Page has two functions: navigation and select Test Plan
 *
 * This file is the first page that the user sees when they log in.
 * Most of the code in it is html but there is some logic that displays
 * based upon the login. 
 * There is also some javascript that handles the form information.
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');

testlinkInitPage($db,TRUE);

$smarty = new TLSmarty();
$tproject_mgr = new testproject($db);
$user = $_SESSION['currentUser'];


$testprojectID = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;

if( isset($_REQUEST['testplan']) ) {
  $testplanID = $_REQUEST['testplan'];

} else {
  $testplanID = isset($_SESSION['testplanID']) ? $_SESSION['testplanID'] : 0;
}
$testplanID = intval($testplanID);


$accessibleItems = $tproject_mgr->get_accessible_for_user($user->dbID,array('output' => 'map_name_with_inactive_mark'));
$tprojectQty = $tproject_mgr->getItemCount();
$userIsBlindFolded = (is_null($accessibleItems) || count($accessibleItems) == 0) && $tprojectQty > 0;

if($userIsBlindFolded) {
  $testprojectID = $testplanID = 0;
  $_SESSION['testprojectTopMenu'] = '';
}

$tplan2check = null;
$currentUser = $_SESSION['currentUser'];
$userID = $currentUser->dbID;

$gui = new stdClass();
$gui->grants = getGrants($db,$user,$userIsBlindFolded);
$gui->hasTestCases = false;

if($gui->grants['view_tc']) { 
	$gui->hasTestCases = $tproject_mgr->count_testcases($testprojectID) > 0 ? 1 : 0;
}

$gui->hasKeywords = false;
if($gui->hasTestCases) {
  $gui->hasKeywords = $tproject_mgr->hasKeywords($testprojectID);
}  


// ----- Test Plan Section --------------------------------
/** 
 * @TODO - franciscom - we must understand if these two calls are really needed,
 * or is enough just call to getAccessibleTestPlans()
 */
$filters = array('plan_status' => ACTIVE);
$gui->num_active_tplans = $tproject_mgr->getActiveTestPlansCount($testprojectID);

// get Test Plans available for the user 
$arrPlans = (array)$currentUser->getAccessibleTestPlans($db,$testprojectID);

if($testplanID > 0) {
	// if this test plan is present on $arrPlans
	//	  OK we will set it on $arrPlans as selected one.
	// else 
	//    need to set test plan on session
	//
	$index=0;
	$found=0;
	$loop2do=count($arrPlans);
	for($idx=0; $idx < $loop2do; $idx++) {
  	if( $arrPlans[$idx]['id'] == $testplanID ) {
     	$found = 1;
     	$index = $idx;
     	break;
    }
  }
  if( $found == 0 ) {
    // update test plan id
    $index = 0;
    $testplanID = $arrPlans[$index]['id'];
  } 

  setSessionTestPlan($arrPlans[$index]);         
  $arrPlans[$index]['selected']=1;
}

$gui->testplanRole = null;
if ($testplanID)  {

  $rd = null; 
  // Role can be configured or inherited
  if( isset($currentUser->tplanRoles[$testplanID]) ) {
    // Configured
    $role = $currentUser->tplanRoles[$testplanID];
    $rd = $role->getDisplayName();
  } else {
    if( config_get('testplan_role_inheritance_mode') == 'global' ) {
      $rd = $currentUser->globalRole->name;
    }
  } 

  if( null != $rd ) {
    $gui->testplanRole = $tlCfg->gui->role_separator_open .$rd . $tlCfg->gui->role_separator_close;
  }
}
$rights2check = array('testplan_execute','testplan_create_build',
                      'testplan_metrics','testplan_planning',
                      'testplan_user_role_assignment',
                      'mgt_testplan_create',
                      'cfield_view', 'cfield_management',
                      'testplan_milestone_overview',
                      'exec_testcases_assigned_to_me',
                      'exec_assign_testcases','exec_ro_access',
                      'testplan_add_remove_platforms',
                      'testplan_update_linked_testcase_versions',
                      'testplan_set_urgent_testcases',
                      'testplan_show_testcases_newest_versions');

foreach($rights2check as $key => $the_right) {
  $gui->grants[$the_right] = $userIsBlindFolded ? 'no' : $currentUser->hasRight($db,$the_right,$testprojectID,$testplanID);
}
                         
$gui->grants['tproject_user_role_assignment'] = "no";
if( $currentUser->hasRight($db,"testproject_user_role_assignment",$testprojectID,-1) == "yes" ||
    $currentUser->hasRight($db,"user_role_assignment",null,-1) == "yes" )
{ 
    $gui->grants['tproject_user_role_assignment'] = "yes";
}


$gui->url = array('metrics_dashboard' => 'lib/results/metricsDashboard.php',
                  'testcase_assignments' => 'lib/testcases/tcAssignedToUser.php');
$gui->launcher = 'lib/general/frmWorkArea.php';
$gui->arrPlans = $arrPlans;                   
$gui->countPlans = count($gui->arrPlans);


$gui->testprojectID = $testprojectID;
$gui->testplanID = $testplanID;

$gui->docs = config_get('userDocOnDesktop') ? getUserDocumentation() : null;

$secCfg = config_get('config_check_warning_frequence');
$gui->securityNotes = '';
if( (strcmp($secCfg, 'ALWAYS') == 0) || 
      (strcmp($secCfg, 'ONCE_FOR_SESSION') == 0 && !isset($_SESSION['getSecurityNotesOnMainPageDone'])) )
{
  $_SESSION['getSecurityNotesOnMainPageDone'] = 1;
  $gui->securityNotes = getSecurityNotes($db);
}  

$gui->opt_requirements = isset($_SESSION['testprojectOptions']->requirementsEnabled) ? 
                         $_SESSION['testprojectOptions']->requirementsEnabled : null; 


$gui->plugins = array();
foreach(array('EVENT_LEFTMENU_TOP',
              'EVENT_LEFTMENU_BOTTOM',
              'EVENT_RIGHTMENU_TOP',
              'EVENT_RIGHTMENU_BOTTOM') as $menu_item) 
{
  # to be compatible with PHP 5.4
  $menu_content = event_signal($menu_item);
  if( !empty($menu_content) )
  {
    $gui->plugins[$menu_item] = $menu_content;
  }
}

$tplKey = 'mainPage';
$tpl = $tplKey . '.tpl';
$tplCfg = config_get('tpl');
if( null !== $tplCfg && isset($tplCfg[$tplKey]) ) {
  $tpl = $tplCfg->$tplKey;
} 

$smarty->assign('gui',$gui);
$smarty->display($tpl);


/**
 * Get User Documentation 
 * based on contribution by Eugenia Drosdezki
 */
function getUserDocumentation()
{
  $target_dir = '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'docs';
  $documents = null;
    
  if ($handle = opendir($target_dir)) 
  {
    while (false !== ($file = readdir($handle))) 
    {
      clearstatcache();
      if (($file != ".") && ($file != "..")) 
      {
        if (is_file($target_dir . DIRECTORY_SEPARATOR . $file))
        {
          $documents[] = $file;
        }    
      }
    }
    closedir($handle);
  }
  return $documents;
}

/**
 *
 */
function getGrants($dbHandler,$user,$forceToNo=false)
{
  // User has test project rights
  // This talks about Default/Global
  //
  // key: more or less verbose
  // value: string present on rights table
  $right2check = array('project_edit' => 'mgt_modify_product',
                       'reqs_view' => "mgt_view_req", 
                       'monitor_req' => "monitor_requirement", 
                       'req_tcase_link_management' => "req_tcase_link_management",
                       'reqs_edit' => "mgt_modify_req",
                       'keywords_view' => "mgt_view_key",
                       'keyword_assignment' => "keyword_assignment",
                       'keywords_edit' => "mgt_modify_key",
                       'platform_management' => "platform_management",
                       'issuetracker_management' => "issuetracker_management",
                       'issuetracker_view' => "issuetracker_view",
                       'codetracker_management' => "codetracker_management",
                       'codetracker_view' => "codetracker_view",
                       'configuration' => "system_configuraton",
                       'cfield_management' => 'cfield_management',
                       'cfield_view' => 'cfield_view',
                       'cfield_assignment' => 'cfield_assignment',
                       'usergroups' => "mgt_view_usergroups",
                       'view_tc' => "mgt_view_tc",
                       'view_testcase_spec' => "mgt_view_tc",
                       'project_inventory_view' => 'project_inventory_view',
                       'project_inventory_management' => 'project_inventory_management',
                       'modify_tc' => 'mgt_modify_tc',
                       'exec_edit_notes' => 'exec_edit_notes', 'exec_delete' => 'exec_delete',
                       'testplan_unlink_executed_testcases' => 'testplan_unlink_executed_testcases',
                       'testproject_delete_executed_testcases' => 'testproject_delete_executed_testcases',
                       'exec_ro_access' => 'exec_ro_access');
 if($forceToNo)
 {
    $grants = array_fill_keys(array_keys($right2check), 'no');
    return $grants;      
 }  
  
  
 $grants['project_edit'] = $user->hasRight($dbHandler,$right2check['project_edit']); 

  /** redirect admin to create testproject if not found */
  if ($grants['project_edit'] && !isset($_SESSION['testprojectID']))
  {
	  redirect($_SESSION['basehref'] . 'lib/project/projectEdit.php?doAction=create');
	  exit();
  }
  
  foreach($right2check as $humankey => $right)
  {
    $grants[$humankey] = $user->hasRight($dbHandler,$right); 
  }


  // check right ONLY if option is enables
  if($_SESSION['testprojectOptions']->inventoryEnabled) {
    $invr = array('project_inventory_view','project_inventory_management');
    foreach($invr as $r){
      $grants[$r] = ($user->hasRight($dbHandler,$r) == 'yes') ? 1 : 0;
    }
  }

  return $grants;  
}

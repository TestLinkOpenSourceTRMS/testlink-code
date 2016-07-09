<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource  navBar.php
 *
 * This file manages the navigation bar. 
 *
 * @internal revisions
 * @since 1.9.15
 *
**/
require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db,('initProject' == 'initProject'));

$tproject_mgr = new testproject($db);
$args = init_args();
$gui = new stdClass();
$gui_cfg = config_get("gui");

$gui->tprojectID = intval(isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0);
$gui->tcasePrefix = '';
$gui->searchSize = 8;
if($gui->tprojectID > 0)
{
  $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($gui->tprojectID) . config_get('testcase_cfg')->glue_character;
  $gui->searchSize = tlStringLen($gui->tcasePrefix) + $gui_cfg->dynamic_quick_tcase_search_input_size;
}
$gui->TestProjects = $tproject_mgr->get_accessible_for_user($args->user->dbID,
                                                            array('output' => 'map_name_with_inactive_mark',
                                                            	  'field_set' => $tlCfg->gui->tprojects_combo_format,
                                                                  'order_by' => $tlCfg->gui->tprojects_combo_order_by));



$gui->TestProjectCount = sizeof($gui->TestProjects);
$gui->TestPlanCount = 0; 

$tprojectQty = $tproject_mgr->getItemCount();
if($gui->TestProjectCount == 0 && $tprojectQty > 0)
{
  // User rights configurations does not allow access to ANY test project
  $_SESSION['testprojectTopMenu'] = '';
  $gui->tprojectID = 0;
}

if($gui->tprojectID)
{
	$testPlanSet = $args->user->getAccessibleTestPlans($db,$gui->tprojectID);
  $gui->TestPlanCount = sizeof($testPlanSet);

	$tplanID = isset($_SESSION['testplanID']) ? intval($_SESSION['testplanID']) : null;
  if( !is_null($tplanID) )
  {
    // Need to set this info on session with first Test Plan from $testPlanSet
		// if this test plan is present on $testPlanSet
		//	  OK we will set it on $testPlanSet as selected one.
		// else 
		//    need to set test plan on session
		//
		$index=0;
		$testPlanFound=0;
		$loop2do=count($testPlanSet);
		for($idx=0; $idx < $loop2do; $idx++)
		{
    	if( $testPlanSet[$idx]['id'] == $tplanID )
    	{
        $testPlanFound = 1;
    	  $index = $idx;
    	  $break;
    	}
    }
    if( $testPlanFound == 0 )
    {
			$tplanID = $testPlanSet[0]['id'];
			setSessionTestPlan($testPlanSet[0]);     	
    } 
    $testPlanSet[$index]['selected']=1;
  }
}	

if ($gui->tprojectID && isset($args->user->tprojectRoles[$gui->tprojectID]))
{
	// test project specific role applied
	$role = $args->user->tprojectRoles[$gui->tprojectID];
	$testprojectRole = $role->getDisplayName();
}
else
{
	// general role applied
	$testprojectRole = $args->user->globalRole->getDisplayName();
}	
$gui->whoami = $args->user->getDisplayName() . ' ' . $tlCfg->gui->role_separator_open . 
	             $testprojectRole . $tlCfg->gui->role_separator_close;
                   

// only when the user has changed project using the combo the _GET has this key.
// Use this clue to launch a refresh of other frames present on the screen
// using the onload HTML body attribute
$gui->updateMainPage = 0;
if ($args->testproject)
{
  // set test project ID for the next session
	$gui->updateMainPage = is_null($args->caller);
  $cookie_path = config_get('cookie_path');
	setcookie('TL_lastTestProjectForUserID_'. $args->user->dbID, $args->testproject, TL_COOKIE_KEEPTIME, $cookie_path);
}

$gui->grants = getGrants($db,$args->user);
$gui->viewer = $args->viewer;

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display('navBar.tpl');


/**
 * 
 */
function getGrants(&$db,&$userObj)
{
  $grants = new stdClass();
  $grants->view_testcase_spec = $userObj->hasRight($db,"mgt_view_tc");
  return $grants;  
}

function init_args()
{
	$iParams = array("testproject" => array(tlInputParameter::INT_N),
                   "caller" => array(tlInputParameter::STRING_N,1,6),
                   "viewer" => array(tlInputParameter::STRING_N, 0, 3)
                  );
	$args = new stdClass();
	$pParams = G_PARAMS($iParams,$args);

  if( is_null($args->viewer) || $args->viewer == '' )
  {
    $args->viewer = isset($_SESSION['viewer']) ? $_SESSION['viewer'] : null;
  }  

  $args->user = $_SESSION['currentUser'];
	return $args;
}

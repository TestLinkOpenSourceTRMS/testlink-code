<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: navBar.php,v $
 *
 * @version $Revision: 1.48 $
 * @modified $Date: 2009/05/18 20:22:10 $ $Author: schlundus $
 *
 * This file manages the navigation bar. 
 *
 * rev :
 *       20090404 - franciscom - adjust size of test case input using len of tcase prefix
 *       20080504 - franciscom - add code based on contribution by Eugenia Drosdezki
 *                               get files present on docs directory, and pass to template.
 *
 *       20070505 - franciscom - use of role_separator configuration
 *
**/
require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db,true);

$tproject_mgr = new testproject($db);
$args = init_args();
// $args = init_args();
$gui = new stdClass();

$gui->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$gui->tcasePrefix = '';
$gui->searchSize = 8; // magic default
if($gui->tprojectID > 0)
{
    $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($gui->tprojectID) . 
                        config_get('testcase_cfg')->glue_character;
                        
    $gui->searchSize = tlStringLen($gui->tcasePrefix) + 7; // magic again
}

$user = $_SESSION['currentUser'];
$userID = $user->dbID;

$gui->TestProjects = $tproject_mgr->get_accessible_for_user($userID,'map',$tlCfg->gui->tprojects_combo_order_by);
$gui->TestProjectCount = sizeof($gui->TestProjects);
$gui->TestPlanCount = getNumberOfAccessibleTestPlans($db,$gui->tprojectID);
$gui->docs = getUserDocumentation();

if ($gui->tprojectID)
{
	$tplanID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : null;
	getAccessibleTestPlans($db,$gui->tprojectID,$userID,$tplanID);
}	

if ($gui->tprojectID && isset($user->tprojectRoles[$gui->tprojectID]))
{
	// test project specific role applied
	$role = $user->tprojectRoles[$gui->tprojectID];
	$testprojectRole = $role->name;
}
else
{
	// general role applied
	$testprojectRole = $user->globalRole->name;
}	
$gui->whoami = $user->getDisplayName() . ' ' . $tlCfg->gui->role_separator_open . 
	            $testprojectRole . $tlCfg->gui->role_separator_close;
                   

// only when the user has changed the product using the combo
// the _GET has this key.
// Use this clue to launch a refresh of other frames present on the screen
// using the onload HTML body attribute
$gui->updateMainPage = 0;
if ($args->testproject)
{
	$gui->updateMainPage = 1;
	// set test project ID for the next session
	setcookie('TL_lastTestProjectForUserID_'. $userID, $args->testproject, TL_COOKIE_KEEPTIME, '/');
}

$gui->grants = getGrants($db);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display('navBar.tpl');


/*
  function: 

  args :
  
  returns: 

*/
function getGrants($dbHandler)
{
    $grants = new stdClass();
    $grants->view_events_mgmt = has_rights($db,"mgt_view_events");
    $grants->view_testcases = has_rights($db,"mgt_view_tc");
    $grants->view_testcase_spec = has_rights($db,"mgt_view_tc");
    $grants->testplan_execute = has_rights($db,"testplan_execute");
    $grants->testplan_metrics = has_rights($db,"testplan_metrics");
    $grants->user_mgmt = has_rights($db,"mgt_users");
    
    return $grants;  
}

/*
  function: getUserDocumentation
            based on contribution by Eugenia Drosdezki
  args :
  
  returns: 

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

function init_args()
{
	$iParams = array(
			"testproject" => array(tlInputParameter::INT_N),
		);

	$args = new stdClass();
	$pParams = G_PARAMS($iParams,$args);

	return $args;
}
?>

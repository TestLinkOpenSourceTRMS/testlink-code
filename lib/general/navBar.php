<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: navBar.php,v $
 *
 * @version $Revision: 1.39 $
 * @modified $Date: 2008/05/05 09:11:43 $ $Author: franciscom $
 *
 * This file manages the navigation bar. 
 *
 * rev :
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

$guiCfg = config_get('gui');

$gui = new stdClass();
$gui->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$user = $_SESSION['currentUser'];
$userID = $user->dbID;

$order_by = $tlCfg->gui->tprojects_combo_order_by;
$gui->TestProjects = $tproject_mgr->get_accessible_for_user($userID,'map', $order_by);
$gui->TestProjectCount = sizeof($gui->TestProjects);
$gui->TestPlanCount = getNumberOfAccessibleTestPlans($db,$gui->tprojectID, $_SESSION['filter_tp_by_product'],null);
$gui->docs = getUserDocumentation();
$gui->logo =$guiCfg->html_logo;


if ($gui->tprojectID)
{
  $tplanID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : null;
	getAccessibleTestPlans($db,$gui->tprojectID,$userID,1,$tplanID);
}	

if ($gui->tprojectID && isset($user->tprojectRoles[$gui->tprojectID]))
{
	// test project specific role applied
	$role = $user->tprojectRoles[$gui->currentTprojectID];
	$testprojectRole = $role->name;
}
else
{
	// general role applied
	$testprojectRole = $user->globalRole->name;
}	
$gui->whoami =$user->getDisplayName() . ' ' . $guiCfg->role_separator_open . 
	            $testprojectRole . $guiCfg->role_separator_close;
                   

// only when the user has changed the product using the combo
// the _GET has this key.
// Use this clue to launch a refresh of other frames present on the screen
// using the onload HTML body attribute
$gui->updateMainPage = 0;
if (isset($_GET['testproject']))
{
	$gui->updateMainPage = 1;
	// set test project ID for the next session
	setcookie('lastProductForUser'. $userID, $_GET['testproject'], TL_COOKIE_KEEPTIME, '/');
}

$gui->grants=getGrants($db);

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
    $target_dir='..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'docs';
    $documents=null;
    
    if ($handle = opendir($target_dir)) 
    {
        while (false !== ($file = readdir($handle))) 
        {
            clearstatcache();
            if ( ($file != ".") && ($file != "..")  ) 
            {
               if (is_file($target_dir . DIRECTORY_SEPARATOR . $file) )
               {
                   $documents[] = $file;
               }    
            }
        }
        closedir($handle);
    }
    return $documents;
}
?>

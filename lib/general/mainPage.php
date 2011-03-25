<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	mainPage.php
 * @author Martin Havlat
 * 
 * Page has two functions: navigation and select Test Plan
 *
 * This file is the first page that the user sees when they log in.
 * Most of the code in it is html but there is some logic that displays
 * based upon the login. 
 * There is also some javascript that handles the form information.
 *
 * @internal revisions
 * 20110325 - franciscom - BUGID 4062
 **/

require_once('../../config.inc.php');
require_once('common.php');
if(function_exists('memory_get_usage') && function_exists('memory_get_peak_usage'))
{
    tlog("mainPage.php: Memory after common.php> Usage: ".memory_get_usage(), 'DEBUG');
}

testlinkInitPage($db,TRUE);

$smarty = new TLSmarty();
$tproject_mgr = new testproject($db);

$testprojectID = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
$testplanID = isset($_SESSION['testplanID']) ? intval($_SESSION['testplanID']) : 0;

$tplan2check = null;
$currentUser = $_SESSION['currentUser'];
$userID = $currentUser->dbID;

$gui = new stdClass();
$gui->grants=array();

// User has test project rights
$gui->grants['project_edit'] = $currentUser->hasRight($db,'mgt_modify_product'); 

// ----------------------------------------------------------------------
/** redirect admin to create testproject if not found */
if ($gui->grants['project_edit'] && !isset($_SESSION['testprojectID']))
{
	tLog('No project found: Assume a new installation and redirect to create it','WARNING'); 
	redirect($_SESSION['basehref'] . 'lib/project/projectEdit.php?doAction=create');
	exit();
}
// ----------------------------------------------------------------------

$gui->grants['reqs_view'] = $currentUser->hasRight($db,"mgt_view_req"); 
$gui->grants['reqs_edit'] = $currentUser->hasRight($db,"mgt_modify_req"); 
$gui->grants['keywords_view'] = $currentUser->hasRight($db,"mgt_view_key");
$gui->grants['keywords_edit'] = $currentUser->hasRight($db,"mgt_modify_key");
$gui->grants['platform_management'] = $currentUser->hasRight($db,"platform_management");
$gui->grants['configuration'] = $currentUser->hasRight($db,"system_configuraton");
$gui->grants['usergroups'] = $currentUser->hasRight($db,"mgt_view_usergroups");
$gui->grants['view_tc'] = $currentUser->hasRight($db,"mgt_view_tc");
$gui->grants['project_inventory_view'] = ($_SESSION['testprojectOptions']->inventoryEnabled 
	&& ($currentUser->hasRight($db,"project_inventory_view") == 'yes')) ? 1 : 0;
$gui->grants['modify_tc'] = null; 
$gui->hasTestCases = false;

if($gui->grants['view_tc'])
{ 
    $gui->grants['modify_tc'] = $currentUser->hasRight($db,"mgt_modify_tc"); 
	$gui->hasTestCases = $tproject_mgr->count_testcases($testprojectID) > 0 ? 1 : 0;
}

$smarty->assign('opt_requirements', isset($_SESSION['testprojectOptions']->requirementsEnabled) 
		? $_SESSION['testprojectOptions']->requirementsEnabled : null); 


// ----- Test Plan Section --------------------------------------------------------------
/** 
 * @TODO - franciscom - we must understand if these two calls are really needed,
 * or is enough just call to getAccessibleTestPlans()
 */
$filters = array('plan_status' => ACTIVE);
$gui->num_active_tplans = sizeof($tproject_mgr->get_all_testplans($testprojectID,$filters));

// get Test Plans available for the user 
$arrPlans = $currentUser->getAccessibleTestPlans($db,$testprojectID);

if($testplanID > 0)
{
	// if this test plan is present on $arrPlans
	//	  OK we will set it on $arrPlans as selected one.
	// else 
	//    need to set test plan on session
	//
	$index=0;
	$found=0;
	$loop2do=count($arrPlans);
	for($idx=0; $idx < $loop2do; $idx++)
	{
    	if( $arrPlans[$idx]['id'] == $testplanID )
    	{
        	$found = 1;
        	$index = $idx;
        	$break;
        }
    }
    if( $found == 0 )
    {
        // update test plan id
		$testplanID = $arrPlans[0]['id'];
		setSessionTestPlan($arrPlans[0]);     	
    } 
    $arrPlans[$index]['selected']=1;
}

$gui->testplanRole = null;
if ($testplanID && isset($currentUser->tplanRoles[$testplanID]))
{
	$role = $currentUser->tplanRoles[$testplanID];
	$gui->testplanRole = $tlCfg->gui->role_separator_open . $role->getDisplayName() . $tlCfg->gui->role_separator_close;
}

$rights2check = array('testplan_execute','testplan_create_build','testplan_metrics','testplan_planning',
                      'testplan_user_role_assignment','mgt_testplan_create','mgt_users',
                      'cfield_view', 'cfield_management');
foreach($rights2check as $key => $the_right)
{
	// trying to remove Evil global coupling
    // $gui->grants[$the_right] = $currentUser->hasRight($db,$the_right);
    $gui->grants[$the_right] = $currentUser->hasRight($db,$the_right,$testprojectID,$testplanID);
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
$gui->securityNotes = getSecurityNotes($db);
$gui->testprojectID = $testprojectID;
$gui->testplanID = $testplanID;
$gui->docs = getUserDocumentation();

$smarty->assign('gui',$gui);
$smarty->display('mainPage.tpl');


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

?>
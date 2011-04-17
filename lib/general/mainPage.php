<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	mainPage.php
 * @package 	TestLink
 * @copyright 	2005,2011 TestLink community 
 * @link 		http://www.teamst.org/index.php
 * @author 		Martin Havlat
 * 
 * Page has two functions: navigation and select Test Plan
 *
 * This file is the first page that the user sees when they log in.
 * Most of the code in it is html but there is some logic that displays
 * based upon the login. 
 * There is also some javascript that handles the form information.
 *
 * @internal revisions
 * 20110417 - franciscom - BUGID 4429: Code refactoring to remove global coupling as much as possible
 * 20110401 - franciscom - BUGID 3615 - right to allow ONLY MANAGEMENT of requirements link to testcases
 * 20110325 - franciscom - BUGID 4062
 **/

require_once('../../config.inc.php');
require_once('common.php');
if(function_exists('memory_get_usage') && function_exists('memory_get_peak_usage'))
{
    tlog("mainPage.php: Memory after common.php> Usage: ".memory_get_usage(), 'DEBUG');
}

testlinkInitPage($db,TL_UPDATE_ENVIRONMENT);

$smarty = new TLSmarty();
$tproject_mgr = new testproject($db);
$tprojectQty = $tproject_mgr->getTotalCount();
$currentUser = $_SESSION['currentUser'];
$userID = $currentUser->dbID;

$gui = new stdClass();
$gui->grants=array();
$gui->testprojectID = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
$gui->testplanID = isset($_SESSION['testplanID']) ? intval($_SESSION['testplanID']) : 0;


// User has test project rights ?
$gui->grants['project_edit'] = $currentUser->hasRight($db,'mgt_modify_product',$gui->testprojectID,$gui->testplanID); 

// ----------------------------------------------------------------------
/** redirect admin to create testproject if not found */
if ($gui->grants['project_edit'] && ($tprojectQty == 0))
{
	tLog('No project found: Assume a new installation and redirect to create it','WARNING'); 
	redirect($_SESSION['basehref'] . 'lib/project/projectEdit.php?doAction=create');
	exit();
}
// ----------------------------------------------------------------------

$gui->grants = array_merge($gui->grants, initGrants($db,$currentUser,$gui->testprojectID,$gui->testplanID));

$gui->grants['project_inventory_view'] = ($_SESSION['testprojectOptions']->inventoryEnabled 
	&& ($currentUser->hasRight($db,"project_inventory_view",$gui->testprojectID,$gui->testplanID) == 'yes')) ? 1 : 0;
$gui->grants['modify_tc'] = null; 
$gui->hasTestCases = false;

if($gui->grants['view_tc'])
{ 
    // $gui->grants['modify_tc'] = $currentUser->hasRight($db,"mgt_modify_tc",$gui->testprojectID,$gui->testplanID); 
	$gui->hasTestCases = $tproject_mgr->count_testcases($gui->testprojectID) > 0 ? 1 : 0;
}

$smarty->assign('opt_requirements', isset($_SESSION['testprojectOptions']->requirementsEnabled) 
		? $_SESSION['testprojectOptions']->requirementsEnabled : null); 


// ----- Test Plan Section --------------------------------------------------------------
/** 
 * @TODO - franciscom - we must understand if these two calls are really needed,
 * or is enough just call to getAccessibleTestPlans()
 */
$filters = array('plan_status' => ACTIVE);
$gui->num_active_tplans = sizeof($tproject_mgr->get_all_testplans($gui->testprojectID,$filters));

// get Test Plans available for the user 
$arrPlans = $currentUser->getAccessibleTestPlans($db,$gui->testprojectID);

if($gui->testplanID > 0)
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
    	if( $arrPlans[$idx]['id'] == $gui->testplanID )
    	{
        	$found = 1;
        	$index = $idx;
        	$break;
        }
    }
    if( $found == 0 )
    {
        // update test plan id
		$gui->testplanID = $arrPlans[0]['id'];
		setSessionTestPlan($arrPlans[0]);     	
    } 
    $arrPlans[$index]['selected']=1;
}


$gui->testplanRole = null;
if ($gui->testplanID && isset($currentUser->tplanRoles[$gui->testplanID]))
{
	$role = $currentUser->tplanRoles[$gui->testplanID];
	$gui->testplanRole = $tlCfg->gui->role_separator_open . $role->getDisplayName() . $tlCfg->gui->role_separator_close;
}

$rights2check = array('testplan_execute','testplan_create_build','testplan_metrics','testplan_planning',
                      'testplan_user_role_assignment','mgt_testplan_create','mgt_users',
                      'cfield_view', 'cfield_management');
foreach($rights2check as $key => $the_right)
{
	// trying to remove Evil global coupling
    $gui->grants[$the_right] = $currentUser->hasRight($db,$the_right,$gui->testprojectID,$gui->testplanID);
}                         

$gui->grants['tproject_user_role_assignment'] = "no";
if( $currentUser->hasRight($db,"testproject_user_role_assignment",$gui->testprojectID,-1) == "yes" ||
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


function initGrants(&$dbHandler,&$userObj,$tprojectID,$testplanID)
{
	$grantKeys = array(	'reqs_view' => "mgt_view_req", 'reqs_edit' => "mgt_modify_req",
						'req_tcase_assignment' => 'req_tcase_assignment',
						'keywords_view'=> "mgt_view_key",'keywords_edit' => "mgt_modify_key",
						'platform_management' => 'platform_management',
						'configuration' => "system_configuraton",
						'usergroups' => "mgt_view_usergroups",
						'view_tc' => "mgt_view_tc", 'modify_tc' => 'mgt_modify_tc');
					
	$grants = array();		
	foreach($grantKeys as $key => $right)
	{
		$grants[$key] = $userObj->hasRight($dbHandler,$right,$tprojectID,$testplanID);  
	}
	return $grants;
}

?>
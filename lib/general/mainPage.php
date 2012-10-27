<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	mainPage.php
 * @package 	  TestLink
 * @copyright   2005,2012 TestLink community 
 * @link        http://www.teamst.org/index.php
 * @author 	    Martin Havlat
 * 
 * Page has two functions: navigation and select Test Plan
 *
 * This file is the first page that the user sees when they log in.
 * Most of the code in it is html but there is some logic that displays
 * based upon the login. 
 * There is also some javascript that handles the form information.
 *
 * @internal revisions
 **/

require_once('../../config.inc.php');
require_once('common.php');
if(function_exists('memory_get_usage') && function_exists('memory_get_peak_usage'))
{
    tlog("mainPage.php: Memory after common.php> Usage: ".memory_get_usage(), 'DEBUG');
}

testlinkInitPage($db);

$smarty = new TLSmarty();
$tproject_mgr = new testproject($db);
$tprojectQty = $tproject_mgr->getTotalCount();
$currentUser = $_SESSION['currentUser'];
$userID = $currentUser->dbID;

$gui = new stdClass();
$gui->grants=array();
$gui->testprojectID = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
$gui->testplanID = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;
if($gui->testplanID == 0)
{
	$gui->testplanID = isset($_REQUEST['testplan']) ? intval($_REQUEST['testplan']) : 0;
}

$gui->tprojectOptions = new stdClass();
$gui->tprojectOptions->inventoryEnabled = 0;
$gui->tprojectOptions->requirementsEnabled = 0;
if($gui->testprojectID > 0)
{
	$dummy = $tproject_mgr->get_by_id($gui->testprojectID);
	$gui->tprojectOptions = $dummy['opt'];
}


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

$gui->grants['project_inventory_view'] = ($gui->tprojectOptions->inventoryEnabled && 
                                         ($currentUser->hasRight($db,"project_inventory_view",
										                                             $gui->testprojectID,$gui->testplanID) == 'yes')) ? 1 : 0;
$gui->hasTestCases = false;

if($gui->grants['view_tc'])
{ 
	$gui->hasTestCases = $tproject_mgr->count_testcases($gui->testprojectID) > 0 ? 1 : 0;
}

$smarty->assign('opt_requirements', 
				isset($gui->tprojectOptions->requirementsEnabled) ? $gui->tprojectOptions->requirementsEnabled : null); 


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



/**
 */
function initEnvironment(&$dbHandler,&$userObj)
{
	$argsObj = new stdClass();
	$guiObj = new stdClass();
	$cfg = config_get("gui");
	$tprojectMgr = new testproject($dbHandler);
	
	$_REQUEST=strings_stripSlashes($_REQUEST);
	$iParams = array("tprojectIDNavBar" => array(tlInputParameter::INT_N),
					         "tproject_id" => array(tlInputParameter::INT_N),
					         "tplan_id" => array(tlInputParameter::INT_N));
	R_PARAMS($iParams,$argsObj);
	
	$guiObj->tcasePrefix = '';
	$guiObj->tplanCount = 0; 
	$guiObj->tprojectSet = $tprojectMgr->get_accessible_for_user($userObj->dbID);
	$guiObj->tprojectCount = sizeof($guiObj->tprojectSet);

	// -----------------------------------------------------------------------------------------------------
	// Important Logic 
	// -----------------------------------------------------------------------------------------------------
	$argsObj->tprojectIDNavBar = intval($argsObj->tprojectIDNavBar);
	$argsObj->tproject_id = intval($argsObj->tproject_id);
	$argsObj->tproject_id = ($argsObj->tproject_id > 0) ? $argsObj->tproject_id : $argsObj->tprojectIDNavBar;
	if($argsObj->tproject_id == 0)
	{
		$argsObj->tproject_id = key($guiObj->tprojectSet);
	} 
	$guiObj->tprojectID = $argsObj->tproject_id;
	$guiObj->tprojectOptions = null;
	$guiObj->tprojectTopMenu = null;
	if($guiObj->tprojectID > 0)
	{
		$dummy = $tprojectMgr->get_by_id($guiObj->tprojectID);
		$guiObj->tprojectOptions = $dummy['opt'];
		
	} 
	// -----------------------------------------------------------------------------------------------------

	$argsObj->tplan_id = intval($argsObj->tplan_id);
	$guiObj->tplanID = $argsObj->tplan_id;
	
	
	// Julian: left magic here - do think this value will never be used as a project with a prefix
	//         has to be created after first login -> searchSize should be set dynamically.
	//         If any reviewer agrees on that feel free to change it.
	$guiObj->searchSize = 8;
	if($guiObj->tprojectID > 0)
	{
	  $guiObj->tcasePrefix = $tprojectMgr->getTestCasePrefix($guiObj->tprojectID) . 
	                         config_get('testcase_cfg')->glue_character;
	  $guiObj->searchSize = tlStringLen($guiObj->tcasePrefix) + $cfg->dynamic_quick_tcase_search_input_size;

    $guiObj->tplanSet = $userObj->getAccessibleTestPlans($dbHandler,$guiObj->tprojectID);
	  $guiObj->tplanCount = sizeof($guiObj->tplanSet);
	  if( $guiObj->tplanID == 0 )
	  {
	    $guiObj->tplanID = $guiObj->tplanSet[0]['id'];
	    $guiObj->tplanSet[0]['selected']=1;
	  }
	}	
	
	return array($argsObj,$guiObj);
}

function initGrants(&$dbHandler,&$userObj,$tprojectID,$testplanID)
{
	$grantKeys = array('reqs_view' => "mgt_view_req", 'reqs_edit' => "mgt_modify_req",
						         'req_tcase_assignment' => 'req_tcase_assignment',
        						 'keywords_view'=> "mgt_view_key",'keywords_edit' => "mgt_modify_key",
        						 'keywords_assignment' => 'keyword_assignment',
        						 'platform_management' => 'platform_management',
        						 'configuration' => "system_configuraton",
        						 'usergroups' => "mgt_view_usergroups",
        						 'view_tc' => "mgt_view_tc", 'modify_tc' => 'mgt_modify_tc',
        						 'issuetracker_management' => 'issuetracker_management',
        						 'exec_edit_notes' => 'exec_edit_notes', 'exec_delete' => 'exec_delete');
					
	$grants = array();		
	foreach($grantKeys as $key => $right)
	{
		$grants[$key] = $userObj->hasRight($dbHandler,$right,$tprojectID,$testplanID);
	}
	return $grants;
}

?>
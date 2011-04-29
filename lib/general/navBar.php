<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	navBar.php
 * @package 	TestLink
 * @copyright 	2006-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * This file manages the navigation bar. 
 *
 * @internal revisions
 * 20110429 - franciscom - refactoring to remove global coupling
 * 20101028 - Julian - BUGID 3950 - use config parameter to dynamically set input size of quick tc search
 *
**/
require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db,TL_UPDATE_ENVIRONMENT);

$user = $_SESSION['currentUser'];
$userID = $user->dbID;
list($args,$gui) = initEnvironment($db,$user);

if ($gui->tprojectID && isset($user->tprojectRoles[$gui->tprojectID]))
{
	// test project specific role applied
	$role = $user->tprojectRoles[$gui->tprojectID];
	$testprojectRole = $role->getDisplayName();
}
else
{
	// general role applied
	$testprojectRole = $user->globalRole->getDisplayName();
}	
$gui->whoami = $user->getDisplayName() . ' ' . $tlCfg->gui->role_separator_open . 
	           $testprojectRole . $tlCfg->gui->role_separator_close;
                   

// only when the user has changed project using the combo the _GET has this key.
// Use this clue to launch a refresh of other frames present on the screen
// using the onload HTML body attribute
$gui->updateMainPage = 0;
if ($args->tprojectIDNavBar > 0)
{
	$gui->updateMainPage = 1;
	setcookie('TL_lastTestProjectForUserID_'. $userID, $args->tprojectIDNavBar, TL_COOKIE_KEEPTIME, '/');
}

$gui->grants = getGrants($db,$user,$gui->tprojectID,$gui->tplanID);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display('navBar.tpl');


/**
 */
function getGrants(&$dbHandler,&$userObj,$tproject_id,$tplan_id)
{
    $grants = new stdClass();
    $grants->view_testcase_spec = $userObj->hasRight($dbHandler,"mgt_view_tc",$tproject_id,$tplan_id);
    return $grants;  
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

	$guiObj->tprojectSet = $tprojectMgr->get_accessible_for_user($userObj->dbID,'map',$cfg->tprojects_combo_order_by);
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
?>

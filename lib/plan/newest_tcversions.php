<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	newest_tcversions.php
 * @package 	TestLink
 * @author 		Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright 	2005-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * @internal revisions:
 * 20110425 - franciscom - 	BUGID 4429: Code refactoring to remove global coupling as much as possible
 *							BUGID 4339: Working with two different projects within one Browser (same session) 
 *							is not possible without heavy side-effects
 *
 *
 */         
require('../../config.inc.php');
require_once("common.php");

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$tplan_mgr = new testplan($db); 
$tproject_mgr = new testproject($db); 
$args = init_args($tproject_mgr);
checkRights($db,$_SESSION['currentUser'],$args);


$gui = initializeGui($db,$args,$_SESSION['currentUser'],$tproject_mgr);

if($gui->doIt)
{
    if( ($qty_newest = count($gui->testcases)) > 0)
    {
        $gui->show_details = 1;
    
        // get path
        $tcaseSet=array_keys($gui->testcases);
        $path_info=$tproject_mgr->tree_manager->get_full_path_verbose($tcaseSet);
        foreach($gui->testcases as $tcase_id => $value)
        {
            $path=$path_info[$tcase_id];
            unset($path[0]);
            $path[]='';
            $gui->testcases[$tcase_id]['path']=implode(' / ',$path);
        }
    }
    else
    {
        $gui->user_feedback = lang_get('no_newest_version_of_linked_tcversions');  
    }
} 
else
{
    $gui->user_feedback = lang_get('no_linked_tcversions');  
}


$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



/**
 * init_args
 *
 */
function init_args(&$tprojectMgr)
{
	$_REQUEST = strings_stripSlashes($_REQUEST);
    
    $args = new stdClass();
    $args->user_id = $_SESSION['userID'];

    $args->id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
    $args->version_id = isset($_REQUEST['version_id']) ? $_REQUEST['version_id'] : 0;
    $args->level = isset($_REQUEST['level']) ? $_REQUEST['level'] : null;
    
    // Can be a list (string with , (comma) has item separator), 
    $args->keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;
    
    $args->tproject_name = '';
	$args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
	if( $args->tproject_id > 0 )
	{
		$dummy = $tprojectMgr->tree_manager->get_node_hierarchy_info($args->tproject_id);
		$args->tproject_name = $dummy['name'];
	}

    
    $args->tplan_id = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;
    

    return $args;  
}


function initializeGui(&$dbHandler,&$argsObj,&$userObj,&$tprojectMgr)
{
	$tcaseCfg = config_get('testcase_cfg');

	$guiObj = new stdClass();
	$guiObj->can_manage_testplans=$userObj->hasRight($dbHandler,"mgt_testplan_create",
													 $argsObj->tproject_id,$argsObj->tplan_id);
	$guiObj->show_details = 0;
	$guiObj->user_feedback = '';
	$guiObj->tcasePrefix = $tprojectMgr->getTestCasePrefix($argsObj->tproject_id) . $tcaseCfg->glue_character;
	
	$guiObj->tproject_name = $argsObj->tproject_name;

	$tplanMgr = new testplan($dbHandler);
	$tplan_info = $tplanMgr->get_by_id($argsObj->tplan_id);
	$guiObj->tplan_name = $tplan_info['name'];
	$guiObj->tplan_id = $argsObj->tplan_id;
	
	$guiObj->testcases = $tplanMgr->get_linked_and_newest_tcversions($argsObj->tplan_id);

	$linked_tcases = $tplanMgr->get_linked_items_id($argsObj->tplan_id);
	$guiObj->doIt = (count($linked_tcases) > 0);

	$guiObj->tplans = array();
	$tplans = $userObj->getAccessibleTestPlans($dbHandler,$argsObj->tproject_id);
	foreach($tplans as $key => $value)
	{
		$guiObj->tplans[$value['id']] = $value['name'];
	}


	return $guiObj;
}


/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('testplan_planning'),'and');
}

?>
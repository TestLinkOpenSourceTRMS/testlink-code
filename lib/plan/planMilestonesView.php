<?php

/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * Define urgency of a Test Suite. 
 * It requires "prioritization" feature enabled.
 *
 * @filesource	planMilestonesView.php
 * @package 	TestLink
 * @author 		Francisco Mancardi
 * @copyright 	2003-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 * 
 * @internal Revisions:
 *	20100427 - franciscom - added standard documentation file header
 **/

require_once("../../config.inc.php");
require_once("common.php");
require_once("testplan.class.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$args = init_args($db);
checkRights($db,$_SESSION['currentUser'],$args);

$gui = initialize_gui($db,$args);
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/*
  function: 

  args :
  
  returns: 

*/
function init_args(&$dbHandler)
{
	$args = new stdClass();

	$treeMgr = new tree($dbHandler);
	$args->tproject_name = '';
	$args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
	if( $args->tproject_id > 0 )
	{
	    $info = $treeMgr->get_node_hierarchy_info($args->tproject_id);
	    $args->tproject_name = $info['name'];
  	}
	
	$args->tplan_name = '';
	$args->tplan_id = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;
	if( $args->tplan_id > 0 )
	{
	    $info = $treeMgr->get_node_hierarchy_info($args->tplan_id);
	    $args->tplan_name = $info['name'];
  	}
	return $args;
}


/*
  function: initialize_gui

  args : -

  returns:

*/
function initialize_gui(&$dbHandler,&$userObj,&$argsObj)
{
    $manager = new milestone_mgr($dbHandler);
    $gui = new stdClass();
    
    $gui->user_feedback = null;
    $gui->main_descr = lang_get('title_milestones') . " " . $argsObj->tplan_name;
    $gui->action_descr = null;

    $gui->tplan_name = $argsObj->tplan_name;
    $gui->tplan_id = $argsObj->tplan_id;
    $gui->tproject_name = $argsObj->tproject_name;
    $gui->tproject_id = $argsObj->tproject_id;

	$gui->items = $manager->get_all_by_testplan($argsObj->tplan_id);
	
	$gui->grants = new stdClass();
    $gui->grants->milestone_mgmt = $userObj->hasRight($dbHandler,"testplan_planning",
     							   $gui->tproject_id,$gui->tplan_id);
    
	$gui->grants->mgt_view_events = $userObj->hasRight($dbHandler,"mgt_view_events",
	 												   $gui->tproject_id,$gui->tplan_id);
	return $gui;
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
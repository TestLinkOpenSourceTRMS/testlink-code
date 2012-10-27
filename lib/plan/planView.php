<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	planView.php
 * @package 	TestLink
 * @author 		TestLink community
 * @copyright 	2007-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
*/
require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();

$args = init_args($db);
checkRights($db,$_SESSION['currentUser'],$args);

$gui = initializeGui($args);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * init_args
 *
 */
function init_args(&$dbHandler)
{
  $args = new stdClass();
	$args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0 ;
	$args->tplans = null;
	if($args->tproject_id >0)
	{
		$tprojectMgr = new testproject($dbHandler);
		$dummy = $tprojectMgr->tree_manager->get_node_hierarchy_info($args->tproject_id);
		$args->tproject_name = $dummy['name'];
		$args->tplans = $tprojectMgr->get_all_testplans($args->tproject_id);
	}
  
  $args->grants = new stdClass();
  $args->grants->testplan_create = $_SESSION['currentUser']->hasRight($dbHandler,"mgt_testplan_create",$args->tproject_id);
  
  return $args;
}

function initializeGui($argsObj)
{
  $gui = new stdClass();
  $gui->tproject_id = $argsObj->tproject_id;
  $gui->goback_url = "lib/plan/planView.php?tproject_id={$argsObj->tproject_id}";
  $gui->user_feedback = '';
  $gui->main_descr = lang_get('testplan_title_tp_management'). " - " . 
                     lang_get('testproject') . ' ' . $argsObj->tproject_name;

  $gui->tplans = $argsObj->tplans;
  $gui->grants = $argsObj->grants;
  
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
	checkSecurityClearance($db,$userObj,$env,array('mgt_testplan_create'),'and');
}

?>
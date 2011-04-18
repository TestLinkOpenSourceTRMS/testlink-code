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
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$args=init_args($db);

new dBug($args);

$gui = new stdClass();
$gui->tproject_id = $args->tproject_id;
$gui->user_feedback = '';
$gui->grants = new stdClass();
$gui->grants->testplan_create=has_rights($db,"mgt_testplan_create");
$gui->main_descr = lang_get('testplan_title_tp_management'). " - " . 
                   lang_get('testproject') . ' ' . $args->tproject_name;

$gui->tplans = $args->tplans;


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
    return $args;
}

/**
 * checkRights
 *
 */
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_testplan_create');
}
?>
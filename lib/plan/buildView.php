<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	buildView.php
 * @package 	TestLink
 * @author 		franciscom
 * @copyright 	2005-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * @internal revisions
 *       
 *
*/
require('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tplan_mgr = new testplan($db);
$build_mgr = new build_mgr($db);

$gui = new StdClass();
$gui->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
$gui->tplan_name = ' ';
$gui->tplan_id = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;

checkRights($db,$_SESSION['currentUser'],$gui);

if($gui->tplan_id > 0)
{
	$dummy = $tplan_mgr->get_by_id($gui->tplan_id);
	$gui->tplan_name = $dummy['name'];
} 
$gui->manageURL = "lib/plan/buildEdit.php?tproject_id={$gui->tproject_id}&tplan_id={$gui->tplan_id}";
$gui->editAction = $gui->manageURL . "&do_action=edit&build_id=";
$gui->deleteAction = $gui->manageURL . "&do_action=do_delete&build_id=";
$gui->createAction = $gui->manageURL . "&do_action=create";


$gui->buildSet = $tplan_mgr->get_builds($gui->tplan_id);
$gui->user_feedback = null;

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('testplan_create_build'),'and');
}
?>

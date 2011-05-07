<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * View project inventory 
 * 
 * @filesource	inventoryView.php
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009, TestLink community 
 *
 *	@todo redirect if no right
 *
 * @internal Revisions:
 *
 **/

require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db);
list($args,$gui) = init_args($db,$_SESSION['currentUser']);
checkRights($db,$_SESSION['currentUser'],$args);


$templateCfg = templateConfiguration();

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * init_args()
 *
 */
function init_args(&$dbHandler,&$userObj)
{
	$argsObj = new stdClass();
	$argsObj->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;


	$guiObj = new stdClass();
	$guiObj->rightEdit = $userObj->hasRight($dbHandler,"project_inventory_management",$argsObj->tproject_id);
	$guiObj->rightView = $userObj->hasRight($dbHandler,"project_inventory_view",$argsObj->tproject_id);
	$guiObj->tproject_id = $argsObj->tproject_id;

	return array($argsObj,$guiObj);

}

/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('project_inventory_view'),'and');
}
?>
<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  platformsView.php
 * @package 	TestLink
 * @copyright 	2003-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * allows users to manage platforms. 
 * @internal revisions
 */
require_once("../../config.inc.php");
require_once("common.php");
// testlinkInitPage($db,!TL_UPDATE_ENVIRONMENT,false,"checkRights");
testlinkInitPage($db);
$templateCfg = templateConfiguration();
$args = init_args();
checkRights($db,$_SESSION['currentUser'],$args);

$gui = initializeGui($db,$args);
new dBug($gui);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * 
 *
 */
function init_args()
{
	$args = new stdClass();
	$args->currentUser = $_SESSION['currentUser']; 

	$args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;

	return $args;
}


function initializeGui(&$dbHandler,&$argsObj)
{
	$platform_mgr = new tlPlatform($dbHandler, $argsObj->tproject_id);
	
	$guiObj = new stdClass();
	$guiObj->platforms = $platform_mgr->getAll(array('include_linked_count' => true));
	$guiObj->canManage = $argsObj->currentUser->hasRight($dbHandler,"platform_management",$argsObj->tproject_id);
	$guiObj->user_feedback = null;
	$guiObj->tproject_id = $argsObj->tproject_id;
	return $guiObj;
}

function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,argsObj,array('platform_management','platform_view'),'or');
}
?>
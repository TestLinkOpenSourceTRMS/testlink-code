<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	keywordsView.php
 * @package 	TestLink
 * @copyright 	2005,2011 TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * allows users to manage keywords. 
 *
 * @internal revisions
 * 20110417 - franciscom - BUGID 4429: Code refactoring to remove global coupling as much as possible
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$args = init_args();
checkRights($db,$_SESSION['currentUser'],$args);


$tproject = new testproject($db);


$gui = new stdClass();
$gui->keywords = $tproject->getKeywords($args->tproject_id);
$gui->tproject_id = $args->tproject_id;
$gui->canManage = $_SESSION['currentUser']->hasRight($db,"mgt_modify_key",$args->tproject_id);

$smarty = new TLSmarty();
$smarty->assign('action',null);
$smarty->assign('sqlResult',null);
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * @return object returns the arguments for the page
 */
function init_args()
{
	$args = new stdClass();
	$args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;

	return $args;
}

/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('mgt_view_key'),'and');
}
?>
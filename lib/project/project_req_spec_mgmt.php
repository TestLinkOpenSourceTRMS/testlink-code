<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	project_req_spec_mgmt.php
 * @author 		Martin Havlat
 *
 * Allows you to show test suites, test cases.
 * Normally launched from tree navigator.
 *
 */
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);


$args = init_args($db);
checkRights($db,$_SESSION['currentUser'],$args);


$gui = new stdClass();
$gui->main_descr = lang_get('testproject') .  TITLE_SEP . $args->tproject_name;
$gui->tproject_id = $args->tproject_id;
$gui->tproject_name = $args->tproject_name;
$gui->refresh_tree = 'no';


new dBug($gui);

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display('requirements/project_req_spec_mgmt.tpl');


function init_args(&$dbHandler)
{
	$argsObj = new stdClass();
	
	$argsObj->tproject_name = '';
	$argsObj->tproject_id   = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
	if( $argsObj->tproject_id > 0 )
	{
		$treeMgr = new tree($dbHandler);
		$dummy = $treeMgr->get_node_hierarchy_info($argsObj->tproject_id);
		$argsObj->tproject_name = $dummy['name'];
	}
	
	new dbug($argsObj);
	return $argsObj;
}

/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('mgt_view_req','mgt_modify_req'),'and');
}

?>
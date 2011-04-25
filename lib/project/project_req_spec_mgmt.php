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
testlinkInitPage($db,!TL_UPDATE_ENVIRONMENT,false,"checkRights");


$args = init_args($db);
$gui = new stdClass();
$gui->main_descr = lang_get('testproject') .  TITLE_SEP . $args->tproject_name;
$gui->tproject_id = $args->tproject_id;
$gui->tproject_name = $args->tproject_name;
$gui->refresh_tree = 'no';

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
	return $argsObj;
}

function checkRights(&$db,&$user)
{
	return ($user->hasRight($db,'mgt_view_req') && $user->hasRight($db,'mgt_modify_req'));
}
?>

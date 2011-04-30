<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	cfieldsView.php
 *
 *
**/
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once("common.php");

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$cfield_mgr = new cfield_mgr($db);
$args = init_args($db);
checkRights($db,$_SESSION['currentUser'],$args);


$gui = new stdClass();
$gui->tproject_id = $args->tproject_id;
$gui->tproject_name = $args->tproject_name;
$gui->cf_map = $cfield_mgr->get_all();
$gui->cf_types = $cfield_mgr->get_available_types();

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



/**
 * create object with all user inputs
 *
 * @internal revisions
 * 20110417 - franciscom - BUGID 4429: Code refactoring to remove global coupling as much as possible
 */
function init_args(&$dbHandler)
{
  	$_REQUEST = strings_stripSlashes($_REQUEST);
    $args = new stdClass();
	$args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
	$args->tproject_name = '';
	if( $args->tproject_id > 0 )
	{
		$treeMgr = new tree($dbHandler);
		$dummy = $treeMgr->get_node_hierarchy_info($args->tproject_id);
		$args->tproject_name = $dummy['name'];
	}

	return $args;
}

/**
 * 
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('cfield_view'),'and');
}

?>
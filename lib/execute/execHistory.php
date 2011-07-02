<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	execHistory.php
 *
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once("web_editor.php");

testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tcase_mgr = new testcase($db);
$args = init_args();
$gui = new stdClass();

$node['basic'] = $tcase_mgr->tree_manager->get_node_hierarchy_info($args->tcase_id); 
$node['specific'] = $tcase_mgr->getExternalID($args->tcase_id); 
$idCard = $node['specific'][0] . ' : ' . $node['basic']['name'];

// $linkedItems = $tcase_mgr->get_linked_versions($args->tcase_id,null,array('output' => 'minimal'));
$gui->execSet = $tcase_mgr->getExecutionSet($args->tcase_id);
$gui->execPlatformSet = null;
if(!is_null($gui->execSet) )
{
	$gui->execPlatformSet = $tcase_mgr->getExecutedPlatforms($args->tcase_id);
}
$gui->displayPlatformCol = !is_null($gui->execPlatformSet) ? 1 : 0;

$gui->main_descr = lang_get('execution_history');
$gui->detailed_descr = lang_get('test_case') . ' ' . $idCard;
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);  
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function init_args($cfgObj)
{
	$args = new stdClass();
	$_REQUEST = strings_stripSlashes($_REQUEST);
	$iParams = array("tcase_id" => array(tlInputParameter::INT_N));
	$pParams = R_PARAMS($iParams);

	$args = new stdClass();
	$args->tcase_id = intval($pParams["tcase_id"]);
	
	return $args;
}
?>
<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	reqSpecSearchForm.php
 * @package 	TestLink
 * @author		asimon
 * @copyright 	2005-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * This page presents the search formular for requiremnt specifications.
 *
 * @internal Revisions:
 * 20100806 - asimon - type displayed wrong selection: req types instead of req spec types
 */

require_once("../../config.inc.php");
require_once("../functions/common.php");
testlinkInitPage($db);


$templateCfg = templateConfiguration();
$tproject_mgr = new testproject($db);

$args = init_args($tproject_mgr);
checkRights($db,$_SESSION['currentUser'],$args);

$gui = new stdClass();
$gui->tcasePrefix = '';
$gui->tproject_id = $args->tproject_id;
 
$gui->mainCaption = lang_get('testproject') . " " . $args->tproject_name;

$enabled = 1;
$no_filters = null;

$gui->design_cf = $tproject_mgr->cfield_mgr->get_linked_cfields_at_design($args->tproject_id,$enabled,
                    $no_filters,'requirement_spec');

$reqSpecSet = $tproject_mgr->getOptionReqSpec($args->tproject_id,testproject::GET_NOT_EMPTY_REQSPEC);

$gui->filter_by['design_scope_custom_fields'] = !is_null($gui->design_cf);
$gui->filter_by['requirement_doc_id'] = !is_null($reqSpecSet);

$reqSpecCfg = config_get('req_spec_cfg');
$gui->types = init_labels($reqSpecCfg->type_labels);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . 'reqSpecSearchForm.tpl');

/*
  function: 

  args:
  
  returns: 

*/
function init_args(&$tprojectMgr)
{              
  	$args = new stdClass();
    $args->tproject_name = '';
    $args->tproject_id = isset($_REQUEST['tproject_id']) ?  intval($_REQUEST['tproject_id']) : 0;
	if($args->tproject_id > 0) 
	{
		$dummy = $tprojectMgr->tree_manager->get_node_hierarchy_info($args->tproject_id);
		$args->tproject_name = $dummy['name'];
	}       
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
	checkSecurityClearance($db,$userObj,$env,array('mgt_view_req'),'and');
}
?>

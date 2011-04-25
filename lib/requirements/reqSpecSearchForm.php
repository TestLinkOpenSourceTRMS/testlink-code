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
$gui = new stdClass();
$gui->tcasePrefix = '';
 
$gui->mainCaption = lang_get('testproject') . " " . $args->tprojectName;

$enabled = 1;
$no_filters = null;

$gui->design_cf = $tproject_mgr->cfield_mgr->get_linked_cfields_at_design($args->tprojectID,$enabled,
                    $no_filters,'requirement_spec');

$reqSpecSet = $tproject_mgr->getOptionReqSpec($args->tprojectID,testproject::GET_NOT_EMPTY_REQSPEC);

$gui->filter_by['design_scope_custom_fields'] = !is_null($gui->design_cf);
$gui->filter_by['requirement_doc_id'] = !is_null($reqSpecSet);

// 20100806 - asimon - type displayed wrong selection: req types instead of req spec types
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
    $args->tprojectName = '';
    $args->tprojectID = isset($_REQUEST['tproject_id']) ?  intval($_REQUEST['tproject_id']) : 0;
	if($args->tprojectID > 0) 
	{
		$dummy = $tprojectMgr->tree_manager->get_node_hierarchy_info($args->tprojectID);
		$args->tprojectName = $dummy['name'];
	}       
    return $args;
}
?>

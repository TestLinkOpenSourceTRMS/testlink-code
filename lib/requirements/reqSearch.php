<?php

/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package 	TestLink
 * @author		asimon
 * @copyright 	2005-2010, TestLink community 
 * @version    	CVS: $Id: reqSearch.php,v 1.1 2010/01/12 20:03:19 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * Search results for requirements.
 *
 * @internal Revisions:
 */

require_once("../../config.inc.php");
require_once("common.php");
require_once("requirements.inc.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$tproject_mgr = new testproject($db);
    	
$req_cfg = config_get('req_cfg');
$tcase_cfg = config_get('testcase_cfg');
$gui = new stdClass();
$gui->main_descr = lang_get('caption_search_form_req');
$gui->warning_msg = '';
$gui->path_info = null;
$gui->resultSet = null;

$map = null;
$args = init_args();

$gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tprojectID);
$gui->tcasePrefix .= $tcase_cfg->glue_character;

if ($args->tprojectID)
{
	$tables = tlObjectWithDB::getDBTables(
							array('cfield_design_values', 'nodes_hierarchy', 'req_specs', 
								'req_versions', 'requirements', 'req_coverage', 'tcversions'));
	$filter = null;
	$from = null;
	
	if ($args->requirement_document_id) {
		//search by id
		$id=$db->prepare_string($args->requirement_document_id);
		$filter['by_id'] = " AND REQ.req_doc_id like '%{$id}%'";
	}
	
	if ($args->name) {
		//search by name/title
		$title=$db->prepare_string($args->name);
		$filter['by_name'] = " AND NHP.name like '%{$title}%' ";
	}

	if ($args->version) {
		//search by version
		$version = $db->prepare_int($args->version);
		$filter['by_version'] = " AND RV.version = {$version} ";
	}
	
	if ($args->reqType != "notype") {
		//search by type
		$type=$db->prepare_string($args->reqType);
		$filter['by_type'] = " AND RV.type='{$type}' ";
	}
	
	if ($args->scope) {
		//search by scope
		$scope=$db->prepare_string($args->scope);
		$filter['by_scope'] = " AND RV.scope like '%{$scope}%' ";
	}
	
	if ($args->coverage) {
		//search by expected coverage of testcases
		$coverage=$db->prepare_int($args->coverage);
		$filter['by_coverage'] = " AND RV.expected_coverage={$coverage} ";
	}
	
	if($args->custom_field_id > 0) {
		//search by custom fields
        $args->custom_field_id = $db->prepare_string($args->custom_field_id);
        $args->custom_field_value = $db->prepare_string($args->custom_field_value);
        $from['by_custom_field'] = " , {$tables['cfield_design_values']} CFD "; 
        $filter['by_custom_field'] = " AND CFD.field_id={$args->custom_field_id} " .
                                     " AND CFD.node_id=NHP.id " .
                                     " AND CFD.value like '%{$args->custom_field_value}%' ";
    }
	
    if ($args->tcid != "" && strcmp($args->tcid, $gui->tcasePrefix) != 0) {
    	//search for reqs linked to this testcase
    	$tcid = $db->prepare_string($args->tcid);
    	$tcid = str_replace($gui->tcasePrefix, "", $tcid);
    	
    	$from['by_tcid'] = ", {$tables['req_coverage']} RC " .  
                           ", {$tables['tcversions']} TCV " .
    					   ", {$tables['nodes_hierarchy']} NHA " .
    					   ", {$tables['nodes_hierarchy']} NHAP ";
    					   
    	$filter['by_tcid'] = "AND TCV.tc_external_id='$tcid' AND TCV.id = NHA.id " .
    						" AND NHA.parent_id = NHAP.id AND RC.testcase_id = NHAP.id " .
    						" AND RC.req_id = NHP.id ";
    }
    
	if ($args->reqStatus != "nostatus") {
		//search by status
		$status=$db->prepare_string($args->reqStatus);
		$filter['by_status'] = " AND RV.status='{$status}' ";
	}
	
	$sql = "SELECT DISTINCT NHP.id, NHP.name FROM {$tables['nodes_hierarchy']} NH," . 
  			"{$tables['nodes_hierarchy']} NHP, {$tables['requirements']} REQ," .
			"{$tables['req_versions']} RV {$from['by_custom_field']} {$from['by_tcid']} " .
			"WHERE NH.parent_id = NHP.id AND RV.id=NH.id AND REQ.id=NHP.id ";
	
	if ($filter)
	{
		$sql .= implode("",$filter);
	}
	$map = $db->fetchRowsIntoMap($sql,'id');
	
	//dont show requirements from different testprojects than the selected one
	foreach ($map as $item) {
		$id = $item['id'];
		$pid = $tproject_mgr->tree_manager->getTreeRoot($id);
		if ($pid != $args->tprojectID) {
			unset($map[$id]);
		}
	}
}

$smarty = new TLSmarty();
$gui->row_qty=count($map);
if($gui->row_qty)
{
	$tpl = 'reqSearchResults.tpl';
	$gui->pageTitle = $gui->main_descr . " - " . lang_get('match_count') . ": " . $gui->row_qty;
	$gui->resultSet=$map;
	if($gui->row_qty <= $req_cfg->search->max_qty_for_display)
	{
		$req_set=array_keys($map);
		$gui->path_info=$tproject_mgr->tree_manager->get_full_path_verbose($req_set);
	}
	else
	{
		$gui->warning_msg=lang_get('too_wide_search_criteria');
	}
}
else
{
	$the_tpl = config_get('tpl');
	$tpl = isset($the_tpl['reqSearchView']) ? $the_tpl['reqSearchView'] : 'reqViewVersions.tpl';
}

$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $tpl);

/*
 function:

 args:

 returns:

 */
function init_args()
{
	$args = new stdClass();
	$_REQUEST = strings_stripSlashes($_REQUEST);

	$strnull = array('requirement_document_id', 'name','scope', 'reqStatus',
						'custom_field_value', 'targetRequirement',
						'version', 'tcid', 'reqType');
	foreach($strnull as $keyvar)
	{
		$args->$keyvar = isset($_REQUEST[$keyvar]) ? trim($_REQUEST[$keyvar]) : null;
		$args->$keyvar = !is_null($args->$keyvar) && strlen($args->$keyvar) > 0 ? trim($args->$keyvar) : null;
	}

	$int0 = array('custom_field_id', 'coverage');
	foreach($int0 as $keyvar)
	{
		$args->$keyvar = isset($_REQUEST[$keyvar]) ? intval($_REQUEST[$keyvar]) : 0;
	}

	$args->userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
	$args->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

	return $args;
}
?>
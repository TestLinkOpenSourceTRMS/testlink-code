<?php

/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package 	TestLink
 * @author		Andreas Simon
 * @copyright 	2005-2010, TestLink community 
 * @version    	CVS: $Id: reqSearch.php,v 1.20 2010/10/26 12:21:25 mx-julian Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * Search results for requirements.
 *
 * @internal Revisions:
 * 20101026 - Julian - BUGID 3930 - Localized dateformat for datepicker
 * 20101021 - asimon - BUGID 3716: replaced old separated inputs for day/month/year by ext js calendar
 * 20101015 - Julian - used title_key for exttable columns instead of title to be able to use 
 *                     table state independent from localization
 * 20101005 - asimon - replaced linked requirement title by linked icon
 * 20100929 - asimon - added req doc id to result table
 * 20100920 - Julian - BUGID 3793 - use exttable to display search results
 *                   - created function to build table
 * 20100920 - franciscom - minor refactoring
 * 20100908 - Julian - BUGID 2877 -  Custom Fields linked to Req versions
 * 20100324 - asimon - added searching for requirement relation type (BUGID 1748)
 */

require_once("../../config.inc.php");
require_once("common.php");
require_once("requirements.inc.php");
require_once('exttable.class.php');
testlinkInitPage($db);
$date_format_cfg = config_get('date_format');

$templateCfg = templateConfiguration();
$tpl = 'reqSearchResults.tpl';

$tproject_mgr = new testproject($db);
    	
$req_cfg = config_get('req_cfg');
$tcase_cfg = config_get('testcase_cfg');
$charset = config_get('charset');

$commandMgr = new reqCommands($db);
$gui = $commandMgr->initGuiBean();

$gui->main_descr = lang_get('caption_search_form_req');
$gui->warning_msg = '';
$gui->path_info = null;
$gui->resultSet = null;
$gui->tableSet = null;

$edit_label = lang_get('requirement');
$edit_icon = TL_THEME_IMG_DIR . "edit_icon.png";

$map = null;
$args = init_args($date_format_cfg);

$gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tprojectID);
$gui->tcasePrefix .= $tcase_cfg->glue_character;

if ($args->tprojectID)
{
	$tables = tlObjectWithDB::getDBTables(
							array('cfield_design_values', 'nodes_hierarchy', 'req_specs', 'req_relations', 
								'req_versions', 'requirements', 'req_coverage', 'tcversions'));
	$filter = null;
    $from = array('by_custom_field' => null, 'by_relation_type' => null, 'by_tcid' => null);

	
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
	
	// BUGID 3716	
	// creation date
    if($args->creation_date_from)
    {
        $filter['by_creation_date_from'] = " AND RV.creation_ts >= '{$args->creation_date_from}' ";
	}

    if($args->creation_date_to)
    {
        $filter['by_creation_date_to'] = " AND RV.creation_ts <= '{$args->creation_date_to}' ";
	}
	
	// modification date
    if($args->modification_date_from)
    {
        $filter['by_modification_date_from'] = " AND RV.modification_ts >= '{$args->modification_date_from}' ";
	}

    if($args->modification_date_to)
    {
        $filter['by_modification_date_to'] = " AND RV.modification_ts <= '{$args->modification_date_to}' ";
	}
	
	if ($args->relation_type != "notype") {
		
		// search by relation type		
		// $args->relation_type is a string in following form
		// e.g. 3_destination or 2_source or only 4
		// must be treated different
		
		$relation_type = (int)current((explode('_',$args->relation_type)));
		
		if (strpos($args->relation_type, "_destination")) {
			$relation_side = "destination_id=NHP.id ";
		} else if (strpos($args->relation_type, "_source")) {
			$relation_side = "source_id=NHP.id ";
		} else {
			$relation_side = " source_id=NHP.id OR destination_id=NHP.id ";
		}		
		
		$from['by_relation_type'] = " , {$tables['req_relations']} RR "; 
        $filter['by_relation_type'] = " AND RR.relation_type={$relation_type} " .
                                      " AND ( $relation_side ) ";
	}
	
	if($args->custom_field_id > 0) {
        $args->custom_field_id = $db->prepare_string($args->custom_field_id);
        $args->custom_field_value = $db->prepare_string($args->custom_field_value);
        $from['by_custom_field'] = " , {$tables['cfield_design_values']} CFD "; 

        // BUGID 2877 -  Custom Fields linked to Req versions
        $filter['by_custom_field'] = " AND CFD.field_id={$args->custom_field_id} " .
                                     " AND CFD.node_id=RV.id " .
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
	
	$sql = "SELECT DISTINCT NHP.id, NHP.name, REQ.req_doc_id FROM {$tables['nodes_hierarchy']} NH," .
  			"{$tables['nodes_hierarchy']} NHP, {$tables['requirements']} REQ," .
			"{$tables['req_versions']} RV {$from['by_custom_field']} {$from['by_tcid']} {$from['by_relation_type']} " .
			"WHERE NH.parent_id = NHP.id AND RV.id=NH.id AND REQ.id=NHP.id ";
	
	if ($filter)
	{
		$sql .= implode("",$filter);
	}
	
	$map = $db->fetchRowsIntoMap($sql,'id');

	//dont show requirements from different testprojects than the selected one
	if (count($map)) {
		foreach ($map as $item) {
			$id = $item['id'];
			$pid = $tproject_mgr->tree_manager->getTreeRoot($id);
			if ($pid != $args->tprojectID) {
				unset($map[$id]);
			}
		}
	}
}

$smarty = new TLSmarty();
$gui->row_qty=count($map);
if($gui->row_qty > 0)
{
	$gui->resultSet=$map;
	if($gui->row_qty <= $req_cfg->search->max_qty_for_display)
	{
		$req_set=array_keys($map);
		$options = array('output_format' => 'path_as_string');
		$gui->path_info=$tproject_mgr->tree_manager->get_full_path_verbose($req_set,$options);
	}
	else
	{
		$gui->warning_msg=lang_get('too_wide_search_criteria');
	}
}
else
{
	$gui->warning_msg=lang_get('no_records_found');
}

$table = buildExtTable($gui, $charset, $edit_icon, $edit_label);

if (!is_null($table)) {
	$gui->tableSet[] = $table;
}

$gui->pageTitle = $gui->main_descr . " - " . lang_get('match_count') . ": " . $gui->row_qty;
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $tpl);

/**
 * 
 *
 */
function buildExtTable($gui, $charset, $edit_icon, $edit_label) {
	$table = null;
	if(count($gui->resultSet) > 0) {
		$labels = array('req_spec' => lang_get('req_spec'), 'requirement' => lang_get('requirement'));
		$columns = array();
		
		$columns[] = array('title_key' => 'req_spec');
		$columns[] = array('title_key' => 'requirement', 'type' => 'text');
	
		// Extract the relevant data and build a matrix
		$matrixData = array();
		
		foreach($gui->resultSet as $result) {
			$rowData = array();
			$rowData[] = htmlentities($gui->path_info[$result['id']], ENT_QUOTES, $charset);

			// build requirement link
			$edit_link = "<a href=\"javascript:openLinkedReqWindow(" . $result['id'] . ")\">" .
						 "<img title=\"{$edit_label}\" src=\"{$edit_icon}\" /></a> ";
			$title = htmlentities($result['req_doc_id'], ENT_QUOTES, $charset) . ":" .
			         htmlentities($result['name'], ENT_QUOTES, $charset);
			$link = $edit_link . $title;
			$rowData[] = $link;

//			$rowData[] = "<a href=\"lib/requirements/reqView.php?item=requirement&requirement_id={$result['id']}\">" .
//			             htmlentities($result['req_doc_id'], ENT_QUOTES, $charset) . ":" .
//			             htmlentities($result['name'], ENT_QUOTES, $charset);
			
			$matrixData[] = $rowData;
		}
	
		$table = new tlExtTable($columns, $matrixData, 'tl_table_req_search');
		
		$table->setGroupByColumnName($labels['req_spec']);
		$table->setSortByColumnName($labels['requirement']);
		$table->sortDirection = 'DESC';
		
		$table->showToolbar = true;
		$table->allowMultiSort = false;
		$table->toolbarRefreshButton = false;
		$table->toolbarShowAllColumnsButton = false;
		
		$table->addCustomBehaviour('text', array('render' => 'columnWrap'));
		
		// dont save settings for this table
		$table->storeTableState = false;
	}
	return($table);
}

/*
 function:

 args:

 returns:

 */
function init_args($dateFormat)
{
	$args = new stdClass();
	$_REQUEST = strings_stripSlashes($_REQUEST);

	// BUGID 3716
	$strnull = array('requirement_document_id', 'name','scope', 'reqStatus',
	                 'custom_field_value', 'targetRequirement',
	                 'version', 'tcid', 'reqType', 'relation_type',
	                 'creation_date_from','creation_date_to',
	                 'modification_date_from','modification_date_to');
	
	foreach($strnull as $keyvar) {
		$args->$keyvar = isset($_REQUEST[$keyvar]) ? trim($_REQUEST[$keyvar]) : null;
		$args->$keyvar = !is_null($args->$keyvar) && strlen($args->$keyvar) > 0 ? trim($args->$keyvar) : null;
	}

	$int0 = array('custom_field_id', 'coverage');
	foreach($int0 as $keyvar)
	{
		$args->$keyvar = isset($_REQUEST[$keyvar]) ? intval($_REQUEST[$keyvar]) : 0;
	}
	
	// BUGID 3716
	// convert "creation date from" to iso format for database usage
    if (isset($args->creation_date_from) && $args->creation_date_from != '') {
		$date_array = split_localized_date($args->creation_date_from, $dateFormat);
		if ($date_array != null) {
			// set date in iso format
			$args->creation_date_from = $date_array['year'] . "-" . $date_array['month'] . "-" . $date_array['day'];
		}
	}
	
	// convert "creation date to" to iso format for database usage
    if (isset($args->creation_date_to) && $args->creation_date_to != '') {
		$date_array = split_localized_date($args->creation_date_to, $dateFormat);
		if ($date_array != null) {
			// set date in iso format
			// date to means end of selected day -> add 23:59:59 to selected date
			$args->creation_date_to = $date_array['year'] . "-" . $date_array['month'] . "-" .
			                          $date_array['day'] . " 23:59:59";
		}
	}
	
	// convert "modification date from" to iso format for database usage
    if (isset($args->modification_date_from) && $args->modification_date_from != '') {
		$date_array = split_localized_date($args->modification_date_from, $dateFormat);
		if ($date_array != null) {
			// set date in iso format
			$args->modification_date_from= $date_array['year'] . "-" . $date_array['month'] . "-" . $date_array['day'];
		}
	}
	
	//$args->modification_date_to = strtotime($args->modification_date_to);
	// convert "creation date to" to iso format for database usage
    if (isset($args->modification_date_to) && $args->modification_date_to != '') {
		$date_array = split_localized_date($args->modification_date_to, $dateFormat);
		if ($date_array != null) {
			// set date in iso format
			// date to means end of selected day -> add 23:59:59 to selected date
			$args->modification_date_to = $date_array['year'] . "-" . $date_array['month'] . "-" .
			                          $date_array['day'] . " 23:59:59";
		}
	}
	
	$args->userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
	$args->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

	return $args;
}
?>
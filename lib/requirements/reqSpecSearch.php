<?php

/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package 	TestLink
 * @author		asimon
 * @copyright 	2005-2009, TestLink community 
 * @version    	CVS: $Id: reqSpecSearch.php,v 1.11 2010/10/15 11:43:26 mx-julian Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * This page presents the search results for requirement specifications.
 *
 * @internal Revisions:
 * 20101015 - Julian - used title_key for exttable columns instead of title to be able to use 
 *                     table state independent from localization
 * 20101005 - asimon - replaced linked req spec title by linked icon
 * 20100929 - asimon - added req doc id to result table
 * 20100920 - Julian - BUGID 3793 - use exttable for search result
 */

require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$tpl = 'reqSpecSearchResults.tpl';

$tproject_mgr = new testproject($db);

$req_cfg = config_get('req_cfg');
$charset = config_get('charset');

$commandMgr = new reqSpecCommands($db);
$gui = $commandMgr->initGuiBean();

$edit_label = lang_get('requirement_spec');
$edit_icon = TL_THEME_IMG_DIR . "edit_icon.png";

$gui->main_descr = lang_get('caption_search_form_req_spec');
$gui->warning_msg = '';
$gui->path_info = null;
$gui->resultSet = null;
$gui->tableSet = null;

$map = null;
$args = init_args();

if ($args->tprojectID)
{
	$tables = tlObjectWithDB::getDBTables(array("cfield_design_values", 'nodes_hierarchy', 'req_specs'));
	$filter = null;
	$from = null;

	if ($args->requirement_document_id) {
		//search by id
		$id=$db->prepare_string($args->requirement_document_id);
		$filter['by_id'] = " AND RS.doc_id like '%{$id}%' ";
	}
	
	if ($args->name) {
		//search by name/title
		$title=$db->prepare_string($args->name);
		$filter['by_name'] = " AND NH.name like '%{$title}%' ";
	}

	if ($args->reqSpecType != "notype") {
		//search by type
		$type=$db->prepare_string($args->reqSpecType);
		$filter['by_type'] = " AND RS.type='{$type}' ";
	}
	
	if ($args->scope) {
		//search by scope
		$scope=$db->prepare_string($args->scope);
		$filter['by_scope'] = " AND RS.scope like '%{$scope}%' ";
	}
	
	if($args->custom_field_id > 0) {
		//search by custom fields
        $args->custom_field_id = $db->prepare_int($args->custom_field_id);
        $args->custom_field_value = $db->prepare_string($args->custom_field_value);
        $from['by_custom_field'] = ", {$tables['cfield_design_values']} CFD "; 
        $filter['by_custom_field'] = " AND CFD.field_id={$args->custom_field_id} " .
                                     " AND CFD.node_id=NH.id " .
                                     " AND CFD.value like '%{$args->custom_field_value}%' ";
    }

    $sql = " SELECT NH.id AS id,NH.name as name,RS.doc_id " .
		   " FROM {$tables['nodes_hierarchy']} NH, " . 
		   " {$tables['req_specs']} RS {$from['by_custom_field']} " .
           " WHERE NH.id = RS.id " .
    	   " AND RS.testproject_id = {$args->tprojectID} ";
	 
	if ($filter)
	{
		$sql .= implode("",$filter);
	}
	$map = $db->fetchRowsIntoMap($sql,'id');
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
		$gui->path_info=$tproject_mgr->tree_manager->get_full_path_verbose($req_set, $options);
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


function buildExtTable($gui, $charset, $edit_icon, $edit_label) {
	$table = null;
	if(count($gui->resultSet) > 0) {
		$labels = array('req_spec' => lang_get('req_spec'));
		$columns = array();
		$columns[] = array('title_key' => 'req_spec', 'type' => 'text', 'groupable' => 'false', 
		                   'hideable' => 'false');
	
		// Extract the relevant data and build a matrix
		$matrixData = array();
		
		foreach($gui->resultSet as $result) {
			$rowData = array();
			$path = ($gui->path_info[$result['id']]) ? $gui->path_info[$result['id']] . " / " : "";
			// use html comment to properly sort by full path
			// build req spec link
//			$rowData[] = "<!-- " . htmlentities($path, ENT_QUOTES, $charset) . htmlentities($result['name'], ENT_QUOTES, $charset) ." -->" .
//			             htmlentities($path, ENT_QUOTES, $charset) .
//			             "<a href=\"lib/requirements/reqSpecView.php?item=req_spec&req_spec_id={$result['id']}\">" .
//			             htmlentities($result['doc_id'], ENT_QUOTES, $charset) . ":" .
//			             htmlentities($result['name'], ENT_QUOTES, $charset);

			$edit_link = "<a href=\"javascript:openLinkedReqSpecWindow(" . $result['id'] . ")\">" .
						 "<img title=\"{$edit_label}\" src=\"{$edit_icon}\" /></a> ";
			$title = htmlentities($result['doc_id'], ENT_QUOTES, $charset) . ":" .
			         htmlentities($result['name'], ENT_QUOTES, $charset);
			$link = $edit_link . $title;
			$rowData[] = $link;

			$matrixData[] = $rowData;
		}

		$table = new tlExtTable($columns, $matrixData, 'tl_table_req_spec_search');
		
		$table->setSortByColumnName($labels['req_spec']);
		$table->sortDirection = 'ASC';
		
		$table->showToolbar = false;
		
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
function init_args()
{
	$args = new stdClass();
	$_REQUEST = strings_stripSlashes($_REQUEST);

	$strnull = array('requirement_document_id', 'name', 'scope', 'coverage',
						'custom_field_value', 'reqSpecType');
	foreach($strnull as $keyvar)
	{
		$args->$keyvar = isset($_REQUEST[$keyvar]) ? trim($_REQUEST[$keyvar]) : null;
		$args->$keyvar = !is_null($args->$keyvar) && strlen($args->$keyvar) > 0 ? trim($args->$keyvar) : null;
	}

	$int0 = array('custom_field_id');
	foreach($int0 as $keyvar)
	{
		$args->$keyvar = isset($_REQUEST[$keyvar]) ? intval($_REQUEST[$keyvar]) : 0;
	}

	$args->userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
	$args->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

	return $args;
}
?>
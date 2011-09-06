<?php

/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	reqSpecSearch.php
 * @package 	TestLink
 * @author		asimon
 * @copyright 	2005-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * This page presents the search results for requirement specifications.
 *
 * @internal revisions
 * 20110903 - franciscom - search on log message and provide link/url to multiple results 
 *
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
$gui->tproject_id = $args->tproject_id;

$itemSet = null;
$args = init_args();

if ($args->tproject_id)
{
	$tables = tlObjectWithDB::getDBTables(array('cfield_design_values', 'nodes_hierarchy', 
												'req_specs','req_specs_revisions'));
	$filter = null;
	$join = null;
	


	// we use same approach used on requirements search => search on revisions
	if ($args->requirement_document_id) 
	{
		$id = $db->prepare_string($args->requirement_document_id);
		$filter['by_id'] = " AND RSPECREV.doc_id like '%{$id}%' ";
	}
	
	if ($args->name) 
	{
		$title = $db->prepare_string($args->name);
		$filter['by_name'] = " AND NHRSPEC.name like '%{$title}%' ";
	}

	if (!is_null($args->reqSpecType)) 
	{
		$type=$db->prepare_string($args->reqSpecType);
		$filter['by_type'] = " AND RSPECREV.type='{$type}' ";
	}
	
	if ($args->scope) 
	{
		$scope = $db->prepare_string($args->scope);
		$filter['by_scope'] = " AND RSPECREV.scope like '%{$scope}%' ";
	}

	if ($args->log_message) 
	{
		$log_message = $db->prepare_string($args->log_message);
		$filter['by_log_message'] = " AND RSPECREV.log_message like '%{$log_message}%' ";
	}

	
	if($args->custom_field_id > 0) 
	{
        $args->custom_field_id = $db->prepare_int($args->custom_field_id);
        $args->custom_field_value = $db->prepare_string($args->custom_field_value);
        $join['by_custom_field'] = " JOIN {$tables['cfield_design_values']} CFD " .
         						   " ON CFD.node_id=RSPECREV.id ";
        $filter['by_custom_field'] = " AND CFD.field_id={$args->custom_field_id} " .
                                     " AND CFD.node_id=NH.id " .
                                     " AND CFD.value like '%{$args->custom_field_value}%' ";
    }

	$sql =	" SELECT NHRSPEC.name, NHRSPEC.id, RSPEC.doc_id, RSPECREV.id AS revision_id, RSPECREV.revision " .
			" FROM {$tables['req_specs']} RSPEC JOIN {$tables['req_specs_revisions']} RSPECREV " .	 
			" ON RSPEC.id=RSPECREV.parent_id " .
			" JOIN {$tables['nodes_hierarchy']} NHRSPEC " .
			" ON NHRSPEC.id = RSPEC.id ";

	if(!is_null($join))
	{
		$sql .= implode("",$join);
	}

	$sql .= " AND RSPEC.testproject_id = {$args->tproject_id} ";
	 
	if(!is_null($filter))
	{
		$sql .= implode("",$filter);
	}

	$sql .= ' ORDER BY id ASC, revision DESC ';	
	$itemSet = $db->fetchRowsIntoMap($sql,'id',database::CUMULATIVE);
	
}

$smarty = new TLSmarty();
$gui->row_qty=count($itemSet);
if($gui->row_qty > 0)
{
	$gui->resultSet = $itemSet;
	if($gui->row_qty <= $req_cfg->search->max_qty_for_display)
	{
		$req_set=array_keys($itemSet);
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

$table = buildExtTable($gui, $charset);
if (!is_null($table)) {
	$gui->tableSet[] = $table;
}

$gui->pageTitle = $gui->main_descr . " - " . lang_get('match_count') . ": " . $gui->row_qty;
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $tpl);


function buildExtTable($gui, $charset, $edit_icon, $edit_label) 
{
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

			$edit_link = "<a href=\"javascript:openLinkedReqSpecWindow({$gui->tproject_id}," . $result['id'] . ")\">" .
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

	$strnull = array('requirement_document_id', 'name', 'scope', 'coverage','custom_field_value','reqSpecType');
	foreach($strnull as $keyvar)
	{
		$args->$keyvar = isset($_REQUEST[$keyvar]) ? trim($_REQUEST[$keyvar]) : null;
		$args->$keyvar = !is_null($args->$keyvar) && strlen($args->$keyvar) > 0 ? trim($args->$keyvar) : null;
	}

	$intzero = array('custom_field_id');
	foreach($intzero as $keyvar)
	{
		$args->$keyvar = isset($_REQUEST[$keyvar]) ? intval($_REQUEST[$keyvar]) : 0;
	}

	$args->userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
	$args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;

	return $args;
}
?>
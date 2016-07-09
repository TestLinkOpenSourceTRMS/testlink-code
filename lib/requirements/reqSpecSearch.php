<?php

/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  reqSpecSearch.php
 * @package   TestLink
 * @author    asimon
 * @copyright   2005-2013
 * @link    http://www.teamst.org/index.php
 *
 * This page presents the search results for requirement specifications.
 *
 * @internal revisions
 * @since 1.9.8
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

$args = init_args();

$commandMgr = new reqSpecCommands($db,$args->tprojectID);
$gui = $commandMgr->initGuiBean();

$edit_label = lang_get('requirement_spec');
$edit_icon = TL_THEME_IMG_DIR . "edit_icon.png";

$gui->main_descr = lang_get('caption_search_form_req_spec');
$gui->warning_msg = '';
$gui->path_info = null;
$gui->resultSet = null;
$gui->tableSet = null;

$itemSet = null;
if ($args->tprojectID)
{
  $tables = tlObjectWithDB::getDBTables(array('cfield_design_values', 'nodes_hierarchy', 
                        'req_specs','req_specs_revisions'));
  $filter = null;
  $join = null;
  


  // we use same approach used on requirements search => search on revisions
  if ($args->requirement_document_id) {
    $id=$db->prepare_string($args->requirement_document_id);
    $filter['by_id'] = " AND RSPECREV.doc_id like '%{$id}%' ";
  }
  
  if ($args->name) {
    $title=$db->prepare_string($args->name);
    $filter['by_name'] = " AND NHRSPEC.name like '%{$title}%' ";
  }

  if ($args->reqSpecType != "notype") {
    $type=$db->prepare_string($args->reqSpecType);
    $filter['by_type'] = " AND RSPECREV.type='{$type}' ";
  }
  
  if ($args->scope) {
    $scope=$db->prepare_string($args->scope);
    $filter['by_scope'] = " AND RSPECREV.scope like '%{$scope}%' ";
  }

  if ($args->log_message) {
    $log_message = $db->prepare_string($args->log_message);
    $filter['by_log_message'] = " AND RSPECREV.log_message like '%{$log_message}%' ";
  }

  
  if($args->custom_field_id > 0) {
        $args->custom_field_id = $db->prepare_int($args->custom_field_id);
        $args->custom_field_value = $db->prepare_string($args->custom_field_value);
        $join['by_custom_field'] = " JOIN {$tables['cfield_design_values']} CFD " .
                       " ON CFD.node_id=RSPECREV.id ";
        $filter['by_custom_field'] = " AND CFD.field_id={$args->custom_field_id} " .
                                     " AND CFD.value like '%{$args->custom_field_value}%' ";
    }

  $sql =  " SELECT NHRSPEC.name, NHRSPEC.id, RSPEC.doc_id, RSPECREV.id AS revision_id, RSPECREV.revision " .
      " FROM {$tables['req_specs']} RSPEC JOIN {$tables['req_specs_revisions']} RSPECREV " .   
      " ON RSPEC.id=RSPECREV.parent_id " .
      " JOIN {$tables['nodes_hierarchy']} NHRSPEC " .
      " ON NHRSPEC.id = RSPEC.id ";

  if(!is_null($join))
  {
    $sql .= implode("",$join);
  }

  $sql .= " AND RSPEC.testproject_id = {$args->tprojectID} ";
   
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


function buildExtTable($gui, $charset) 
{
  $lbl = array('edit' => 'requirement_spec', 'rev' => 'revision_short','req_spec' => 'req_spec',
         'revision_tag' => 'revision_tag', 'open_on_new_window' => 'open_on_new_window');
  $labels = init_labels($lbl);
  $edit_icon = TL_THEME_IMG_DIR . "edit_icon.png";
  $table = null;

  // $gui->resultSet - 
  // key: reqspec_id 
  // value: array of matches
  // array
  // {
  // [232][0]=>{"name" => "QA","id" => "232","doc_id" => "QA",
  //           "revision_id" => "251", "revision" => "4"}
  //      [1]=>{"name" => "QA","id" => "232","doc_id" => "QA",
  //           "revision_id" => "251", "revision" => "3"}
  // ...
  // }
  //
  //
  if(count($gui->resultSet) > 0) 
  {
    $matrixData = array();
    $columns = array();
    $columns[] = array('title_key' => 'req_spec', 'type' => 'text', 'groupable' => 'false', 
                       'hideable' => 'false');
  
    $key2loop = array_keys($gui->resultSet);
    foreach($key2loop as $rspec_id)
    {
      $rowData = array();

      $itemSet = $gui->resultSet[$rspec_id];
      $rfx = &$itemSet[0];
      $path = ($gui->path_info[$rfx['id']]) ? $gui->path_info[$rfx['id']] . " / " : "";
      $edit_link = "<a href=\"javascript:openLinkedReqSpecWindow(" . $rfx['id'] . ")\">" .
             "<img title=\"{$labels['edit']}\" src=\"{$edit_icon}\" /></a> ";

      $title = htmlentities($rfx['doc_id'], ENT_QUOTES, $charset) . ":" .
             htmlentities($rfx['name'], ENT_QUOTES, $charset);
      $cm = '<a href="javascript:openReqSpecRevisionWindow(%s)" title="' . $labels['open_on_new_window'] .'" >' . 
          $labels['revision_tag'] . ' </a>'; 
      // $link = $edit_link;
      $matches = '';
      foreach($itemSet as $rx) 
      {
        $matches .= sprintf($cm,$rx['revision_id'],$rx['revision']);
      }
      $rowData[] = $edit_link . $title . ' ' . $matches;
      $matrixData[] = $rowData;
    } 
      
    $table = new tlExtTable($columns, $matrixData, 'tl_table_req_spec_search');
    $table->setSortByColumnName($labels['req_spec']);
    $table->sortDirection = 'ASC';
    
    $table->showToolbar = false;
    $table->addCustomBehaviour('text', array('render' => 'columnWrap'));
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
           'custom_field_value', 'reqSpecType', 'log_message');

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
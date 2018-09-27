<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  reqSearch.php
 * @package     TestLink
 * @copyright   2005-2018, TestLink community 
 * @link        http://www.testlink.org/index.php
 *
 * Search results for requirements.
 *
 *
 */

require_once("../../config.inc.php");
require_once("common.php");
require_once("requirements.inc.php");
require_once('exttable.class.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$tpl = 'reqSearchResults.tpl';

$tproject_mgr = new testproject($db);
      
$date_format_cfg = config_get('date_format');
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

$map = null;
$args = init_args($date_format_cfg);

$gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tprojectID);
$gui->tcasePrefix .= $tcase_cfg->glue_character;

if ($args->tprojectID) {
  $sql = build_search_sql($db,$args,$gui);

  // key: req id (db id)
  // value: array of versions and revisions
  //
  $map = $db->fetchRowsIntoMap($sql,'id',database::CUMULATIVE);

  // dont show requirements from different testprojects than the selected one
  if (count($map)) {
    $reqIDSet = array_keys($map);
    foreach ($reqIDSet as $item)  {
      $pid = $tproject_mgr->tree_manager->getTreeRoot($item);
      if ($pid != $args->tprojectID) {
        unset($map[$item]);
      }
    }
  }
}

$smarty = new TLSmarty();
$gui->row_qty = count($map);
if($gui->row_qty > 0) {
  $gui->resultSet = $map;
  if($gui->row_qty <= $req_cfg->search->max_qty_for_display) {
    $req_set = array_keys($map);
    $options = array('output_format' => 'path_as_string');
    $gui->path_info = 
      $tproject_mgr->tree_manager->get_full_path_verbose($req_set,$options);
  } else {
    $gui->warning_msg = lang_get('too_wide_search_criteria');
  }
} else {
  $gui->warning_msg = lang_get('no_records_found');
}

$table = buildExtTable($gui, $charset);

if (!is_null($table)) {
  $gui->tableSet[] = $table;
}

$gui->pageTitle = 
  $gui->main_descr . " - " . lang_get('match_count') . ": " . $gui->row_qty;

$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $tpl);

/**
 * 
 *
 */
function buildExtTable($gui, $charset) {
  $table = null;
  $lbl = array('edit' => 'requirement', 'rev' => 'revision_short', 
               'ver' => 'version_short', 
               'req_spec' => 'req_spec', 'requirement' => 'requirement',
               'version_revision_tag' => 'version_revision_tag');

  $labels = init_labels($lbl);
  $edit_icon = TL_THEME_IMG_DIR . "edit_icon.png";
  
  // $gui->resultSet - 
  // key: reqspec_id 
  // value: array of matches
  // array
  // {
  // [4][0]=>{"name" => "QAZ MNNN","id" => "4","req_doc_id" => "QAZ",
  //        "version_id" => 5, "version" => 1, 
  //      "revision_id" => -1, "revision" => 2}   -> revisio_id < 0 => lives on REQ VERSIONS TABLE
    //
  //    [1]=>{"name" => "QAZ MNNN","id" => "4","req_doc_id" => "QAZ",
  //        "version_id" => 5, "version" => 1, 
  //      "revision_id" => 6, "revision" => 1}   
  // ...
  // }
  //
  //

  if(count($gui->resultSet) > 0) {
    $columns = array();
    
    $columns[] = array('title_key' => 'req_spec');
    $columns[] = array('title_key' => 'requirement', 'type' => 'text');
  
    // Extract the relevant data and build a matrix
    $matrixData = array();
    
    $key2loop = array_keys($gui->resultSet);
    $img = "<img title=\"{$labels['edit']}\" src=\"{$edit_icon}\" />";
    // req_id, req_version_id
    $reqVerHref = '<a href="javascript:openLinkedReqVersionWindow(%s,%s)">' . $labels['version_revision_tag'] . ' </a>'; 
    // req_revision_id
    $reqRevHref = '<a href="javascript:openReqRevisionWindow(%s)">' . $labels['version_revision_tag'] . ' </a>'; 
    
    foreach($key2loop as $req_id) {
      $rowData = array();
      $itemSet = $gui->resultSet[$req_id];
      $rfx = &$itemSet[0];
      
      // We Group by Requirement path
      $rowData[] = htmlentities($gui->path_info[$rfx['id']], ENT_QUOTES, $charset);

      $edit_link = "<a href=\"javascript:openLinkedReqWindow(" . $rfx['id'] . ")\">" . "{$img}</a> ";
      $title = htmlentities($rfx['req_doc_id'], ENT_QUOTES, $charset) . ":" .
               htmlentities($rfx['name'], ENT_QUOTES, $charset);

      $matches = '';
      foreach($itemSet as $rx) {
        if($rx['revision_id'] > 0) {
          $dummy = sprintf($reqRevHref,$rx['revision_id'],$rx['version'],
                           $rx['revision']);
        } else {
          $dummy = sprintf($reqVerHref,$req_id,$rx['version_id'],$rx['version'],
                           $rx['revision']);
        } 
        $matches .= $dummy;
      }
      $rowData[] = $edit_link . $title . ' ' . $matches;
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
    $table->storeTableState = false;
    
    $table->addCustomBehaviour('text', array('render' => 'columnWrap'));
  }
  return $table;
}

/*
 function:

 args:

 returns:

 */
function init_args($dateFormat) {
  $args = new stdClass();
  $_REQUEST = strings_stripSlashes($_REQUEST);

  $strnull = array('requirement_document_id', 'name','scope', 'reqStatus',
                   'custom_field_value', 'targetRequirement',
                   'version', 'tcid', 'reqType', 'relation_type',
                   'creation_date_from','creation_date_to','log_message',
                   'modification_date_from','modification_date_to');
  
  foreach($strnull as $keyvar) {
    $args->$keyvar = isset($_REQUEST[$keyvar]) ? trim($_REQUEST[$keyvar]) : null;
    $args->$keyvar = !is_null($args->$keyvar) && strlen($args->$keyvar) > 0 ? trim($args->$keyvar) : null;
  }

  $int0 = array('custom_field_id', 'coverage');
  foreach($int0 as $keyvar) {
    $args->$keyvar = isset($_REQUEST[$keyvar]) ? intval($_REQUEST[$keyvar]) : 0;
  }
  
  // convert "creation date from" to iso format for database usage
  if (isset($args->creation_date_from) && $args->creation_date_from != '') {
     $date_array = split_localized_date($args->creation_date_from, $dateFormat);
      
     if ($date_array != null) {
       // set date in iso format
       $args->creation_date_from = $date_array['year'] . "-" . 
         $date_array['month'] . "-" . $date_array['day'];
     }
  }
  
  // convert "creation date to" to iso format for database usage
  if (isset($args->creation_date_to) && $args->creation_date_to != '') {
    $date_array = split_localized_date($args->creation_date_to, $dateFormat);
    
    if ($date_array != null) {
      // set date in iso format
      // date to means end of selected day -> add 23:59:59 to selected date
      $args->creation_date_to = $date_array['year'] . "-" . $date_array['month'] . 
                                  "-" . $date_array['day'] . " 23:59:59";
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



/**
 * 
 *
 */
function build_search_sql(&$dbHandler,&$argsObj,&$guiObj) {
  $tables = tlObjectWithDB::getDBTables(array('cfield_design_values', 
              'nodes_hierarchy', 'req_specs', 'req_relations', 'req_versions', 
              'req_revisions','requirements', 'req_coverage', 'tcversions'));

  // ver => REQ Versions
  // rev => REQ Revisions
  // Some filters can be applied on REQ Versions & REQ Revisions
  // other ONLY on REQ Versions and other ONLY REQ Revisions
  // that's why we have developer logic using UNION.
  // Using just UNION and not UNION ALL, we will try to remove duplicates
  // if possible.
  //
  // That's why to certain extent filter seems to work in OR mode.
  // May be this is a BUG, that was never reported.
  //
  $filter = array();
  $filter['ver'] = null;
  $filter['rev'] = null;

  // -----------------------------------------------------------------------
  // date filters can be build using algorithm
  $date_fields = array('creation_ts' => 'ts' ,'modification_ts' => 'ts');
  $date_keys = array('date_from' => '>=' ,'date_to' => '<=');
  foreach($date_fields as $fx => $needle) {
    foreach($date_keys as $fk => $op) {
      $fkey = str_replace($needle,$fk,$fx);
      if($argsObj->$fkey) {
        $filter['ver'][$fkey] = " AND REQV.$fx $op '{$argsObj->$fkey}' ";
        $filter['rev'][$fkey] = " AND REQR.$fx $op '{$argsObj->$fkey}' ";
      }
    }   
  }
  // -----------------------------------------------------------------------

  // key: args key
  // value: map
  //      key: table field
  //      value: map 
  //         key: filter scope, will identify with part of SQL affects
  //         value: table alias
  //  
  $likeKeys = array('name' => 
                      array('name' => array('ver' => "NH_REQ", 'rev' => "REQR")),
                    'requirement_document_id' => 
                      array('req_doc_id' => array('ver' => 'REQ', 'rev' => 'REQR')),
                    'scope' => 
                      array('scope' => array('ver' => 'REQV', 'rev' => 'REQR')),
                    'log_message' 
                      => array('log_message'=> array('ver' => 'REQV','rev' =>'REQR')));

  foreach($likeKeys as $key => $fcfg) {
    if($argsObj->$key) {
      $value = $dbHandler->prepare_string($argsObj->$key);
      $field = key($fcfg);
      foreach($fcfg[$field] as $table => $alias) {
        $filter[$table][$field] = " AND {$alias}.{$field} like '%{$value}%' ";
      }
    }
  }           

  $char_keys = array( 'reqType' => 
                 array('type' => array('ver' => "REQV", 'rev' => "REQR")),
                       'reqStatus' => 
                         array('status' => array('ver' => 'REQV', 'rev' => 'REQR')));

  foreach($char_keys as $key => $fcfg) {
    if($argsObj->$key) {
      $value = $dbHandler->prepare_string($argsObj->$key);
      $field = key($fcfg);
      foreach($fcfg[$field] as $table => $alias) {
        $filter[$table][$field] = " AND {$alias}.{$field} = '{$value}' ";
      }
    }
  }           

  if ($argsObj->version) {
    $version = $dbHandler->prepare_int($argsObj->version);
    $filter['ver']['version'] = " AND REQV.version = {$version} ";
    $filter['rev']['version'] = $filter['versions']['by_version'];
  }
  
  if ($argsObj->coverage) {
    //search by expected coverage of testcases
    $coverage=$dbHandler->prepare_int($argsObj->coverage);
    $filter['ver']['coverage'] = " AND REQV.expected_coverage = {$coverage} ";
    $filter['rev']['coverage'] = " AND REQR.expected_coverage = {$coverage} ";
  }
  
  
  // Complex processing
  if(!is_null($argsObj->relation_type)) {
    // search by relation type    
    // $argsObj->relation_type is a string in following form
    // e.g. 3_destination or 2_source or only 4
    // must be treated different
    $dummy = explode('_',$argsObj->relation_type);
    $rel_type = $dummy[0];
    $side = isset($dummy[1]) ? " RR.{$dummy[1]}_id = NH_REQ.id " : 
        " RR.source_id = NH_REQ.id OR RR.destination_id = NH_REQ.id ";

    $from['ver']['relation_type'] = " JOIN {$tables['req_relations']} RR " .
                    " ON ($side) AND RR.relation_type = {$rel_type} "; 
    $from['rev']['relation_type'] = $from['ver']['relation_type'];

  } 

  if($argsObj->custom_field_id > 0) {
    $cfield_id = $dbHandler->prepare_string($argsObj->custom_field_id);
    $cfield_value = $dbHandler->prepare_string($argsObj->custom_field_value);
    $from['ver']['custom_field'] =  
          " JOIN {$tables['cfield_design_values']} CFD " .
          " ON CFD.node_id = REQV.id "; 

    $from['rev']['custom_field'] =  
          " JOIN {$tables['cfield_design_values']} CFD " .
          " ON CFD.node_id = REQR.id "; 

    $filter['ver']['custom_field'] = " AND CFD.field_id = {$cfield_id} " .
                                         " AND CFD.value like '%{$cfield_value}%' ";
                                       
    $filter['rev']['custom_field'] = $filter['ver']['custom_field'];                               
  } 

  if ($argsObj->tcid != "" && strcmp($argsObj->tcid, $guiObj->tcasePrefix) != 0) {
    // search for reqs linked to this testcase
    $tcid = $dbHandler->prepare_string($argsObj->tcid);
    $tcid = str_replace($guiObj->tcasePrefix, "", $tcid);

    $filter['ver']['tcid'] = " AND TCV.tc_external_id = '$tcid' ";
    $filter['rev']['tcid'] = $filter['ver']['by_tcid'];
      
    $from['ver']['tcid'] =  

        " /* 1.9.18 Changed */ " .
        " /* Look for Req Coverage info */ " .
        " JOIN {$tables['req_coverage']} RC ON RC.req_version_id = NH_REQV.id " .  

        " /* 1.9.18 Changed */ " .
        " /* Need Test case children => test case versions */ ".
        " JOIN {$tables['nodes_hierarchy']} NH_TCV 
          ON NH_TCV.id = RC.tcversion_id " .
        
        " /* Needed to search using External ID  */ ".
        " JOIN {$tables['tcversions']} TCV ON TCV.id = NH_TCV.id ";

      $from['rev']['tcid'] = $from['ver']['tcid']; 
  }

  // We will search on two steps
  // STEP 1
  // Search on REQ Versions
  //
  $common = " SELECT NH_REQ.name, REQ.id, REQ.req_doc_id,"; 
  $sql =  $common .
          " REQV.id as version_id, REQV.version, REQV.revision, -1 AS revision_id " .
          " /*  */" .
          " /* Main table to get Last Version REQ_DOC_ID */" .
          " FROM {$tables['requirements']} REQ " .
          " JOIN {$tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id=REQ.id " .
          " /* */ " .
          " /* Need to get all REQ children => REQ Versions */ " .
          " JOIN {$tables['nodes_hierarchy']} 
            NH_REQV ON NH_REQV.parent_id = NH_REQ.id " .  
          " /* */ " .
          " /* Go for REQ REV data */ " .
          " JOIN {$tables['req_versions']} REQV ON REQV.id=NH_REQV.id " .
          " /* */ ";
      
  $map2use = array('from','filter'); // ORDER IS CRITIC to build SQL statement
  foreach($map2use as $vv) {
    $ref = &$$vv;
    if(!is_null($ref['ver'])) {
      $sql .= ($vv == 'filter') ? ' WHERE 1=1 ' : '';
      $sql .= implode("",$ref['ver']);
    }   
  }   
  $stm['ver'] = $sql;
  

  // STEP 1
  // Search on REQ Revisions
  //
  $sql4Union =  $common .
      " REQR.parent_id AS version_id, REQV.version, REQR.revision, REQR.id AS revision_id " .
      " /* SQL For Req REVISIONS - */ " .
      " /* SQL For Req REVISIONS - Main table to get Last Version REQ_DOC_ID */" .
      " FROM {$tables['requirements']} REQ " .
      " JOIN {$tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id=REQ.id " .
      " /* SQL For Req REVISIONS - */ " .
      " /* SQL For Req REVISIONS - Need to get all REQ children => REQ Versions because they are parent of REVISIONS */ " .
      " JOIN {$tables['nodes_hierarchy']} NH_REQV ON NH_REQV.parent_id = NH_REQ.id " .  
      " /* SQL For Req REVISIONS - */ " .
      " /* SQL For Req REVISIONS - Go for REQ REVISION DATA */" .
      " JOIN {$tables['req_versions']} REQV ON REQV.id=NH_REQV.id " .
      " /* SQL For Req REVISIONS - */ " .
      " /* SQL For Req REVISIONS - Now need to go for revisions */ " .
      " JOIN {$tables['req_revisions']} REQR ON REQR.parent_id=REQV.id ";

  foreach($map2use as $vv) {
    $ref = &$$vv;
    if(!is_null($ref['rev'])) {
      $sql4Union .= ($vv == 'filter') ? ' WHERE 1=1 ' : '';
      $sql4Union .= implode("",$ref['rev']);
    }   
  }   


  // add additional joins that depends on user search criteria
  $sql = $stm['ver'] . " UNION ({$sql4Union}) ORDER BY id ASC, version DESC, revision DESC ";
  echo $sql;
  return $sql;
}
<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Display test cases search results. 
 * Search is done ONLY ON CURRENT test project
 *
 *
 * @filesource  tcSearch.php
 * @package     TestLink
 * @author      TestLink community
 * @copyright   2007-2013, TestLink community 
 * @link        http://www.teamst.org/index.php
 *
 *
 *  @internal revisions
 *  @since 1.9.9
 *  20130916 - franciscom - TICKET 5922: Filters on creation and modification dates is ignored in test cases search
 **/
require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db);
$date_format_cfg = config_get('date_format');

$templateCfg = templateConfiguration();
$smarty = new TLSmarty();

$tpl = 'tcSearchResults.tpl';
$tproject_mgr = new testproject($db);

$tcase_cfg = config_get('testcase_cfg');
$charset = config_get('charset');
$filter = null;
list($args,$filter) = init_args($date_format_cfg);

$gui = initializeGui($args);
$map = null;

if ($args->tprojectID)
{
  $tables = tlObjectWithDB::getDBTables(array('cfield_design_values','nodes_hierarchy',
                                              'requirements','req_coverage','tcsteps',
                                              'testcase_keywords','tcversions','users'));
                                
  $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tprojectID);
  $gui->tcasePrefix .= $tcase_cfg->glue_character;

  $from = array('by_keyword_id' => ' ', 'by_custom_field' => ' ', 'by_requirement_doc_id' => '', 'users' => '');
  $tcaseID = null;

  $k2w = array('name' => 'NH_TC', 'summary' => 'TCV', 'preconditions' => 'TCV');
  foreach($k2w as $kf => $alias)
  {
    if($args->$kf != "")
    {
      $args->$kf =  $db->prepare_string($args->$kf);
      $filter[$kf] = " AND {$alias}.{$kf} like '%{$args->$kf}%' ";
    }
  } 
    
   
  if($args->targetTestCase != "" && strcmp($args->targetTestCase,$gui->tcasePrefix) != 0)
  {
      if (strpos($args->targetTestCase,$tcase_cfg->glue_character) === false)
      {
        $args->targetTestCase = $gui->tcasePrefix . $args->targetTestCase;
      }
        
      $tcase_mgr = new testcase ($db);
      $tcaseID = $tcase_mgr->getInternalID($args->targetTestCase);
      $filter['by_tc_id'] = " AND NH_TCV.parent_id = {$tcaseID} ";
  }
  else
  {
    $tproject_mgr->get_all_testcases_id($args->tprojectID,$a_tcid);
    $filter['by_tc_id'] = " AND NH_TCV.parent_id IN (" . implode(",",$a_tcid) . ") ";
  }
  if($args->version)
  {
    $filter['by_version'] = " AND TCV.version = {$args->version} ";
  }
    
  if($args->keyword_id)       
  {
  	 $from['by_keyword_id'] = " JOIN {$tables['testcase_keywords']} KW ON KW.testcase_id = NH_TC.id ";
     $filter['by_keyword_id'] = " AND KW.keyword_id  = " . $args->keyword_id; 
  }
    
  if($args->steps != "")
  {
    $args->steps = $db->prepare_string($args->steps);
    $filter['by_steps'] = " AND TCSTEPS.actions like '%{$args->steps}%' ";  
  }    
    
  if($args->expected_results != "")
  {
    $args->expected_results = $db->prepare_string($args->expected_results);
    $filter['by_expected_results'] = " AND TCSTEPS.expected_results like '%{$args->expected_results}%' "; 
  }    
    
  if($args->custom_field_id > 0)
  {
    $args->custom_field_id = $db->prepare_string($args->custom_field_id);
    $args->custom_field_value = $db->prepare_string($args->custom_field_value);
    $from['by_custom_field']= " JOIN {$tables['cfield_design_values']} CFD " .
                              " ON CFD.node_id=NH_TCV.id ";
    $filter['by_custom_field'] = " AND CFD.field_id={$args->custom_field_id} " .
                                 " AND CFD.value like '%{$args->custom_field_value}%' ";
  }

  if($args->requirement_doc_id != "")
  {
    $args->requirement_doc_id = $db->prepare_string($args->requirement_doc_id);
    $from['by_requirement_doc_id'] = " JOIN {$tables['req_coverage']} RC" .  
                                     " ON RC.testcase_id = NH_TC.id " .
                                     " JOIN {$tables['requirements']} REQ " .
                                     " ON REQ.id=RC.req_id " ;
    $filter['by_requirement_doc_id'] = " AND REQ.req_doc_id like '%{$args->requirement_doc_id}%' ";
  }   

  if( $args->importance > 0)
  {
    $filter['importance'] = " AND TCV.importance = {$args->importance} ";
  }  

  $args->created_by = trim($args->created_by);
  if( $args->created_by != '' )
  {
    $from['users'] = " JOIN {$tables['users']} AUTHOR ON AUTHOR.id = TCV.author_id ";
    $filter['author'] = " AND ( AUTHOR.login LIKE '%{$args->created_by}%' OR " .
                        "       AUTHOR.first LIKE '%{$args->created_by}%' OR " .
                        "       AUTHOR.last LIKE '%{$args->created_by}%') ";
  }  

  $args->edited_by = trim($args->edited_by);
  if( $args->edited_by != '' )
  {
    $from['users'] = " JOIN {$tables['users']} UPDATER ON UPDATER.id = TCV.updater_id ";
    $filter['modifier'] = " AND ( UPDATER.login LIKE '%{$args->edited_by}%' OR " .
                        "         UPDATER.first LIKE '%{$args->edited_by}%' OR " .
                        "         UPDATER.last LIKE '%{$args->edited_by}%') ";
  }  
    
  $sqlFields = " SELECT NH_TC.id AS testcase_id,NH_TC.name,TCV.id AS tcversion_id," .
               " TCV.summary, TCV.version, TCV.tc_external_id "; 
    
  // Count Test Cases NOT Test Case Versions
  // ATTENTION:
  // Keywords are stored AT TEST CASE LEVEL, not test case version.
  $sqlCount  = "SELECT COUNT(DISTINCT(NH_TC.id)) ";

  // search fails if test case has 0 steps - Added LEFT OUTER
  $sqlPart2 = " FROM {$tables['nodes_hierarchy']} NH_TC " .
              " JOIN {$tables['nodes_hierarchy']} NH_TCV ON NH_TCV.parent_id = NH_TC.id  " .
              " JOIN {$tables['tcversions']} TCV ON NH_TCV.id = TCV.id " .
              " LEFT OUTER JOIN {$tables['nodes_hierarchy']} NH_TCSTEPS ON NH_TCSTEPS.parent_id = NH_TCV.id " .
              " LEFT OUTER JOIN {$tables['tcsteps']} TCSTEPS ON NH_TCSTEPS.id = TCSTEPS.id  " .
              " {$from['by_keyword_id']} {$from['by_custom_field']} {$from['by_requirement_doc_id']} " .
              " {$from['users']} " .
              " WHERE 1=1 ";
           
           
  // if user fill in test case [external] id filter, and we were not able to get tcaseID, do any query is useless
  $applyFilters = true;
  if( !is_null($filter) && isset($filter['by_tc_id']) && !is_null($tcaseID) && ($tcaseID <= 0) )
  {
    // get the right feedback message
    $applyFilters = false;
    $gui->warning_msg = $tcaseID == 0 ? lang_get('testcase_does_not_exists') : lang_get('prefix_does_not_exists');
  }

  if( $applyFilters )
  {      
    if ($filter)
    {
      $sqlPart2 .= implode("",$filter);
    }
  
    // Count results
    $sql = $sqlCount . $sqlPart2;
    $gui->row_qty = $db->fetchOneValue($sql); 
    if ($gui->row_qty)
    {
      if ($gui->row_qty <= $tcase_cfg->search->max_qty_for_display)
      {
        $sql = $sqlFields . $sqlPart2;
        $map = $db->fetchRowsIntoMap($sql,'testcase_id'); 
      }
      else
      {
        $gui->warning_msg = lang_get('too_wide_search_criteria');
      } 
    }
  }
}


$gui->pageTitle .= " - " . lang_get('match_count') . " : " . $gui->row_qty;
if($gui->row_qty > 0)
{ 
  if ($map)
  {
    $tcase_mgr = new testcase($db);   
    $tcase_set = array_keys($map);
    $options = array('output_format' => 'path_as_string');
    $gui->path_info = $tproject_mgr->tree_manager->get_full_path_verbose($tcase_set, $options);
    $gui->resultSet = $map;
  }
}
else
{
  $gui->warning_msg=lang_get('no_records_found');
}

$img = $smarty->getImages();
$table = buildExtTable($gui, $charset, $img['edit_icon'], $img['history_small']);
if (!is_null($table)) 
{
  $gui->tableSet[] = $table;
}

$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $tpl);

/**
 * 
 *
 */
function buildExtTable($gui, $charset, $edit_icon, $history_icon) 
{
  $table = null;
  if(count($gui->resultSet) > 0) 
  {
    $labels = array('test_suite' => lang_get('test_suite'), 'test_case' => lang_get('test_case'));
    $columns = array();
    
    $columns[] = array('title_key' => 'test_suite');
    $columns[] = array('title_key' => 'test_case', 'type' => 'text');

    $columns[] = array('title_key' => 'summary');
  
    // Extract the relevant data and build a matrix
    $matrixData = array();
    
    $titleSeperator = config_get('gui_title_separator_1');
    
    foreach($gui->resultSet as $result) 
    {
      $rowData = array();
      $rowData[] = htmlentities($gui->path_info[$result['testcase_id']], ENT_QUOTES, $charset);
      
      // build test case link
      $history_link = "<a href=\"javascript:openExecHistoryWindow({$result['testcase_id']});\">" .
                      "<img title=\"". lang_get('execution_history') . "\" src=\"{$history_icon}\" /></a> ";
      $edit_link = "<a href=\"javascript:openTCEditWindow({$result['testcase_id']});\">" .
                   "<img title=\"". lang_get('design') . "\" src=\"{$edit_icon}\" /></a> ";
      $tcaseName = htmlentities($gui->tcasePrefix, ENT_QUOTES, $charset) . $result['tc_external_id'] . 
                   " [v" . $result['version'] . "]" . $titleSeperator .
                   htmlentities($result['name'], ENT_QUOTES, $charset);

      $rowData[] = $history_link . $edit_link . $tcaseName;
      $rowData[] = $result['summary'];

      $matrixData[] = $rowData;
    }
    
    $table = new tlExtTable($columns, $matrixData, 'tl_table_test_case_search');
    
    $table->setGroupByColumnName($labels['test_suite']);
    $table->setSortByColumnName($labels['test_case']);
    $table->sortDirection = 'DESC';
    
    $table->showToolbar = true;
    $table->allowMultiSort = false;
    $table->toolbarRefreshButton = false;
    $table->toolbarShowAllColumnsButton = false;
    
    $table->addCustomBehaviour('text', array('render' => 'columnWrap'));
    $table->storeTableState = false;
  }
  return($table);
}


/**
 *
 *
 */
function init_args($dateFormat)
{
  $args = new stdClass();
  $iParams = array("keyword_id" => array(tlInputParameter::INT_N),
                   "version" => array(tlInputParameter::INT_N,999),
                   "custom_field_id" => array(tlInputParameter::INT_N),
                   "name" => array(tlInputParameter::STRING_N,0,50),
                   "created_by" => array(tlInputParameter::STRING_N,0,50),
                   "edited_by" => array(tlInputParameter::STRING_N,0,50),
                   "summary" => array(tlInputParameter::STRING_N,0,50),
                   "steps" => array(tlInputParameter::STRING_N,0,50),
                   "expected_results" => array(tlInputParameter::STRING_N,0,50),
                   "custom_field_value" => array(tlInputParameter::STRING_N,0,20),
                   "targetTestCase" => array(tlInputParameter::STRING_N,0,30),
                   "preconditions" => array(tlInputParameter::STRING_N,0,50),
                   "requirement_doc_id" => array(tlInputParameter::STRING_N,0,32),
                   "importance" => array(tlInputParameter::INT_N),
                   "creation_date_from" => array(tlInputParameter::STRING_N),
                   "creation_date_to" => array(tlInputParameter::STRING_N),
                   "modification_date_from" => array(tlInputParameter::STRING_N),
                   "modification_date_to" => array(tlInputParameter::STRING_N));
    
  $args = new stdClass();
  R_PARAMS($iParams,$args);

  $_REQUEST=strings_stripSlashes($_REQUEST);

  $args->userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
  $args->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

  // convert "creation date from" to iso format for database usage
  $k2w = array('creation_date_from' => '','creation_date_to' => " 23:59:59",
               'modification_date_from' => '', 'modification_date_to' => " 23:59:59");


  $k2f = array('creation_date_from' => ' creation_ts >= ',
               'creation_date_to' => 'creation_ts <= ',
               'modification_date_from' => ' modification_ts >= ', 
               'modification_date_to' => ' modification_ts <= ');


  $filter = null;
  foreach($k2w as $key => $value)
  {
    if (isset($args->$key) && $args->$key != '') 
    {
      $da = split_localized_date($args->$key, $dateFormat);
      if ($da != null) 
      {
        $args->$key = $da['year'] . "-" . $da['month'] . "-" . $da['day'] . $value; // set date in iso format
        $filter[$key] = " AND TCV.{$k2f[$key]} '{$args->$key}' ";
      }
    }
  } 
  return array($args,$filter);
}


/**
 * 
 *
 */
function initializeGui(&$argsObj)
{
  $gui = new stdClass();

  $gui->pageTitle = lang_get('caption_search_form');
  $gui->warning_msg = '';
  $gui->tcasePrefix = '';
  $gui->path_info = null;
  $gui->resultSet = null;
  $gui->tableSet = null;
  $gui->bodyOnLoad = null;
  $gui->bodyOnUnload = null;
  $gui->refresh_tree = false;
  $gui->hilite_testcase_name = false;
  $gui->show_match_count = false;
  $gui->tc_current_version = null;
  $gui->row_qty = 0;
  
  return $gui;
}
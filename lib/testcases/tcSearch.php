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
 * @copyright   2007-2018, TestLink community 
 * @link        http://www.testlink.org/
 *
 **/
require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$smarty = new TLSmarty();

$tpl = 'tcSearchResults.tpl';
$tproject_mgr = new testproject($db);
$tcase_mgr = new testcase ($db);
 
$tcase_cfg = config_get('testcase_cfg');
$charset = config_get('charset');
$filter = null;
list($args,$filter) = init_args($tproject_mgr);

//Kint::dump($_REQUEST);die();

$ga = initializeGui($args,$tproject_mgr);
$gx = $tcase_mgr->getTcSearchSkeleton($args);
$gui = (object)array_merge((array)$ga,(array)$gx);

initSearch($gui,$args,$tproject_mgr);



$map = null;

if ($args->tprojectID && $args->doAction == 'doSearch')
{
  $tables = tlObjectWithDB::getDBTables(array('cfield_design_values','nodes_hierarchy',
                                              'requirements','req_coverage','tcsteps',
                                              'testcase_keywords','tcversions','users'));
                                
  $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tprojectID);
  $gui->tcasePrefix .= $tcase_cfg->glue_character;

  $from = array('by_keyword_id' => ' ', 'by_custom_field' => ' ', 'by_requirement_doc_id' => '', 'users' => '');
  $tcaseID = null;
  $emptyTestProject = false;

  if($args->targetTestCase != "" && strcmp($args->targetTestCase,$gui->tcasePrefix) != 0) {
    if (strpos($args->targetTestCase,$tcase_cfg->glue_character) === false) {
      $args->targetTestCase = $gui->tcasePrefix . $args->targetTestCase;
    }
        
    $tcaseID = $tcase_mgr->getInternalID($args->targetTestCase);
    $filter['by_tc_id'] = " AND NH_TCV.parent_id = " . intval($tcaseID);
  }
  else {
    $tproject_mgr->get_all_testcases_id($args->tprojectID,$a_tcid);

    if(!is_null($a_tcid)) {
      $filter['by_tc_id'] = " AND NH_TCV.parent_id IN (" . implode(",",$a_tcid) . ") ";
    }  
    else {
      // Force Nothing extracted, because test project 
      // has no test case defined 
      $emptyTestProject = true;
      $filter['by_tc_id'] = " AND 1 = 0 ";
    }  
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
    

  $useOr = false;
  $filterSpecial = null;
  $feOp = " AND ";
  $filterSpecial['tricky'] = " 1=1 ";
  if($args->jolly != "")
  {
    // $filterSpecial['trick'] = " 1=1 ";
    $useOr = true;
    $feOp = " OR ";
    $filterSpecial['tricky'] = " 1=0 ";
    $args->steps = $args->expected_results = $args->jolly;
  }  
    
  if($args->steps != "")
  {
    $args->steps = $db->prepare_string($args->steps);
    $filterSpecial['by_steps'] = $feOp . " TCSTEPS.actions like '%{$args->steps}%' ";  
  }    
    
  if($args->expected_results != "")
  {
    $args->expected_results = $db->prepare_string($args->expected_results);
    $filterSpecial['by_expected_results'] = $feOp . " TCSTEPS.expected_results like '%{$args->expected_results}%' "; 
  }    

  $k2w = array('name' => 'NH_TC', 'summary' => 'TCV', 'preconditions' => 'TCV');
  $jollyEscaped = $db->prepare_string($args->jolly);
  foreach($k2w as $kf => $alias)
  {
    if($args->$kf != "" || $args->jolly != '')
    {
      if( $args->jolly == '' )
      {
        $args->$kf =  $db->prepare_string($args->$kf);
      }  
      $filterSpecial[$kf] = " {$feOp} {$alias}.{$kf} like ";
      $filterSpecial[$kf] .= ($args->jolly == '') ? " '%{$args->$kf}%' " : " '%{$jollyEscaped}%' "; 
    }
  } 
 
  $otherFilters = '';  
  if(!is_null($filterSpecial))
  {
    $otherFilters = " AND (" . implode("",$filterSpecial) . ")";
  }  


  if($args->custom_field_id > 0)
  {

    // Need to understand custom type to fomat the value

    $args->custom_field_id = $db->prepare_string($args->custom_field_id);

    $cf_def = $gui->design_cf[$args->custom_field_id];
    $from['by_custom_field']= " JOIN {$tables['cfield_design_values']} CFD " .
                              " ON CFD.node_id=NH_TCV.id ";
    
    $filter['by_custom_field'] = " AND CFD.field_id={$args->custom_field_id} ";
    
    switch($gui->cf_types[$cf_def['type']])
    {
      case 'date':
        $args->custom_field_value = $tproject_mgr->cfield_mgr->cfdate2mktime($args->custom_field_value);
        
        $filter['by_custom_field'] .= " AND CFD.value = {$args->custom_field_value}";
      break;

      case 'datetime':
        $args->custom_field_value = $tproject_mgr->cfield_mgr->cfdatetime2mktime($args->custom_field_value);
        
        $filter['by_custom_field'] .= " AND CFD.value = {$args->custom_field_value}";
      break;

      default:
        $args->custom_field_value = $db->prepare_string($args->custom_field_value);
        $filter['by_custom_field'] .= " AND CFD.value like '%{$args->custom_field_value}%' ";
      break;

    }
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

  if( $args->status > 0)
  {
    $filter['status'] = " AND TCV.status = {$args->status} ";
  }  


  $args->created_by = trim($args->created_by);
  $from['users'] = '';
  if( $args->created_by != '' )
  {
    $from['users'] .= " JOIN {$tables['users']} AUTHOR ON AUTHOR.id = TCV.author_id ";
    $filter['author'] = " AND ( AUTHOR.login LIKE '%{$args->created_by}%' OR " .
                        "       AUTHOR.first LIKE '%{$args->created_by}%' OR " .
                        "       AUTHOR.last LIKE '%{$args->created_by}%') ";
  }  

  $args->edited_by = trim($args->edited_by);
  if( $args->edited_by != '' )
  {
    $from['users'] .= " JOIN {$tables['users']} UPDATER ON UPDATER.id = TCV.updater_id ";
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
  
    $sqlPart2 .= $otherFilters;


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

if($gui->doSearch)
{
  $gui->pageTitle .= " - " . lang_get('match_count') . " : " . $gui->row_qty;
}  

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
else if ($emptyTestProject) 
{
  $gui->warning_msg = lang_get('empty_testproject');
}
else
{
  $gui->warning_msg = lang_get('no_records_found');
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
  $designCfg = getWebEditorCfg('design');
  $designType = $designCfg['type'];
  
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
      $rowData[] = ($designType == 'none' ? nl2br($result['summary']) : $result['summary']);

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
 */
function init_args(&$tprojectMgr)
{
  $_REQUEST=strings_stripSlashes($_REQUEST);

  $args = new stdClass();
  $iParams = array("doAction" => array(tlInputParameter::STRING_N,0,10),
                   "tproject_id" => array(tlInputParameter::INT_N), 
                   "status" => array(tlInputParameter::INT_N),
                   "keyword_id" => array(tlInputParameter::INT_N),
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
                   "modification_date_to" => array(tlInputParameter::STRING_N),
                   "jolly" => array(tlInputParameter::STRING_N));
    
  $args = new stdClass();
  R_PARAMS($iParams,$args);

  // sanitize targetTestCase against XSS
  // remove all blanks
  // remove some html entities
  // remove ()
  $tt = array(' ','<','>','(',')');
  $args->targetTestCase = str_replace($tt,'',$args->targetTestCase);

  $args->userID = intval(isset($_SESSION['userID']) ? $_SESSION['userID'] : 0);

  if(is_null($args->tproject_id) || intval($args->tproject_id) <= 0)
  {
    $args->tprojectID = intval(isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0);
    $args->tprojectName = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 0;
  }  
  else
  {
    $args->tprojectID = intval($args->tproject_id);
    $info = $tprojectMgr->get_by_id($args->tprojectID);
    $args->tprojectName = $info['name'];
  }  

  if($args->tprojectID <= 0)
  {
    throw new Exception("Error Processing Request - Invalid Test project id " . __FILE__);
  }   

  // convert "creation date from" to iso format for database usage
  $k2w = array('creation_date_from' => '','creation_date_to' => " 23:59:59",
               'modification_date_from' => '', 'modification_date_to' => " 23:59:59");

  $k2f = array('creation_date_from' => ' creation_ts >= ',
               'creation_date_to' => 'creation_ts <= ',
               'modification_date_from' => ' modification_ts >= ', 
               'modification_date_to' => ' modification_ts <= ');


  $dateFormat = config_get('date_format');
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
function initializeGui(&$argsObj,&$tprojectMgr)
{
  $gui = new stdClass();

  $gui->pageTitle = lang_get('caption_search_form');
  $gui->warning_msg = '';
  $gui->path_info = null;
  $gui->resultSet = null;
  $gui->tableSet = null;
  $gui->bodyOnLoad = null;
  $gui->bodyOnUnload = null;
  $gui->refresh_tree = false;
  $gui->hilite_testcase_name = false;
  $gui->show_match_count = false;
  $gui->row_qty = 0;
  $gui->doSearch = ($argsObj->doAction == 'doSearch');
  $gui->tproject_id = intval($argsObj->tprojectID);
  
  // ----------------------------------------------------
  $gui->mainCaption = lang_get('testproject') . " " . $argsObj->tprojectName;
 
  $gui->creation_date_from = null;
  $gui->creation_date_to = null;
  $gui->modification_date_from = null;
  $gui->modification_date_to = null;
  $gui->search_important_notice = sprintf(lang_get('search_important_notice'),$argsObj->tprojectName);

  // need to set values that where used on latest search (if any was done)
  // $gui->importance = config_get('testcase_importance_default');

  return $gui;
}

/**
 *
 */
function initSearch(&$gui,&$argsObj,&$tprojectMgr)
{
  $gui->design_cf = $tprojectMgr->cfield_mgr->get_linked_cfields_at_design($argsObj->tprojectID,
                                                                           cfield_mgr::ENABLED,null,'testcase');
  
  $gui->cf_types = $tprojectMgr->cfield_mgr->custom_field_types;
  $gui->filter_by['design_scope_custom_fields'] = !is_null($gui->design_cf);

  $gui->keywords = $tprojectMgr->getKeywords($argsObj->tprojectID);
  $gui->filter_by['keyword'] = !is_null($gui->keywords);

  $oo = $tprojectMgr->getOptions($argsObj->tprojectID);
  $gui->filter_by['requirement_doc_id'] = $oo->requirementsEnabled;

  $gui->importance = intval($argsObj->importance);
  $gui->status = intval($argsObj->status);
  $gui->tcversion = (is_null($argsObj->version) || $argsObj->version == '') ? '' : intval($argsObj->version);

  $gui->tcasePrefix = $tprojectMgr->getTestCasePrefix($argsObj->tprojectID) . config_get('testcase_cfg')->glue_character;


  $gui->targetTestCase = (is_null($argsObj->targetTestCase) || $argsObj->targetTestCase == '') ? 
                         $gui->tcasePrefix : $argsObj->targetTestCase;

  
  $txtin = array("created_by","edited_by","jolly");   
  $jollyKilled = array("summary","steps","expected_results","preconditions","name");
  $txtin = array_merge($txtin, $jollyKilled);
  
  foreach($txtin as $key )
  {
    $gui->$key = $argsObj->$key;
  }  

  if($argsObj->jolly != '')
  {
    foreach($jollyKilled as $key)
    {
      $gui->$key = '';  
    }  
  }  

}
<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Execute Search. 
 * Search is done ONLY ON CURRENT test project
 *
 *
 * @filesource  search.php
 * @package     TestLink
 * @author      TestLink community
 * @copyright   2007-2017, TestLink community 
 * @link        http://www.testlink.org/
 *
 *
 **/
require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$smarty = new TLSmarty();
$tpl = 'searchResults.tpl';

$charset = config_get('charset');
$filter = null;

$cmdMgr = new searchCommands($db);
$cmdMgr->initEnv();

$args = $cmdMgr->getArgs();
$gui = $cmdMgr->getGui();
$cmdMgr->initSchema();
$treeMgr = new tree($db);
$cfieldMgr = new cfield_mgr($db);


$targetSet = cleanUpTarget($db,$args->target);
$canUseTarget = (count($targetSet) > 0);

if($args->oneCheck == false)
{
  $gui->caller = 'search';
  $smarty->assign('gui',$gui);
  $smarty->display($templateCfg->template_dir . $tpl);
  exit();
}  

if($canUseTarget == false && $args->oneValueOK == false)
{
  $smarty->assign('gui',$gui);
  $smarty->display($templateCfg->template_dir . $tpl);
  exit();
}  

// Processing
$map = null;

// CF belongs to ?
$tc_cf_id = null;
$req_cf_id = null;
if( $args->custom_field_id > 0)
{
  if ( isset( $gui->design_cf_tc[$args->custom_field_id] ) )
  {
    $tc_cf_id = $args->custom_field_id;
  }

  if ( isset( $gui->design_cf_req[$args->custom_field_id] ) )
  {
    $req_cf_id = $args->custom_field_id;
  }  
}

$args->reqType = null;
if($args->reqType != '')
{
  $args->reqType = str_replace('RQ','', $args->reqTypes);
}  

if( ($args->tproject_id > 0) && $args->doAction == 'doSearch')
{
  $tables = $cmdMgr->getTables();
  $views = $cmdMgr->getViews();

  $from = array('by_keyword_id' => ' ', 'by_custom_field' => ' ', 
                'by_requirement_doc_id' => '', 'users' => '');
  $tcaseID = null;

  $emptyTestProject = true;

  // Need to get all test cases to filter
  $tcaseSet = $cmdMgr->getTestCaseIDSet($args->tproject_id);
}
        

$mapTC = null;
$mapTS = null;
$mapRS = null;
$mapRQ = null;

// Search on Test Suites
if( $canUseTarget && ($args->ts_summary || $args->ts_title) )
{
  $mapTS = $cmdMgr->searchTestSuites($targetSet,$canUseTarget);
}

// Requirment SPECification
if( $canUseTarget && ($args->rs_scope || $args->rs_title) )
{
  $mapRS = $cmdMgr->searchReqSpec($targetSet,$canUseTarget);
} 

// REQuirements
if( $args->rq_scope || $args->rq_title || $args->rq_doc_id || ($req_cf_id > 0) )
{
  $mapRQ = $cmdMgr->searchReq($targetSet,$canUseTarget,$req_cf_id);  
} 

  
$hasTestCases = (!is_null($tcaseSet) && count($tcaseSet) > 0);
if( $hasTestCases )
{
  $emptyTestProject = false;
  $mapTC = $cmdMgr->searchTestCases($tcaseSet,$targetSet,$canUseTarget,$tc_cf_id);
}  

// Render Results
if( !is_null($mapTC) )
{
  $tcase_mgr = new testcase($db);   
  $tcase_set = array_keys($mapTC);
  $options = array('output_format' => 'path_as_string');
  $gui->path_info = $treeMgr->get_full_path_verbose($tcase_set, $options);
  $gui->resultSet = $mapTC;
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
$table = buildTCExtTable($gui, $charset, $img['edit_icon'], $img['history_small']);

if (!is_null($table)) 
{
  $gui->tableSet[] = $table;
}

$table = null;
if( !is_null($mapTS))
{
  $gui->resultTestSuite = $mapTS;
  $table = buildTSExtTable($gui, $charset, $img['edit_icon'], $img['history_small']); 
}  
  
$gui->warning_msg = '';
if(!is_null($table))
{
  $gui->tableSet[] = $table;
}  

$table = null;
if( !is_null($mapRS))
{
  $gui->resultReqSpec = $mapRS;
  $table = buildRSExtTable($gui, $charset, $img['edit_icon'], $img['history_small']); 
}  
  
$gui->warning_msg = '';
if(!is_null($table))
{
  $gui->tableSet[] = $table;
}  

$table = null;
if( !is_null($mapRQ))
{
  $gui->resultReq = $mapRQ;
  $req_set = array_keys($mapRQ);
  $options = array('output_format' => 'path_as_string');
  $gui->path_info = $treeMgr->get_full_path_verbose($req_set,$options);

  $table = buildRQExtTable($gui, $charset);
}

$gui->warning_msg = '';
if(!is_null($table))
{
  $gui->tableSet[] = $table;
}  

$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $tpl);


/**
 * 
 *
 */
function buildTCExtTable($gui, $charset, $edit_icon, $history_icon) 
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
    
    $titleSeparator = config_get('gui_title_separator_1');
    
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
                   " [v" . $result['version'] . "]" . $titleSeparator .
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
 *
 */
function buildTSExtTable($gui, $charset, $edit_icon, $history_icon) 
{
  $table = null;
  $designCfg = getWebEditorCfg('design');
  $designType = $designCfg['type'];
  
  if(count($gui->resultTestSuite) > 0) 
  {
    $labels = array('test_suite' => lang_get('test_suite'), 
                    'details' => lang_get('details'));
    $columns = array();
    
    $columns[] = array('title_key' => 'test_suite', 'type' => 'text');
    $columns[] = array('title_key' => 'details');
  
    // Extract the relevant data and build a matrix
    $matrixData = array();
    
    foreach($gui->resultTestSuite as $result) 
    {
     $edit_link = "<a href=\"javascript:openTSEditWindow({$result['id']});\">" .
                   "<img title=\"". lang_get('design') . "\" src=\"{$edit_icon}\" /></a> ";
  
      $rowData = array();
      
      $rowData[] = $edit_link . htmlentities($result['name'], ENT_QUOTES, $charset);
  
      $rowData[] = ($designType == 'none' ? nl2br($result['details']) : $result['details']);

      $matrixData[] = $rowData;
    }
    
    $table = new tlExtTable($columns, $matrixData, 'tl_table_test_suite_search');
    
    $table->setSortByColumnName($labels['test_suite']);
    $table->sortDirection = 'DESC';
    
    $table->showToolbar = true;
    $table->allowMultiSort = false;
    $table->toolbarRefreshButton = false;
    $table->toolbarShowAllColumnsButton = false;
    
    $table->addCustomBehaviour('text', array('render' => 'columnWrap'));
    $table->storeTableState = false;
  }
  return $table;
}

/**
 * 
 *
 */
function buildRSExtTable($gui, $charset, $edit_icon, $history_icon) 
{
  $table = null;
  $designCfg = getWebEditorCfg('design');
  $designType = $designCfg['type'];
  
  if(count($gui->resultReqSpec) > 0) 
  {
    $labels = array('req_spec' => lang_get('req_spec'), 
                    'scope' => lang_get('scope'));
    $columns = array();
    
    $columns[] = array('title_key' => 'req_spec', 'type' => 'text');
    $columns[] = array('title_key' => 'scope');
  
    // Extract the relevant data and build a matrix
    $matrixData = array();
    
    foreach($gui->resultReqSpec as $result) 
    {
     $edit_link = "<a href=\"javascript:openLinkedReqSpecWindow({$result['req_spec_id']});\">" .
                   "<img title=\"". lang_get('design') . "\" src=\"{$edit_icon}\" /></a> ";
  
      $rowData = array();
      
      $rowData[] = $edit_link . 
                   htmlentities($result['name'] . "[r{$result['revision']}]", ENT_QUOTES, $charset);
  
      $rowData[] = ($designType == 'none' ? nl2br($result['scope']) : $result['scope']);

      $matrixData[] = $rowData;
    }
    
    $table = new tlExtTable($columns, $matrixData, 'tl_table_req_spec_search');
    
    $table->setSortByColumnName($labels['req_spec']);
    $table->sortDirection = 'DESC';
    
    $table->showToolbar = true;
    $table->allowMultiSort = false;
    $table->toolbarRefreshButton = false;
    $table->toolbarShowAllColumnsButton = false;
    
    $table->addCustomBehaviour('text', array('render' => 'columnWrap'));
    $table->storeTableState = false;
  }
  return $table;
}



/**
 * 
 *
 */
function buildRQExtTable($gui, $charset)
{
  $table = null;
  $designCfg = getWebEditorCfg('design');
  $designType = $designCfg['type'];

  $lbl = array('edit' => 'requirement', 'req_spec' => 'req_spec', 
               'requirement' => 'requirement','scope' => 'scope', 
               'version_revision_tag' => 'version_revision_tag');

  $labels = init_labels($lbl);
  $edit_icon = TL_THEME_IMG_DIR . "edit_icon.png";
  
  if(count($gui->resultReq) > 0) 
  {
    $columns = array();
    
    $columns[] = array('title_key' => 'req_spec');
    $columns[] = array('title_key' => 'requirement', 'type' => 'text');
  
    $columns[] = array('title_key' => 'scope');

    // Extract the relevant data and build a matrix
    $matrixData = array();
    
    $key2loop = array_keys($gui->resultReq);
    $img = "<img title=\"{$labels['edit']}\" src=\"{$edit_icon}\" />";
    $reqVerHref = '<a href="javascript:openLinkedReqVersionWindow(%s,%s)">' . $labels['version_revision_tag'] . ' </a>'; 
    $reqRevHref = '<a href="javascript:openReqRevisionWindow(%s)">' . $labels['version_revision_tag'] . ' </a>'; 
  
    foreach($key2loop as $req_id)
    {
      $rowData = array();
      $itemSet = $gui->resultReq[$req_id];
      $rfx = $itemSet;
      
      // We Group by Requirement path
      $rowData[] = htmlentities($gui->path_info[$rfx['req_id']], ENT_QUOTES, $charset);

      $edit_link = "<a href=\"javascript:openLinkedReqWindow(" . $rfx['req_id'] . ")\">" . "{$img}</a> ";
      $title = htmlentities($rfx['req_doc_id'], ENT_QUOTES, $charset) . ":" .
               htmlentities($rfx['name'], ENT_QUOTES, $charset);

      $matches = '';
      $rowData[] = $edit_link . $title . ' ' . $matches;
      $rowData[] = ($designType == 'none' ? nl2br($rfx['scope']) : $rfx['scope']);
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
  return($table);
}




/**
 *
 */
function cleanUpTarget(&$dbHandler,$target)
{
  $s = preg_replace("/ {2,}/", " ", $target);
  $theSet = explode(' ',$s);
  $targetSet = array();
  foreach($theSet as $idx => $val)
  {
    if(trim($val) != '')
    {
      $targetSet[] = $dbHandler->prepare_string($val);
    }  
  } 
  return $targetSet;
}



/*
create view latest_tcase_version_number  AS
SELECT NH_TC.id AS testcase_id,max(TCV.version) AS version
FROM nodes_hierarchy NH_TC 
JOIN nodes_hierarchy NH_TCV ON NH_TCV.parent_id = NH_TC.id 
JOIN tcversions TCV ON NH_TCV.id = TCV.id 
group by testcase_id
===========

SELECT LVN.testcase_id, TCV.id,TCV.version 
FROM latest_tcase_version_number LVN 
JOIN nodes_hierarchy NH_TCV ON NH_TCV.parent_id = LVN.testcase_id 
JOIN tcversions TCV ON NH_TCV.id = TCV.id AND LVN.version = TCV.version
WHERE 1=1 AND NH_TCV.parent_id IN (7945) AND ( 1=1 AND TCV.summary like '%three%' )

create view latest_rspec_revision AS
SELECT parent_id AS req_spec_id,testproject_id, max(revision) AS revision
FROM req_specs_revisions RSR
JOIN req_specs RS ON RS.id = RSR.parent_id 
group by parent_id,testproject_id

CREATE VIEW latest_req_version AS
SELECT RQ.id as req_id, MAX(RQV.version) vresion FROM nodes_hierarchy NHRQV
JOIN requirements RQ on RQ.id = NHRQV.parent_id
JOIN req_versions RQV on RQV.id = NHRQV.id
GROUP BY RQ.id


select RQV.scope,RQ.req_doc_id,NHRQ.name  from nodes_hierarchy NHRQV
JOIN latest_req_version LV on LV.req_id = NHRQV.parent_id
JOIN req_versions RQV on NHRQV.id = RQV.id AND RQV.version = LV.version
JOIN nodes_hierarchy NHRQ on NHRQ.id = LV.req_id
JOIN requirements RQ on RQ.id = LV.req_id 

*/

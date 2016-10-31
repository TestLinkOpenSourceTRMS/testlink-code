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
 * @copyright   2007-2016, TestLink community 
 * @link        http://www.testlink.org/
 *
 *
 * @internal revisions
 * @since 1.9.16
 **/
require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$smarty = new TLSmarty();

$tpl = 'searchResults.tpl';
$tproject_mgr = new testproject($db);
$tcase_mgr = new testcase ($db);
 
$tcase_cfg = config_get('testcase_cfg');
$charset = config_get('charset');
$filter = null;
list($args,$filter) = init_args($tproject_mgr);
Kint::dump($_REQUEST);
Kint::dump($args);

$ga = initializeGui($args,$tproject_mgr);
$gx = $tcase_mgr->getTcSearchSkeleton($args);
$gui = (object)array_merge((array)$ga,(array)$gx);

initSearch($gui,$args,$tproject_mgr);


echo __FILE__;

$map = null;

if ($args->tprojectID && $args->doAction == 'doSearch')
{
  $tables = tlObjectWithDB::getDBTables(array('cfield_design_values','nodes_hierarchy',
                                              'requirements','req_coverage','tcsteps',
                                              'testcase_keywords','req_specs_revisions',
                                              'testsuites','tcversions','users'));
                                
  $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tprojectID);
  $gui->tcasePrefix .= $tcase_cfg->glue_character;

  $from = array('by_keyword_id' => ' ', 'by_custom_field' => ' ', 'by_requirement_doc_id' => '', 'users' => '');
  $tcaseID = null;
  $emptyTestProject = false;

  // Need to get all test cases to filter
  $tcaseSet = array();
  $tproject_mgr->get_all_testcases_id($args->tprojectID,$tcaseSet);

  $reqspec_mgr = new requirement_spec_mgr($db);
  $reqSpecSet = $reqspec_mgr->get_all_id_in_testproject($args->tprojectID,array('output' => 'id'));

   $reqSet = $tproject_mgr->get_all_requirement_ids($args->tprojectID);


    $nt2exclude = array('testcase' => 'exclude_me',
                        'testplan' => 'exclude_me',
                        'requirement_spec'=> 'exclude_me',
                        'requirement'=> 'exclude_me');
                                                  

    $nt2exclude_children=array('testcase' => 'exclude_my_children',
                               'requirement_spec'=> 'exclude_my_children');

    $my['options'] = array('recursive' => 0, 'output' => 'id');
    $my['filters'] = array('exclude_node_types' => $nt2exclude,
                           'exclude_children_of' => $nt2exclude_children);
    $tsuiteSet = $tproject_mgr->tree_manager->get_subtree(
                              $args->tprojectID,$my['filters'],$my['options']);
    
    if(!is_null($tcaseSet))
    {
      $filter['by_tc_id'] = " AND NH_TCV.parent_id IN (" . implode(",",$tcaseSet) . ") ";
    }  
    else
    {
      // Force Nothing extracted, because test project 
      // has no test case defined 
      $emptyTestProject = true;
      $filter['by_tc_id'] = " AND 1 = 0 ";
    }  
  }
        
  //echo __LINE__; die();
  // search has to be done only on latest version

  $doFilterOnTestCase = false;
  $filterSpecial['tricky'] = " 1=0 ";
  
  Kint::dump($args->target); 
  // Multiple space clean up
  $s = preg_replace("/ {2,}/", " ", $args->target);
  $targetSet = explode(' ',$s);
  foreach($targetSet as $idx => $val)
  {
    $targetSet[$id] = $db->prepare_string($val);
  } 

  Kint::dump($_REQUEST);

  // REQSPEC
  if( $args->rs_scope || $args->rs_title )
  {
    $sql = "SELECT RSRV.name, RSRV.scope, LRSR.req_spec_id, RSRV.id,LRSR.revision " . 
           "FROM latest_rspec_revision LRSR " .
           "JOIN {$tables['req_specs_revisions']} RSRV " .
           "ON RSRV.parent_id = LRSR.req_spec_id " .
           "AND RSRV.revision = LRSR.revision " .
           "WHERE LRSR.testproject_id = " . $args->tprojectID;

    $filterSpecial['tricky'] = " 1=0 ";
  
    $filterSpecial['scope'] = ' OR ( ';
    $filterSpecial['scope'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
    foreach($targetSet as $target)
    {
      $filterSpecial['scope'] .= $args->and_or . " RSRV.scope like '%{$target}%' ";  
    }  
    $filterSpecial['scope'] .= ')';
  
    $filterSpecial['name'] = ' OR ( ';
    $filterSpecial['name'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
    foreach($targetSet as $target)
    {
      $filterSpecial['name'] .= $args->and_or . " RSRV.name like '%{$target}%' ";  
    }  
    $filterSpecial['name'] .= ')';

    $otherFilters = '';  
    if(!is_null($filterSpecial))
    {
      $otherFilters = " AND (" . implode("",$filterSpecial) . ")";
    }  

    $sql .= $otherFilters;
    $mapRS = $db->fetchRowsIntoMap($sql,'req_spec_id'); 
    Kint::dump($mapRS);
   

  } 

  // REQ
  $doFilterOnReq = false;
  if( $args->rq_scope || $args->rq_title || $args->rq_doc_id)
  {
    $fi = null;
    $doFilterOnReq = true;
    $args->created_by = trim($args->created_by);
    $from['users'] = '';
    if( $args->created_by != '' )
    {
      $from['users'] .= " JOIN {$tables['users']} AUTHOR ON RQAUTHOR.id = RQV.author_id ";
      $fi['author'] = " AND ( AUTHOR.login LIKE '%{$args->created_by}%' OR " .
                      "       AUTHOR.first LIKE '%{$args->created_by}%' OR " .
                      "       AUTHOR.last LIKE '%{$args->created_by}%') ";
    }  
  
    $args->edited_by = trim($args->edited_by);
    if( $args->edited_by != '' )
    {
      $doFilterOnTestCase = true;
      $from['users'] .= " JOIN {$tables['users']} UPDATER ON UPDATER.id = RQV.modifier_id ";
      $fi['modifier'] = " AND ( UPDATER.login LIKE '%{$args->edited_by}%' OR " .
                            "       UPDATER.first LIKE '%{$args->edited_by}%' OR " .
                            "       UPDATER.last LIKE '%{$args->edited_by}%') ";
    }  


    $sql = " select RQ.id AS req_id, RQV.scope,RQ.req_doc_id,NHRQ.name  " .
           " from nodes_hierarchy NHRQV " .
           " JOIN latest_req_version LV on LV.req_id = NHRQV.parent_id " .
           " JOIN req_versions RQV on NHRQV.id = RQV.id AND RQV.version = LV.version " .
           " JOIN nodes_hierarchy NHRQ on NHRQ.id = LV.req_id " .
           " JOIN requirements RQ on RQ.id = LV.req_id " .
           $from['users'] .
           " WHERE RQ.id IN(" . implode(',', $reqSet) . ")";

    $filterSpecial['tricky'] = " 1=0 ";
  
    $filterSpecial['scope'] = ' OR ( ';
    $filterSpecial['scope'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
    foreach($targetSet as $target)
    {
      $filterSpecial['scope'] .= $args->and_or . " RQV.scope like '%{$target}%' ";  
    }  
    $filterSpecial['scope'] .= ')';
  
    $filterSpecial['name'] = ' OR ( ';
    $filterSpecial['name'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
    foreach($targetSet as $target)
    {
      $filterSpecial['name'] .= $args->and_or . " NHRQ.name like '%{$target}%' ";  
    }  
    $filterSpecial['name'] .= ')';

    $filterSpecial['req_doc_id'] = ' OR ( ';
    $filterSpecial['req_doc_id'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
    foreach($targetSet as $target)
    {
      $filterSpecial['req_doc_id'] .= $args->and_or . " RQ.req_doc_id like '%{$target}%' ";  
    }  
    $filterSpecial['req_doc_id'] .= ')';


    $otherFilters = '';  
    if(!is_null($filterSpecial))
    {
      $otherFilters = " AND (" . implode("",$filterSpecial) . ")";
    }  

    $xfil = ''; 
    if(!is_null($fi))
    {
      $xfil = implode("",$fi);
    }  

    $sql .= $xfil . $otherFilters;
    $mapRQ = $db->fetchRowsIntoMap($sql,'req_id'); 
    Kint::dump($mapRQ);
  } 





  //$target = $db->prepare_string($args->target);
  $doFilterOnTestCase = false;
  $from['tc_steps'] = "";
  if($args->tc_steps || $args->tc_expected_results)
  {
    $doFilterOnTestCase = true;
    $from['tc_steps'] = " LEFT OUTER JOIN {$tables['nodes_hierarchy']} " .
                        " NH_TCSTEPS ON NH_TCSTEPS.parent_id = NH_TCV.id " .
                        " LEFT OUTER JOIN {$tables['tcsteps']} TCSTEPS " .
                        " ON NH_TCSTEPS.id = TCSTEPS.id  ";
  }

  if($args->tc_steps)
  {
    $filterSpecial['by_steps'] = ' OR ( ';
    $filterSpecial['by_steps'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
    
    foreach($targetSet as $target)
    {
      $filterSpecial['by_steps'] .= $args->and_or . " TCSTEPS.actions like '%{$target}%' ";  
    }  
    $filterSpecial['by_steps'] .= ')';
  }    
    
  if($args->tc_expected_results)
  {
    $filterSpecial['by_expected_results'] = ' OR ( ';
    $filterSpecial['by_expected_results'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
    
    foreach($targetSet as $target)
    {
      $filterSpecial['by_expected_results'] .= $args->and_or . 
                   " TCSTEPS.expected_results like '%{$target}%' "; 
    }  
    $filterSpecial['by_expected_results'] .= ')';
  }    

  $k2w = array('name' => 'NH_TC', 'summary' => 'TCV', 'preconditions' => 'TCV');
  $i2s = array('name' => 'tc_title', 'summary' => 'tc_summary', 
               'preconditions' => 'tc_preconditions');
  foreach($k2w as $kf => $alias)
  {
    $in = $i2s[$kf];
    echo $args->$in . '<br>';
    if($args->$in)
    {
      $doFilterOnTestCase = true;
 
      $filterSpecial[$kf] = ' OR ( ';
      $filterSpecial[$kf] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
 
      foreach($targetSet as $target)
      {
        echo $target . '<br>';
        $filterSpecial[$kf] .= " {$args->and_or} {$alias}.{$kf} like ";
        $filterSpecial[$kf] .= " '%{$target}%' "; 
      }  
      $filterSpecial[$kf] .= ' )';
    }
  } 

  Kint::dump($filterSpecial);
  $otherFilters = '';  
  if(!is_null($filterSpecial))
  {
    $otherFilters = " AND (" . implode("",$filterSpecial) . ")";
  }  


/*
create view latest_version_number  AS
SELECT NH_TC.id AS testcase_id,max(TCV.version) AS version
FROM nodes_hierarchy NH_TC 
JOIN nodes_hierarchy NH_TCV ON NH_TCV.parent_id = NH_TC.id 
JOIN tcversions TCV ON NH_TCV.id = TCV.id 
group by testcase_id
===========

SELECT LVN.testcase_id, TCV.id,TCV.version 
FROM latest_version_number LVN 
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

  // Search on latest test case version using view    
  $sqlFields = " SELECT LVN.testcase_id, NH_TC.name, TCV.id AS tcversion_id," .
               " TCV.summary, TCV.version, TCV.tc_external_id "; 
    


  if($doFilterOnTestCase)
  {
    $args->created_by = trim($args->created_by);
    $from['users'] = '';
    if( $args->created_by != '' )
    {
      $doFilterOnTestCase = true;
      $from['users'] .= " JOIN {$tables['users']} AUTHOR ON AUTHOR.id = TCV.author_id ";
      $filter['author'] = " AND ( AUTHOR.login LIKE '%{$args->created_by}%' OR " .
                          "       AUTHOR.first LIKE '%{$args->created_by}%' OR " .
                          "       AUTHOR.last LIKE '%{$args->created_by}%') ";
    }  
  
    $args->edited_by = trim($args->edited_by);
    if( $args->edited_by != '' )
    {
      $doFilterOnTestCase = true;
      $from['users'] .= " JOIN {$tables['users']} UPDATER ON UPDATER.id = TCV.updater_id ";
      $filter['modifier'] = " AND ( UPDATER.login LIKE '%{$args->edited_by}%' OR " .
                          "         UPDATER.first LIKE '%{$args->edited_by}%' OR " .
                          "         UPDATER.last LIKE '%{$args->edited_by}%') ";
    }  
  }


  // search fails if test case has 0 steps - Added LEFT OUTER
  $sqlPart2 = " FROM latest_version_number LVN " .
              " JOIN {$tables['nodes_hierarchy']} NH_TC ON NH_TC.id = LVN.testcase_id " .
              " JOIN {$tables['nodes_hierarchy']} NH_TCV ON NH_TCV.parent_id = NH_TC.id  " .
              " JOIN {$tables['tcversions']} TCV ON NH_TCV.id = TCV.id " .
              " AND TCV.version = LVN.version " . 
              $from['tc_steps'] . $from['users'] .
              " WHERE LVN.testcase_id IN (" . implode(',', $tcaseSet) . ")";

  if($doFilterOnTestCase)
  {
    if ($filter)
    {
      $sqlPart2 .= implode("",$filter);
    }
 
    $sql = $sqlFields . $sqlPart2 . $otherFilters;
    echo $sql;
    $mapTC = $db->fetchRowsIntoMap($sql,'testcase_id'); 
    Kint::dump($mapTC);   
  }  

  // Search on Test Suites
  // Search on latest test case version using view    
  $filterSpecial = null;
  $filterSpecial['tricky'] = " 1=0 ";

  if($args->ts_summary)
  {
    $filterSpecial['ts_summary'] = ' OR ( ';
    $filterSpecial['ts_summary'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
    
    foreach($targetSet as $target)
    {
      $filterSpecial['ts_summary'] .= $args->and_or . " TS.details like '%{$target}%' ";
    }  
    $filterSpecial['ts_summary'] .= ')';
  }  

  if($args->ts_title)
  {
    $filterSpecial['ts_title'] = ' OR ( ';
    $filterSpecial['ts_title'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';

    foreach($targetSet as $target)
    {
      $filterSpecial['ts_title'] .= $args->and_or . " NH_TS.name like '%{$target}%' ";
    }  
    $filterSpecial['ts_title'] .= ')';
  }  

  $otherFilters = '';  
  if(!is_null($filterSpecial))
  {
    $otherFilters = " AND (" . implode("",$filterSpecial) . ")";
  }  

  $mapTS = null;
  if($args->ts_title || $args->ts_summary)
  {
    $sqlFields = " SELECT NH_TS.name, TS.id, TS.details " .
                 " FROM {$tables['nodes_hierarchy']} NH_TS " .
                 " JOIN {$tables['testsuites']} TS ON TS.id = NH_TS.id " .
                 " WHERE TS.id IN (" . implode(',', $tsuiteSet) . ")";
    
    $sql = $sqlFields . $otherFilters;
    $mapTS = $db->fetchRowsIntoMap($sql,'id'); 

    Kint::dump($mapTS);
  }  

  if ($mapTC)
  {
    $tcase_mgr = new testcase($db);   
    $tcase_set = array_keys($mapTC);
    $options = array('output_format' => 'path_as_string');
    $gui->path_info = $tproject_mgr->tree_manager->get_full_path_verbose($tcase_set, $options);
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

  // TS
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

  // RSPEC
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
    $gui->path_info = $tproject_mgr->tree_manager->get_full_path_verbose($req_set,$options);

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
    
    $titleSeperator = config_get('gui_title_separator_1');
    
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
    
    $titleSeperator = config_get('gui_title_separator_1');
    
    foreach($gui->resultReqSpec as $result) 
    {
     $edit_link = "<a href=\"javascript:openRSEditWindow({$result['id']});\">" .
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
               'requirement' => 'requirement','scope' => 'scope');

  $labels = init_labels($lbl);
  $edit_icon = TL_THEME_IMG_DIR . "edit_icon.png";
  
  //Kint::dump($gui->resultReq);die();

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
    // req_revision_id
    $reqRevHref = '<a href="javascript:openReqRevisionWindow(%s)">' . $labels['version_revision_tag'] . ' </a>'; 
  
    Kint::dump($gui->resultReq);
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
function init_args(&$tprojectMgr)
{
  $_REQUEST=strings_stripSlashes($_REQUEST);

  $args = new stdClass();
  $iParams = array("target" => array(tlInputParameter::STRING_N),
                   "doAction" => array(tlInputParameter::STRING_N,0,10),
                   "tproject_id" => array(tlInputParameter::INT_N), 
                   "status" => array(tlInputParameter::INT_N),
                   "keyword_id" => array(tlInputParameter::INT_N),
                   "custom_field_id" => array(tlInputParameter::INT_N),
                   "created_by" => array(tlInputParameter::STRING_N,0,50),
                   "edited_by" => array(tlInputParameter::STRING_N,0,50),

                   "rq_scope" => array(tlInputParameter::CB_BOOL),
                   "rq_title" => array(tlInputParameter::CB_BOOL),
                   "rq_doc_id" => array(tlInputParameter::CB_BOOL),

                   "rs_scope" => array(tlInputParameter::CB_BOOL),
                   "rs_title" => array(tlInputParameter::CB_BOOL),
                   "tc_summary" => array(tlInputParameter::CB_BOOL),
                   "tc_title" => array(tlInputParameter::CB_BOOL),
                   "tc_steps" => array(tlInputParameter::CB_BOOL),
                   "tc_expected_results" => array(tlInputParameter::CB_BOOL),
                   "ts_summary" => array(tlInputParameter::CB_BOOL),
                   "ts_title" => array(tlInputParameter::CB_BOOL),

                   "custom_field_value" => array(tlInputParameter::STRING_N,0,20),
                   "creation_date_from" => array(tlInputParameter::STRING_N),
                   "creation_date_to" => array(tlInputParameter::STRING_N),
                   "modification_date_from" => array(tlInputParameter::STRING_N),
                   "modification_date_to" => array(tlInputParameter::STRING_N));
    
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

  // 
  $args->and_or = isset($_REQUEST['and_or']) ? $_REQUEST['and_or'] : 'or'; 
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

  $gui->filter_by['design_scope_custom_fields'] = !is_null($gui->design_cf);

  $gui->keywords = $tprojectMgr->getKeywords($argsObj->tprojectID);
  $gui->filter_by['keyword'] = !is_null($gui->keywords);

  $reqSpecSet = $tprojectMgr->genComboReqSpec($argsObj->tprojectID);
  $gui->filter_by['requirement_doc_id'] = !is_null($reqSpecSet);
  $reqSpecSet = null; 

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
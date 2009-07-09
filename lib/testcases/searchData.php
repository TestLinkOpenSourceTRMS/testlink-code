<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: searchData.php,v 1.45 2009/07/09 19:02:55 schlundus Exp $
 * Purpose:  This page presents the search results. 
 *
 * rev:
 *     20090228 - franciscom - if targetTestCase == test case prefix => 
 *                             consider as empty => means search all.
 *
 *     20090125 - franciscom - BUGID - search by requirement doc id
 *     20081115 - franciscom - refactored to improve:
 *     performance and information displayed.
 *              
**/
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$tproject_mgr = new testproject($db);

$tcase_cfg = config_get('testcase_cfg');
$gui = new stdClass();
$gui->pageTitle = lang_get('caption_search_form');
$gui->warning_msg = '';
$gui->tcasePrefix = '';
$gui->path_info = null;
$gui->resultSet = null;
$gui->bodyOnLoad = null;
$gui->bodyOnUnload = null;
$gui->refresh_tree = false;
$gui->hilite_testcase_name = false;
$gui->show_match_count = false;
$gui->tc_current_version = null;

$map = null;
$args = init_args();
if ($args->tprojectID)
{
    $tables['cfield_design_values'] = DB_TABLE_PREFIX . 'cfield_design_values';
    $tables['nodes_hierarchy'] = DB_TABLE_PREFIX . 'nodes_hierarchy';
    $tables['requirements'] = DB_TABLE_PREFIX . 'requirements';
    $tables['req_coverage'] = DB_TABLE_PREFIX . 'req_coverage';
    $tables['testcase_keywords'] = DB_TABLE_PREFIX . 'testcase_keywords'; 
    $tables['tcversions'] = DB_TABLE_PREFIX . 'tcversions';
    
    $gui->tcasePrefix=$tproject_mgr->getTestCasePrefix($args->tprojectID);
    $gui->tcasePrefix .= $tcase_cfg->glue_character;

    $from = array('by_keyword_id' => ' ', 'by_custom_field' => ' ', 'by_requirement_doc_id' => '');
    $filter = null;
    
    if($args->targetTestCase && strcmp($args->targetTestCase,$gui->tcasePrefix) !=0 )
    {
        $tcase_mgr = new testcase ($db);
        $tcaseID = $tcase_mgr->getInternalID($args->targetTestCase,$tcase_cfg->glue_character); 
        $filter['by_tc_id'] = " AND NHB.parent_id = {$tcaseID} ";
    }
    else
    {
        $tproject_mgr->get_all_testcases_id($args->tprojectID,$a_tcid);
        $filter['by_tc_id'] = " AND NHB.parent_id IN (" . implode(",",$a_tcid) . ") ";
    }
    
    if($args->version)
    {
        $filter['by_version'] = " AND version = {$args->version} ";
    }
    
    if($args->keyword_id)				
    {
        $from['by_keyword_id'] = " ,{$tables['testcase_keywords']} KW ";
        $filter['by_keyword_id'] = " AND NHA.id = KW.testcase_id AND KW.keyword_id = {$args->keyword_id} ";	
    }
    
    if($args->name != "")
    {
        $args->name =  $db->prepare_string($args->name);
        $filter['by_name'] = " AND NHA.name like '%{$args->name}%' ";
    }
    
    if($args->summary != "")
    {
        $summary = $db->prepare_string($args->summary);
        $filter['by_summary'] = " AND summary like '%{$args->summary}%' ";
    }    
    
    if($args->steps != "")
    {
        $args->steps = $db->prepare_string($args->steps);
        $filter['by_steps'] = " AND steps like '%{$args->steps}%' ";	
    }    
    
    if($args->expected_results != "")
    {
        $args->expected_results = $db->prepare_string($args->expected_results);
        $filter['by_expected_results'] = " AND expected_results like '%{$args->expected_results}%' ";	
    }    
    
    // ------------------------------------------------------------------------------------
    // BUGID
    if($args->custom_field_id > 0)
    {
        $args->custom_field_id = $db->prepare_string($args->custom_field_id);
        $args->custom_field_value = $db->prepare_string($args->custom_field_value);
        $from['by_custom_field']= " ,{$tables['cfield_design_values']} CFD "; 
        $filter['by_custom_field'] = " AND CFD.field_id={$args->custom_field_id} " .
                                     " AND CFD.node_id=NHA.id " .
                                     " AND CFD.value like '%{$args->custom_field_value}%' ";
    }
   
   	if($args->requirement_doc_id != "")
    {
       $args->requirement_doc_id = $db->prepare_string($args->requirement_doc_id);
       $from['by_requirement_doc_id']= " , {$tables['requirements']} REQ, " .
                                       " {$tables['req_coverage']}  RC";  
       $filter['by_requirement_doc_id']=" AND RC.testcase_id = NHA.id " .
                                        " AND REQ.req_doc_id like '%{$args->requirement_doc_id}%' " .
                                        " AND REQ.id=RC.req_id "; 
    }   
    
    
    // ------------------------------------------------------------------------------------
    $sql = " SELECT NHA.id AS testcase_id,NHA.name,TCV.id AS tcversion_id," .
           " summary,steps,expected_results,version,tc_external_id".
           " FROM {$tables['nodes_hierarchy']} NHA, " .
           " {$tables['nodes_hierarchy']} NHB, {$tables['tcversions']} TCV " .
           " {$from['by_keyword_id']} {$from['by_custom_field']} {$from['by_requirement_doc_id']}".
           " WHERE NHA.id = NHB.parent_id AND NHB.id = TCV.id ";
           
    if ($filter)
    {
        $sql .= implode("",$filter);
    }
    
    $map = $db->fetchRowsIntoMap($sql,'testcase_id');	
}

$smarty = new TLSmarty();
$gui->row_qty=count($map);
if($gui->row_qty)
{
	$tpl = 'tcSearchResults.tpl';
  $gui->pageTitle .= " - " . lang_get('match_count') . ":" . $gui->row_qty;
  if($gui->row_qty <= $tcase_cfg->search->max_qty_for_display)
  {	
	    $tcase_mgr = new testcase($db);   
      $tcase_set=array_keys($map);
      $gui->path_info=$tproject_mgr->tree_manager->get_full_path_verbose($tcase_set);
	    $gui->resultSet=$map;
	}
	else
	{
	    $gui->warning_msg=lang_get('too_wide_search_criteria');
	}
}
else
{
	$the_tpl = config_get('tpl');
	$tpl=$the_tpl['tcView'];
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
  
    $strnull = array('name','summary','steps','expected_results','custom_field_value',
                     'targetTestCase','requirement_doc_id');
    foreach($strnull as $keyvar)
    {
        $args->$keyvar = isset($_REQUEST[$keyvar]) ? trim($_REQUEST[$keyvar]) : null;  
    }

    $int0 = array('keyword_id','version','custom_field_id');
    foreach($int0 as $keyvar)
    {
        $args->$keyvar = isset($_REQUEST[$keyvar]) ? intval($_REQUEST[$keyvar]) : 0;  
    }
    
    $args->userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
    $args->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

    return $args;
}
?>
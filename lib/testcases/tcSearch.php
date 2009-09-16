<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: tcSearch.php,v 1.1 2009/09/16 19:53:01 schlundus Exp $
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
$gui->row_qty = 0;
$map = null;
$args = init_args();
if ($args->tprojectID)
{
	$tables = tlObjectWithDB::getDBTables(
							array("cfield_design_values",'nodes_hierarchy',
								'requirements','req_coverage','testcase_keywords','tcversions'
							));
    $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tprojectID);
    $gui->tcasePrefix .= $tcase_cfg->glue_character;

    $from = array('by_keyword_id' => ' ', 'by_custom_field' => ' ', 'by_requirement_doc_id' => '');
    $filter = null;
    
    if($args->targetTestCase != "" && strcmp($args->targetTestCase,$gui->tcasePrefix) != 0)
    {
     	if (strpos($args->targetTestCase,$tcase_cfg->glue_character) === false)
    		$args->targetTestCase = $gui->tcasePrefix . $args->targetTestCase;
   	 
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
        $args->summary = $db->prepare_string($args->summary);
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
     	$from['by_requirement_doc_id'] = " , {$tables['requirements']} REQ, " .
                                       " {$tables['req_coverage']}  RC";  
    	$filter['by_requirement_doc_id'] = " AND RC.testcase_id = NHA.id " .
                                        " AND REQ.req_doc_id like '%{$args->requirement_doc_id}%' " .
                                        " AND REQ.id=RC.req_id "; 
    }   
    
    $sql = " SELECT NHA.id AS testcase_id,NHA.name,TCV.id AS tcversion_id," .
           " summary,steps,expected_results,version,tc_external_id";
    
    $sqlCount  = "SELECT COUNT(NHA.id) ";
    
    $sqlPart2 = " FROM {$tables['nodes_hierarchy']} NHA, " .
           " {$tables['nodes_hierarchy']} NHB, {$tables['tcversions']} TCV " .
           " {$from['by_keyword_id']} {$from['by_custom_field']} {$from['by_requirement_doc_id']}".
           " WHERE NHA.id = NHB.parent_id AND NHB.id = TCV.id ";
           
    if ($filter)
        $sqlPart2 .= implode("",$filter);
    
    $gui->row_qty = $db->fetchOneValue($sqlCount.$sqlPart2); 
    if ($gui->row_qty)
    {
    	if ($gui->row_qty <= $tcase_cfg->search->max_qty_for_display)
    		$map = $db->fetchRowsIntoMap($sql.$sqlPart2,'testcase_id');	
		else
			$gui->warning_msg = lang_get('too_wide_search_criteria');
	}
}

$smarty = new TLSmarty();
if($gui->row_qty)
{
	$tpl = 'tcSearchResults.tpl';
	$gui->pageTitle .= " - " . lang_get('match_count') . " : " . $gui->row_qty;
	if ($map)
	{
		$tcase_mgr = new testcase($db);   
	    $tcase_set = array_keys($map);
	    $gui->path_info = $tproject_mgr->tree_manager->get_full_path_verbose($tcase_set);
		$gui->resultSet = $map;
	}
}
else
{
	$the_tpl = config_get('tpl');
	$tpl = $the_tpl['tcView'];
}
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $tpl);


function init_args()
{
	$args = new stdClass();
	$iParams = array("keyword_id" => array(tlInputParameter::INT_N),
			         "version" => array(tlInputParameter::INT_N,999),
					 "custom_field_id" => array(tlInputParameter::INT_N),
					 "name" => array(tlInputParameter::STRING_N,0,50),
					 "summary" => array(tlInputParameter::STRING_N,0,50),
					 "steps" => array(tlInputParameter::STRING_N,0,50),
					 "expected_results" => array(tlInputParameter::STRING_N,0,50),
					 "custom_field_value" => array(tlInputParameter::STRING_N,0,20),
					 "targetTestCase" => array(tlInputParameter::STRING_N,0,30),
					 "requirement_doc_id" => array(tlInputParameter::STRING_N,0,32),
	);	
		
	$args = new stdClass();
	R_PARAMS($iParams,$args);

	$args->userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
    $args->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

    return $args;
}
?>
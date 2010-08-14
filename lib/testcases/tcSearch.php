<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Display test cases search results. 
 *
 * @package 	TestLink
 * @author 		TestLink community
 * @copyright 	2007-2009, TestLink community 
 * @version    	CVS: $Id: tcSearch.php,v 1.15 2010/08/14 15:48:50 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 *
 *	@internal revisions
 *	20100814 - franciscom - improvements on logic and feedback when user fill in test case id filter
 *	20100609 - franciscom - BUGID 1627: Search Test Case by Date of Creation
 *  20100526 - Julian - BUGID 3490 - Search Test Cases based on requirements
 *	20100409 - franciscom - BUGID 3371 - Search Test Cases based on Test Importance
 *	20100326 - franciscom - BUGID 3334 - search fails if test case has 0 steps
 *  20100124 - franciscom - BUGID 3077 - search on preconditions
 *	20100106 - franciscom - Multiple Test Case Steps Feature
 *	20090228 - franciscom - if targetTestCase == test case prefix => 
 *                             consider as empty => means search all.
 *
 *	20090125 - franciscom - BUGID - search by requirement doc id
 **/
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$tproject_mgr = new testproject($db);

$tcase_cfg = config_get('testcase_cfg');
$args = init_args();

$gui = initializeGui($args);
$map = null;

if ($args->tprojectID)
{
	$tables = tlObjectWithDB::getDBTables(array('cfield_design_values','nodes_hierarchy',
								                'requirements','req_coverage','tcsteps',
								                'testcase_keywords','tcversions'));
								                
    $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tprojectID);
    $gui->tcasePrefix .= $tcase_cfg->glue_character;

    $from = array('by_keyword_id' => ' ', 'by_custom_field' => ' ', 'by_requirement_doc_id' => '');
    $filter = null;
	$tcaseID = null;
    
    // if Both dates exists check From >= To
    if( !is_null($args->creation_date_from) &&  !is_null($args->creation_date_to) )
    {
    	$date_from = date_create_from_format('Y-n-j', $args->creation_date_from);
    	$date_to = date_create_from_format('Y-n-j', $args->creation_date_to);
    }
    
    if( !is_null($args->creation_date_from) )
    {
    	$db_date = $db->db->DBdate($args->creation_date_from);
        $filter['by_creation_date_from'] = " AND TCV.creation_ts >= {$db_date} ";
	}

    if( !is_null($args->creation_date_to) )
    {
    	$db_date = $db->db->DBdate($args->creation_date_to);
        $filter['by_creation_date_to'] = " AND TCV.creation_ts <= {$db_date} ";
	}
    
    
    if($args->targetTestCase != "" && strcmp($args->targetTestCase,$gui->tcasePrefix) != 0)
    {
     	if (strpos($args->targetTestCase,$tcase_cfg->glue_character) === false)
     	{
    		$args->targetTestCase = $gui->tcasePrefix . $args->targetTestCase;
   	    }
   	    
        $tcase_mgr = new testcase ($db);
        $tcaseID = $tcase_mgr->getInternalID($args->targetTestCase,$tcase_cfg->glue_character); 
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
        $filter['by_keyword_id'] = " AND KW.keyword_id = {$args->keyword_id} ";	
        
    }
    
    if($args->name != "")
    {
        $args->name =  $db->prepare_string($args->name);
        $filter['by_name'] = " AND NH_TC.name like '%{$args->name}%' ";
    }
    
    if($args->summary != "")
    {
        $args->summary = $db->prepare_string($args->summary);
        $filter['by_summary'] = " AND TCV.summary like '%{$args->summary}%' ";
    }    

    if($args->preconditions != "")
    {
        $args->preconditions = $db->prepare_string($args->preconditions);
        $filter['by_preconditions'] = " AND TCV.preconditions like '%{$args->preconditions}%' ";
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
                                  " ON CFD.node_id=NH_TC.id ";
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

	// BUGID 3371 
   	if( $args->importance > 0)
    {
        $filter['importance'] = " AND TCV.importance = {$args->importance} ";
	}  
    
    $sqlFields = " SELECT NH_TC.id AS testcase_id,NH_TC.name,TCV.id AS tcversion_id," .
                 " TCV.summary, TCV.version, TCV.tc_external_id "; 
    
    $sqlCount  = "SELECT COUNT(NH_TC.id) ";
    
    // BUGID 3334 - search fails if test case has 0 steps
    // Added LEFT OUTER
    $sqlPart2 = " FROM {$tables['nodes_hierarchy']} NH_TC " .
                " JOIN {$tables['nodes_hierarchy']} NH_TCV ON NH_TCV.parent_id = NH_TC.id  " .
                " JOIN {$tables['tcversions']} TCV ON NH_TCV.id = TCV.id " .
                " LEFT OUTER JOIN {$tables['nodes_hierarchy']} NH_TCSTEPS ON NH_TCSTEPS.parent_id = NH_TCV.id " .
                " LEFT OUTER JOIN {$tables['tcsteps']} TCSTEPS ON NH_TCSTEPS.id = TCSTEPS.id  " .
                " {$from['by_keyword_id']} {$from['by_custom_field']} {$from['by_requirement_doc_id']} " .
                " WHERE 1=1 ";
           
           
    // 20100814 - franciscom
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

$smarty = new TLSmarty();
if($gui->row_qty > 0)
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
    $tpl = isset($the_tpl['tcSearchView']) ? $the_tpl['tcSearchView'] : 'tcView.tpl'; 
}
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $tpl);


/**
 *
 *
 */
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
					 "preconditions" => array(tlInputParameter::STRING_N,0,50),
					 "requirement_doc_id" => array(tlInputParameter::STRING_N,0,32),
					 "importance" => array(tlInputParameter::INT_N),
					 "creation_date_from_Day" => array(tlInputParameter::INT_N),
					 "creation_date_from_Month" => array(tlInputParameter::INT_N),
					 "creation_date_from_Year" => array(tlInputParameter::INT_N),
					 "creation_date_to_Day" => array(tlInputParameter::INT_N),
					 "creation_date_to_Month" => array(tlInputParameter::INT_N),
					 "creation_date_to_Year" => array(tlInputParameter::INT_N) );	
		
	$args = new stdClass();
	R_PARAMS($iParams,$args);

	$args->userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
    $args->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;


	// special situation: dates
	$date_vars = array('target_date_Year','target_date_Month','target_date_Day');
  	$start_date_vars = array('start_date_Year','start_date_Month','start_date_Day');

	$date_vars_prefix = array('creation_date_from','creation_date_to');
	$date_pieces = array('_Year','_Month','_Day');
	$create_date = array('creation_date_from' => true,'creation_date_to' => true);
	
	foreach($date_vars_prefix as $target)
  	{
  		$xx = array();
  		$args->$target = null;
  		foreach($date_pieces as $pk)
  		{
  			$accessKey = $target . $pk;
  	    	$create_date[$target] = $create_date[$target] && !is_null($args->$accessKey) && trim($args->$accessKey) != '' && 
  	    							intval($args->$accessKey) > 0;
  	    	$xx[] = $args->$accessKey;
  		}
  		if($create_date[$target])
  		{
  			$args->$target = implode('-',$xx);
  		}
  	}

	new dBug($args);
    return $args;
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
	$gui->bodyOnLoad = null;
	$gui->bodyOnUnload = null;
	$gui->refresh_tree = false;
	$gui->hilite_testcase_name = false;
	$gui->show_match_count = false;
	$gui->tc_current_version = null;
	$gui->row_qty = 0;
	$gui->creation_date_from = $argsObj->creation_date_from;
	$gui->creation_date_to = $argsObj->creation_date_to;
	
    return $gui;
}

?>
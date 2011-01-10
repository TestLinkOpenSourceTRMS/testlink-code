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
 * @version    	CVS: $Id: tcSearch.php,v 1.25.2.2 2011/01/10 15:38:59 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 *
 *	@internal revisions
 *  20101026 - Julian - BUGID 3930 - Localized dateformat for datepicker
 *  20101021 - asimon - BUGID 3716: replaced old separated inputs for day/month/year by ext js calendar
 *  20101015 - Julian - used title_key for exttable columns instead of title to be able to use 
 *                      table state independent from localization
 *  20101005 - asimon - replaced linked test case title by linked icon for editing
 *	20100920 - Julian - BUGID 3793 - use exttable to display search results
 *	20100908 - Julian - BUGID 2877 - Custom Fields linked to TC versions
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
require_once('exttable.class.php');
testlinkInitPage($db);
$date_format_cfg = config_get('date_format');

$templateCfg = templateConfiguration();
$tpl = 'tcSearchResults.tpl';
$tproject_mgr = new testproject($db);

$tcase_cfg = config_get('testcase_cfg');
$charset = config_get('charset');
$args = init_args($date_format_cfg);

$edit_label = lang_get('design');
$edit_icon = TL_THEME_IMG_DIR . "edit_icon.png";

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
    
	// BUGID 3716	
	// creation date
    if($args->creation_date_from)
    {
        $filter['by_creation_date_from'] = " AND TCV.creation_ts >= '{$args->creation_date_from}' ";
	}

    if($args->creation_date_to)
    {
        $filter['by_creation_date_to'] = " AND TCV.creation_ts <= '{$args->creation_date_to}' ";
	}
	
	// modification date
    if($args->modification_date_from)
    {
        $filter['by_modification_date_from'] = " AND TCV.modification_ts >= '{$args->modification_date_from}' ";
	}

    if($args->modification_date_to)
    {
        $filter['by_modification_date_to'] = " AND TCV.modification_ts <= '{$args->modification_date_to}' ";
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
                                  //BUGID 2877 - Custom Fields linked to TC versions
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

$table = buildExtTable($gui, $charset, $edit_icon, $edit_label);

if (!is_null($table)) {
	$gui->tableSet[] = $table;
}

$gui->pageTitle .= " - " . lang_get('match_count') . " : " . $gui->row_qty;
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $tpl);

/**
 * 
 *
 */
function buildExtTable($gui, $charset, $edit_icon, $edit_label) {
	$table = null;
	if(count($gui->resultSet) > 0) {
		$labels = array('test_suite' => lang_get('test_suite'), 'test_case' => lang_get('test_case'));
		$columns = array();
		
		$columns[] = array('title_key' => 'test_suite');
		$columns[] = array('title_key' => 'test_case', 'type' => 'text');
	
		// Extract the relevant data and build a matrix
		$matrixData = array();
		
		$titleSeperator = config_get('gui_title_separator_1');
		
		foreach($gui->resultSet as $result) {
			$rowData = array();
			$rowData[] = htmlentities($gui->path_info[$result['testcase_id']], ENT_QUOTES, $charset);
			
			// build test case link
//			$rowData[] = "<a href=\"lib/testcases/archiveData.php?edit=testcase&id={$result['testcase_id']}\">" .
//			             htmlentities($gui->tcasePrefix, ENT_QUOTES, $charset) . $result['tc_external_id'] . $titleSeperator .
//			             htmlentities($result['name'], ENT_QUOTES, $charset);
			$edit_link = "<a href=\"javascript:openTCEditWindow({$result['testcase_id']});\">" .
						 "<img title=\"{$edit_label}\" src=\"{$edit_icon}\" /></a> ";
			$tcaseName = htmlentities($gui->tcasePrefix, ENT_QUOTES, $charset) . $result['tc_external_id'] . $titleSeperator .
			             htmlentities($result['name'], ENT_QUOTES, $charset);
		    $tcLink = $edit_link . $tcaseName;
			$rowData[] = $tcLink;

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
		
		// dont save settings for this table
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
	
	// BUGID 3716
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
					 "creation_date_from" => array(tlInputParameter::STRING_N),
					 "creation_date_to" => array(tlInputParameter::STRING_N),
	                 "modification_date_from" => array(tlInputParameter::STRING_N),
					 "modification_date_to" => array(tlInputParameter::STRING_N));
		
	$args = new stdClass();
	R_PARAMS($iParams,$args);

	// BUGID 4066 - take care of proper escaping when magic_quotes_gpc is enabled
	$_REQUEST=strings_stripSlashes($_REQUEST);

	$args->userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
    $args->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

	// BUGID 3716
	// convert "creation date from" to iso format for database usage
    if (isset($args->creation_date_from) && $args->creation_date_from != '') {
		$date_array = split_localized_date($args->creation_date_from, $dateFormat);
		if ($date_array != null) {
			// set date in iso format
			$args->creation_date_from = $date_array['year'] . "-" . $date_array['month'] . "-" . $date_array['day'];
		}
	}
	
	// convert "creation date to" to iso format for database usage
    if (isset($args->creation_date_to) && $args->creation_date_to != '') {
		$date_array = split_localized_date($args->creation_date_to, $dateFormat);
		if ($date_array != null) {
			// set date in iso format
			// date to means end of selected day -> add 23:59:59 to selected date
			$args->creation_date_to = $date_array['year'] . "-" . $date_array['month'] . "-" .
			                          $date_array['day'] . " 23:59:59";
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

?>
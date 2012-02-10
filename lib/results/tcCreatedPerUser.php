<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource	tcCreatedPerUser.php
 * @package 	TestLink
 * @copyright 	2005,2011 TestLink community 
 * @author 		Bruno P. Kinoshita - brunodepaulak@yahoo.com.br
 * @link 		http://www.teamst.org/index.php
 * @since 		1.9.4
 * 
 * Generates report of test cases created per user. It produces a report with 
 * all test cases created within a project. 
 * 
 * @internal revisions:
 * 20111120 - kinow - BUGID 1761
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('displayMgr.php');
require_once('exttable.class.php');
require_once('results.class.php');
	
testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();
$date_format_cfg = config_get('date_format');

// init arguments
$args = init_args($db);
$gui = initializeGui($db,$args,$date_format_cfg);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
displayReport($templateCfg->template_dir . $templateCfg->default_template, $smarty);

/**
* initialize Gui
*/
function initializeGui(&$db,&$args,$dateFormat)
{
	$gui=new stdClass();
	$gui->glueChar = config_get('testcase_cfg')->glue_character;
	$gui->tproject_id = $args->tproject_id;
	$gui->tproject_name = $args->tproject_name;
	$gui->warning_msg = '';
	$gui->tableSet = null;
	
	$history_img = TL_THEME_IMG_DIR . "history_small.png";
	$exec_img = TL_THEME_IMG_DIR . "exec_icon.png";
	$edit_img = TL_THEME_IMG_DIR . "edit_icon.png";
	
	$l18n = init_labels(array('tcversion_indicator' => null,'goto_testspec' => null, 'version' => null, 
							  'testplan' => null, 'assigned_tc_overview' => null,'testcases_created_per_user' => null,
	                           'design' => null, 'execution' => null, 'execution_history' => null, 
							   'low_priority' => null, 'medium_priority' => null, 'high_priority' => null));
	
	$gui->pageTitle=sprintf($l18n['testcases_created_per_user'],$gui->tproject_name);
	
	$results_config = config_get('results');
	$map_status_code = $results_config['status_code'];
	$map_code_status = $results_config['code_status'];
	$map_status_label = $results_config['status_label'];
	$map_statuscode_css = array();
	foreach($map_code_status as $code => $status) {
		if (isset($map_status_label[$status])) {
			$label = $map_status_label[$status];
			$map_statuscode_css[$code] = array();
			$map_statuscode_css[$code]['translation'] = lang_get($label);
			$map_statuscode_css[$code]['css_class'] = $map_code_status[$code] . '_text';
		}
	}
	
	$options = new stdClass();
	
	// convert starttime to iso format for database usage
	if (isset($args->selected_start_date) && sizeof($args->selected_start_date) > 0) {
		$date_array = split_localized_date($args->selected_start_date[0], $dateFormat);
		if ($date_array != null) {
			// set date in iso format
			$options->startTime = $date_array['year'] . "-" . $date_array['month'] . "-" . $date_array['day'];
		}
	}
	
	// convert starttime to iso format for database usage
	if (isset($args->selected_end_date) && sizeof($args->selected_end_date) > 0) {
		$date_array = split_localized_date($args->selected_end_date[0], $dateFormat);
		if ($date_array != null) {
			// set date in iso format
			$options->endTime = $date_array['year'] . "-" . $date_array['month'] . "-" . $date_array['day'];
		}
	}
	
	$start_hour = isset($args->start_Hour) ? $args->start_Hour : "00";
	$options->startTime = $options->startTime . " " . $start_hour . ":00:00";
	$end_hour = isset($args->end_Hour) ? $args->end_Hour : "00";
	$options->endTime = $options->endTime . " " . $end_hour . ":59:59";
	
	$options->mode = 'full_path'; // TBD: remove it
	$options->tplan_id = $args->tplan_id;
	
	// used to retrieve test cases from
	$tcase_mgr = new testcase($db);
	
	$gui->resultSet = $tcase_mgr->get_created_per_user($args->user_id, $args->tproject_id, $options);
	
	if(!is_null($gui->resultSet)) {	
		
	    $tables = tlObjectWithDB::getDBTables(array('nodes_hierarchy'));
	
	    $tplanSet = array_keys($gui->resultSet);
	    $sql = "SELECT name,id FROM {$tables['nodes_hierarchy']} " .
	         "WHERE id IN (" . implode(',',$tplanSet) . ")";
	    $gui->tplanNames=$db->fetchRowsIntoMap($sql,'id');
	
		$optColumns = array('priority' => $args->priority_enabled);
		
		// For each test case set under a test plan ID, create the rows and columns
		foreach ($gui->resultSet as $tplan_id => $tcase_set) {
			list($columns, $sortByColumn) = getColumnsDefinition($optColumns);
			$rows = array();
	
			foreach ($tcase_set as $tcase_platform) {
				foreach ($tcase_platform as $tcase) {
					$current_row = array();
					$tcase_id = $tcase['testcase_id'];
					$tcversion_id = $tcase['tcversion_id'];
	
					$current_row[] = htmlspecialchars($tcase['login']);
					$current_row[] = htmlspecialchars($tcase['tcase_full_path']);
	
					// Create linked icons
					
					$exec_history_link = "<a href=\"javascript:openExecHistoryWindow({$tcase_id});\">" .
					                     "<img title=\"{$l18n['execution_history']}\" src=\"{$history_img}\" /></a> ";
	
					$edit_link = "<a href=\"javascript:openTCEditWindow({$tcase_id});\">" .
					             "<img title=\"{$l18n['design']}\" src=\"{$edit_img}\" /></a> ";
					
					$current_row[] = "<!-- " . sprintf("%010d", $tcase['tc_external_id']) . " -->" . $exec_history_link . 
									 $edit_link . htmlspecialchars($tcase['prefix']) . $gui->glueChar . 
					                 $tcase['tc_external_id'] . " : " . htmlspecialchars($tcase['name']) .
					        		 sprintf($l18n['tcversion_indicator'],$tcase['version']);
					
					$last_execution = $tcase_mgr->get_last_execution($tcase_id, $tcversion_id, $tplan_id, 
					                                                 $tcase['build_id'], 
					                                                 $tcase['platform_id']);
					$status = $last_execution[$tcversion_id]['status'];
					if (!$status) {
						$status = $map_status_code['not_run'];
					}
					
					$current_row[] = array (
						"value" => $status,
						"text" => $map_statuscode_css[$status]['translation'],
						"cssClass" => $map_statuscode_css[$status]['css_class']
					);
					
					$current_row[] = $tcase['creation_ts'];
					$current_row[] = $tcase['modification_ts'];
					
					// add this row to the others
					$rows[] = $current_row;
				}
			}
			
			// Different table ID for different reports:
			$table_id = "tl_table_tc_created_per_user_";
	
			// Add test plan ID to table ID
			$table_id .= $tplan_id;
			
			$matrix = new tlExtTable($columns, $rows, $table_id);
			$matrix->title = $l18n['testplan'] . ": " . htmlspecialchars($gui->tplanNames[$tplan_id]['name']);
			
			// Default grouping by first column, which is user for overview, build otherwise
			$matrix->setGroupByColumnName(lang_get($columns[0]['title_key']));
			
			// Make table collapsible if more than 1 table is shown and surround by frame
			if (count($tplanSet) > 1) {
				$matrix->collapsible = true;
				$matrix->frame = true;
			}
			
			// Define toolbar
			$matrix->showToolbar = true;
			$matrix->toolbarExpandCollapseGroupsButton = true;
			$matrix->toolbarShowAllColumnsButton = true;
			
			$matrix->setSortByColumnName($sortByColumn);
			$matrix->sortDirection = 'DESC';
			$gui->tableSet[$tplan_id] = $matrix;
		}
	}
	
	return $gui;
}

/**
 * Gets the arguments used to create the report. 
 * 
 * Some of these arguments are set in the $_REQUEST, and some in $_SESSION. Having 
 * these arguments in hand, the init_args method will use TestLink objects, such 
 * as a Test Project Manager (testproject class) to retrieve other information 
 * that is displayed on the screen (e.g.: project name).
 * 
 * @param $dbHandler handler to TestLink database
 * 
 * @return object of stdClass
 */
function init_args(&$dbHandler)
{
	$iParams = array(
		"user" => array(tlInputParameter::INT_N),
		"tplan_id" => array(tlInputParameter::INT_N),
		"selected_start_date" => array(tlInputParameter::ARRAY_STRING_N),
		"selected_end_date" => array(tlInputParameter::ARRAY_STRING_N),
		"start_Hour" => array(tlInputParameter::INT_N),
		"end_Hour" => array(tlInputParameter::INT_N)
	);
	
    $_REQUEST=strings_stripSlashes($_REQUEST);
    $pParams = R_PARAMS($iParams);
    
    $args = new stdClass();
    
    $args->tplan_id = $pParams["tplan_id"];
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->user_id = $pParams["user"];
	$args->selected_start_date = $pParams["selected_start_date"];
	$args->selected_end_date = $pParams["selected_end_date"];
	$args->start_Hour = $pParams["start_Hour"];
	$args->end_Hour = $pParams["end_Hour"];
	
	// TODO: validate and show warning message if invalid parameters
	
	return $args;
}


/**
 * Gets the columns definitions used in the report table.
 * 
 * @param $optionalColumns optional columns (e.g.: priority)
 * 
 * @return array containing columns and sort information
 */
function getColumnsDefinition($optionalColumns)
{
  	static $labels;
	if( is_null($labels) )
	{
		$lbl2get = array('user' => null, 'testsuite' => null,'testcase' => null,
		       			 'priority' => null,'status' => null, 'version' => null, 
						'title_created' => null);
		$labels = init_labels($lbl2get);
	}

	$colDef = array();
	// sort by test suite per default
	$sortByCol = $labels['testsuite'];
	
	$colDef[] = array('title_key' => '', 'width' => 80);
	$colDef[] = array('title_key' => 'testsuite', 'width' => 130);
	$colDef[] = array('title_key' => 'testcase', 'width' => 130);
	
	// if priority is enabled
	if ($optionalColumns['priority']) 
	{
	  	$sortByCol = $labels['priority'];
	  	$prios_for_filter = array(lang_get('low_priority'),lang_get('medium_priority'),lang_get('high_priority'));
		$colDef[] = array('title_key' => 'priority', 'width' => 50, 'filter' => 'ListSimpleMatch', 'filterOptions' => $prios_for_filter);
	}
	
	$colDef[] = array('title_key' => 'status', 'width' => 50, 'type' => 'status');
	$colDef[] = array('title_key' => 'title_created', 'width' => 75);
	$colDef[] = array('title_key' => 'title_last_mod', 'width' => 75);

	return array($colDef, $sortByCol);
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>

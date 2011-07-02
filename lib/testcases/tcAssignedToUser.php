<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource	tcAssignedToUser.php
 * @package 	TestLink
 * @copyright 	2005,2011 TestLink community 
 * @author 		Francisco Mancardi - francisco.mancardi@gmail.com
 * @link 		http://www.teamst.org/index.php
 *
 * @internal revisions:
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("exttable.class.php");

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$user = new tlUser($db);
$names = $user->getNames($db);

$results_config = config_get('results');

$args=init_args($db);
if ($args->user_id > 0) {
	$args->user_name = $names[$args->user_id]['login'];
}

$tcase_mgr = new testcase($db);
$tplan_mgr = new testplan($db);

$gui=new stdClass();
//20101013 - asimon - disable "show also closed builds" checkbox when a specific build is selected
$gui->show_build_selector = ($args->build_id == 0);
$gui->glueChar = config_get('testcase_cfg')->glue_character;
$gui->tproject_id = $args->tproject_id;
$gui->tproject_name = $args->tproject_name;
$gui->warning_msg = '';
$gui->tableSet = null;
$gui->show_closed_builds = $args->show_closed_builds;

$exec_img = TL_THEME_IMG_DIR . "exec_icon.png";
$edit_img = TL_THEME_IMG_DIR . "edit_icon.png";


$l18n = init_labels(array('tcversion_indicator' => null,'goto_testspec' => null, 'version' => null, 
						  'testplan' => null, 'assigned_tc_overview' => null,'testcases_assigned_to_user' => null,
                           'design' => null, 'execution' => null));

if ($args->show_all_users) {
	$gui->pageTitle=sprintf($l18n['assigned_tc_overview'], $gui->tproject_name);
} else {
	$gui->pageTitle=sprintf($l18n['testcases_assigned_to_user'],$gui->tproject_name, $args->user_name);
}

$priority = array(LOW => lang_get('low_priority'),MEDIUM => lang_get('medium_priority'),HIGH => lang_get('high_priority'));

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

// Get all test cases assigned to user without filtering by execution status
$options = new stdClass();
$options->mode = 'full_path';

$filters = array();
$filters['tplan_status'] = $args->show_inactive_tplans ? 'all' : 'active';
$filters['build_status'] = $args->show_closed_builds ? 'all' : 'open';

if ($args->build_id) 
{
	$filters['build_id'] = $args->build_id;
	
	// if build_id is set, show assignments regardless of build and tplan status
	$filters['build_status'] = 'all';
	$filters['tplan_status'] = 'all';
}

$tplan_param = ($args->tplan_id) ? array($args->tplan_id) : testcase::ALL_TESTPLANS;
$gui->resultSet=$tcase_mgr->get_assigned_to_user($args->user_id, $args->tproject_id,
                                                 $tplan_param, $options, $filters);

if( ($doIt = !is_null($gui->resultSet)) )
{	
	$tables = tlObjectWithDB::getDBTables(array('nodes_hierarchy'));

    $tplanSet=array_keys($gui->resultSet);
    $sql="SELECT name,id FROM {$tables['nodes_hierarchy']} " .
         "WHERE id IN (" . implode(',',$tplanSet) . ")";
    $gui->tplanNames=$db->fetchRowsIntoMap($sql,'id');

	$optColumns = array('user' => $args->show_user_column, 'priority' => $args->priority_enabled);

	foreach ($gui->resultSet as $tplan_id => $tcase_set) {

		$show_platforms = !is_null($tplan_mgr->getPlatforms($tplan_id));
		$platforms = $tplan_mgr->getPlatforms($tplan_id);
		list($columns, $sortByColumn) = getColumnsDefinition($optColumns, $show_platforms,$platforms);
		$rows = array();

		foreach ($tcase_set as $tcase_platform) {
			foreach ($tcase_platform as $tcase) {
				$current_row = array();
				$tcase_id = $tcase['testcase_id'];
				$tcversion_id = $tcase['tcversion_id'];
				
				if ($args->show_user_column) {
					$current_row[] = htmlspecialchars($names[$tcase['user_id']]['login']);
				}
		
				$current_row[] = htmlspecialchars($tcase['build_name']);
				$current_row[] = htmlspecialchars($tcase['tcase_full_path']);

				// create linked icons
				$exec_link = "<a href=\"javascript:openExecutionWindow(" .
				             "{$tcase_id},{$tcversion_id},{$tcase['build_id']}," .
				             "{$tcase['testplan_id']},{$tcase['platform_id']});\">" .
						     "<img title=\"{$l18n['execution']}\" src=\"{$exec_img}\" /></a> ";

				$edit_link = "<a href=\"javascript:openTCEditWindow({$gui->tproject_id},{$tcase_id});\">" .
				             "<img title=\"{$l18n['design']}\" src=\"{$edit_img}\" /></a> ";
				
				$current_row[] = "<!-- " . sprintf("%010d", $tcase['tc_external_id']) . " -->" . $exec_link .
				                 $edit_link . htmlspecialchars($tcase['prefix']) . $gui->glueChar . 
				                 $tcase['tc_external_id'] . " : " . htmlspecialchars($tcase['name']) .
				        		 sprintf($l18n['tcversion_indicator'],$tcase['version']);

				if ($show_platforms)
				{
					$current_row[] = htmlspecialchars($tcase['platform_name']);
				}
				
				if ($args->priority_enabled) {
					
					//BUGID 4418 - clean up priority usage
					$current_row[] = "<!-- " . $tcase['priority'] . " -->" . $priority[priority_to_level($tcase['priority'])];
				}
				
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
				
				$current_row[] = htmlspecialchars($tcase['creation_ts']) . 
				                 " (" . get_date_diff($tcase['creation_ts']) . ")";
				
				// add this row to the others
				$rows[] = $current_row;
			}
		}
		
		/* different table id for different reports:
		 * - Assignment Overview if $args->show_all_users is set
		 * - Test Cases assigned to user if $args->build_id > 0
		 * - Test Cases assigned to me else
		 */
		$table_id = "tl_table_tc_assigned_to_me_for_tplan_";
		if($args->show_all_users) {
			$table_id = "tl_table_tc_assignment_overview_for_tplan_";
		}
		if($args->build_id) {
			$table_id = "tl_table_tc_assigned_to_user_for_tplan_";
		}
		
		// add test plan id to table id
		$table_id .= $tplan_id;
		
		$matrix = new tlExtTable($columns, $rows, $table_id);
		$matrix->title = $l18n['testplan'] . ": " . htmlspecialchars($gui->tplanNames[$tplan_id]['name']);
		
		// default grouping by first column, which is user for overview, build otherwise
		$matrix->setGroupByColumnName(lang_get($columns[0]['title_key']));
		
		// make table collapsible if more than 1 table is shown and surround by frame
		if (count($tplanSet) > 1) {
			$matrix->collapsible = true;
			$matrix->frame = true;
		}
		
		// define toolbar
		$matrix->showToolbar = true;
		$matrix->toolbarExpandCollapseGroupsButton = true;
		$matrix->toolbarShowAllColumnsButton = true;
		
		$matrix->setSortByColumnName($sortByColumn);
		$matrix->sortDirection = 'DESC';
		$gui->tableSet[$tplan_id] = $matrix;
	}
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * Replacement for the smarty helper function to get that functionality outside of templates.
 * Returns difference between a given date and the current time in days.
 * @author Andreas Simon
 * @param $date
 */
function get_date_diff($date) {
	$date = (is_string($date)) ? strtotime($date) : $date;
	$i = 1/60/60/24;
	return floor((time() - $date) * $i);
}


/**
 * init_args()
 * Get in an object all data that has arrived to page through _REQUEST or _SESSION.
 * If you think this page as a function, you can consider this data arguments (args)
 * to a function call.
 * Using all this data as one object property will help developer to understand
 * if data is received or produced on page.
 *
 * @author franciscom - francisco.mancardi@gmail.com
 * @args - used global coupling accessing $_REQUEST and $_SESSION
 * 
 * @return object of stdClass
 *
 * 
 * @internal revisions:
 *  20100731 - asimon - additional arguments show_all_users and show_inactive_and_closed
 */
function init_args(&$dbHandler)
{
    $_REQUEST=strings_stripSlashes($_REQUEST);
    $args = new stdClass();
    
    $args->tproject_id = isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : 0;
	$args->tproject_name = '';
	if($args->tproject_id >0)
	{ 
		$tproject_mgr = new testproject($dbHandler);
		$dummy = $tproject_mgr->get_by_id($args->tproject_id);
		$args->tproject_name = $dummy['name'];
		$args->priority_enabled = $dummy['opt']->testPriorityEnabled ? true : false;
		unset($tproject_mgr);
	}
	
    $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : 0;
	$args->build_id = isset($_REQUEST['build_id']) && is_numeric($_REQUEST['build_id']) ? 
					  intval($_REQUEST['build_id']) : 0;

	// BUGID 4009
	$args->show_inactive_tplans = isset($_REQUEST['show_inactive_tplans']) ? true : false;
	                  
	$args->show_all_users = (isset($_REQUEST['show_all_users']) && $_REQUEST['show_all_users'] =! 0);
	$args->show_user_column = $args->show_all_users; 

    $args->user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
    if( $args->user_id == 0)
    {
        $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
        $args->user_name = $_SESSION['currentUser']->login;
    }	

	// BUGID 3824
    $show_closed_builds = isset($_REQUEST['show_closed_builds']) ? true : false;
	$show_closed_builds_hidden = isset($_REQUEST['show_closed_builds_hidden']) ? true : false;
	$selection = false;
	if ($show_closed_builds) {
		$selection = true;
	} else if ($show_closed_builds_hidden) {
		$selection = false;
	} else if (isset($_SESSION['show_closed_builds'])) {
		$selection = $_SESSION['show_closed_builds'];
	}
	
	$args->show_closed_builds = $_SESSION['show_closed_builds'] = $selection;

	if ($args->show_all_users) {
		$args->user_id = TL_USER_ANYBODY;
	}
	
	
	$args->show_inactive_and_closed = isset($_REQUEST['show_inactive_and_closed']) && 
									  $_REQUEST['show_inactive_and_closed'] =! 0 ? true : false;

	
	return $args;
}


/**
 * get Columns definition for table to display
 *
 */
function getColumnsDefinition($optionalColumns, $show_platforms, $platforms)
{
  	static $labels;
	if( is_null($labels) )
	{
		$lbl2get = array('build' => null,'testsuite' => null,'testcase' => null,'platform' => null,
		       			 'user' => null, 'priority' => null,'status' => null, 'version' => null, 'due_since' => null);
		$labels = init_labels($lbl2get);
	}

	$colDef = array();
	// sort by test suite per default
	$sortByCol = $labels['testsuite'];
	
	// user column is only shown for assignment overview
	if ($optionalColumns['user']) 
	{
		$colDef[] = array('title_key' => 'user', 'width' => 80);
		// for assignment overview sort by build
		$sortByCol = $labels['build'];
	}
	
	$colDef[] = array('title_key' => 'build', 'width' => 80);
	$colDef[] = array('title_key' => 'testsuite', 'width' => 130);
	$colDef[] = array('title_key' => 'testcase', 'width' => 130);
	if ($show_platforms)
	{
		$platforms_for_filter = array();
		foreach($platforms as $platform) {
			$platforms_for_filter[] = $platform['name'];
		}
		$colDef[] = array('title_key' => 'platform', 'width' => 50, 'filter' => 'list', 'filterOptions' => $platforms_for_filter);
	}
	
	// 20100816 - asimon - if priority is enabled, enable default sorting by that column
	if ($optionalColumns['priority']) 
	{
	  	$sortByCol = $labels['priority'];
	  	$prios_for_filter = array(lang_get('low_priority'),lang_get('medium_priority'),lang_get('high_priority'));
		$colDef[] = array('title_key' => 'priority', 'width' => 50, 'filter' => 'ListSimpleMatch', 'filterOptions' => $prios_for_filter);
	}
	
	$colDef[] = array('title_key' => 'status', 'width' => 50, 'type' => 'status');
	$colDef[] = array('title_key' => 'due_since', 'width' => 100);

	return array($colDef, $sortByCol);
}
?>

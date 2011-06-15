<?php

/**
 * 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Andreas Simon
 * @copyright 2010, TestLink community
 * @version CVS: $Id: resultsByTesterPerBuild.php,v 1.16.2.1 2011/02/11 14:51:10 asimon83 Exp $
 *
 * Lists results and progress by tester per build.
 * 
 * @internal revisions:
 * 20110510 - Julian - set proper filters for table columns
 * 20110211 - asimon - BUGID 4192: show only open builds by default
 * 20101019 - asimon - BUGID 3911: show warning message instead of table if table is empty
 * 20100923 - eloff - refactored to use improved table interface
 * 20100923 - Julian - BUGID 3803
 *                   - added status label to status percentage column to be able to reorder columns
 *                     without losing the context
 * 20100823 - asimon - refactoring: $table_id
 * 20100816 - asimon - enable default sorting by progress column
 * 20100731 - asimon - initial commit
 * 		
 */

require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$tproject_mgr = new testproject($db);
$tplan_mgr = new testplan($db);
$user = new tlUser($db);

$args = init_args($tproject_mgr, $tplan_mgr);
$gui = init_gui($args);
$charset = config_get('charset');
$results_config = config_get('results');

// BUGID 4192
$progress = new build_progress($db, $args->tplan_id, $args->show_closed_builds);
$matrix = $progress->get_results_matrix();
$status_map = $progress->get_status_map();
$build_set = $progress->get_build_set();
$names = $user->getNames($db);

// build the table header
$columns = array();
$columns[] = array('title_key' => 'build', 'width' => 50, 'type' => 'text', 'sortType' => 'asText',
	               'filter' => 'string');
$columns[] = array('title_key' => 'user', 'width' => 50, 'type' => 'text', 'sortType' => 'asText',
	               'filter' => 'string');
$columns[] = array('title_key' => 'th_tc_assigned', 'width' => 50, 'sortType' => 'asFloat',
                   'filter' => 'numeric');

foreach ($status_map as $status => $code) {
	$label = $results_config['status_label'][$status];
	$columns[] = array('title_key' => $label, 'width' => 20, 'sortType' => 'asInt',
	                   'filter' => 'numeric');
	$columns[] = array('title' => lang_get($label).' '.lang_get('in_percent'),
	                   'col_id' => 'id_'.$label.'_percent', 'width' => 30, 
	                   'type' => 'float', 'sortType' => 'asFloat', 'filter' => 'numeric');
}

$columns[] = array('title_key' => 'progress', 'width' => 30, 'type' => 'float',
                   'sortType' => 'asFloat', 'filter' => 'numeric');

// get the progress of the whole build based on executions of single users
$build_statistics = array();
foreach ($matrix as $build_id => $build_execution_map) {
	$build_statistics[$build_id]['total'] = 0;
	$build_statistics[$build_id]['executed'] = 0;
	foreach ($build_execution_map as $user_id => $statistics) {
		// total assigned test cases
		$build_statistics[$build_id]['total'] += $statistics['total'];
		// total executed testcases
		$executed = $statistics['total'] - $statistics['not_run']['count']; 
		$build_statistics[$build_id]['executed'] += $executed;
	}
	// build progress
	$build_statistics[$build_id]['progress'] = round($build_statistics[$build_id]['executed'] / $build_statistics[$build_id]['total'] * 100,2);
}

// build the content of the table
$rows = array();

foreach ($matrix as $build_id => $build_execution_map) {
	foreach ($build_execution_map as $user_id => $statistics) {
		$current_row = array();
		
		// add build name to row including Progress
		$current_row[] = $build_set[$build_id]['name'] . " - " . lang_get('progress_absolute') . 
		                 " {$build_statistics[$build_id]['progress']}%";
		
		// add username and link it to tcAssignedToUser.php
		if ($user_id == TL_NO_USER) {
			$name = lang_get('executions_without_assignment');
		} else {
			$username = $names[$user_id]['login'];
			$name = "<a href=\"javascript:openAssignmentOverviewWindow(" .
			        "{$user_id}, {$build_id}, {$args->tplan_id});\">{$username}</a>";
		}		
		$current_row[] = $name;
		
		// total count of testcases assigned to this user on this build
		$current_row[] = $statistics['total'];
		
		// add count and percentage for each possible status
		foreach ($status_map as $status => $code) {
			$current_row[] = $statistics[$status]['count'];
			
			//use html comment to allow js sort this column properly
			$status_percentage = is_numeric($statistics[$status]['percentage']) ? 
			                     $statistics[$status]['percentage'] : -1;
			
			$current_row[] = $status_percentage;
		}
		
		// add general progress for this user
		// add html comment with which js can sort the column
		$percentage = is_numeric($statistics['progress']) ? $statistics['progress'] : -1;
		
		$current_row[] = $percentage;
		
		// add this row to the others
		$rows[] = $current_row;
	}
}

// create the table object
$matrix = new tlExtTable($columns, $rows, 'tl_table_results_by_tester_per_build');
$matrix->title = lang_get('results_by_tester_per_build');

//group by build
$matrix->setGroupByColumnName(lang_get('build'));

// 20100816 - asimon - enable default sorting by progress column
$matrix->setSortByColumnName(lang_get('progress'));

//define toolbar
$matrix->showToolbar = true;
$matrix->toolbarExpandCollapseGroupsButton = true;
$matrix->toolbarShowAllColumnsButton = true;

$gui->tableSet = array($matrix);

// BUGID 3911: show warning message instead of table if table is empty
$gui->warning_message = (count($rows) > 0) ? '' : lang_get('no_testers_per_build');

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * initialize user input
 * 
 * @param resource &$tproject_mgr reference to testproject manager
 * @return array $args array with user input information
 */
function init_args(&$tproject_mgr, &$tplan_mgr) {
	$iParams = array("format" => array(tlInputParameter::INT_N),
		             "tplan_id" => array(tlInputParameter::INT_N));

	$args = new stdClass();
	R_PARAMS($iParams,$args);
    
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	
	if($args->tproject_id > 0) {
		$args->tproject_info = $tproject_mgr->get_by_id($args->tproject_id);
		$args->tproject_name = $args->tproject_info['name'];
		$args->tproject_description = $args->tproject_info['notes'];
	}
	
	if ($args->tplan_id > 0) {
		$args->tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
		
	}
	
	// BUGID 4192
	$selection = false;
    $show_closed_builds = isset($_REQUEST['show_closed_builds']) ? true : false;
	$show_closed_builds_hidden = isset($_REQUEST['show_closed_builds_hidden']) ? true : false;
	if ($show_closed_builds) {
		$selection = true;
	} else if ($show_closed_builds_hidden) {
		$selection = false;
	} else if (isset($_SESSION['reports_show_closed_builds'])) {
		$selection = $_SESSION['reports_show_closed_builds'];
	}
	$args->show_closed_builds = $_SESSION['reports_show_closed_builds'] = $selection;
	
	return $args;
}


/**
 * initialize GUI
 * 
 * @param stdClass $argsObj reference to user input
 * @return stdClass $gui gui data
 */
function init_gui(&$argsObj) {
	$gui = new stdClass();
	
	$gui->pageTitle = lang_get('caption_results_by_tester_per_build');
	$gui->warning_msg = '';
	$gui->tproject_name = $argsObj->tproject_name;
	$gui->tplan_name = $argsObj->tplan_info['name'];
	
	// BUGID 4192
	$gui->show_closed_builds = $argsObj->show_closed_builds;
	
	return $gui;
}


/*
 * rights check function for testlinkInitPage()
 */
function checkRights(&$db, &$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>

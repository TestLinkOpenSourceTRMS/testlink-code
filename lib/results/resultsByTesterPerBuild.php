<?php

/**
 * 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Andreas Simon
 * @copyright 2010, TestLink community
 * @version CVS: $Id: resultsByTesterPerBuild.php,v 1.2 2010/08/16 12:35:20 asimon83 Exp $
 *
 * Lists results and progress by tester per build.
 * 
 * @internal revisions:
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

$progress = new build_progress($db, $args->tplan_id);
$matrix = $progress->get_results_matrix();
$status_map = $progress->get_status_map();
$build_set = $progress->get_build_set();
$names = $user->getNames($db);


// build the table header
$columns = array();
$columns[] = array('title' => lang_get('build'), 'width' => 50);
$columns[] = array('title' => lang_get('user'), 'width' => 50);
$columns[] = array('title' => lang_get('th_tc_assigned'), 'width' => 50);

foreach ($status_map as $status => $code) {
	$label = $results_config['status_label'][$status];
	$columns[] = array('title' => lang_get($label), 'width' => 20);
	$columns[] = array('title' => '[%]', 'width' => 20);
}

$columns[] = array('title' => lang_get('progress'), 'width' => 30);

// build the content of the table
$rows = array();

foreach ($matrix as $build_id => $build_execution_map) {
	foreach ($build_execution_map as $user_id => $statistics) {
		$current_row = array();
		
		// add build name to row
		$current_row[] = $build_set[$build_id]['name'];
		
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
			$current_row[] = $statistics[$status]['percentage'];
		}
		
		// add general progress for this user
		// add html comment with which js can sort the column
		$percentage = is_numeric($statistics['progress']) ? $statistics['progress'] : 0;
		$padded_percentage = sprintf("%010d", $percentage); //bring all percentages to same length
		$commented_progress = "<!-- $padded_percentage --> {$statistics['progress']}";
		
		$current_row[] = $commented_progress;
		
		// add this row to the others
		$rows[] = $current_row;
	}
}


// create the table object
$matrix = new tlExtTable($columns, $rows, 0);
$matrix->title = lang_get('results_by_tester_per_build');
$matrix->groupByColumn = 0; // default grouping by first column, which is build in this case

// 20100816 - asimon - enable default sorting by progress column
$matrix->sortByColumn = count($columns)-1; // sort by last column, which is progress

$gui->tableSet = array($matrix);


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
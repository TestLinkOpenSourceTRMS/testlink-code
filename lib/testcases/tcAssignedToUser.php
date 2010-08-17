<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource $RCSfile: tcAssignedToUser.php,v $
 * @version $Revision: 1.8 $
 * @modified $Date: 2010/08/17 14:30:35 $  $Author: mx-julian $
 * @author Francisco Mancardi - francisco.mancardi@gmail.com
 * 
 * @internal revisions:
 *  20100816 - asimon - if priority is enabled, enable default sorting by that column
 *  20100802 - asimon - BUGID 3647, filtering by build
 *  20100731 - asimon - heavy refactoring, modified to include more parameters and flexibility,
 *                      changed table to ExtJS format
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("exttable.class.php");

testlinkInitPage($db);
$user = new tlUser($db);
$names = $user->getNames($db);

$gui=new stdClass();
$gui->glueChar = config_get('testcase_cfg')->glue_character;
$urgencyImportance = config_get('urgencyImportance');
$results_config = config_get('results');

$templateCfg = templateConfiguration();
$args=init_args();

if ($args->user_id > 0) {
	$args->user_name = $names[$args->user_id]['login'];
}

$tcase_mgr = new testcase($db);
$tproject_mgr = new testproject($db);
$tproject_info=$tproject_mgr->get_by_id($args->tproject_id);
$gui->tproject_name=$tproject_info['name'];

if ($args->show_all_users) {
	$gui->pageTitle=sprintf(lang_get('assigned_tc_overview'), $gui->tproject_name);
} else {
	$gui->pageTitle=sprintf(lang_get('testcases_assigned_to_user'), 
	                        $gui->tproject_name, $args->user_name);
}

$tcversion_indicator = lang_get('tcversion_indicator');
$goto_testspec = lang_get('goto_testspec');
$version = lang_get('version');
$testplan = lang_get('testplan');
$priority = array('low' => lang_get('low_priority'),
                  'medium' => lang_get('medium_priority'),
                  'high' => lang_get('high_priority'));

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
$options=new stdClass();
$options->mode='full_path';

$filters = array();

// if opened by click on username from page "results by user per build", show all testplans
if (!$args->show_inactive_and_closed) {
	//BUGID 3575: show only assigned test cases for ACTIVE test plans
	$filters['tplan_status'] = 'active';
}

// 3647
if ($args->build_id) {
	$filters['build_id'] = $args->build_id;
}

$tplan_param = ($args->tplan_id) ? array($args->tplan_id) : testcase::ALL_TESTPLANS;

$gui->resultSet=$tcase_mgr->get_assigned_to_user($args->user_id, $args->tproject_id, 
                                                 $tplan_param, $options, $filters);

if( !is_null($gui->resultSet) )
{	
	$tables = tlObjectWithDB::getDBTables(array('nodes_hierarchy'));

    $tplanSet=array_keys($gui->resultSet);
    $sql="SELECT name,id FROM {$tables['nodes_hierarchy']} " .
         "WHERE id IN (" . implode(',',$tplanSet) . ")";
    $gui->tplanNames=$db->fetchRowsIntoMap($sql,'id');
}
$gui->warning_msg='';


foreach ($gui->resultSet as $tplan_id => $tcase_set) {
	
	$tableID = $tplan_id;
	$tplan_name = htmlspecialchars($gui->tplanNames[$tplan_id]['name']);
	
	$columns = array();
	
	if ($args->show_user_column) {
		$columns[] = array('title' => lang_get('user'), 'width' => 80);
	}
	
	$columns[] = array('title' => lang_get('build'), 'width' => 80);
	$columns[] = array('title' => lang_get('testsuite'), 'width' => 80);
	$columns[] = array('title' => lang_get('testcase'), 'width' => 80);
	$columns[] = array('title' => lang_get('platform'), 'width' => 80);
	
	// 20100816 - asimon - if priority is enabled, enable default sorting by that column
	$column_count_before_priority = count($columns);
	if ($args->priority_enabled) {
		$columns[] = array('title' => lang_get('priority'), 'width' => 80);
	}
	
	$columns[] = array('title' => lang_get('status'), 'width' => 80);
	$columns[] = array('title' => lang_get('due_since'), 'width' => 150);
		
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
			
			$link = "<a href=\"lib/testcases/archiveData.php?edit=testcase&id={$tcase_id}\" " . 
			        " title=\"{$goto_testspec}\">" .
			        htmlspecialchars($tcase['prefix']) . $gui->glueChar . $tcase['tc_external_id'] . 
			        ":" . htmlspecialchars($tcase['name']) . "&nbsp(" . $version . ": " . 
			        $tcase['version'] . ")</a>";
			$current_row[] = $link;
			
			$current_row[] = htmlspecialchars($tcase['platform_name']);
			
			if ($args->priority_enabled) {
				if ($tcase['priority'] >= $urgencyImportance->threshold['high']) {
					$prio = $priority['high'];
				} else if ($tcase['priority'] < $urgencyImportance->threshold['low']) {
					$prio = $priority['low'];
				} else {
					$prio = $priority['medium'];
				}
				$current_row[] = $prio;
			}
			
			$last_execution = $tcase_mgr->get_last_execution($tcase_id, $tcversion_id, $tplan_id, 
			                                                 $tcase['build_id'], 
			                                                 $tcase['platform_id']);
			$status = $last_execution[$tcversion_id]['status'];
			if (!$status) {
				$status = $map_status_code['not_run'];
			}
			$version_info = sprintf($tcversion_indicator, $tcase['version']);
			$status_rich = '<span class="' . $map_statuscode_css[$status]['css_class'] . '">' . 
			               $map_statuscode_css[$status]['translation'] . ' ' . 
			               $version_info . '</span>';
			$current_row[] = $status_rich;
			
			$current_row[] = htmlspecialchars($tcase['creation_ts']) . 
			                 " (" . get_date_diff($tcase['creation_ts']) . ")";
			
			// add this row to the others
			$rows[] = $current_row;
		}
	}
	
	// create the table object
	$matrix = new tlExtTable($columns, $rows, $tableID);
	$matrix->title = $testplan . ": {$tplan_name}";
	// default grouping by first column, which is user for overview, build otherwise
	$matrix->groupByColumn = 0;
	
	//define toolbar
	$matrix->show_toolbar = true;
	$matrix->toolbar_expand_collapse_groups_button = true;
	$matrix->toolbar_show_all_columns_button = true;
	
	// 20100816 - asimon - if priority is enabled, enable default sorting by that column
	if ($args->priority_enabled) {
		$matrix->sortByColumn = $column_count_before_priority;
	} else {
		$matrix->sortByColumn = 1;
	}
	
	$gui->tableSet[$tableID] = $matrix;
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
 * @since 20090131 - franciscom
 * 
 * @internal revisions:
 *  20100731 - asimon - additional arguments show_all_users and show_inactive_and_closed
 */
function init_args()
{
    $_REQUEST=strings_stripSlashes($_REQUEST);
    $args = new stdClass();
    
    $args->tproject_id = isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : 0;
    if( $args->tproject_id == 0)
    {
        $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    }

    $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : 0;
//    if( $args->tplan_id == 0)
//    {
//        $args->tplan_id = isset($_SESSION['testplanID']) ? $_SESSION['testplanID'] : 0;
//    }
    
    $args->user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
    if( $args->user_id == 0)
    {
        $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
        $args->user_name = $_SESSION['currentUser']->login;
    }	

	$args->build_id = isset($_REQUEST['build_id']) && is_numeric($_REQUEST['build_id']) ? 
	                  $_REQUEST['build_id'] : 0;

	$args->show_all_users = isset($_REQUEST['show_all_users']) && $_REQUEST['show_all_users'] =! 0 ? 
	                        true : false;
	
	if ($args->show_all_users) {
		$args->user_id = TL_USER_ANYBODY;
	}
	
	$args->show_user_column = false;
	if ($args->show_all_users ) {
		$args->show_user_column = true;
	}
	
	$args->show_inactive_and_closed = isset($_REQUEST['show_inactive_and_closed']) 
	                                  && $_REQUEST['show_inactive_and_closed'] =! 0 ? 
	                                  true : false;

	$args->priority_enabled = $_SESSION['testprojectOptions']->testPriorityEnabled ? true : false;
	
	return $args;
}
?>
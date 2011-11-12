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
$results_config = config_get('results');

$args=init_args($db);

$tcase_mgr = new testcase($db);

$gui=new stdClass();
$gui->show_build_selector = ($args->build_id == 0);
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
                           'design' => null, 'execution' => null, 'execution_history' => null));

$gui->pageTitle=sprintf($l18n['testcases_created_per_user'],$gui->tproject_name);

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

// Get all test cases created by user in the current project

$options = new stdClass();
$options->mode = 'full_path';

$gui->resultSet=$tcase_mgr->get_created_by_user($args->tproject_id, $args->tplan_id, $options);

if( ($doIt = !is_null($gui->resultSet)) )
{	
	$tables = tlObjectWithDB::getDBTables(array('nodes_hierarchy'));

    $tplanSet=array_keys($gui->resultSet);
    $sql="SELECT name,id FROM {$tables['nodes_hierarchy']} " .
         "WHERE id IN (" . implode(',',$tplanSet) . ")";
    $gui->tplanNames=$db->fetchRowsIntoMap($sql,'id');

	$optColumns = array('priority' => $args->priority_enabled);

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

				// create linked icons
				
				$exec_history_link = "<a href=\"javascript:openExecHistoryWindow({$tcase_id});\">" .
				                     "<img title=\"{$l18n['execution_history']}\" src=\"{$history_img}\" /></a> ";

				$edit_link = "<a href=\"javascript:openTCEditWindow({$gui->tproject_id},{$tcase_id});\">" .
				             "<img title=\"{$l18n['design']}\" src=\"{$edit_img}\" /></a> ";
				
				$current_row[] = "<!-- " . sprintf("%010d", $tcase['tc_external_id']) . " -->" . $exec_history_link . 
								 $edit_link . htmlspecialchars($tcase['prefix']) . $gui->glueChar . 
				                 $tcase['tc_external_id'] . " : " . htmlspecialchars($tcase['name']) .
				        		 sprintf($l18n['tcversion_indicator'],$tcase['version']);
				
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
 * init_args()
 * Get in an object all data that has arrived to page through _REQUEST or _SESSION.
 * If you think this page as a function, you can consider this data arguments (args)
 * to a function call.
 * Using all this data as one object property will help developer to understand
 * if data is received or produced on page.
 *
 * @author franciscom - francisco.mancardi@gmail.com
 * @args - used global coupling accessing $_REQUEST and $_SESSION
 * 	if ($show_platforms)
	{
		$colDef[] = array('title_key' => 'platform', 'width' => 50, 'filter' => 'list', 'filterOptions' => $platforms);
	}
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

    $args->user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
    if( $args->user_id == 0)
    {
        $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
        $args->user_name = $_SESSION['currentUser']->login;
    }	

	return $args;
}


/**
 * get Columns definition for table to display
 *
 */
function getColumnsDefinition($optionalColumns)
{
  	static $labels;
	if( is_null($labels) )
	{
		$lbl2get = array('user' => null, 'testsuite' => null,'testcase' => null,
		       			 'priority' => null,'status' => null, 'version' => null);
		$labels = init_labels($lbl2get);
	}

	$colDef = array();
	// sort by test suite per default
	$sortByCol = $labels['testsuite'];
	
	$colDef[] = array('title_key' => '', 'width' => 80);
	$colDef[] = array('title_key' => 'testsuite', 'width' => 130);
	$colDef[] = array('title_key' => 'testcase', 'width' => 130);
	
	// 20100816 - asimon - if priority is enabled, enable default sorting by that column
	if ($optionalColumns['priority']) 
	{
	  	$sortByCol = $labels['priority'];
	  	$prios_for_filter = array(lang_get('low_priority'),lang_get('medium_priority'),lang_get('high_priority'));
		$colDef[] = array('title_key' => 'priority', 'width' => 50, 'filter' => 'ListSimpleMatch', 'filterOptions' => $prios_for_filter);
	}
	
	$colDef[] = array('title_key' => 'status', 'width' => 50, 'type' => 'status');

	return array($colDef, $sortByCol);
}
?>

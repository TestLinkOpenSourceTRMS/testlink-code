<?php
/**
 * 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	resultsByTesterPerBuild.php
 * @package TestLink
 * @author Andreas Simon
 * @copyright 2010 - 2012 TestLink community
 *
 * Lists results and progress by tester per build.
 * 
 * @internal revisions
 * @since 1.9.4
 * 20120430 - franciscom - TICKET 4992: Reports - Results by Tester per Build - refactoring
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

// By default Only open builds are displayed
// we will check if we have open builds
$openBuildsQty = $tplan_mgr->getNumberOfBuilds($args->tplan_id,null,testplan::OPEN_BUILDS);

// not too wise duplicated code, but effective => Quick & Dirty
if( $openBuildsQty <= 0 )
{
	$gui->warning_message = lang_get('no_open_builds');
	$smarty = new TLSmarty();
	$smarty->assign('gui',$gui);
	$smarty->display($templateCfg->template_dir . $templateCfg->default_template);
	exit();
}


$metricsMgr = new tlTestPlanMetrics($db);
$statusCfg = $metricsMgr->getStatusConfig();
$metrics = $metricsMgr->getStatusTotalsByBuildUAForRender($args->tplan_id,
														  array('processClosedBuilds' => $args->show_closed_builds));
$matrix = $metrics->info;


// Here need to work, because all queries consider ONLY ACTIVE STATUS
$option = $args->show_closed_builds ? null : testplan::GET_OPEN_BUILD;
$build_set = $metricsMgr->get_builds($args->tplan_id, testplan::GET_ACTIVE_BUILD, $option);
$names = $user->getNames($db);

// get the progress of the whole build based on executions of single users
$build_statistics = array();
foreach($matrix as $build_id => $build_execution_map) 
{
	$build_statistics[$build_id]['total'] = 0;
	$build_statistics[$build_id]['executed'] = 0;
	foreach ($build_execution_map as $user_id => $statistics) 
	{
		// total assigned test cases
		$build_statistics[$build_id]['total'] += $statistics['total'];
	
		// total executed testcases
		$executed = $statistics['total'] - $statistics['not_run']['count']; 
		$build_statistics[$build_id]['executed'] += $executed;
	}
	// build progress
	$build_statistics[$build_id]['progress'] = round($build_statistics[$build_id]['executed'] / 
													 $build_statistics[$build_id]['total'] * 100,2);
}

// build the content of the table
$rows = array();

foreach ($matrix as $build_id => $build_execution_map) 
{
	foreach ($build_execution_map as $user_id => $statistics) 
	{
		$current_row = array();
		
		// add build name to row including Progress
		$current_row[] = $build_set[$build_id]['name'] . " - " . lang_get('progress_absolute') . 
		                 " {$build_statistics[$build_id]['progress']}%";
		
		// add username and link it to tcAssignedToUser.php
		// $username = $names[$user_id]['login'];
		$name = "<a href=\"javascript:openAssignmentOverviewWindow(" .
			    "{$user_id}, {$build_id}, {$args->tplan_id});\">{$names[$user_id]['login']}</a>";
		$current_row[] = $name;
		
		// total count of testcases assigned to this user on this build
		$current_row[] = $statistics['total'];
		
		// add count and percentage for each possible status
		foreach ($statusCfg as $status => $code) 
		{
			$current_row[] = $statistics[$status]['count'];
			
			$current_row[] = $statistics[$status]['percentage'];
		}
		
		$current_row[] = $statistics['progress'];
		
		// add this row to the others
		$rows[] = $current_row;
	}
}

$columns = getTableHeader($statusCfg);
$smartTable = new tlExtTable($columns, $rows, 'tl_table_results_by_tester_per_build');
$smartTable->title = lang_get('results_by_tester_per_build');
$smartTable->setGroupByColumnName(lang_get('build'));

// enable default sorting by progress column
$smartTable->setSortByColumnName(lang_get('progress'));

//define toolbar
$smartTable->showToolbar = true;
$smartTable->toolbarExpandCollapseGroupsButton = true;
$smartTable->toolbarShowAllColumnsButton = true;

$gui->tableSet = array($smartTable);

// show warning message instead of table if table is empty
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
function init_args(&$tproject_mgr, &$tplan_mgr) 
{
	$iParams = array("format" => array(tlInputParameter::INT_N),
		             "tplan_id" => array(tlInputParameter::INT_N));

	$args = new stdClass();
	R_PARAMS($iParams,$args);
    
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	
	if($args->tproject_id > 0) 
	{
		$args->tproject_info = $tproject_mgr->get_by_id($args->tproject_id);
		$args->tproject_name = $args->tproject_info['name'];
		$args->tproject_description = $args->tproject_info['notes'];
	}
	
	if ($args->tplan_id > 0) 
	{
		$args->tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
		
	}
	
	$selection = false;
    $show_closed_builds = isset($_REQUEST['show_closed_builds']) ? true : false;
	$show_closed_builds_hidden = isset($_REQUEST['show_closed_builds_hidden']) ? true : false;
	if ($show_closed_builds) 
	{
		$selection = true;
	} 
	else if ($show_closed_builds_hidden) 
	{
		$selection = false;
	} 
	else if (isset($_SESSION['reports_show_closed_builds'])) 
	{
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
function init_gui(&$argsObj) 
{
	$gui = new stdClass();
	
	$gui->pageTitle = lang_get('caption_results_by_tester_per_build');
	$gui->warning_msg = '';
	$gui->tproject_name = $argsObj->tproject_name;
	$gui->tplan_name = $argsObj->tplan_info['name'];
	$gui->show_closed_builds = $argsObj->show_closed_builds;
	
	return $gui;
}

/**
 * 
 * 
 */
function getTableHeader($statusCfg)
{
	$resultsCfg = config_get('results');	

	$colCfg = array();	
	$colCfg[] = array('title_key' => 'build', 'width' => 50, 'type' => 'text', 'sortType' => 'asText',
		               'filter' => 'string');
	$colCfg[] = array('title_key' => 'user', 'width' => 50, 'type' => 'text', 'sortType' => 'asText',
		               'filter' => 'string');
	$colCfg[] = array('title_key' => 'th_tc_assigned', 'width' => 50, 'sortType' => 'asFloat',
	                   'filter' => 'numeric');

	foreach ($statusCfg as $status => $code) 
	{
		$label = $resultsCfg['status_label'][$status];
		$colCfg[] = array('title_key' => $label, 'width' => 20, 'sortType' => 'asInt',
		                   'filter' => 'numeric');
		$colCfg[] = array('title' => lang_get($label).' '.lang_get('in_percent'),
		                   'col_id' => 'id_'.$label.'_percent', 'width' => 30, 
		                   'type' => 'float', 'sortType' => 'asFloat', 'filter' => 'numeric');
	}
	
	$colCfg[] = array('title_key' => 'progress', 'width' => 30, 'type' => 'float',
	                   'sortType' => 'asFloat', 'filter' => 'numeric');
	return $colCfg;	                   
}



/*
 * rights check function for testlinkInitPage()
 */
function checkRights(&$db, &$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>

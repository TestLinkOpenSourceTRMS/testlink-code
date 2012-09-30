<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	metricsDashboard.php
 * @package 	  TestLink
 * @copyright   2007-2011, TestLink community 
 * @author      franciscom
 *
 * @internal revisions
 * @since 2.0
 *
 **/
require('../../config.inc.php');
require_once('common.php');
require_once('exttable.class.php');
testlinkInitPage($db);
$templateCfg = templateConfiguration();
$args = init_args($db);
checkRights($db,$_SESSION['currentUser'],$args);


$gui = new stdClass();
$gui->tproject_name = $args->tproject_name;
$gui->show_only_active = $args->show_only_active;
$result_cfg = config_get('results');
$show_all_status_details = config_get('metrics_dashboard')->show_test_plan_status;
$round_precision = config_get('dashboard_precision');

$labels = init_labels(array('overall_progress' => null, 'test_plan' => null, 'progress' => null,
                            'href_metrics_dashboard' => null, 'progress_absolute' => null,
                            'no_testplans_available' => null, 'not_aplicable' => null,
                            'platform' => null, 'th_active_tc' => null, 'in_percent' => null));

list($gui->tplan_metrics,$gui->show_platforms, $platforms) = getMetrics($db,$_SESSION['currentUser'],$args,$result_cfg, $labels);

$gui->warning_msg = $labels['no_testplans_available'];
if(count($gui->tplan_metrics) > 0) {

	$gui->warning_msg = '';
	
	// Create column headers
	$columns = getColumnsDefinition($gui->show_platforms, $result_cfg, $labels, $platforms);

	// Extract the relevant data and build a matrix
	$matrixData = array();
	
	foreach ($gui->tplan_metrics['testplans'] as $tplan_metrics)
	{
		foreach($tplan_metrics['platforms'] as $key => $platform_metric) {
			$rowData = array();
			
			// if test plan does not use platforms a overall status is not necessary
			$tplan_string = strip_tags($platform_metric['tplan_name']);
			
			if ($show_all_status_details) {
				// add information for all exec statuses
				$tplan_string .= "<br>";
				foreach ($result_cfg['status_label'] as $key => $status)
				{
					$tplan_string .= lang_get($status). ": " .$tplan_metrics['overall'][$key] .
					                 " [" .getPercentage($tplan_metrics['overall'][$key], 
					                                        $tplan_metrics['overall']['active'],
					                                        $round_precision) . "%], ";
				}
			} else {
				$tplan_string .= " - ";
			}
			
			$tplan_string .= $labels['overall_progress'] . ": " . 
			                 getPercentage($tplan_metrics['overall']['executed'],
			                                  $tplan_metrics['overall']['active'],
			                                  $round_precision) . "%";
			
			$rowData[] = $tplan_string;
			
			if ($gui->show_platforms) {
				$rowData[] = strip_tags($platform_metric['platform_name']);
			}
			
			$rowData[] = $platform_metric['active'];
			
			foreach ($result_cfg['status_label'] as $key => $status)
			{
				$rowData[] = $platform_metric[$key];
				$rowData[] = getPercentage($platform_metric[$key], $platform_metric['active'],
				                              $round_precision);
			}

			$rowData[] = getPercentage($platform_metric['executed'], $platform_metric['active'],
			                              $round_precision);
				
			$matrixData[] = $rowData;
		}
	}
	
	$table = new tlExtTable($columns, $matrixData, 'tl_table_metrics_dashboard');

	// if platforms are to be shown -> group by test plan
	// if no platforms are to be shown -> no grouping
	if($gui->show_platforms) {
		$table->setGroupByColumnName($labels['test_plan']);
	}

	$table->setSortByColumnName($labels['progress']);
	$table->sortDirection = 'DESC';
	$table->showGroupItemsCount = true;
	$table->title = $labels['href_metrics_dashboard'];

	$gui->tableSet = array($table);
	
	// collect test project metrics
	$gui->project_metrics = array();
	// get overall progress
	$gui->project_metrics['executed']['value'] = getPercentage($gui->tplan_metrics['total']['executed'], 
	                                                           $gui->tplan_metrics['total']['active'], $round_precision);
	$gui->project_metrics['executed']['label_key'] = 'progress_absolute';
	
	foreach ($result_cfg['status_label'] as $key => $status)
	{
		$gui->project_metrics[$key]['value'] = getPercentage($gui->tplan_metrics['total'][$key], 
	                                                   $gui->tplan_metrics['total']['active'], $round_precision);
	    $gui->project_metrics[$key]['label_key'] = $status;
	}
}

$smarty = new TLSmarty;
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * 
 *	@internal revisions
 *
 */
function getMetrics(&$db,$userObj,$args, $result_cfg, $labels)
{
	$user_id = $args->currentUserID;
	$tproject_id = $args->tproject_id;
	$linked_tcversions = array();
	$metrics = array();
	$tplan_mgr = new testplan($db);
	$show_platforms = false;
	$platforms = array();

	// get all tesplans accessibles  for user, for $tproject_id
	$options['active'] = $args->show_only_active ? ACTIVE : TP_ALL_STATUS; 
	$test_plans = $userObj->getAccessibleTestPlans($db,$tproject_id,null,$options);

	// Get count of testcases linked to every testplan
	foreach($test_plans as $key => $value)
	{
		$tplan_id = $value['id'];
		
		$linked_tcversions[$tplan_id] = null;
		$platformSet=$tplan_mgr->getPlatforms($tplan_id);
		
		if (isset($platformSet)) {
			$platforms = array_merge($platforms, $platformSet);
		} else {
			$platforms[]['name'] = $labels['not_aplicable'];
		}
		$show_platforms_for_tplan = !is_null($platformSet);

		if(!$show_platforms_for_tplan)
		{
			// Julian: replaced array(0=>'')
			$platformSet=array(0=>array('id'=> 0));
		} else {
			// 20110615 - Julian - if at least 1 test plan of the test project uses platforms
			//                     we need to display platform column on metrics dashboard
			$show_platforms = true;
		}

		# initialize counters
		foreach($platformSet as $platform_id => $platform_name)
		{
			$metrics['testplans'][$tplan_id]['platforms'][$platform_name['id']]['tplan_name'] = $value['name'];
			$metrics['testplans'][$tplan_id]['platforms'][$platform_name['id']]['platform_name'] = $platform_name['id'] == 0 ?
			$labels['not_aplicable'] : $platform_name['name'];
			 
			$metrics['testplans'][$tplan_id]['platforms'][$platform_name['id']]['active'] = 0;
			$metrics['testplans'][$tplan_id]['overall']['active'] = 0;
			$metrics['total']['active'] = 0;

			$metrics['testplans'][$tplan_id]['platforms'][$platform_name['id']]['executed'] = 0;
			$metrics['testplans'][$tplan_id]['overall']['executed'] = 0;
			$metrics['total']['executed'] = 0;
			
			foreach ($result_cfg['status_label'] as $key => $status)
			{
				$metrics['testplans'][$tplan_id]['platforms'][$platform_name['id']][$key] = 0;
				$metrics['testplans'][$tplan_id]['overall'][$key] = 0;
				$metrics['total'][$key] = 0;			
			}
		}

		
		if( ($linkedItemsQty = $tplan_mgr->count_testcases($tplan_id)) > 0 )
		{

			$executed = null;		
			$not_run = null;
					
			// get executions ON ACTIVE BUILDS
			//
			// IMPORTANTE NOTICE
			// using 'output' => 'mapOfMap' means we will get JUST ONE exec record for test case / platform
			// 
			$options = array('output' => 'mapOfMap', 'steps_info' => 0, 'build_active_status' => 'active');
			$filters=null;
			$executed[$tplan_id] = $tplan_mgr->get_linked_tcversions($tplan_id,$filters,$options);
	
			// Simple test to cope with active/inactive build
			if( is_null($executed[$tplan_id]) )
			{	
				// need a simple call to get linked items and set status to NOT RUN on all items.
				$filters=null;
				$options = array('output' => 'mapOfMap', 'steps_info' => 0, 
								 'forced_exec_status' => $result_cfg['status_code']['not_run']);
				$executed[$tplan_id] = $tplan_mgr->get_linked_tcversions($tplan_id,$filters,$options);
			}
			else
			{		
				// get NOT EXECUTED on ACTIVE BUILDS and EXECUTED on INACTIVE BUILDS
				// EXECUTED on INACTIVE BUILDS are candidate to become NOT EXECUTED on ACTIVE BUILDS
				//
				$options = array('output' => 'mapOfMap', 'steps_info' => 0,
								 'build_active_status' => 'active',
								 'forced_exec_status' => $result_cfg['status_code']['not_run']);
				$filters = array('exec_status' => $result_cfg['status_code']['not_run']);
				$not_run[$tplan_id] = $tplan_mgr->get_linked_tcversions($tplan_id,$filters,$options);
			}
			
			// Time to work on keys
			$notRunKeys = array_keys($not_run[$tplan_id]);
			foreach($notRunKeys as $tcaseIDkey)
			{
				// BUGID 4362
				// Mistake was this:
				// isset($executed[$tplan_id][$key2copy]) 
				// just means we have found at least one execution.
				// But inside the element we have a map indexed by platform id.
				// If we have N platforms, and have exec on M, we have M elements
				// and MISS TO ADD the N-M NOT EXECUTED generating the issue.
				if( !isset($executed[$tplan_id][$tcaseIDkey]) )
				{
					$executed[$tplan_id][$tcaseIDkey] = array();
				}
				$executed[$tplan_id][$tcaseIDkey] += $not_run[$tplan_id][$tcaseIDkey];		
			}
			$linked_tcversions[$tplan_id] = (array)$executed[$tplan_id];
		}  // test plan has linked items	
	}
	
	// Get count of executed testcases
	foreach($linked_tcversions as $tplan_id => $tcinfo)
	{
		if(!is_null($tcinfo))
		{
			foreach($tcinfo as $tcase_id => $tc)
			{
				foreach($tc as $platform_id => $value)
				{
					if($value['active'])
					{
						// count number of active test cases for each platform, each test plan 
						// and whole project
						$metrics['testplans'][$tplan_id]['platforms'][$platform_id]['active']++;
						$metrics['testplans'][$tplan_id]['overall']['active']++;
						$metrics['total']['active']++;
						
						// count number of test cases depending on execution status (result) for
						// each platform, each test plan and whole project
						$status_key = array_keys($result_cfg['status_code'], $value['exec_status']);
						$metrics['testplans'][$tplan_id]['platforms'][$platform_id][$status_key[0]]++;
						$metrics['testplans'][$tplan_id]['overall'][$status_key[0]]++;
						$metrics['total'][$status_key[0]]++;
						
						// count number of executed test cases for each platform, each test plan and
						// the whole project
						//
						// 20110317 - do not know how we do not have tested for exec status <> not_run
						// After change done to fix inactive build behaviour we need to check for
						// execution status
						// if ($value['exec_id'] > 0) 
						if( $value['exec_status'] != $result_cfg['status_code']['not_run'] )
						{
							$metrics['testplans'][$tplan_id]['platforms'][$platform_id]['executed']++;
							$metrics['testplans'][$tplan_id]['overall']['executed']++;
							$metrics['total']['executed']++;
						}
					}
				}
			}
		}
	}
	
	// remove duplicate platform names
	$platforms_no_duplicates = array();
	foreach($platforms as $platform) {
		if(!in_array($platform['name'], $platforms_no_duplicates)) {
			$platforms_no_duplicates[] = $platform['name'];
		}
	}
	
	return array($metrics, $show_platforms, $platforms_no_duplicates);
}

/**
 * 
 *
 */
function getPercentage($denominator, $numerator, $round_precision)
{
	$percentage = ($numerator > 0) ? (round(($denominator / $numerator) * 100,$round_precision)) : 0;

	return $percentage;
}

/**
 * get Columns definition for table to display
 *
 */
function getColumnsDefinition($showPlatforms, $result_cfg, $labels, $platforms)
{
	$colDef = array();

	$colDef[] = array('title_key' => 'test_plan', 'width' => 60, 'type' => 'text', 'sortType' => 'asText',
	                  'filter' => 'string');

	if ($showPlatforms)
	{
		$colDef[] = array('title_key' => 'platform', 'width' => 60, 'sortType' => 'asText',
		                  'filter' => 'list', 'filterOptions' => $platforms);
	}

	$colDef[] = array('title_key' => 'th_active_tc', 'width' => 40, 'sortType' => 'asInt',
	                  'filter' => 'numeric');
	
	// create 2 columns for each defined status
	foreach ($result_cfg['status_label'] as $key => $status)
	{
		$colDef[] = array('title_key' => $status, 'width' => 40, 'hidden' => true, 'type' => 'int',
		                  'sortType' => 'asInt', 'filter' => 'numeric');
		
		$colDef[] = array('title' => lang_get($status) . " " . $labels['in_percent'], 'width' => 40,
		                  'col_id' => 'id_'.$status.'_percent', 'type' => 'float', 'sortType' => 'asFloat',
		                  'filter' => 'numeric');
	}
	
	$colDef[] = array('title_key' => 'progress', 'width' => 40, 'sortType' => 'asFloat', 'filter' => 'numeric');

	return $colDef;

}

function init_args(&$dbHandler)
{
	$args = new stdClass();

	// BUGID 4066 - take care of proper escaping when magic_quotes_gpc is enabled
	$_REQUEST=strings_stripSlashes($_REQUEST);

	$args->tproject_name = '';
	$args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
	if($args->tproject_id > 0)
	{
		$treeMgr = new tree($dbHandler);
		$dummy = $treeMgr->get_node_hierarchy_info($args->tproject_id); 
		$args->tproject_name = $dummy['name'];
	}

	$args->currentUserID = $_SESSION['currentUser']->dbID;

	$show_only_active = isset($_REQUEST['show_only_active']) ? true : false;
	$show_only_active_hidden = isset($_REQUEST['show_only_active_hidden']) ? true : false;
	if ($show_only_active) {
		$selection = true;
	} else if ($show_only_active_hidden) {
		$selection = false;
	} else if (isset($_SESSION['show_only_active'])) {
		$selection = $_SESSION['show_only_active'];
	} else {
		$selection = true;
	}
	$args->show_only_active = $_SESSION['show_only_active'] = $selection;

	return $args;
}


/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('testplan_metrics','testplan_execute'),'or');
}
?>
<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * Filename $RCSfile: metricsDashboard.php,v $
 *
 * @version $Revision: 1.25 $
 * @modified $Date: 2010/10/15 09:00:12 $ $Author: mx-julian $
 *
 * @author franciscom
 *
 * @internal revisions
 * 20101015 - Julian - refactored exttable column titles
 * 20101014 - Julian - BUGID 3893 - Extended metrics dashboard
 * 20100922 - Julian - Hide "Progress (Executed/Total)"-Column by default
 * 20100917 - Julian - BUGID 3724 - checkbox to show all/active test plans
 *                                - use of exttable
 * 20100526 - Julian - fixed wrong access to platform array
 * 20100525 - Julian - added option 'step_info' => 0 to get_linked_tcversions call
 *                     to improve performance
 * 20090919 - franciscom - added platform info
 *
 **/
require('../../config.inc.php');
require_once('common.php');
require_once('exttable.class.php');
testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();

$args = init_args();
$gui = new stdClass();
$gui->tproject_name = $args->tproject_name;
$gui->show_only_active = $args->show_only_active;
$gui->warning_msg = '';
$result_cfg = config_get('results');
$show_all_status_details = config_get('metrics_dashboard')->show_test_plan_status;
$round_precision = config_get('dashboard_precision');

$labels = init_labels(array('overall_progress' => null, 'test_plan' => null, 'progress' => null,
                            'href_metrics_dashboard' => null, 'progress_absolute' => null,
                            'no_testplans_available' => null, 'not_aplicable' => null,
                            'platform' => null, 'th_active_tc' => null, 'in_percent' => null));

list($gui->tplan_metrics,$gui->show_platforms) = getMetrics($db,$args,$result_cfg, $labels);

if(count($gui->tplan_metrics) > 0) {

	// Create column headers
	$columns = getColumnsDefinition($gui->show_platforms, $result_cfg, $labels);

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
					                 " [" .formatPercentage($tplan_metrics['overall'][$key], 
					                                        $tplan_metrics['overall']['active'],
					                                        $round_precision) . "%], ";
				}
			} else {
				$tplan_string .= " - ";
			}
			
			$tplan_string .= $labels['overall_progress'] . ": " . 
			                 formatPercentage($tplan_metrics['overall']['executed'],
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
				$rowData[] = formatPercentage($platform_metric[$key], $platform_metric['active'],
				                              $round_precision);
			}

			$rowData[] = formatPercentage($platform_metric['executed'], $platform_metric['active'],
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

	$table->showToolbar = true;
	$table->toolbarExpandCollapseGroupsButton = true;
	$table->toolbarShowAllColumnsButton = true;
	$table->title = $labels['href_metrics_dashboard'];
	$table->showGroupItemsCount = true;

	$gui->tableSet = array($table);
	
	// collect test project metrics
	$gui->project_metrics = array();
	$gui->project_metrics['progress_absolute'] = getPercentage($gui->tplan_metrics['total']['executed'], 
	                                                 $gui->tplan_metrics['total']['active'], $round_precision);
	foreach ($result_cfg['status_label'] as $key => $status)
	{
		$gui->project_metrics[$status] = getPercentage($gui->tplan_metrics['total'][$key], 
	                                                $gui->tplan_metrics['total']['active'], $round_precision);
	}
} else {
	$gui->warning_msg = $labels['no_testplans_available'];
}

$smarty = new TLSmarty;
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

function getMetrics(&$db,$args, $result_cfg, $labels)
{
	$user_id = $args->currentUserID;
	$tproject_id = $args->tproject_id;
	$linked_tcversions = array();
	$metrics = array();
	$tplan_mgr = new testplan($db);
	$show_platforms = false;

	// BUGID 1215
	// get all tesplans accessibles  for user, for $tproject_id
	if($args->show_only_active) {
		$options = array('active' => ACTIVE);
	} else {
		$options = array('active' => TP_ALL_STATUS);
	}

	$test_plans = $_SESSION['currentUser']->getAccessibleTestPlans($db,$tproject_id,null,$options);

	// Get count of testcases linked to every testplan
	foreach($test_plans as $key => $value)
	{
		$tplan_id = $value['id'];
		$filters=null;
		$options = array('output' => 'mapOfMap', 'steps_info' => 0);
		$linked_tcversions[$tplan_id] = $tplan_mgr->get_linked_tcversions($tplan_id,$filters,$options);
		$platformSet=$tplan_mgr->getPlatforms($tplan_id);

		if( is_null($platformSet) )
		{
			// Julian: replaced array(0=>'')
			$platformSet=array(0=>array('id'=> 0));
		}
		else
		{
			$show_platforms = true;
		}

		# initlialize counters
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
						// count number of active test cases for each platform, each test plan and 
						// the whole project
						$metrics['testplans'][$tplan_id]['platforms'][$platform_id]['active']++;
						$metrics['testplans'][$tplan_id]['overall']['active']++;
						$metrics['total']['active']++;
						
						// count number of test cases depending on execution status (result) for
						// each platform, each test plan and the whole project
						$status_key = array_keys($result_cfg['status_code'], $value['exec_status']);
						$metrics['testplans'][$tplan_id]['platforms'][$platform_id][$status_key[0]]++;
						$metrics['testplans'][$tplan_id]['overall'][$status_key[0]]++;
						$metrics['total'][$status_key[0]]++;
						
						// count number of executed test cases for each platform, each test plan and
						// the whole project
						if ($value['exec_id'] > 0) {
							$metrics['testplans'][$tplan_id]['platforms'][$platform_id]['executed']++;
							$metrics['testplans'][$tplan_id]['overall']['executed']++;
							$metrics['total']['executed']++;
						}
					}
				}
			}
		}
	}
	return array($metrics, $show_platforms);
}

function formatPercentage($denominator, $numerator, $round_precision)
{
	// use html comment to be able to properly sort by percentage columns on exttable
	$formatted_percentage = "<!-- 0 -->0";
	if ($numerator > 0) {
		$percentage = round(($denominator / $numerator) * 100,$round_precision);
		$percentage_comment = sprintf("%010.{$round_precision}f", $percentage);
		$formatted_percentage = "<!-- $percentage_comment -->" . $percentage;
	}

	return($formatted_percentage);
}

function getPercentage($denominator, $numerator, $round_precision)
{
	$percentage = ($numerator > 0) ? (round(($denominator / $numerator) * 100,$round_precision)) : 0;

	return($percentage);
}
/**
 * get Columns definition for table to display
 *
 */
function getColumnsDefinition($showPlatforms, $result_cfg, $labels)
{
	$colDef = array();

	$colDef[] = array('title_key' => 'test_plan', 'width' => 60, 'type' => 'text');

	if ($showPlatforms)
	{
		$colDef[] = array('title_key' => 'platform', 'width' => 60);
	}

	$colDef[] = array('title_key' => 'th_active_tc', 'width' => 40);
	
	// create 2 columns for each defined status
	foreach ($result_cfg['status_label'] as $key => $status)
	{
		$colDef[] = array('title_key' => $status, 'width' => 40, 'hidden' => true);
		$colDef[] = array('title' => lang_get($status) . " " . $labels['in_percent'], 'width' => 40,
		                  'col_id' => 'id_'.$status.'_percent');
	}
	
	$colDef[] = array('title_key' => 'progress', 'width' => 40);

	return $colDef;

}

function init_args()
{
	$args = new stdClass();

	$args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
	$args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
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

function checkRights(&$db,&$user)
{
	return ($user->hasRight($db,'testplan_metrics') || $user->hasRight($db,'testplan_execute'));
}
?>
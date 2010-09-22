<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: metricsDashboard.php,v $
 *
 * @version $Revision: 1.19 $
 * @modified $Date: 2010/09/22 12:35:28 $ $Author: mx-julian $
 *
 * @author franciscom
 *
 * @internal revisions
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
list($gui->tplan_metrics,$gui->show_platforms) = getMetrics($db,$args);

if(count($gui->tplan_metrics) > 0) {

	// Create column headers
	$columns = getColumnsDefinition($gui->show_platforms);
	
	// Extract the relevant data and build a matrix
	$matrixData = array();
	
	foreach ($gui->tplan_metrics as $tplan_metrics)
	{
		foreach($tplan_metrics as $platform_metric) {
			$rowData = array();
			$rowData[] = strip_tags($platform_metric['tplan_name']);
			if ($gui->show_platforms) {
				$rowData[] = strip_tags($platform_metric['platform_name']);
			}
			$rowData[] = $platform_metric['total'];
			$rowData[] = $platform_metric['active'];
			$rowData[] = $platform_metric['executed'];
	
			//to be able to properly sort by percentage add html comment
			//show percentage for platforms/testplan with no executed test cases as 0
			$executed_vs_active_string = "<!-- 0 -->0";
			if ($platform_metric['executed_vs_active'] > 0) {
				$percentage_comment = sprintf("%010d", $platform_metric['executed_vs_active']);
				$executed_vs_active_string = "<!-- $percentage_comment -->" . $platform_metric['executed_vs_active'];
			}
			$rowData[] = $executed_vs_active_string;
			              
			$executed_vs_total_string = "<!-- 0 -->0";
			if ($platform_metric['executed_vs_total'] > 0) {
				$percentage_comment = sprintf("%010d", $platform_metric['executed_vs_total']);
				$executed_vs_total_string = "<!-- $percentage_comment -->" . $platform_metric['executed_vs_total'];
			}
			$rowData[] = $executed_vs_total_string;
			
			$matrixData[] = $rowData;
		}
	}
	
	$table = new tlExtTable($columns, $matrixData, 'tl_table_metrics_dashboard');
	
	//if platforms are to be shown -> group by test plan
	// if no platforms are to be shown -> no grouping
	if($gui->show_platforms) {
		$table->setGroupByColumnName(lang_get('test_plan'));
	}
	
	$table->setSortByColumnName(lang_get('th_executed_vs_active'));
	$table->sortDirection = 'DESC';
	
	$table->showToolbar = true;
	$table->toolbarExpandCollapseGroupsButton = true;
	$table->toolbarShowAllColumnsButton = true;
	
	$gui->tableSet = array($table);
} else {
	$gui->warning_msg = lang_get('no_testplans_available');
}

$smarty = new TLSmarty;
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template); 

function getMetrics(&$db,$args)
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
        	//Julian: replaced array(0=>'')
        	$platformSet=array(0=>array('id'=> 0));
        }
        else
        {
        	$show_platforms = true;
        }
        
         
        foreach($platformSet as $platform_id => $platform_name) 
        {    
			$metrics[$tplan_id][$platform_name['id']]['tplan_name'] = $value['name'];
			$metrics[$tplan_id][$platform_name['id']]['platform_name'] = $platform_name['id'] == 0 ?
			                                      lang_get('not_aplicable') : $platform_name['name'];
			$metrics[$tplan_id][$platform_name['id']]['executed'] = 0;
			$metrics[$tplan_id][$platform_name['id']]['active'] = 0;
			$metrics[$tplan_id][$platform_name['id']]['total'] = 0;
    		$metrics[$tplan_id][$platform_name['id']]['executed_vs_active'] = -1;
    		$metrics[$tplan_id][$platform_name['id']]['executed_vs_total'] = -1;
    		$metrics[$tplan_id][$platform_name['id']]['active_vs_total'] = -1;
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
        			if($value['exec_id'] > 0)
        			{
          				$metrics[$tplan_id][$platform_id]['executed']++;
        			}
        			if($value['active'])
        			{
          				$metrics[$tplan_id][$platform_id]['active']++;
        			}
        			$metrics[$tplan_id][$platform_id]['total']++;
      			}
      		}
      		
    	}
  	}
  
	// Calculate percentages
	$round_precision = config_get('dashboard_precision');
	foreach($metrics as $tplan_id => $platform_metrics)
	{
		$platforms = array_keys($platform_metrics);
        foreach($platforms as $platform_id)
        {
			$planMetrics = &$metrics[$tplan_id][$platform_id];
			if($planMetrics['total'] > 0)
    		{
      			if($planMetrics['active'] > 0)
      			{
        			$planMetrics['executed_vs_active'] = $planMetrics['executed']/$planMetrics['active'];
        			$planMetrics['executed_vs_active'] = round($planMetrics['executed_vs_active'] * 100,$round_precision);
      			} 
      			$planMetrics['executed_vs_total'] = $planMetrics['executed']/$planMetrics['total'];
      			$planMetrics['executed_vs_total'] = round($planMetrics['executed_vs_total'] * 100,$round_precision);
        	
      			$planMetrics['active_vs_total'] = $planMetrics['active']/$planMetrics['total'];
      			$planMetrics['active_vs_total'] = round($planMetrics['active_vs_total'] * 100,$round_precision);
    		}
    	}	
 	}
	return array($metrics, $show_platforms);
}

/**
 * get Columns definition for table to display
 *
 */
function getColumnsDefinition($showPlatforms)
{
	$colDef = array();
	
	$colDef[] = array('title' => lang_get('test_plan'), 'width' => 60, 'type' => 'text');
		
	if ($showPlatforms)
	{
		$colDef[] = array('title' => lang_get('platform'), 'width' => 60);
	}
	
	$colDef[] = array('title' => lang_get('th_total_tc'), 'width' => 40);
	$colDef[] = array('title' => lang_get('th_active_tc'), 'width' => 40);
	$colDef[] = array('title' => lang_get('th_executed_tc'), 'width' => 40);
	$colDef[] = array('title' => lang_get('th_executed_vs_active'), 'width' => 40);
	// hide progress related to total number of test cases by default
	$colDef[] = array('title' => lang_get('th_executed_vs_total'), 'width' => 40, 'hidden' => true);
	
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
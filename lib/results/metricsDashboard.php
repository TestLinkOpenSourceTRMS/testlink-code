<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: metricsDashboard.php,v $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2009/06/03 19:51:45 $ $Author: schlundus $
 *
 * @author franciscom
 *
**/
require('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();

$args = init_args();
$gui = new stdClass();
$gui->tproject_name = $args->tproject_name;


$gui->tplan_metrics = getMetrics($db,$args);

$smarty = new TLSmarty;
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template); 

function getMetrics(&$db,$args)
{
	$user_id = $args->currentUser;
	$tproject_id = $args->tproject_id;
	$linked_tcversions = array();
	$metrics = array();
	$tplan_mgr = new testplan($db);
  
	// BUGID 1215
	// get all tesplans accessibles  for user, for $tproject_id
	$test_plans = getAccessibleTestPlans($db,$tproject_id,$user_id);

	// Get count of testcases linked to every testplan
	foreach($test_plans as $key => $value)
	{
    	$tplan_id = $value['id'];
    	$linked_tcversions[$tplan_id] = $tplan_mgr->get_linked_tcversions($tplan_id);
    
		$metrics[$tplan_id]['tplan_name'] = $value['name'];
		$metrics[$tplan_id]['executed'] = 0;
		$metrics[$tplan_id]['active'] = 0;
		$metrics[$tplan_id]['total'] = 0;
  }
  
	// Get count of executed testcases
 	foreach($linked_tcversions as $tplan_id => $tc)
	{
		$metrics[$tplan_id]['executed'] = 0;
    	$metrics[$tplan_id]['active'] = 0;
    	$metrics[$tplan_id]['total'] = 0;
    	$metrics[$tplan_id]['executed_vs_active'] = -1;
    	$metrics[$tplan_id]['executed_vs_total'] = -1;
    	$metrics[$tplan_id]['active_vs_total'] = -1;
 
    	if(!is_null($tc))
    	{
      		foreach($tc as $key => $value)
      		{
        		if($value['exec_id'] > 0)
        		{
          			$metrics[$tplan_id]['executed']++;
        		}
        		if($value['active'])
        		{
          			$metrics[$tplan_id]['active']++;
        		}
        		$metrics[$tplan_id]['total']++;
      		}
    	}
  	}
  
  
	// Calculate percentages
	$round_precision = config_get('dashboard_precision');
	foreach($metrics as $tplan_id => $value)
	{
		$planMetrics = &$metrics[$tplan_id];
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
	return $metrics;
}

function init_args()
{
	$args = new stdClass();
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
	$args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
	$args->currentUser = $_SESSION['currentUser']->dbID;
	
	return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>
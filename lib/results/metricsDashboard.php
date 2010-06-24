<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: metricsDashboard.php,v $
 *
 * @version $Revision: 1.13 $
 * @modified $Date: 2010/06/24 17:25:52 $ $Author: asimon83 $
 *
 * @author franciscom
 *
 * @internal revisions
 * 20100526 - Julian - fixed wrong access to platform array
 * 20100525 - Julian - added option 'step_info' => 0 to get_linked_tcversions call
 * 					   to improve performance
 * 20090919 - franciscom - added platform info
 *
**/
require('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();

$args = init_args();
$gui = new stdClass();
$gui->tproject_name = $args->tproject_name;
list($gui->tplan_metrics,$gui->show_platforms) = getMetrics($db,$args);

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
	$test_plans = $_SESSION['currentUser']->getAccessibleTestPlans($db,$tproject_id);

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
			                                                             '' : $platform_name['name'];
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

function init_args()
{
	$args = new stdClass();
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
	$args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
	$args->currentUserID = $_SESSION['currentUser']->dbID;
	
	return $args;
}

function checkRights(&$db,&$user)
{
	return ($user->hasRight($db,'testplan_metrics') || $user->hasRight($db,'testplan_execute'));
}
?>
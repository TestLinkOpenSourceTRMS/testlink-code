<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource $RCSfile: resultsGeneral.php,v $
 * @version $Revision: 1.58 $
 * @modified $Date: 2009/11/04 08:09:34 $ by $Author: franciscom $
 * @author	Martin Havlat <havlat at users.sourceforge.net>
 * 
 * This page show Test Results over all Builds.
 *
 * Revisions:
 *  20091103 - franciscom - keywords, assigned testers, platform results refactored,
 *                          noew use method from test plan class.
 *
 *  20090209 - franciscom - BUGID 2080
 *  20080928 - franciscom - removed useless requires
 * 	20050807 - fm - refactoring:  changes in getTestSuiteReport() call
 * 	20050905 - fm - reduce global coupling
 *  20070101 - KL - upgraded to 1.7
 * 	20080626 - mht - added milestomes, priority report, refactorization
 * 
 * ----------------------------------------------------------------------------------- */
require('../../config.inc.php');
require_once('common.php');
require_once('results.class.php');
require_once('displayMgr.php');
testlinkInitPage($db,true,false,"checkRights");

$args = init_args();
$templateCfg = templateConfiguration();

$gui = new stdClass();
$gui->showPlatforms=true;

$arrDataSuite = array();
$do_report = array();
$gui->colDefinition = array();
$gui->columnsDefinition = new stdClass();
$gui->columnsDefinition->keywords = null;
$gui->columnsDefinition->testers = null;
$gui->columnsDefinition->platform = null;

$gui->statistics = new stdClass();
$gui->statistics->keywords = null;
$gui->statistics->testers = null;
$gui->statistics->milestones = null;

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);
$gui->platformSet = $tplan_mgr->getPlatforms($args->tplan_id,'map');

if( is_null($gui->platformSet) )
{
	$gui->platformSet = array('');
	$gui->showPlatforms=false;
}

$metricsMgr = new tlTestPlanMetrics($db);

$re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,
                  ALL_TEST_SUITES,ALL_BUILDS);
// ----------------------------------------------------------------------------
$topLevelSuites = $re->getTopLevelSuites();

if(is_null($topLevelSuites))
{
	// no test cases -> no report
	$do_report['status_ok'] = 0;
	$do_report['msg'] = lang_get('report_tspec_has_no_tsuites');
	tLog('Overall Metrics page: no test cases defined');
}
else // do report
{
	$do_report['status_ok'] = 1;
	$do_report['msg'] = '';

	$items2loop = array('keywords','assigned_testers');

	$kwr = $tplan_mgr->getStatusTotalsByKeyword($args->tplan_id);
    $gui->statistics->keywords = $tplan_mgr->tallyResultsForReport($kwr);

    $usr=$tplan_mgr->getStatusTotalsByAssignedTester($args->tplan_id);
    $gui->statistics->assigned_testers = $tplan_mgr->tallyResultsForReport($usr);

	if( $gui->showPlatforms )
	{
		$items2loop[] = 'platform';
	    $platr = $tplan_mgr->getStatusTotalsByPlatform($args->tplan_id);
        $gui->statistics->platform = $tplan_mgr->tallyResultsForReport($platr);
	}

    // new dBug($gui->statistics);	
	foreach($items2loop as $item)
	{
      	if( !is_null($gui->statistics->$item) )
      	{
        	// Get labels
          	$dummy = current($gui->statistics->$item);
          	foreach($dummy['details'] as $status_verbose => $value)
          	{
              	$dummy['details'][$status_verbose]['qty'] = 
              			lang_get($tlCfg->results['status_label'][$status_verbose]);
            	$dummy['details'][$status_verbose]['percentage'] = "[%]";
            }
          	$gui->columnsDefinition->$item = $dummy['details'];
         } 
  	} 





  	$mapOfAggregate = $re->getAggregateMap();
  	$arrDataSuite = null;
  	$arrDataSuiteIndex = 0;

	// collect data for top test suites and users  
  	if (is_array($topLevelSuites)) 
  	{
      	foreach($topLevelSuites as $key => $suiteNameID)
      	{
      		$results = $mapOfAggregate[$suiteNameID['id']];
      			
      		$element['tsuite_name'] = $suiteNameID['name'];
      		$element['total_tc'] = $results['total'];
      		$element['percentage_completed'] = get_percentage($results['total'], 
      		$results['total'] - $results['not_run']);

        	unset($results['total']);
        	foreach($results as $key => $value)
        	{
      	    	$element['details'][$key]['qty'] = $results[$key];
      		}
      		$element['details']['not_run']['qty'] = $results['not_run'];
      	   
      		$arrDataSuite[$arrDataSuiteIndex] = $element;
      		$arrDataSuiteIndex++;
      	} 

    	$gui->statistics->testsuites = $arrDataSuite;

      	// Get labels
    	$dummy = current($gui->statistics->testsuites);
      	foreach($dummy['details'] as $status_verbose => $value)
    	{
          	$dummy['details'][$status_verbose]['qty'] = 
          			lang_get($tlCfg->results['status_label'][$status_verbose]);
      	}
      	$gui->columnsDefinition->testsuites = $dummy['details'];
  	}

	// ----------------------------------------------------------------------------
  	/* BUILDS REPORT */
    // $buildSet = $tplan_mgr->get_builds($args->tplan_id); //,testplan::ACTIVE_BUILDS);
    // 
    // 
    // $filters=null;
    // $options=array('output' => 'array' , 'last_execution' => true, 'only_executed' => true, 'execution_details' => 'add_build');
    // $myRBB = $tplan_mgr->get_linked_tcversions($args->tplan_id,$filters,$options);
    // $loop2do=count($myRBB);
    // $code_verbose=$tplan_mgr->getStatusForReports();
    // foreach($buildSet as $key => $elem )
    // {
    // 	foreach($code_verbose as $code => $verbose)
    // 	{
    // 		$buildResults[$key][$code]=0;		
    // 	}	
    // }
    // 
    // for($idx=0; $idx < $loop2do; $idx++)
    // {
    // 	$buildID=$myRBB[$idx]['build_id'];
    // 	$exec_status=$myRBB[$idx]['exec_status'];
    // 	$buildResults[$buildID][$exec_status]++;
    // 	// $buildResults[$buildID]	
    // }  
    // 
    // foreach($buildResults as $key => $value)
    // {
    // 
    // }
    //      
    // new dBug($buildResults);
    // 
    // new dBug($options);
    // new dBug($myRBB);
    // 
	// $results = $re->getAggregateBuildResults();
    // new dBug($results);    
    

	$colDefiniton = null;
	$results = null;
	if($do_report['status_ok'])
	{
  		$results = $re->getAggregateBuildResults();


  		if ($results != null) 
  		{
      		// Get labels
      		$resultsCfg = config_get('results');
      		$labels = $resultsCfg['status_label'];
      
      		// I will add not_run if not exists
		  	$keys2display = array('not_run' => 'not_run');
		  	foreach($resultsCfg['status_label_for_exec_ui'] as $key => $value)
		  	{
		      	if($key != 'not_run')
		      	{
		        	$keys2display[$key] = $key;  
		      	}  
		  	}
      
      		foreach($keys2display as $status_verbose => $value)
      		{
            	$l18n_label = isset($labels[$status_verbose]) ? lang_get($labels[$status_verbose]) : 
                              lang_get($status_verbose); 
            
            	$colDefinition[$status_verbose]['qty'] = $l18n_label;
            	$colDefinition[$status_verbose]['percentage'] = '[%]';
      		}
  		}    
	}  


	
  	/* MILESTONE & PRIORITY REPORT */
    $planMetrics = $tplan_mgr->getStatusTotals($args->tplan_id);
    // new dBug($planMetrics);


	$filters=null;
	$options=array('output' => 'map', 'only_executed' => true, 'execution_details' => 'add_build');
    $execResults = $tplan_mgr->get_linked_tcversions($args->tplan_id,$filters,$options);
    // new dBug($options);
    // new dBug($execResults);
    
    $options=array('output' => 'mapOfArray', 'only_executed' => true, 'execution_details' => 'add_build');
    $execResults = $tplan_mgr->get_linked_tcversions($args->tplan_id,$filters,$options);
    // new dBug($options);
    // new dBug($execResults);
    
    $options=array('output' => 'mapOfMap', 'only_executed' => true, 'execution_details' => 'add_build');
    $execResults = $tplan_mgr->get_linked_tcversions($args->tplan_id,$filters,$options);
    // new dBug($options);
    // new dBug($execResults);
    
    $options=array('output' => 'array', 'only_executed' => true, 'execution_details' => 'add_build');
    $execResults = $tplan_mgr->get_linked_tcversions($args->tplan_id,$filters,$options);
    // new dBug($options);
    // new dBug($execResults);
    
    

	// collect prioritized results for whole Test Plan
	if ($_SESSION['testprojectOptPriority'])
	{
		$set2loop = array('high_percentage' => HIGH,'medium_percentage' => MEDIUM,
		                  'low_percentage' => LOW);
		$gui->statistics->priority_overall = $metricsMgr->getPrioritizedResults($args->tplan_id);
		foreach( $set2loop as $key => $value )
		{
			$gui->statistics->priority_overall[$key] = get_percentage($planMetrics['total'],
				                                                      $gui->statistics->priority_overall[$value]); 
		}
		// echo 'OPI';
		// new dBug($gui->statistics);
		
	}
	// collect milestones
	$milestonesList = $tplan_mgr->get_milestones($args->tplan_id);
    // new dBug($milestonesList);

	if (!empty($milestonesList))
	{
		$xx = $metricsMgr->getMilestonesMetrics($args->tplan_id,$milestonesList);
		// new dBug($xx);
    }
        
	// get test results for milestones
	if (!empty($milestonesList))
	{
	
		$arrPrioritizedTCs = $metricsMgr->getPrioritizedTestCaseCounters($args->tplan_id);
		foreach($milestonesList as $item)
		{
		    $item['tc_total'] = $planMetrics['total'];
		    $item['results'] = $metricsMgr->getPrioritizedResults($item['target_date']);
        
        	$low_percentage = get_percentage($arrPrioritizedTCs[LOW], $item['results'][LOW]); 
		    $item['result_low_percentage'] = $low_percentage;
		    $medium_percentage = get_percentage($arrPrioritizedTCs[MEDIUM], $item['results'][MEDIUM]); 
		    $item['result_medium_percentage'] = $medium_percentage;
		    $high_percentage = get_percentage($arrPrioritizedTCs[HIGH], $item['results'][HIGH]); 
		    $item['result_high_percentage'] = $high_percentage;
		    		    
		    $item['tc_completed'] = $item['results'][HIGH] + $item['results'][MEDIUM] + $item['results'][LOW];
		    $item['percentage_completed'] = get_percentage($item['tc_total'], $item['tc_completed']);
        
       		$item['low_incomplete'] = OFF;
        	$item['medium_incomplete'] = OFF;
	    	$item['high_incomplete'] = OFF;
	    	$item['all_incomplete'] = OFF;
        
		    if ($low_percentage < $item['low_percentage'])
		    {
		    	$item['low_incomplete'] = ON;
		    }	

		    if ($medium_percentage < $item['medium_percentage'])
		    {
		    	$item['medium_incomplete'] = ON;
		    }	
		    	
		    if ($high_percentage < $item['high_percentage'])
		    {
		    	$item['high_incomplete'] = ON;
		    }	

		    if ($item['percentage_completed'] < $item['low_percentage'])
		    {
		    	$item['all_incomplete'] = ON;
        	}
        
		    $item['low_percentage'] = number_format($item['low_percentage'], 2);
		    
		    $gui->statistics->milestones[$item['target_date']] = $item;
	  	}
	  	// new dBug($gui->statistics->milestones);
	  	
	}
} 

// ----------------------------------------------------------------------------
$smarty = new TLSmarty;
$smarty->assign('gui', $gui);
$smarty->assign('do_report', $do_report);
$smarty->assign('tplan_name', $tplan_info['name']);
$smarty->assign('buildColDefinition', $colDefinition);
$smarty->assign('buildResults',$results);
displayReport($templateCfg->template_dir . $templateCfg->default_template, $smarty, $args->format);



/*
  function: init_args 
  args: none
  returns: array 
*/
function init_args()
{
	$iParams = array(
		"tplan_id" => array(tlInputParameter::INT_N),
		"format" => array(tlInputParameter::INT_N),
	);

	$args = new stdClass();
	$pParams = G_PARAMS($iParams,$args);
	
    $args->tproject_id = $_SESSION['testprojectID'];
    
    if (is_null($args->format))
	{
		tlog("Parameter 'format' is not defined", 'ERROR');
		exit();
	}

    return $args;
}

/**
 * calculate percentage and format
 * 
 * @param int $total Total count
 * @param int $parameter a parameter count
 * @return string formatted percentage
 */
function get_percentage($total, $parameter)
{
    if ($total > 0) 
   		$percentCompleted = ($parameter / $total) * 100;
	else 
   		$percentCompleted = 0;

	return number_format($percentCompleted,1);
	
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>
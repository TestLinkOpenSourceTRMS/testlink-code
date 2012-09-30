<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource resultsGeneral.php
 * @author	Martin Havlat <havlat at users.sourceforge.net>
 * 
 * Show Test Results over all Builds.
 *
 * @internal revisions
 * ----------------------------------------------------------------------------------- */
require('../../config.inc.php');
require_once('common.php');
require_once('results.class.php');
require_once('displayMgr.php');
testlinkInitPage($db);

$timerOn = microtime(true);

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);
$templateCfg = templateConfiguration();

$args = init_args();
checkRights($db,$_SESSION['currentUser'],$args);


$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);

$arrDataSuite = array();

$gui = new stdClass();
$gui->tplan_id = $args->tplan_id;
$gui->tproject_id = $args->tproject_id;

$gui->title = lang_get('title_gen_test_rep');
$gui->do_report = array();
$gui->showPlatforms=true;
$gui->columnsDefinition = new stdClass();
$gui->columnsDefinition->keywords = null;
$gui->columnsDefinition->testers = null;
$gui->columnsDefinition->platform = null;
$gui->statistics = new stdClass();
$gui->statistics->keywords = null;
$gui->statistics->testers = null;
$gui->statistics->milestones = null;
$gui->tplan_name = $tplan_info['name'];
$gui->tproject_name = $tproject_info['name'];
$gui->opt_requirements = $tproject_info['opt']->requirementsEnabled;
$gui->requirementsEnabled = $tproject_info['opt']->requirementsEnabled;
$gui->automationEnabled = $tproject_info['opt']->automationEnabled;
$gui->testPriorityEnabled = $tproject_info['opt']->testPriorityEnabled;
$gui->elapsed_time = 0; 

$mailCfg = buildMailCfg($gui);

$getOpt = array('outputFormat' => 'map');
$gui->platformSet = $tplan_mgr->getPlatforms($args->tplan_id,$getOpt);
if( is_null($gui->platformSet) )
{
	$gui->platformSet = array('');
	$gui->showPlatforms=false;
}

$metricsMgr = new tlTestPlanMetrics($db);

$re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,ALL_TEST_SUITES,ALL_BUILDS,ALL_PLATFORMS);
// ----------------------------------------------------------------------------
$topLevelSuites = $re->getTopLevelSuites();

if(is_null($topLevelSuites))
{
	// no test cases -> no report
	$gui->do_report['status_ok'] = 0;
	$gui->do_report['msg'] = lang_get('report_tspec_has_no_tsuites');
	tLog('Overall Metrics page: no test cases defined');
}
else // do report
{
	$gui->do_report['status_ok'] = 1;
	$gui->do_report['msg'] = '';

	//$items2loop = array('keywords','assigned_testers');
	$items2loop = array('keywords');
	
	$kwr = $tplan_mgr->getStatusTotalsByKeyword($args->tplan_id);
    $gui->statistics->keywords = $tplan_mgr->tallyResultsForReport($kwr);


	if( $gui->showPlatforms )
	{
		$items2loop[] = 'platform';
		$platr = $tplan_mgr->getStatusTotalsByPlatform($args->tplan_id);
		$gui->statistics->platform = $tplan_mgr->tallyResultsForReport($platr);
	}
	
	if($tproject_info['opt']->testPriorityEnabled)
	{
		$items2loop[] = 'priorities';
		$prios = $tplan_mgr->getStatusTotalsByPriority($args->tplan_id);
		$gui->statistics->priorities = $tplan_mgr->tallyResultsForReport($prios);
	}

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

      		// BUGID 4377 - do not unset total now, because we need in foreach loop
        	// unset($results['total']);
        	foreach($results as $key => $value)
        	{
      	    	$element['details'][$key]['qty'] = $results[$key];
      	    	// add percentage for each result
      	    	$element['details'][$key]['percentage'] = get_percentage($results['total'],$results[$key]);
      		}
      		unset($element['details']['total']);
      		$element['details']['not_run']['qty'] = $results['not_run'];
      	   
      		$arrDataSuite[$arrDataSuiteIndex] = $element;
      		$arrDataSuiteIndex++;
      	} 

    	$gui->statistics->testsuites = $arrDataSuite;

      	// Get labels
    	$dummy = current($gui->statistics->testsuites);
      	foreach($dummy['details'] as $status_verbose => $value)
    	{
          	$dummy['details'][$status_verbose]['percentage'] = 
          			lang_get('in_percent');
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
    

	$colDefinition = null;
	$results = null;
	if($gui->do_report['status_ok'])
	{
  		$results = $re->getAggregateBuildResults();
  		if ($results != null) 
  		{
			// BUGID 0003123: General Test Plan Metrics - order of columns with test case exec results
			$code_verbose = $tplan_mgr->getStatusForReports();
      		$resultsCfg = config_get('results');
      		$labels = $resultsCfg['status_label'];
      		foreach($code_verbose as $status_verbose)
      		{
            	$l18n_label = isset($labels[$status_verbose]) ? lang_get($labels[$status_verbose]) : 
                              lang_get($status_verbose); 
            
            	$colDefinition[$status_verbose]['qty'] = $l18n_label;
            	$colDefinition[$status_verbose]['percentage'] = '[%]';
      		}
  		}    
	}  


	
  	/* MILESTONE & PRIORITY REPORT */
	/* what is this ?
    $planMetrics = $tplan_mgr->getStatusTotals($args->tplan_id);

	$filters=null;
	$options=array('output' => 'map', 'only_executed' => true, 'execution_details' => 'add_build');
    $execResults = $tplan_mgr->get_linked_tcversions($args->tplan_id,$filters,$options);

    $options=array('output' => 'mapOfArray', 'only_executed' => true, 'execution_details' => 'add_build');
    $execResults = $tplan_mgr->get_linked_tcversions($args->tplan_id,$filters,$options);
    
    $options=array('output' => 'mapOfMap', 'only_executed' => true, 'execution_details' => 'add_build');
    $execResults = $tplan_mgr->get_linked_tcversions($args->tplan_id,$filters,$options);
    
    $options=array('output' => 'array', 'only_executed' => true, 'execution_details' => 'add_build');
    $execResults = $tplan_mgr->get_linked_tcversions($args->tplan_id,$filters,$options);
    */
   

	$milestonesList = $tplan_mgr->get_milestones($args->tplan_id);
	if (!empty($milestonesList))
	{
		$gui->statistics->milestones = $metricsMgr->getMilestonesMetrics($args->tplan_id,$milestonesList);
    }
} 

// ----------------------------------------------------------------------------
$gui->displayBuildMetrics = !is_null($results);
$gui->buildMetricsFeedback = lang_get('buildMetricsFeedback');

$timerOff = microtime(true);
$gui->elapsed_time = round($timerOff - $timerOn,2);

$smarty = new TLSmarty;
$smarty->assign('gui', $gui);
$smarty->assign('buildColDefinition', $colDefinition);
$smarty->assign('buildResults',$results);
displayReport($templateCfg->template_dir . $templateCfg->default_template, $smarty, $args->format,$mailCfg);



/*
  function: init_args 
  args: none
  returns: array 
*/
function init_args()
{
	$iParams = array("tplan_id" => array(tlInputParameter::INT_N),
					 "tproject_id" => array(tlInputParameter::INT_N),
					 "format" => array(tlInputParameter::INT_N));

	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);
	
    
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
	$percentCompleted = ($total > 0) ? (($parameter / $total) * 100) : 0;
	return number_format($percentCompleted,2);
}

/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('testplan_metrics'),'and');
}

/**
 * 
 *
 */
function buildMailCfg(&$guiObj)
{
	$labels = array('testplan' => lang_get('testplan'), 'testproject' => lang_get('testproject'));
	$cfg = new stdClass();
	$cfg->cc = ''; 
	$cfg->subject = $guiObj->title . ' : ' . $labels['testproject'] . ' : ' . $guiObj->tproject_name . 
	                ' : ' . $labels['testplan'] . ' : ' . $guiObj->tplan_name;
	                 
	return $cfg;
}
?>

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
 * @since 1.9.4
 *
 * 20120429 - franciscom - TICKET 4989: Reports - Overall Build Status - refactoring and final business logic
 *
 * @since 1.9.3
 *  20110405 - Julian - BUGID 4377 - Add percentage for "Results by top level Test Suites"
 *  20110326 - franciscom - BUGID 4355: General Test Plan Metrics - Build without executed 
 *										test cases are not displayed.
 *  20101018 - Julian - BUGID 2236 - Milestones Report broken - removed useless code
 *  20100811 - asimon - removed "results by assigned testers" table,
 *                      was replaced by new report "results by tester per build"
 *  20100621 - eloff - BUGID 3542 - fixed typo
 *  20100206 - eloff - BUGID 3060 - Show verbose priority statistics like other tables.
 *  20100201 - franciscom - BUGID 0003123: General Test Plan Metrics - order of columns
 *                                         with test case exec results
 * ----------------------------------------------------------------------------------- */
require('../../config.inc.php');
require_once('common.php');
require_once('results.class.php');
require_once('displayMgr.php');
testlinkInitPage($db,true,false,"checkRights");

$timerOn = microtime(true);

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);
$templateCfg = templateConfiguration();

$args = init_args();
$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);

$arrDataSuite = array();

$gui = new stdClass();
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
$gui->statistics->overalBuildStatus = null;

$gui->tplan_name = $tplan_info['name'];
$gui->tproject_name = $tproject_info['name'];
$gui->elapsed_time = 0; 
$gui->displayBuildMetrics = false;

$mailCfg = buildMailCfg($gui);

$getOpt = array('outputFormat' => 'map');
$gui->platformSet = $tplan_mgr->getPlatforms($args->tplan_id,$getOpt);

new dBug($gui->platformSet);
if( is_null($gui->platformSet) )
{
	$gui->platformSet = array('');
	$gui->showPlatforms = false;
}

$metricsMgr = new tlTestPlanMetrics($db);
                         
$kyw = $metricsMgr->getExecCountersByKeywordExecStatus($args->tplan_id);
new dBug($kyw);

                         
                         

// $re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,ALL_TEST_SUITES,ALL_BUILDS,ALL_PLATFORMS);
// default is ALL PLATFORMS
// $re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,ALL_TEST_SUITES,ALL_BUILDS);
// ----------------------------------------------------------------------------
// $topLevelSuites = $re->getTopLevelSuites();
$topLevelSuites = true;
if(is_null($topLevelSuites))
{
	// no test cases -> no report
	$gui->do_report['status_ok'] = 0;
	$gui->do_report['msg'] = lang_get('report_tspec_has_no_tsuites');
	tLog('Overall Metrics page: no test cases defined');
}
else
{
	 // do report
	$gui->do_report['status_ok'] = 1;
	$gui->do_report['msg'] = '';

	//$items2loop = array('keywords','assigned_testers');
	$items2loop = array('keywords');
	
	// $kwr = $tplan_mgr->getStatusTotalsByKeyword($args->tplan_id);
    // $gui->statistics->keywords = $tplan_mgr->tallyResultsForReport($kwr);
	// $gui->XXkeywords = $metricsMgr->getStatusTotalsByKeywordForRender($args->tplan_id);
	// new dBug($gui->statistics->keywords);
	// new dBug($gui->XXkeywords);
	$keywordsMetrics = $metricsMgr->getStatusTotalsByKeywordForRender($args->tplan_id);
	$gui->statistics->keywords = !is_null($keywordsMetrics) ? $keywordsMetrics->info : null; 
                              
	if( $gui->showPlatforms )
	{
		$items2loop[] = 'platform';
		// $platr = $tplan_mgr->getStatusTotalsByPlatform($args->tplan_id);
		// $gui->statistics->platform = $tplan_mgr->tallyResultsForReport($platr);
		// new dBug($gui->statistics->platform);
		$platformMetrics = $metricsMgr->getStatusTotalsByPlatformForRender($args->tplan_id);
		$gui->statistics->platform = !is_null($platformMetrics) ? $platformMetrics->info : null; 
	}

	if($_SESSION['testprojectOptions']->testPriorityEnabled)
	{
		$items2loop[] = 'priorities';
		$prios = $tplan_mgr->getStatusTotalsByPriority($args->tplan_id);
		$gui->statistics->priorities = $tplan_mgr->tallyResultsForReport($prios);
		
		// new dBug($gui->statistics->priorities);
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

  	// $mapOfAggregate = $re->getAggregateMap();
  	$arrDataSuite = null;
  	$arrDataSuiteIndex = 0;

	// // collect data for top test suites and users  
  	// if (is_array($topLevelSuites)) 
  	// {
    //   	foreach($topLevelSuites as $key => $suiteNameID)
    //   	{
    //   		$results = $mapOfAggregate[$suiteNameID['id']];
    //   			
    //   		$element['tsuite_name'] = $suiteNameID['name'];
    //   		$element['total_tc'] = $results['total'];
    //   		$element['percentage_completed'] = get_percentage($results['total'], 
    //   		$results['total'] - $results['not_run']);
    // 
    //   		// BUGID 4377 - do not unset total now, because we need in foreach loop
    //     	// unset($results['total']);
    //     	foreach($results as $key => $value)
    //     	{
    //   	    	$element['details'][$key]['qty'] = $results[$key];
    //   	    	// add percentage for each result
    //   	    	$element['details'][$key]['percentage'] = get_percentage($results['total'],$results[$key]);
    //   		}
    //   		unset($element['details']['total']);
    //   		$element['details']['not_run']['qty'] = $results['not_run'];
    //   	   
    //   		$arrDataSuite[$arrDataSuiteIndex] = $element;
    //   		$arrDataSuiteIndex++;
    //   	} 
    // 
    // 	$gui->statistics->testsuites = $arrDataSuite;
    // 
    //   	// Get labels
    // 	$dummy = current($gui->statistics->testsuites);
    //   	foreach($dummy['details'] as $status_verbose => $value)
    // 	{
    //       	$dummy['details'][$status_verbose]['percentage'] = 
    //       			lang_get('in_percent');
    //       	$dummy['details'][$status_verbose]['qty'] = 
    //       			lang_get($tlCfg->results['status_label'][$status_verbose]);
    //   	}
    //   	$gui->columnsDefinition->testsuites = $dummy['details'];
  	// }
    // 
	// ----------------------------------------------------------------------------
  	/* BUILDS REPORT */
	$colDefinition = null;
	$results = null;
	if($gui->do_report['status_ok'])
	{
		$gui->statistics->overallBuildStatus = $metricsMgr->getOverallBuildStatusForRender($args->tplan_id);
		$gui->displayBuildMetrics = !is_null($gui->statistics->overallBuildStatus);
	}  


	
  	/* MILESTONE & PRIORITY REPORT */
	$milestonesList = $tplan_mgr->get_milestones($args->tplan_id);
	if (!empty($milestonesList))
	{
		$gui->statistics->milestones = $metricsMgr->getMilestonesMetrics($args->tplan_id,$milestonesList);
    }
} 

// ----------------------------------------------------------------------------
$gui->buildMetricsFeedback = lang_get('buildMetricsFeedback');

$timerOff = microtime(true);
$gui->elapsed_time = round($timerOff - $timerOn,2);
$smarty = new TLSmarty;
$smarty->assign('gui', $gui);
displayReport($templateCfg->template_dir . $templateCfg->default_template, $smarty, $args->format,$mailCfg);



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

	return number_format($percentCompleted,2);
	
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
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
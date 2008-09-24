<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource $RCSfile: resultsGeneral.php,v $
 * @version $Revision: 1.42 $
 * @modified $Date: 2008/09/24 20:17:55 $ by $Author: schlundus $
 * @author	Martin Havlat <havlat at users.sourceforge.net>
 * 
 * This page show Test Results over all Builds.
 *
 * Revisions:
 * 	20050807 - fm - refactoring:  changes in getTestSuiteReport() call
 * 	20050905 - fm - reduce global coupling
 *  20070101 - KL - upgraded to 1.7
 * 	20080626 - mht - added milestomes, priority report, refactorization
 * 
 * ----------------------------------------------------------------------------------- */

require('../../config.inc.php');
require_once('common.php');
//require_once('builds.inc.php'); martin: it seems obsolete
require_once('results.class.php');
require_once('testplan.class.php');
require_once('displayMgr.php');

testlinkInitPage($db);
$args = init_args();

$template_dir = 'results/';
$arrDataSuite = array();
$do_report = array();

$columnsDefinition = new stdClass();
$columnsDefinition->keywords = null;
$columnsDefinition->testers = null;
$statistics = new stdClass(); // array with metrics data
$statistics->keywords = null;
$statistics->testers = null;

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);
$re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,
                  ALL_TEST_SUITES,ALL_BUILDS);


// ----------------------------------------------------------------------------

$topLevelSuites = $re->getTopLevelSuites();

if( is_null($topLevelSuites) )
{
	// no test cases -> no report
	$do_report['status_ok'] = 0;
	$do_report['msg'] = lang_get('report_tspec_has_no_tsuites');
	tLog('Overall Metrics page: no test cases defined');
}

// ----------------------------------------------------------------------------
else // do report
{
	$do_report['status_ok']=1;
	$do_report['msg']='';


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

    	$statistics->testsuites = $arrDataSuite;

      	// Get labels
    	$dummy = current($statistics->testsuites);
      	foreach($dummy['details'] as $status_verbose => $value)
    	{
          	$dummy['details'][$status_verbose]['qty'] = 
          			lang_get($tlCfg->results['status_label'][$status_verbose]);
      	}
      	$columnsDefinition->testsuites = $dummy['details'];
  	} // end if 

	// ----------------------------------------------------------------------------
  	/* BUILDS REPORT */

	$colDefiniton=null;
	$results=null;
	if( $do_report['status_ok'] )
	{
  		$results = $re->getAggregateBuildResults();
  		if ($results != null) 
  		{
      		// Get labels
      		$resultsCfg=config_get('results');
      		$labels=$resultsCfg['status_label'];
      
      		// I will add not_run if not exists
		  	$keys2display=array('not_run' => 'not_run');
		  	foreach($resultsCfg['status_label_for_exec_ui'] as $key => $value)
		  	{
		      	if( $key != 'not_run')
		      	{
		          $keys2display[$key]=$key;  
		      	}  
		  	}
      
      		$colDefinition=array();
      		foreach($keys2display as $status_verbose => $value)
      		{
            	$l18n_label = isset($labels[$status_verbose]) ? lang_get($labels[$status_verbose]) : 
                          lang_get($status_verbose); 
            
            	$colDefinition[$status_verbose]['qty']=$l18n_label;
            	$colDefinition[$status_verbose]['percentage']='[%]';
      		}
  		}    
	}  


	// ----------------------------------------------------------------------------
  	/* MILESTONE & PRIORITY REPORT */
	$planMetrics = $re->getTotalsForPlan();

	// collect prioritized results for whole Test Plan
	if ($_SESSION['testprojectOptPriority'])
	{
		tLog('collect prioritized results for whole Test Plan','ERROR');
		$statistics->priority_overall = $re->getPrioritizedResults();
		$statistics->priority_overall['high_percentage'] = get_percentage($planMetrics['total'],
				$statistics->priority_overall[HIGH]); 
		$statistics->priority_overall['medium_percentage'] = get_percentage($planMetrics['total'],
				$statistics->priority_overall[MEDIUM]); 
		$statistics->priority_overall['low_percentage'] = get_percentage($planMetrics['total'],
				$statistics->priority_overall[LOW]); 
	}
	// collect milestones
	$milestonesList = $tplan_mgr->get_milestones($args->tplan_id);

	// get test results for milestones
	if (!empty($milestonesList))
	{
	  $arrPrioritizedTCs = $re->getPrioritizedTestCases();
	  foreach($milestonesList as $item)
	  {
		$item['tc_total'] = $planMetrics['total'];
		$item['results'] = $re->getPrioritizedResults($item['target_date']);

		$item['low_percentage'] = get_percentage($arrPrioritizedTCs[LOW], $item['results'][LOW]); 
		$item['medium_percentage'] = get_percentage($arrPrioritizedTCs[MEDIUM], $item['results'][MEDIUM]); 
		$item['high_percentage'] = get_percentage($arrPrioritizedTCs[HIGH], $item['results'][HIGH]); 
		$item['tc_completed'] = $item['results'][HIGH] + $item['results'][MEDIUM] + $item['results'][LOW];
		$item['percentage_completed'] = get_percentage($item['tc_total'], $item['tc_completed']);

		if ($item['low_percentage'] < $item['A'])
			$item['low_incomplete'] = ON;
		else
			$item['low_incomplete'] = OFF;
		if ($item['medium_percentage'] < $item['B'])
			$item['medium_incomplete'] = ON;
		else
			$item['medium_incomplete'] = OFF;
		if ($item['high_percentage'] < $item['C'])
			$item['high_incomplete'] = ON;
		else
			$item['high_incomplete'] = OFF;
		if ($item['percentage_completed'] < $item['B'])
			$item['all_incomplete'] = ON;
		else
			$item['all_incomplete'] = OFF;

		$item['B'] = number_format($item['B'], 2);
		
		// save array
		$statistics->milestones[$item['target_date']] = $item;
	  } // end foreach
	}

	//debug	
//	$arrPriority = $statistics->milestones;

  
	// ----------------------------------------------------------------------------
	/* Keywords report */
	$items2loop=array('keywords' => 'getAggregateKeywordResults',
                    'testers' => 'getAggregateOwnerResults');
                    
	foreach($items2loop as $item => $aggregateMethod)
	{
      	$statistics->$item = $re->$aggregateMethod();
      	if( !is_null($statistics->$item) )
      	{
        	// Get labels
          	$dummy = current($statistics->$item);
          	foreach($dummy['details'] as $status_verbose => $value)
          	{
              	$dummy['details'][$status_verbose]['qty'] = 
              			lang_get($tlCfg->results['status_label'][$status_verbose]);
            	$dummy['details'][$status_verbose]['percentage'] = "[%]";
              
            	// This statement generates an error:
            	// $columnsDefinition->$item[$status_verbose]['percentage']="[%]";   
            	// Fatal error: Cannot use string offset as an array in
            	// That I do not understand.
          	}
          	$columnsDefinition->$item=$dummy['details'];
    	} 
  	} // foreach
  	
} //!is_null()


// ----------------------------------------------------------------------------
$smarty = new TLSmarty;
//$smarty->assign('aaa', $arrPriority);
$smarty->assign('do_report', $do_report);
$smarty->assign('tplan_name', $tplan_info['name']);
$smarty->assign('columnsDefinition', $columnsDefinition);
$smarty->assign('buildColDefinition', $colDefinition);
$smarty->assign('buildResults',$results);
$smarty->assign('statistics', $statistics);

displayReport($template_dir . 'resultsGeneral', $smarty, $args->format);



/*
  function: init_args 
  args: none
  returns: array 
*/
function init_args()
{
    $_REQUEST = strings_stripSlashes($_REQUEST);
    $args=new stdClass();
    $args->tplan_id=$_REQUEST['tplan_id'];
    $args->tproject_id=$_SESSION['testprojectID'];
    $args->format = isset($_REQUEST['format']) ? intval($_REQUEST['format']) : null;

	if (is_null($args->format))
	{
		tlog('$_GET["format"] is not defined', 'ERROR');
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

?>
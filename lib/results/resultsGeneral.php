<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource $RCSfile: resultsGeneral.php,v $
 * @version $Revision: 1.39 $
 * @modified $Date: 2008/06/26 21:47:49 $ by $Author: havlat $
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
/** Top Level Suites */
$topLevelSuites = $re->getTopLevelSuites();

if( is_null($topLevelSuites) )
{
	// no test cases -> no report
	$do_report['status_ok'] = 0;
	$do_report['msg'] = lang_get('report_tspec_has_no_tsuites');
	tLog('Overall Metrics page: no test cases defined');
}

else //if( $do_report['status_ok'] )
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
      		$element['percentage_completed'] = get_completed_percentage($results['total'], 
      				$results['not_run']);

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
  	/* MILESTONE & PRIORITY REPORT */
	$milestonesList = $tplan_mgr->get_milestones($args->tplan_id);
	$planMetrics = $re->getTotalsForPlan();
	foreach($milestonesList as $item)
	{
		$item['tc_total'] = $planMetrics['total'];
		$item['tc_not_run'] = $planMetrics['not_run'];
		$item['tc_completed'] = $planMetrics['total'] - $planMetrics['not_run'];
		$item['percentage_completed'] = get_completed_percentage($planMetrics['total'], 
				$planMetrics['not_run']);
		if ($item['percentage_completed'] < $item['B'])
			$item['B_incomplete'] = ON;
		else
			$item['B_incomplete'] = OFF;
		$item['B'] = number_format($item['B'], 2);
		
		// save array
		$statistics->milestones[$item['target_date']] = $item;
	}

/*
 *   			<td>{$res.tc_completed}</td>
  			<td>{$res.tc_not_run}</td>
			<td>{$res.percentage_completed}</td>

 * {$aaa}	Array (5)
total => 2
not_run => 2
passed => 0
failed => 0
blocked => 0
 */
  
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
//$smarty->assign('aaa', $ccc);
$smarty->assign('do_report', $do_report);
$smarty->assign('tplan_name', $tplan_info['name']);
//$smarty->assign('milestonesList', $milestonesList);
$smarty->assign('columnsDefinition', $columnsDefinition);
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

function get_completed_percentage($total, $not_run)
{
    if ($total > 0) 
   		$percentCompleted = (($total - $not_run) / $total) * 100;
	else 
   		$percentCompleted = 0;

	$percentCompleted = number_format($percentCompleted,2);
	return $percentCompleted;
	
}

?>
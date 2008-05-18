<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: resultsGeneral.php,v $
 * @version $Revision: 1.38 $
 * @modified $Date: 2008/05/18 16:56:09 $ by $Author: franciscom $
 * @author	Martin Havlat <havlat@users.sourceforge.net>
 * 
 * This page show Test Results over all Builds.
 *
 * @author 20050905 - fm - reduce global coupling
 *
 * @author 20050807 - fm
 * refactoring:  changes in getTestSuiteReport() call
 *
 * @author 20070101 - KL
 * upgraded to 1.7
 * 
 */

require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');
require_once('results.class.php');
require_once('testplan.class.php');
require_once('displayMgr.php');
testlinkInitPage($db);

$template_dir='results/';

$args=init_args();
$arrDataPriority=array();
$arrDataSuite=array();

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);

$tplan_name = $tplan_info['name'];
$tproject_name = $tproject_info['name'];

$re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,
                  ALL_TEST_SUITES,ALL_BUILDS);


$resultsCfg=config_get('results');
$tc_status_labels=$resultsCfg['status_label'];

$columnsDefinition = new stdClass();
$statistics = new stdClass();

$columnsDefinition->keywords=null;
$columnsDefinition->testers=null;
$statistics->keywords=null;
$statistics->testers=null;


/** 
* Top Level Suites 
*/
$topLevelSuites = $re->getTopLevelSuites();
$do_report=array();
$do_report['status_ok']=1;
$do_report['msg']='';

if( is_null($topLevelSuites) )
{
  $do_report['status_ok']=0;
  $do_report['msg']=lang_get('report_tspec_has_no_tsuites');
}

if( $do_report['status_ok'] )
{
  $mapOfAggregate = $re->getAggregateMap();
  $arrDataSuite = null;
  $arrDataSuiteIndex = 0;
  
  if (is_array($topLevelSuites)) 
  {
      foreach($topLevelSuites as $key => $suiteNameID)
      {
      	$results = $mapOfAggregate[$suiteNameID['id']];	

      	if ($results['total'] > 0) {
      	   $percentCompleted = (($results['total'] - $results['not_run']) / $results['total']) * 100;
      	}
      	else {
      	   $percentCompleted = 0;
      	}
      	$percentCompleted = number_format($percentCompleted,2);
      	
      	$element['tsuite_name']=$suiteNameID['name'];
      	$element['total_tc']=$results['total'];
      	$element['percentage_completed']=$percentCompleted;

        unset($results['total']);
        foreach($results as $key => $value)
        {
      	    $element['details'][$key]['qty']=$results[$key];
      	}
      	$element['details']['not_run']['qty']=$results['not_run'];
      	   
      	   
      	$arrDataSuite[$arrDataSuiteIndex] = $element;
      	$arrDataSuiteIndex++;
      } 
      $statistics->testsuites=$arrDataSuite;

      // Get labels
      $dummy=current($statistics->testsuites);
      foreach($dummy['details'] as $status_verbose => $value)
      {
          $dummy['details'][$status_verbose]['qty']=lang_get($tc_status_labels[$status_verbose]);
      }
      $columnsDefinition->testsuites=$dummy['details'];
  } // end if 
  
  /**
  * PRIORITY REPORT
  */
  $arrDataPriority = null;


  
  /**
  * Keywords report
  */
  $items2loop=array('keywords' => 'getAggregateKeywordResults',
                    'testers' => 'getAggregateOwnerResults');
                    
  foreach($items2loop as $item => $aggregateMethod)
  {
      $statistics->$item = $re->$aggregateMethod();
      if( !is_null($statistics->$item) )
      {

          // Get labels
          $dummy=current($statistics->$item);
          foreach($dummy['details'] as $status_verbose => $value)
          {
              $dummy['details'][$status_verbose]['qty']=lang_get($tc_status_labels[$status_verbose]);
              $dummy['details'][$status_verbose]['percentage']="[%]";
              
              // This statement generates an error:
              // $columnsDefinition->$item[$status_verbose]['percentage']="[%]";   
              // Fatal error: Cannot use string offset as an array in
              // That I do not understand.
          }
          $columnsDefinition->$item=$dummy['details'];
      } 
  }
} //!is_null()

$smarty = new TLSmarty;
$smarty->assign('do_report', $do_report);
$smarty->assign('tproject_name', $tproject_name);
$smarty->assign('tplan_name', $tplan_name);
$smarty->assign('arrDataPriority', $arrDataPriority);
$smarty->assign('columnsDefinition', $columnsDefinition);
$smarty->assign('statistics', $statistics);


if (is_null($args->format))
{
	tlog('$_GET["format"] is not defined', 'ERROR');
	exit();
}

displayReport($template_dir . 'resultsGeneral', $smarty, $args->format);



/*
  function: init_args 

  args:
  
  returns: 

*/
function init_args()
{
    $_REQUEST = strings_stripSlashes($_REQUEST);
    $args=new stdClass();
    $args->tplan_id=$_REQUEST['tplan_id'];
    $args->tproject_id=$_SESSION['testprojectID'];
    $args->format = isset($_REQUEST['format']) ? intval($_REQUEST['format']) : null;
    return $args;
}

?>
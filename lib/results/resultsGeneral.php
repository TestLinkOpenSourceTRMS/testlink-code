<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: resultsGeneral.php,v $
 * @version $Revision: 1.33 $
 * @modified $Date: 2007/09/17 06:29:07 $ by $Author: franciscom $
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

$arrDataPriority=array();
$arrDataSuite=array();
$arrDataOwner2=array();
$arrDataKeys2=array();

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$tplan_id=$_REQUEST['tplan_id'];
$tproject_id=$_SESSION['testprojectID'];

$tplan_info = $tplan_mgr->get_by_id($tplan_id);
$tproject_info = $tproject_mgr->get_by_id($tproject_id);

$tplan_name = $tplan_info['name'];
$tproject_name = $tproject_info['name'];

$re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,
                  ALL_TEST_SUITES,ALL_BUILDS);


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
  if (is_array($topLevelSuites)) {
  while ($i = key($topLevelSuites)) {
  	$pairArray = $topLevelSuites[$i];
  	$currentSuiteId = $pairArray['id'];
  	$currentSuiteName = $pairArray['name'];
  	$resultArray = $mapOfAggregate[$currentSuiteId];	
  	$total = $resultArray['total'];
  	$notRun = $resultArray['notRun'];
  	if ($total > 0) {
  	   $percentCompleted = (($total - $notRun) / $total) * 100;
  	}
  	else {
  	   $percentCompleted = 0;
  	}
  	$percentCompleted = number_format($percentCompleted,2);
  	$arrDataSuite[$arrDataSuiteIndex] = array($currentSuiteName,$total,$resultArray['pass'],
  	                                          $resultArray['fail'],$resultArray['blocked'],
  	                                          $notRun,$percentCompleted);
  	$arrDataSuiteIndex++;
  	next($topLevelSuites);
  } 
  } // end if 
  /**
  * PRIORITY REPORT
  */
  $arrDataPriority = null;
  
  /**
  * KEYWORDS REPORT
  */
  $arrDataKeys = $re->getAggregateKeywordResults();
  $i = 0;
  $arrDataKeys2 = null;
  
  if ($arrDataKeys != null) {
     while ($keywordId = key($arrDataKeys)) {
        $arr = $arrDataKeys[$keywordId];
        $arrDataKeys2[$i] = $arr;
        $i++;
        next($arrDataKeys);
     }
  }
  
  /** 
  * OWNERS REPORT 
  */
  $arrDataOwner = $re->getAggregateOwnerResults();
  
  $i = 0;
  $arrDataOwner2 = null;
  if ($arrDataOwner != null) {
     while ($ownerId = key($arrDataOwner)) {
       $arr = $arrDataOwner[$ownerId];
       $arrDataOwner2[$i] = $arr;
       $i++;
       next($arrDataOwner);
     }
  }
} //!is_null()

$smarty = new TLSmarty;
$smarty->assign('do_report', $do_report);
$smarty->assign('tproject_name', $tproject_name);
$smarty->assign('tplan_name', $tplan_name);
$smarty->assign('arrDataPriority', $arrDataPriority);
$smarty->assign('arrDataSuite', $arrDataSuite);
$smarty->assign('arrDataOwner', $arrDataOwner2);
$smarty->assign('arrDataKeys', $arrDataKeys2);

$report_type = isset($_GET['report_type']) ? intval($_GET['report_type']) : null;
if (!isset($_GET['report_type']))
{
	tlog('$_GET["report_type"] is not defined');
	exit();
}

displayReport('resultsGeneral', $smarty, $report_type);

?>
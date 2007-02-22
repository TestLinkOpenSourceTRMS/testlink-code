<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: resultsGeneral.php,v $
 * @version $Revision: 1.28 $
 * @modified $Date: 2007/02/22 16:29:22 $ by $Author: kevinlevy $
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

// require_once('TestPlanResultsObj.php');

require_once('timer.php');
require_once('../functions/results.class.php');
require_once('../functions/testplan.class.php');
require_once('displayMgr.php');

testlinkInitPage($db);
$tpID = $_SESSION['testPlanId']; 

$tp = new testplan($db);
$builds_to_query = 'a';
$suitesSelected = 'all';
$re = new results($db, $tp, $suitesSelected, $builds_to_query);

// TO-DO figure out how to use TestPlanResultsObj
//$excelWriter = new TestPlanResultsObj();

/** 
* COMPONENTS REPORT 
*/
$topLevelSuites = $re->getTopLevelSuites();
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
	$arrDataSuite[$arrDataSuiteIndex] = array($currentSuiteName,$total,$resultArray['pass'],$resultArray['fail'],$resultArray['blocked'],$notRun,$percentCompleted);
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

/**
* SMARTY ASSIGNMENTS
*/ 

$smarty = new TLSmarty;
$smarty->assign('tpName', $_SESSION['testPlanName']);
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
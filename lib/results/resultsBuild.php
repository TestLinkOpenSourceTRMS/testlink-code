<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsBuild.php,v 1.21 2007/01/02 03:16:07 kevinlevy Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* 
* This page show Metrics of one Build.
*
* @author Kevin Levy - KL - update to 1.7
* 
*
*/
require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');
require_once('../functions/results.class.php');
require_once('../functions/testplan.class.php');

//print "Warning Message - KL - 20061126 - all tables functional except for priority report <BR>";

$builds_to_query = isset($_GET['build']) ? intval($_GET['build']) : null;
if (!isset($_GET['build']))
{
	tlog('$_GET["build"] is not defined');
	exit();
}

testlinkInitPage($db);

$tpID = $_SESSION['testPlanId']; 

$buildInfo = getBuild_by_id($db,$builds_to_query);
$buildName = "";
if ($buildInfo)
	$buildName = $buildInfo['name'];

$tp = new testplan($db);
$suitesSelected = 'all';
$re = new results($db, $tp, $suitesSelected, $builds_to_query);

/** 

* TOP LEVEL SUITES REPORT 

*/
$topLevelSuites = $re->getTopLevelSuites();
$mapOfAggregate = $re->getAggregateMap();
$arrDataSuite = null;
$arrDataSuiteIndex = 0;
while ($i = key($topLevelSuites)) {
	$pairArray = $topLevelSuites[$i];
	$currentSuiteId = $pairArray['id'];
	$currentSuiteName = $pairArray['name'];
	$resultArray = $mapOfAggregate[$currentSuiteId];	
	$total = $resultArray['total'];
	$notRun = $resultArray['notRun'];
	$percentCompleted = (($total - $notRun) / $total) * 100;
	$percentCompleted = number_format($percentCompleted,2);
	$arrDataSuite[$arrDataSuiteIndex] = array($currentSuiteName,$total,$resultArray['pass'],$resultArray['fail'],$resultArray['blocked'],$notRun,$percentCompleted);
	$arrDataSuiteIndex++;
	next($topLevelSuites);
} 

/** 
* ALL SUITES REPORT 
*/
$allSuites = $re->getAllSuites();
$arrDataAllSuites = null;
$index = 0;
// TO-DO - lookup risk, importance, and priority for each suites
$risk = '?';
$importance = '?';
$priority = '?';
while ($i = key($allSuites)) {
	$pairArray = $allSuites[$i];
	$currentSuiteId = $pairArray['id'];
	$currentSuiteName = $pairArray['name'];
	$resultArray = $mapOfAggregate[$currentSuiteId];	
	$total = $resultArray['total'];
	$notRun = $resultArray['notRun'];
	$percentCompleted = (($total - $notRun) / $total) * 100;
	$percentCompleted = number_format($percentCompleted,2);
	$arrDataAllSuites[$index] = array($currentSuiteName, $risk, $importance, $priority,$total,$resultArray['pass'],$resultArray['fail'],$resultArray['blocked'],$notRun,$percentCompleted);
	$index++;
	next($allSuites);
} 

/**
* PRIORITY REPORT
*/
//$arrDataPriority = getPriorityReport($db,$tpID);
$arrDataPriority = null;

/**
* KEYWORDS REPORT
*/
$arrDataKeys = $re->getAggregateKeywordResults();
$i = 0;
$arrDataKeys2 = null;
while ($keywordId = key($arrDataKeys)) {
   $arr = $arrDataKeys[$keywordId];
   $arrDataKeys2[$i] = $arr;
   $i++;
   next($arrDataKeys);
}

/** 
* OWNERS REPORT 
*/
$arrDataOwner = $re->getAggregateOwnerResults();

$i = 0;
$arrDataOwner2 = null;
while ($ownerId = key($arrDataOwner)) {
   $arr = $arrDataOwner[$ownerId];
   $arrDataOwner2[$i] = $arr;
   $i++;
   next($arrDataOwner);
}

/**
* SMARTY ASSIGNMENTS
*/
$smarty = new TLSmarty;
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->assign('buildName', $buildName);
$smarty->assign('arrDataPriority', $arrDataPriority);
$smarty->assign('arrDataAllSuites', $arrDataAllSuites);
$smarty->assign('arrDataSuite', $arrDataSuite);
$smarty->assign('arrDataKeys', $arrDataKeys2);
$smarty->display('resultsBuild.tpl');

?>
<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsBuild.php,v 1.26 2007/01/10 07:31:42 kevinlevy Exp $ 
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
// has the sendMail() method
require_once('info.inc.php');

//print "Warning Message - KL - 20061126 - all tables functional except for priority report <BR>";

$builds_to_query = isset($_GET['build']) ? intval($_GET['build']) : null;
if (!isset($_GET['build']))
{
	tlog('$_GET["build"] is not defined');
	exit();
}

$report_type = isset($_GET['report_type']) ? intval($_GET['report_type']) : null;
if (!isset($_GET['report_type']))
{
	tlog('$_GET["report_type"] is not defined');
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

if (is_array($topLevelSuites)) {
while ($i = key($topLevelSuites)) {
	$pairArray = $topLevelSuites[$i];
	$currentSuiteId = $pairArray['id'];
	$currentSuiteName = $pairArray['name'];
	$resultArray = $mapOfAggregate[$currentSuiteId];	
	$total = $resultArray['total'];
	$notRun = $resultArray['notRun'];
	$percentCompleted = 0;
	if ($total != 0) {
	   $percentCompleted = (($total - $notRun) / $total) * 100;
	}
	$percentCompleted = number_format($percentCompleted,2);
	$arrDataSuite[$arrDataSuiteIndex] = array($currentSuiteName,$total,$resultArray['pass'],$resultArray['fail'],$resultArray['blocked'],$notRun,$percentCompleted);
	$arrDataSuiteIndex++;
	next($topLevelSuites);
} 
} // end if 

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
if (is_array($allSuites)) {
while ($i = key($allSuites)) {
	$pairArray = $allSuites[$i];
	$currentSuiteId = $pairArray['id'];
	$currentSuiteName = $pairArray['name'];
	$resultArray = $mapOfAggregate[$currentSuiteId];	
	$total = $resultArray['total'];
	$notRun = $resultArray['notRun'];
	$percentCompleted = 0;
	if ($total != 0) {
	   $percentCompleted = (($total - $notRun) / $total) * 100;
	}
	$percentCompleted = number_format($percentCompleted,2);
	$arrDataAllSuites[$index] = array($currentSuiteName, $risk, $importance, $priority, $total, $resultArray['pass'], $resultArray['fail'], $resultArray['blocked'], $notRun, $percentCompleted);
	$index++;
	next($allSuites);
} 
} // end if
/**
* PRIORITY REPORT
*/
//$arrDataPriority = getPriorityReport($db,$tpID);
$arrDataPriority = null;

/**
* KEYWORDS REPORT
*/
$arrDataKeys = $re->getAggregateKeywordResults();
$arrDataKeys2 = null;
$i = 0;
if ($arrDataKeys != 0) {
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
$smarty->assign('buildName', $buildName);
$smarty->assign('arrDataPriority', $arrDataPriority);
$smarty->assign('arrDataAllSuites', $arrDataAllSuites);
$smarty->assign('arrDataSuite', $arrDataSuite);
$smarty->assign('arrDataKeys', $arrDataKeys2);

if ($report_type == '0') {
	$smarty->display('resultsBuild.tpl');
}
else if ($report_type == '1'){
	print "MS Excel report for resultsBuild.php is not yet implemented - KL - 20070109 <BR>";
}
else if ($report_type == '2'){
	//print "HTML email report for resultsBuild.php is not yet implemented - KL - 20070109 <BR>";	
	$html_report = $smarty->fetch('resultsBuild.tpl');
	// $message = sendMail($_SESSION['email'],$_POST['to'], $_POST['subject'], $msgBody,$send_cc_to_myself);
	$htmlReportType = true;
	$send_cc_to_myself = false;
	$subjectOfMail = $_SESSION['testPlanName'] . ": Metrics Of Active Build: " .  $buildName;
	
	$emailFrom = $_SESSION['email'];
	$emailTo = $_SESSION['email'];
	print "emailTo = $emailTo <BR>";
	$message = sendMail($emailFrom, $emailTo, $subjectOfMail, $send_cc_to_myself, $htmlReportType);
	
	$smarty = new TLSmarty;
	$smarty->assign('message', $message);
	$smarty->display('emailSent.tpl');

}
else if ($report_type == '3'){
	print "text email report for resultsBuild.php is not yet implemented - KL - 20070109 <BR>";
}




?>
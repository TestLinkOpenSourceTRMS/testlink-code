<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsBuild.php,v 1.38 2009/03/25 20:53:18 schlundus Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* 
* Metrics of one Build.
* @TODO: schlundus, this file doesn't seems to be in use
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
require_once('displayMgr.php');
testlinkInitPage($db);
$templateCfg = templateConfiguration();

//print "Warning Message - KL - 20061126 - all tables functional except for priority report <BR>";
$format = isset($_GET['format']) ? intval($_GET['format']) : null;
if (!isset($_GET['format']))
{
	tlog('$_GET["format"] is not defined', 'ERROR');
	exit();
}
$builds_to_query = isset($_GET['build']) ? intval($_GET['build']) : null;

//@TODO: schlundus, should be replaced with a function of class testplan
$buildInfo = getBuild_by_id($db,$builds_to_query);
$buildName = "";
if ($buildInfo)
	$buildName = $buildInfo['name'];

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$tplan_id=$_REQUEST['tplan_id'];
$tproject_id=$_SESSION['testprojectID'];

$tplan_info = $tplan_mgr->get_by_id($tplan_id);
$tproject_info = $tproject_mgr->get_by_id($tproject_id);
$tplan_name = $tplan_info['name'];

$re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,
                  ALL_TEST_SUITES,$builds_to_query);



/** 
* TOP LEVEL SUITES REPORT 
*/
$topLevelSuites = $re->getTopLevelSuites();
$mapOfAggregate = $re->getAggregateMap();
$arrDataSuite = null;
$arrDataSuiteIndex = 0;

if (is_array($topLevelSuites))
{
	while ($i = key($topLevelSuites))
	{
		$pairArray = $topLevelSuites[$i];
		$currentSuiteId = $pairArray['id'];
		$currentSuiteName = $pairArray['name'];
		$resultArray = $mapOfAggregate[$currentSuiteId];	
		$total = $resultArray['total'];
		$notRun = $resultArray['notRun'];
		$percentCompleted = 0;
		if ($total != 0)
		   $percentCompleted = (($total - $notRun) / $total) * 100;
		
		$percentCompleted = number_format($percentCompleted,2);
		$arrDataSuite[$arrDataSuiteIndex] = array($currentSuiteName,$total,$resultArray['pass'],$resultArray['fail'],$resultArray['blocked'],$notRun,$percentCompleted);
		$arrDataSuiteIndex++;
		next($topLevelSuites);
	}
} 

/** 
* ALL SUITES REPORT 
*/
$allSuites = $re->getAllSuites();
$arrDataAllSuites = null;
$index = 0;
// TO-DO - lookup risk, importance, and priority for each suites
/**
 KL - 20070222 - NOT in the 1.7 release
$risk = '?';
$importance = '?';
$priority = '?';
*/

if (is_array($allSuites))
{
	while ($i = key($allSuites))
	{
		$pairArray = $allSuites[$i];
		$currentSuiteId = $pairArray['id'];
		$currentSuiteName = $pairArray['name'];
		$resultArray = $mapOfAggregate[$currentSuiteId];	
		$total = $resultArray['total'];
		$notRun = $resultArray['notRun'];
		$percentCompleted = 0;
		if ($total != 0)
		   $percentCompleted = (($total - $notRun) / $total) * 100;
		
		$percentCompleted = number_format($percentCompleted,2);
		// KL - 20070222 - these are not in 1.7 $risk, $importance, $priority,  
		$arrDataAllSuites[$index] = array($currentSuiteName, $total, $resultArray['pass'], 
		                                  $resultArray['fail'], $resultArray['blocked'], $notRun, $percentCompleted);
		$index++;
		next($allSuites);
	} 
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
$arrDataKeys2 = null;
$i = 0;
if ($arrDataKeys != 0)
{
	while ($keywordId = key($arrDataKeys))
	{
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
if ($arrDataOwner != null)
{
	while ($ownerId = key($arrDataOwner))
	{
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
$smarty->assign('tpName', $tplan_name);
$smarty->assign('buildName', $buildName);
$smarty->assign('arrDataPriority', $arrDataPriority);
$smarty->assign('arrDataAllSuites', $arrDataAllSuites);
$smarty->assign('arrDataSuite', $arrDataSuite);
$smarty->assign('arrDataKeys', $arrDataKeys2);
displayReport($templateCfg->template_dir . $templateCfg->default_template, $smarty, $report_type, $buildName);
?>

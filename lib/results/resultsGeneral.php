<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: resultsGeneral.php,v $
 * @version $Revision: 1.8 $
 * @modified $Date: 2006/10/29 08:32:02 $ by $Author: kevinlevy $
 * @author	Martin Havlat <havlat@users.sourceforge.net>
 * 
 * This page show Test Results over all Builds.
 *
 * @author 20050905 - fm - reduce global coupling
 *
 * @author 20050807 - fm
 * refactoring:  changes in getTestSuiteReport() call
 *
 * 
 */

print "KL - 20061025 - work in progress <BR>";
require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');
require_once('../functions/results.class.php');
require_once('../functions/testplan.class.php');

testlinkInitPage($db);
$tpID = $_SESSION['testPlanId']; 

$tp = new testplan($db);
$builds_to_query = 'a';
$suitesSelected = 'all';
$re = new results($db, $tp, $suitesSelected, $builds_to_query);
$topLevelSuites = $re->getTopLevelSuites();
$mapOfAggregate = $re->getAggregateMap();

$arrDataSuite = null;
$arrDataSuiteIndex = 0;
while ($i = key($topLevelSuites)) {
	
	//print_r($arrDataSuite);
	//print "<BR>";
	$pairArray = $topLevelSuites[$i];
	$currentSuiteId = $pairArray['id'];
	$currentSuiteName = $pairArray['name'];

	$resultArray = $mapOfAggregate[$currentSuiteId];	
	$total = $resultArray['total'];
	$notRun = $resultArray['notRun'];
	$percentCompleted = (($total - $notRun) / $total) * 100;
	$arrDataSuite[$arrDataSuiteIndex] = array($currentSuiteName,$total,$resultArray['pass'],$resultArray['fail'],$resultArray['blocked'],$notRun,$percentCompleted);
	$arrDataSuiteIndex++;
	next($topLevelSuites);

} 

//$arrDataPriority = getPriorityReport($db,$tpID);
$arrDataPriority = null;

//$arrDataSuite = getTestSuiteReport($db,$tpID);
//print_r($arrDataSuite);
//print "<BR>";

//$arrDataKeys = getKeywordsReport($db,$tpID);
$arrDataKeys = null;

//$arrDataOwner = getOwnerReport($db,$tpID);
$arrDataOwner = null;

$smarty = new TLSmarty;
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->assign('arrDataPriority', $arrDataPriority);
$smarty->assign('arrDataSuite', $arrDataSuite);
$smarty->assign('arrDataOwner', $arrDataOwner);
$smarty->assign('arrDataKeys', $arrDataKeys);
$smarty->display('resultsGeneral.tpl');

?>
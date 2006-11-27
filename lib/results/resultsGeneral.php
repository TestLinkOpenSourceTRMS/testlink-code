<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: resultsGeneral.php,v $
 * @version $Revision: 1.14 $
 * @modified $Date: 2006/11/27 06:59:03 $ by $Author: kevinlevy $
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

print "KL - 20061126 - all tables functional except for priority report <BR>";
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



/** 
* COMPONENTS REPORT 
*/

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
	$percentCompleted = number_format($percentCompleted,2);
	$arrDataSuite[$arrDataSuiteIndex] = array($currentSuiteName,$total,$resultArray['pass'],$resultArray['fail'],$resultArray['blocked'],$notRun,$percentCompleted);
	$arrDataSuiteIndex++;
	next($topLevelSuites);
} 

/**
* PRIORITY REPORT
*/
//$arrDataPriority = getPriorityReport($db,$tpID);
$arrDataPriority = null;


/**
* KEYWORDS REPORT
*/

$arrDataKeys = null;
$arrDataKeysIndex = 0;
$arrKeywords = $tp->get_keywords_map($tpID); 
while ($keyword_id = key($arrKeywords)) {
	$keyword_name = $arrKeywords[$keyword_id] ;
	$specificKeywordResults = new results($db, $tp, $suitesSelected, $builds_to_query, 'a', $keyword_id);
	$resultArray = $specificKeywordResults->getTotalsForPlan();
	$total = $resultArray['total'];
	$notRun = $resultArray['notRun'];
	$percentCompleted = (($total - $notRun) / $total) * 100;
	$percentCompleted = number_format($percentCompleted,2);
	$arrDataKeys[$arrDataKeysIndex] = array($keyword_name,$total,$resultArray['pass'],$resultArray['fail'],$resultArray['blocked'],$notRun,$percentCompleted);
	$arrDataKeysIndex++;
	next($arrKeywords);
}



/** 
* OWNERS REPORT 
*/
define('ALL_USERS_FILTER', null);
define('ADD_BLANK_OPTION', false);
$arrOwners = get_users_for_html_options($db, ALL_USERS_FILTER, ADD_BLANK_OPTION);
//$arrDataOwner = getOwnerReport($db,$tpID);
$arrDataOwner = null;
$arrDataOwnerIndex = 0;

while ($owner_id = key($arrOwners)) {
	$owner_name = $arrOwners[$owner_id] ;
	$specificOwnerResults = new results($db, $tp, $suitesSelected, $builds_to_query, 'a', 0, $owner_id);
	$resultArray = $specificOwnerResults->getTotalsForPlan();
	$total = $resultArray['total'];
	$notRun = $resultArray['notRun'];
	if ($total) {
		$percentCompleted = (($total - $notRun) / $total) * 100;
		$percentCompleted = number_format($percentCompleted,2);
	}
	else
		$percentCompleted = 0.00;
	$arrDataOwner[$arrDataOwnerIndex] = array($owner_name,$total,$resultArray['pass'],$resultArray['fail'],$resultArray['blocked'],$notRun,$percentCompleted);
	$arrDataOwnerIndex++;
	next($arrOwners);
}


/**
* SMARTY ASSIGNMENTS
*/ 

$smarty = new TLSmarty;
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->assign('arrDataPriority', $arrDataPriority);
$smarty->assign('arrDataSuite', $arrDataSuite);
$smarty->assign('arrDataOwner', $arrDataOwner);
$smarty->assign('arrDataKeys', $arrDataKeys);
$smarty->display('resultsGeneral.tpl');

?>
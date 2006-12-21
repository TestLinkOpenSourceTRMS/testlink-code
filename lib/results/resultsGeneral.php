<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: resultsGeneral.php,v $
 * @version $Revision: 1.19 $
 * @modified $Date: 2006/12/21 07:37:01 $ by $Author: kevinlevy $
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

//print "Warning Message - KL - 20061126 - all tables functional except for priority report <BR>";
require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');
require_once('TestPlanResultsObj.php');
require_once('timer.php');
require_once('../functions/results.class.php');
require_once('../functions/testplan.class.php');

testlinkInitPage($db);
$tpID = $_SESSION['testPlanId']; 

$tp = new testplan($db);
$builds_to_query = 'a';
$suitesSelected = 'all';
//print "resultsGeneral.php - create results object <BR>";

$time_start = microtime_float();
$re = new results($db, $tp, $suitesSelected, $builds_to_query);
$time_end = microtime_float();
$time = $time_end - $time_start;
// print "results object created in $time <BR>";


//print "<BR>";
//print "resultsGeneral.php - finished creating object <BR>";


$excelWriter = new TestPlanResultsObj();

/** 
* COMPONENTS REPORT 
*/

//print "resultsGeneral start components report <BR>";
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
//print "resultsGeneral end components report <BR>";


/**
* PRIORITY REPORT
*/
//$arrDataPriority = getPriorityReport($db,$tpID);
$arrDataPriority = null;


/**
* KEYWORDS REPORT
*/
//print "resultsGeneral start keywords report <BR>";
$arrDataKeys = null;
/**
* TO-DO : fix performance of keywords report
* KL - 20061210 - commenting out since performance of this is not good enough
*/
$arrDataKeysIndex = 0;
$arrKeywords = $tp->get_keywords_map($tpID); 

if (is_array($arrKeywords)) {
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
  } // end while
} // end if

//print "resultsGeneral end keywords report <BR>";


/** 
* OWNERS REPORT 
*/
define('ALL_USERS_FILTER', null);
define('ADD_BLANK_OPTION', false);
$arrOwners = get_users_for_html_options($db, ALL_USERS_FILTER, ADD_BLANK_OPTION);
//$arrDataOwner = getOwnerReport($db,$tpID);
$arrDataOwner = null;
$arrDataOwnerIndex = 0;
/**
*  KL - 20061210 - comment out for performance reasons */
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

//print "resultsGeneral - end owners report <BR>";

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
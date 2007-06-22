<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds_buildReport.php,v 1.43 2007/06/22 00:26:41 kevinlevy Exp $ 
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
* 
* This page will forward the user to a form where they can select
* the builds they would like to query results against.
*
* @author Francisco Mancardi - 20050912 - remove unused code
* @author Kevin Levy - 20060603 - starting 1.7 changes
**/
require('../../config.inc.php');
require_once('common.php');
require_once('../functions/results.class.php');
require_once('../functions/users.inc.php');
require_once('displayMgr.php');
testlinkInitPage($db);

$format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'HTML';
$lastStatus = isset($_REQUEST['lastStatus']) ? $_REQUEST['lastStatus'] : null;

// statusForClass is used for results.class.php
// lastStatus is used to be displayed 
$statusForClass = 'a';
// TO-DO localize parameters passed from form
if ($lastStatus == "Passed"){
  $statusForClass = 'p';
 }
elseif ($lastStatus == "Failed"){
   $statusForClass = 'f';
}
elseif ($lastStatus == "Blocked"){
 $statusForClass = 'b';
}
elseif ($lastStatus == "Not Run"){
  $statusForClass = 'n';
}
elseif ($lastStatus == "Any"){
  $statusForClass = 'a';
}

$ownerSelected = isset($_REQUEST['owner']) ? $_REQUEST['owner'] : null;
$buildsSelected = isset($_REQUEST['build']) ? $_REQUEST['build'] : array();
$componentsSelected = isset($_REQUEST['component']) ? $_REQUEST['component'] : array();
$componentIds = null;
$componentNames = null;

for ($id = 0; $id < sizeOf($componentsSelected); $id++)
{
	list($suiteId, $suiteName) = split("\,", $componentsSelected[$id], 2);
	$componentIds[$id] = $suiteId;
	$componentNames[$id] = $suiteName;	
}

$keywordSelected = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : 0;
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
$prodID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tpName = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : '';

$startYear = isset($_REQUEST['start_year']) ? $_REQUEST['start_year'] : "0000";
$startMonth = isset($_REQUEST['start_month']) ? $_REQUEST['start_month'] : "00";
$startDay = isset($_REQUEST['start_day']) ? $_REQUEST['start_day'] : "00";
$startHour = isset($_REQUEST['start_hour']) ? $_REQUEST['start_hour'] : "00";

$endYear = isset($_REQUEST['end_year']) ? $_REQUEST['end_year'] : "9999";
$endMonth = isset($_REQUEST['end_month']) ? $_REQUEST['end_month'] : "00";
$endDay = isset($_REQUEST['end_day']) ? $_REQUEST['end_day'] : "00";
$endHour = isset($_REQUEST['end_hour']) ? $_REQUEST['end_hour'] : "00";

$startTime = $startYear . "-" . $startMonth . "-" . $startDay . " " . $startHour. ":00:00";
$endTime = $endYear . "-" . $endMonth . "-" . $endDay . " " . "$endHour" . ":00:00";

$xls = ($format == 'EXCEL') ? true : false;
$buildsToQuery = -1;

if (sizeof($buildsSelected)) {
	$buildsToQuery = implode(",", $buildsSelected);
}

$tp = new testplan($db);

$re = new results($db, $tp, $componentIds, $buildsToQuery, $statusForClass, $keywordSelected, $ownerSelected, $startTime, $endTime);
$suiteList = $re->getSuiteList();
$flatArray = $re->getFlatArray();
$mapOfSuiteSummary =  $re->getAggregateMap();
$totals = $re->getTotalsForPlan();
$arrKeywords = $tp->get_keywords_map($tpID); 
$arrBuilds = $tp->get_builds($tpID); 
$mapBuilds = $tp->get_builds_for_html_options($tpID);

$arrOwners = get_users_for_html_options($db, ALL_USERS_FILTER, !ADD_BLANK_OPTION);
$smarty = new TLSmarty();
$smarty->assign('arrBuilds', $arrBuilds);
$smarty->assign('mapBuilds', $mapBuilds);
$smarty->assign('mapUsers',$arrOwners);
$smarty->assign('arrKeywords', $arrKeywords);
$smarty->assign('componentsSelected', $componentNames);
$smarty->assign('lastStatus', $lastStatus);
$smarty->assign('buildsSelected', $buildsSelected);
$smarty->assign('keywordsSelected', $keywordSelected);
$smarty->assign('startTime', $startTime);
$smarty->assign('endTime', $endTime);

if ($ownerSelected) {
$smarty->assign('ownerSelected', $arrOwners[$ownerSelected]);
}
$smarty->assign('totals', $totals);
$smarty->assign('testPlanName',$tpName);
$smarty->assign('testplanid', $tpID);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('suiteList', $suiteList);
$smarty->assign('flatArray', $flatArray);
$smarty->assign('mapOfSuiteSummary', $mapOfSuiteSummary);

$report_type = isset($_GET['report_type']) ? intval($_GET['report_type']) : null;
$smarty->assign('report_type', $report_type);
if (!isset($_GET['report_type']))
{
	tlog('$_GET["report_type"] is not defined');
	exit();
}

displayReport('resultsMoreBuilds_report', $smarty, $report_type);

?>
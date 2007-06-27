<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds_buildReport.php,v 1.49 2007/06/27 06:08:21 kevinlevy Exp $ 
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
$lastStatus = isset($_REQUEST['lastStatus']) ? $_REQUEST['lastStatus'] : array();

// statusForClass is used for results.class.php
// lastStatus is used to be displayed 
$statusForClass = 'a';

$displayUnexecutedRows = false;
$displayBlockedRows = false;
$displayPassedRows = false;
$displayFailedRows = false;

$display_suite_summaries = isset($_REQUEST['display_suite_summaries']) ? $_REQUEST['display_suite_summaries'] : true;
$display_totals = isset($_REQUEST['display_totals']) ? $_REQUEST['display_totals'] : true;
$display_query_params = isset($_REQUEST['display_query_params']) ? $_REQUEST['display_query_params'] : true;


for ($i = 0; $i < sizeOf($lastStatus); $i++)
{
	if ($lastStatus[$i] == "p"){
		$displayPassedRows = true;
	}
	elseif ($lastStatus[$i] == "f"){
			$displayFailedRows = true;
	}
	elseif ($lastStatus[$i] == "b"){
 		$displayBlockedRows = true;
	}
	elseif ($lastStatus[$i] == "n"){
 		$displayUnexecutedRows = true;
	}
}
						
$ownerSelected = isset($_REQUEST['owner']) ? $_REQUEST['owner'] : null;
$executorSelected = isset($_REQUEST['executor']) ? $_REQUEST['executor'] : null;
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
//$prodID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tplan_name = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : '';

//$tpName = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : '';
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

$startYear = isset($_REQUEST['start_year']) ? $_REQUEST['start_year'] : "0000";
$startMonth = isset($_REQUEST['start_month']) ? $_REQUEST['start_month'] : "00";
$startDay = isset($_REQUEST['start_day']) ? $_REQUEST['start_day'] : "00";
$startHour = isset($_REQUEST['start_hour']) ? $_REQUEST['start_hour'] : "00";

$endYear = isset($_REQUEST['end_year']) ? $_REQUEST['end_year'] : "9999";
$endMonth = isset($_REQUEST['end_month']) ? $_REQUEST['end_month'] : "00";
$endDay = isset($_REQUEST['end_day']) ? $_REQUEST['end_day'] : "00";
$endHour = isset($_REQUEST['end_hour']) ? $_REQUEST['end_hour'] : "00";

$search_notes_string = isset($_REQUEST['search_notes_string']) ? $_REQUEST['search_notes_string'] : null;

$startTime = $startYear . "-" . $startMonth . "-" . $startDay . " " . $startHour. ":00:00";
$endTime = $endYear . "-" . $endMonth . "-" . $endDay . " " . "$endHour" . ":00:00";

$xls = ($format == 'EXCEL') ? true : false;
$buildsToQuery = -1;

if (sizeof($buildsSelected)) {
	$buildsToQuery = implode(",", $buildsSelected);
}

$tp = new testplan($db);

// KL - 20070625 - used for execution links
$execution_link_build = isset($_GET['build']) ? intval($_GET['build']) : null;

$re = new results($db, $tp, $componentIds, $buildsToQuery, $statusForClass, $keywordSelected, $ownerSelected, $startTime, $endTime, $executorSelected, $search_notes_string, $execution_link_build);
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
if ($executorSelected) {
	$smarty->assign('executorSelected', $arrOwners[$executorSelected]);
}
if ($search_notes_string) {
	$smarty->assign('search_notes_string', $search_notes_string);
}


$smarty->assign('totals', $totals);
$smarty->assign('tplan_name',$tplan_name);
$smarty->assign('tproject_name',$tproject_name);
$smarty->assign('testplanid', $tpID);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('suiteList', $suiteList);
$smarty->assign('flatArray', $flatArray);
$smarty->assign('mapOfSuiteSummary', $mapOfSuiteSummary);
$smarty->assign('displayUnexecutedRows', $displayUnexecutedRows);
$smarty->assign('displayBlockedRows', $displayBlockedRows);
$smarty->assign('displayPassedRows', $displayPassedRows);
$smarty->assign('displayFailedRows', $displayFailedRows);
$smarty->assign('show_summaries', $display_suite_summaries);
$smarty->assign('show_totals', $display_totals);
$smarty->assign('show_query_params', $display_query_params);

$report_type = isset($_GET['report_type']) ? intval($_GET['report_type']) : null;

$smarty->assign('report_type', $report_type);
if (!isset($_GET['report_type']))
{
	tlog('$_GET["report_type"] is not defined');
	exit();
}

displayReport('resultsMoreBuilds_report', $smarty, $report_type);

?>
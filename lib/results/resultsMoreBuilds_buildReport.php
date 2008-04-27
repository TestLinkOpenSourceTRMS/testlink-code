<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds_buildReport.php,v 1.56 2008/04/27 17:35:46 franciscom Exp $ 
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
* 
* This page will forward the user to a form where they can select
* the builds they would like to query results against.
*
* rev :
*      20070901 - franciscom - refactoring
* 
**/
require('../../config.inc.php');
require_once('common.php');
require_once('results.class.php');
require_once('users.inc.php');
require_once('displayMgr.php');
testlinkInitPage($db);
$template_dir='results/';


$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$tplan_id=$_REQUEST['tplan_id'];
$tproject_id=$_SESSION['testprojectID'];

$tplan_info = $tplan_mgr->get_by_id($tplan_id);
$tproject_info = $tproject_mgr->get_by_id($tproject_id);

$tplan_name = $tplan_info['name'];
$tproject_name = $tproject_info['name'];


$reports_cfg=config_get('reportsCfg');
$tc_status_verbose_code=config_get('tc_status');   
$tc_status_verbose_labels=config_get('tc_status_verbose_labels');   


// statusForClass is used for results.class.php
// lastStatus is used to be displayed 
$statusForClass = 'a';


$format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'HTML';
$display_suite_summaries = isset($_REQUEST['display_suite_summaries']) ? $_REQUEST['display_suite_summaries'] : true;
$display_totals = isset($_REQUEST['display_totals']) ? $_REQUEST['display_totals'] : true;
$display_query_params = isset($_REQUEST['display_query_params']) ? $_REQUEST['display_query_params'] : true;
$lastStatus = isset($_REQUEST['lastStatus']) ? $_REQUEST['lastStatus'] : array();

// Config to manage versobe and code status
$tc_status_code_verbose=array_flip($tc_status_verbose_code);

// same key that tcstatus_verbose_code
$displayTCRows=array();
$lastStatus_localized=array();
foreach($reports_cfg->exec_status as $verbose => $label)
{
  $displayTCRows[$verbose]=false;
}

foreach($lastStatus	as $key => $status_code)
{
   $verbose=$tc_status_code_verbose[$status_code];
   $displayTCRows[$verbose]=true;
   $lastStatus_localized[]=lang_get($tc_status_verbose_labels[$verbose]);
}	
// -------------------------------------------------------------------------------------------

$keywordSelected = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : 0;

						
						
$ownerSelected = (isset($_REQUEST['owner']) && $_REQUEST['owner'] > 0 ) ? $_REQUEST['owner'] : null;
$executorSelected = (isset($_REQUEST['executor']) && $_REQUEST['executor'] > 0) ? $_REQUEST['executor'] : null;

$buildsSelected = isset($_REQUEST['build']) ? $_REQUEST['build'] : array();
$testsuitesSelected = isset($_REQUEST['testsuite']) ? $_REQUEST['testsuite'] : array();
$search_notes_string = isset($_REQUEST['search_notes_string']) ? $_REQUEST['search_notes_string'] : null;

$testsuiteIds = null;
$testsuiteNames = null;

$tsuites_qty=sizeOf($testsuitesSelected);
for ($id = 0; $id < $tsuites_qty ; $id++)
{
	list($suiteId, $suiteName) = split("\,", $testsuitesSelected[$id], 2);
	$testsuiteIds[$id] = $suiteId;
	$testsuiteNames[$id] = $suiteName;	
}


$date_range=get_date_range($_REQUEST);
$startDate = $date_range->start->date;
$startTime = $date_range->start->time;
$startHour = $date_range->start->hour;

$endDate = $date_range->end->date;
$endTime = $date_range->end->time;
$endHour = $date_range->end->hour;



$xls = ($format == 'EXCEL') ? true : false;
$buildsToQuery = -1;
if (sizeof($buildsSelected)) {
	$buildsToQuery = implode(",", $buildsSelected);
}


// KL - 20070625 - used for execution links
$execution_link_build = isset($_GET['build']) ? intval($_GET['build']) : null;

$re = new results($db, $tplan_mgr,$tproject_info,$tplan_info, 
                  $testsuiteIds, $buildsToQuery, $statusForClass, 
                  $keywordSelected, $ownerSelected, $startTime, $endTime, $executorSelected, 
                  $search_notes_string, $execution_link_build);
                  
$suiteList = $re->getSuiteList();
$flatArray = $re->getFlatArray();
$mapOfSuiteSummary =  $re->getAggregateMap();
$totals = $re->getTotalsForPlan();
$arrKeywords = $tplan_mgr->get_keywords_map($tplan_id); 
$arrBuilds = $tplan_mgr->get_builds($tplan_id); 
$mapBuilds = $tplan_mgr->get_builds_for_html_options($tplan_id);
$arrOwners = getUsersForHtmlOptions($db, ALL_USERS_FILTER, !ADD_BLANK_OPTION);



$smarty = new TLSmarty();
$smarty->assign('arrBuilds', $arrBuilds);
$smarty->assign('mapBuilds', $mapBuilds);
$smarty->assign('mapUsers',$arrOwners);

$smarty->assign('arrKeywords', $arrKeywords);
$smarty->assign('keyword_qty', count($arrKeywords));

$smarty->assign('testsuitesSelected', $testsuiteNames);
$smarty->assign('lastStatus', $lastStatus_localized);
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
$smarty->assign('testplanid', $tplan_id);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('suiteList', $suiteList);
$smarty->assign('flatArray', $flatArray);
$smarty->assign('mapOfSuiteSummary', $mapOfSuiteSummary);

$smarty->assign('displayUnexecutedRows', $displayTCRows['not_run']);
$smarty->assign('displayBlockedRows', $displayTCRows['blocked']);
$smarty->assign('displayPassedRows', $displayTCRows['passed']);
$smarty->assign('displayFailedRows', $displayTCRows['failed']);

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

displayReport($template_dir . 'resultsMoreBuilds_report', $smarty, $report_type);
?>


<?php
function get_date_range($hash)
{
 
$date_range->start->day=isset($hash['start_Day']) ? $hash['start_Day'] : "01";
$date_range->start->month=isset($hash['start_Month']) ? $hash['start_Month'] : "01";
$date_range->start->year=isset($hash['start_Year']) ? $hash['start_Year'] : "2000";
$date_range->start->hour=isset($hash['start_Hour']) ? $hash['start_Hour'] : "00";

$mm=sprintf("%02d",$date_range->start->month);
$dd=sprintf("%02d",$date_range->start->day);
$date_range->start->date=$date_range->start->year . "-" . $mm . "-" . $dd;
$date_range->start->time=$date_range->start->date . " " . $date_range->start->hour . ":00:00";

$date_range->end->day=isset($hash['end_Day']) ? $hash['end_Day'] : "01";
$date_range->end->month=isset($hash['end_Month']) ? $hash['end_Month'] : "01";
$date_range->end->year=isset($hash['end_Year']) ? $hash['end_Year'] : "2050";
$date_range->end->hour=isset($hash['end_Hour']) ? $hash['end_Hour'] : "00";

$mm=sprintf("%02d",$date_range->end->month);
$dd=sprintf("%02d",$date_range->end->day);
$date_range->end->date=$date_range->end->year . "-" . $mm . "-" . $dd;
$date_range->end->time=$date_range->end->date . " " . $date_range->end->hour . ":00:00";

return $date_range;
}
?>

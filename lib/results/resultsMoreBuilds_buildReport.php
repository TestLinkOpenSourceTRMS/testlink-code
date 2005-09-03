<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds_buildReport.php,v 1.4 2005/09/03 19:42:37 kevinlevy Exp $ 
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
* 
* This page show Metrics a test plan based on a start build,
* end build, keyword, test plan id, and owner.
*
*/

require('../../config.inc.php');
require_once('common.php');
require_once('../functions/resultsMoreBuilds.inc.php');
require_once('../functions/builds.inc.php');

// I'm not sure which one of these contains
// the excel libraries
require_once('builds.inc.php');
require_once('results.inc.php');
require_once("../../lib/functions/lang_api.php");

// init
testlinkInitPage();

$buildsSelected = array();

$xls = null;

if (isset($_GET['format']) && $_GET['format'] =='excel'){
  $xls = TRUE;
 } else {
  $xls = FALSE;
 }


if (isset($_REQUEST['build'])){
  foreach($_REQUEST['build'] AS $val){
    $buildsSelected[] = $val;
  }
}
if (!isset($_GET['build'])) {
	tlog('$_GET["build"] is not defined');
	exit;
}

if (!isset($_GET['keyword'])) {
	tlog('$_GET["keyword"] is not defined');
	exit;
}

if (!isset($_GET['owner'])) {
	tlog('$_GET["owner"] is not defined');
	exit;
}

if (!isset($_GET['projectid'])) {
	tlog('$_GET["projectid"] is not defined');
	exit;
}

if (!isset($_GET['testPlanName'])) {
	tlog('$_GET["testPlanName"] is not defined');
	exit;
}

if (!isset($_GET['lastStatus'])) {
	tlog('$_GET["lastStatus"] is not defined');
	exit;
}

tlTimingStart();
$reportData = createResultsForTestPlan($_GET['testPlanName'],$_SESSION['testPlanId'], $buildsSelected, $_GET['keyword'], $_GET['owner'], $_GET['lastStatus'], $xls);
tlTimingStop();
$queryParameters = $reportData[0];
$summaryOfResults = $reportData[1];
$allComponentData = $reportData[2];
//echo tlTimingCurrent();
//var_dump(strlen($allComponentData));
// display smarty
$smarty = new TLSmarty();
$smarty->assign('queryParameters', $queryParameters);
$smarty->assign('summaryOfResults', $summaryOfResults);
$smarty->assign('allComponentData', $allComponentData);

// for excel send header
if ($xls) {
  sendXlsHeader();
 
  $smarty->assign('printDate', date('"F j, Y, H:m"'));
  $smarty->assign('user', $_SESSION['user']);
 }

// this contains example of how this excel data gets used
// $smarty->display('resultsTC.tpl');

$smarty->assign('xls', $xls);

$smarty->display('resultsMoreBuilds_report.tpl');
?>
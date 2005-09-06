<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds_buildReport.php,v 1.5 2005/09/06 06:42:04 franciscom Exp $ 
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
* 
* Show Metrics a test plan based on a start build,
* end build, keyword, test plan id, and owner.
*
*
* @author Francisco Mancardi - 20050905 - refactoring
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

$xls = FALSE;
if (isset($_GET['format']) && $_GET['format'] =='excel'){
  $xls = TRUE;
}


if (isset($_REQUEST['build'])){
  foreach($_REQUEST['build'] AS $val){
    $buildsSelected[] = $val;
  }
}

$a2check = array('build','keyword','owner','testPlanID','testPlanName',"lastStatus");
if( !check_hash_keys($_GET, $a2check, "is not defined in \$GET")
{
	exit;
}


tlTimingStart();
$reportData = createResultsForTestPlan($_GET['testPlanName'],$_SESSION['testPlanId'], $buildsSelected, 
                                       $_GET['keyword'], $_GET['owner'], $_GET['lastStatus'], $xls);
tlTimingStop();
$queryParameters = $reportData[0];
$summaryOfResults = $reportData[1];
$allComponentData = $reportData[2];
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
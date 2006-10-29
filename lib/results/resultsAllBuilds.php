<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsAllBuilds.php,v 1.7 2006/10/29 10:18:08 kevinlevy Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* 
* This page show Test Results over all Builds.
*
* @author Kevin Levy 20061029 - 1.7 upgrate
*/

require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');	
require_once('../functions/results.class.php');
testlinkInitPage($db);

$tp = new testplan($db);
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0 ;
$arrBuilds = $tp->get_builds($tpID); 
$SUITES_SELECTED = 'all';

$arrDataBuilds = null;
$arrDataBuildsIndex = 0;
for ($i = 0; $i < sizeOf($arrBuilds); $i++) {
	$currentArray = $arrBuilds[$i] ;
	$build_id = $currentArray['id'];
	$build_name = $currentArray['name'];
	$specificBuildResults = new results($db, $tp, $SUITES_SELECTED, $build_id);
	$resultArray = $specificBuildResults->getTotalsForPlan();
	$total = $resultArray['total'];
	$notRun = $resultArray['notRun'];
	$percentNotRun = ($notRun / $total) * 100;
	$percentCompleted = (($total - $notRun) / $total) * 100;
	$pass = $resultArray['pass'];
	$percentPass = ($pass / $total ) * 100;
	$fail = $resultArray['fail'];
	$percentFail = ($fail / $total) * 100;
	$blocked = $resultArray['blocked'];
	$percentBlocked = ($blocked / $total ) * 100;
	$arrDataBuilds[$arrDataBuildsIndex] = array($build_name,$total, $pass, $percentPass, $fail, $percentFail, $blocked, $percentBlocked, $notRun, $percentNotRun);
	$arrDataBuildsIndex++;
}

$smarty = new TLSmarty;
$smarty->assign('tcs_color', $g_tc_sd_color);
$smarty->assign('title', $_SESSION['testPlanName'] . lang_get('title_metrics_x_build'));
$smarty->assign('arrData', $arrDataBuilds);
$smarty->display('resultsAllBuilds.tpl');
?>

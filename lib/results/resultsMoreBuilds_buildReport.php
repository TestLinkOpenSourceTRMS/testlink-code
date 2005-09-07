<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds_buildReport.php,v 1.8 2005/09/07 20:19:25 schlundus Exp $ 
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
require_once('builds.inc.php');
require_once('results.inc.php');
testlinkInitPage();

$tpName = isset($_GET['testPlanName']) ? strings_stripSlashes($_GET['testPlanName']) : null;
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
$keyword = isset($_GET['keyword']) ? strings_stripSlashes($_GET['keyword']) : null;
$owner = isset($_GET['owner']) ? strings_stripSlashes($_GET['owner']) : null;
$lastStatus = isset($_GET['lastStatus']) ? strings_stripSlashes($_GET['lastStatus']) : null; 


$buildsSelected = array();

$xls = FALSE;
if (isset($_GET['format']) && $_GET['format'] =='excel')
{
	$xls = TRUE;
}

if (isset($_REQUEST['build']))
{
	foreach($_REQUEST['build'] AS $val)
	{
		$buildsSelected[] = $val;
	}
}

$a2check = array('build','keyword','owner','testPlanName',"lastStatus");
if(!check_hash_keys($_GET, $a2check, "is not defined in \$GET"))
{
	exit();
}
tlTimingStart();
$reportData = createResultsForTestPlan($tpName,$tpID, $buildsSelected, 
                                       $keyword, $owner, $lastStatus, $xls);
tlTimingStop();
$queryParameters = $reportData[0];
$summaryOfResults = $reportData[1];
$allComponentData = $reportData[2];

/*
var_dump(strlen($summaryOfResults));
var_dump(strlen($allComponentData));
var_dump(tlTimingCurrent());
*/
$smarty = new TLSmarty();
$smarty->assign('queryParameters', $queryParameters);
$smarty->assign('summaryOfResults', $summaryOfResults);
$smarty->assign('allComponentData', $allComponentData);

// for excel send header
if ($xls)
{
	sendXlsHeader();
	$smarty->assign('printDate', strftime($g_date_format, time()) );
	$smarty->assign('user', $_SESSION['user']);
}

$smarty->assign('xls', $xls);
$smarty->display('resultsMoreBuilds_report.tpl');
?>
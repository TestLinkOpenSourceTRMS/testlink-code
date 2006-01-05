<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds_buildReport.php,v 1.17 2006/01/05 07:30:34 franciscom Exp $ 
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
* 
* This page show Metrics a test plan based on a start build,
* end build, keyword, test plan id, and owner.
* @author  Francisco Mancardi - 20050905 refactoring
* 20051022 - scs - cosmetic code changes
*/
require('../../config.inc.php');
require_once('common.php');
require_once('../functions/resultsMoreBuilds.inc.php');
require_once('../functions/builds.inc.php');
require_once('builds.inc.php');
require_once('results.inc.php');
testlinkInitPage($db);

$tpName = isset($_GET['testPlanName']) ? strings_stripSlashes($_GET['testPlanName']) : null;  
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;  
$keyword = isset($_GET['keyword']) ? strings_stripSlashes($_GET['keyword']) : null;  
$owner = isset($_GET['owner']) ? strings_stripSlashes($_GET['owner']) : null;  
$lastStatus = isset($_GET['lastStatus']) ? strings_stripSlashes($_GET['lastStatus']) : null;  
$a2check = array('build','keyword','owner','testPlanName','testPlanName',"lastStatus"); 
$format = isset($_GET['format']) ? strings_stripSlashes($_GET['format']) : null;
if(!check_hash_keys($_GET, $a2check, "is not defined in \$GET")) 
{
	exit();
}

$buildsSelected = array();
$componentsSelected = array();

$xls = FALSE;
if ($format == lang_get('excel_format'))
{
	$xls = TRUE;
} 

if (isset($_REQUEST['build']))
{
	foreach($_REQUEST['build'] as $val)
	{
	    $buildsSelected[] = $val;
	}
}
if (isset($_REQUEST['component']))
{
	foreach($_REQUEST['component'] as $val)
	{
		$componentsSelected[] = $val;
	}
}

$reportData = createResultsForTestPlan($tpName,$tpID, $buildsSelected, $keyword, $owner, $lastStatus, $xls, $componentsSelected);
$queryParameters = $reportData[0];
$summaryOfResults = $reportData[1];
$allComponentData = $reportData[2];

$smarty = new TLSmarty();
$smarty->assign('queryParameters', $queryParameters);
$smarty->assign('summaryOfResults', $summaryOfResults);
$smarty->assign('allComponentData', $allComponentData);
$smarty->assign('xls', $xls);
if ($xls)
{
	sendXlsHeader();
	$smarty->assign('printDate', strftime($g_date_format, time()) ); 
	$smarty->assign('user', $_SESSION['user']);
}

$smarty->display('resultsMoreBuilds_report.tpl');
?>
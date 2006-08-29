<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds_buildReport.php,v 1.22 2006/08/29 21:43:28 kevinlevy Exp $ 
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
* 
* This page will forward the user to a form where they can select
* the builds they would like to query results against.
*
* @author Francisco Mancardi - 20050912 - remove unused code
* @author Kevin Levy - 20060603 - starting 1.7 changes
*/

require('../../config.inc.php');
require_once('common.php');
require_once('../functions/results.class.php');
require_once('../functions/testplan.class.php');
require_once('../functions/tree.class.php');

testlinkInitPage($db);

$prodID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tpID = $_SESSION['testPlanId'];

$buildsSelected = array();
$componentsSelected = array();
$keywordsSelected = array();
$testplansSelected = array();

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

if (isset($_REQUEST['testplans'])) 
{
    foreach($_REQUEST['testplans'] as $val) 
    {
    		$testplansSelected[] = $val;
    }
}



/** i may not needs this - need to investigate "keyword" key that is passed from query form
if (isset($_REQUEST['keyword'])) 
{
    foreach($_REQUEST['keyword'] as $val) 
    {
    		$keywordsSelected[] = $val;
    }
}
*/

$buildsToQuery = implode(",", $buildsSelected);

$tp = new testplan($db);
$tree = new tree($db);

$re = new results($db, $tp, $tree, $prodID, $buildsToQuery);

$suiteList = $re->getSuiteList();
$flatArray = $re->getFlatArray();
$mapOfSuiteSummary =  $re->getAggregateMap();
$totals = $re->getTotalsForPlan();

$arrKeywords = $tp->get_keywords_map($tpID); 
$arrBuilds = $tp->get_builds($tpID); 
$arrComponents = $re->getTopLevelSuites();

$keyword = isset($_GET['keyword']) ? strings_stripSlashes($_GET['keyword']) : null;
$owner = isset($_GET['owner']) ? strings_stripSlashes($_GET['owner']) : null;
$lastStatus = isset($_GET['lastStatus']) ? strings_stripSlashes($_GET['lastStatus']) : null;

$smarty = new TLSmarty();
$smarty->assign('testplans', $testplansSelected);
$smarty->assign('arrBuilds', $arrBuilds);
$smarty->assign('arrComponents', $arrComponents);
$smarty->assign('arrKeywords', $arrKeywords);
$smarty->assign('componentsSelected', $componentsSelected);
$smarty->assign('lastStatus', $lastStatus);
$smarty->assign('buildsSelected', $buildsSelected);
$smarty->assign('keywordsSelected', $keyword);
$smarty->assign('totals', $totals);
$smarty->assign('testPlanName',$_SESSION['testPlanName']);
$smarty->assign('testplanid', $tpID);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('suiteList', $suiteList);
$smarty->assign('flatArray', $flatArray);
$smarty->assign('mapOfSuiteSummary', $mapOfSuiteSummary);
$smarty->display('resultsMoreBuilds_report.tpl');
?>


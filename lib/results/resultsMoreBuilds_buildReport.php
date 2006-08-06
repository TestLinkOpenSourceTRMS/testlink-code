<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds_buildReport.php,v 1.18 2006/08/06 02:38:16 kevinlevy Exp $ 
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
require_once('builds.inc.php');
require_once('../functions/results.class.php');
require_once('../functions/testplan.class.php');
require_once('../functions/tree.class.php');
require_once('resultsMoreBuilds.inc.php');

testlinkInitPage($db);

$prodID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tpID = $_SESSION['testPlanId'];

$tp = new testplan($db);
$tree = new tree($db);
$re = new results($db, $tp, $tree, $prodID);

$suiteList = $re->getSuiteList();
$flatArray = $re->getFlatArray();
//$numberOfSuites = count(array_keys($suiteList));
$mapOfSuiteSummary =  $re->getAggregateMap();
$totals = $re->getTotalsForPlan();

$arrBuilds = getBuilds($db,$tpID, " ORDER BY builds.name "); 
$smarty = new TLSmarty();

$smarty->assign('totals', $totals);
$smarty->assign('testPlanName',$_SESSION['testPlanName']);
$smarty->assign('testplanid', $tpID);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('suiteList', $suiteList);
$smarty->assign('flatArray', $flatArray);
$smarty->assign('mapOfSuiteSummary', $mapOfSuiteSummary);
$smarty->display('resultsMoreBuilds_report.tpl');
?>


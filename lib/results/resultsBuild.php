<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsBuild.php,v 1.7 2006/01/05 07:30:34 franciscom Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* 
* This page show Metrics of one Build.
*
* @author Francisco Mancardi - fm - reduce global coupling
*
*/
require('../../config.inc.php');
require_once('common.php');
require_once('results.inc.php');
require_once("../../lib/functions/lang_api.php");
require_once("../../lib/functions/builds.inc.php");
testlinkInitPage($db);

$buildID = isset($_GET['build']) ? intval($_GET['build']) : null;
if (!isset($_GET['build']))
{
	tlog('$_GET["build"] is not defined');
	exit();
}

$tpID = $_SESSION['testPlanId'];

$builds = getBuilds($db,$tpID, " ORDER BY build.name ");
$buildName = $builds[$buildID];
$arrDataPriority = getPriorityReport($db,$tpID,$buildID);

// get Test Suite data
$arrDataSuite = getBuildMetricsComponent($db,$tpID,$buildID);
$arrDataCategory = getBuildMetricsCategory($db,$tpID,$buildID);
$arrDataKeys = getKeywordsReport($db,$tpID,$buildID);


$smarty = new TLSmarty;
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->assign('build', $buildID);
$smarty->assign('buildName', $buildName);
$smarty->assign('arrDataPriority', $arrDataPriority);
$smarty->assign('arrDataSuite', $arrDataSuite);
$smarty->assign('arrDataCategory', $arrDataCategory);
$smarty->assign('arrDataKeys', $arrDataKeys);
$smarty->display('resultsBuild.tpl');
?>
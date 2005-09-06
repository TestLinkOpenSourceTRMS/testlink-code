<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsBuild.php,v 1.4 2005/09/06 20:19:40 schlundus Exp $ 
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
testlinkInitPage();

$build = isset($_GET['build']) ? intval($_GET['build']) : null;
if (!isset($_GET['build']))
{
	tlog('$_GET["build"] is not defined');
	exit();
}

$tpID = $_SESSION['testPlanId'];

// get Priority data
$builds = getBuilds($tpID);
$buildName = $builds[$build];

$arrDataPriority = getPriorityReport($tpID,$build);

// get Test Suite data
$arrDataSuite = getBuildMetricsComponent($tpID,$build);
$arrDataCategory = getBuildMetricsCategory($tpID,$build);
$arrDataKeys = getKeywordsReport($tpID,$build);


$smarty = new TLSmarty;
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->assign('build', $build);
$smarty->assign('buildName', $buildName);
$smarty->assign('arrDataPriority', $arrDataPriority);
$smarty->assign('arrDataSuite', $arrDataSuite);
$smarty->assign('arrDataCategory', $arrDataCategory);
$smarty->assign('arrDataKeys', $arrDataKeys);
$smarty->display('resultsBuild.tpl');
?>
<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsBuild.php,v 1.2 2005/08/16 18:00:58 franciscom Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* 
* This page show Metrics of one Build.
*
*/
require('../../config.inc.php');
require_once('common.php');
require_once('results.inc.php');
require_once("../../lib/functions/lang_api.php");
require_once("../../lib/functions/builds.inc.php");
testlinkInitPage();

$build = isset($_GET['build']) ? $_GET['build'] : null;
if (!isset($_GET['build']))
{
	tlog('$_GET["build"] is not defined');
	exit();
}

// get Priority data
$arrDataPriority = getPriorityReport($build);

// get Test Suite data
$arrDataSuite = getBuildMetricsComponent($build);
$arrDataCategory = getBuildMetricsCategory($build);
$arrDataKeys = getKeywordsReport($build);

$builds = getBuilds($_SESSION['testPlanId']);

$smarty = new TLSmarty;
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->assign('build', $build);
$smarty->assign('buildName', $builds[$build]);
$smarty->assign('arrDataPriority', $arrDataPriority);
$smarty->assign('arrDataSuite', $arrDataSuite);
$smarty->assign('arrDataCategory', $arrDataCategory);
$smarty->assign('arrDataKeys', $arrDataKeys);
$smarty->display('resultsBuild.tpl');
?>
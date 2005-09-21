<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsBuild.php,v 1.5 2005/09/21 10:32:01 franciscom Exp $ 
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

$buildID = isset($_GET['build']) ? intval($_GET['build']) : null;
if (!isset($_GET['build']))
{
	tlog('$_GET["build"] is not defined');
	exit();
}

$tpID = $_SESSION['testPlanId'];

$builds = getBuilds($tpID);
$buildName = $builds[$buildID];
$arrDataPriority = getPriorityReport($tpID,$buildID);

// get Test Suite data
$arrDataSuite = getBuildMetricsComponent($tpID,$buildID);
$arrDataCategory = getBuildMetricsCategory($tpID,$buildID);
$arrDataKeys = getKeywordsReport($tpID,$buildID);


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
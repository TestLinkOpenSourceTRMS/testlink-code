<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsAllBuilds.php,v 1.6 2006/10/26 06:47:31 kevinlevy Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* 
* This page show Test Results over all Builds.
*
* @author Francisco Mancardi - 20051002 - refactoring
*/

require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');	
require_once('../functions/results.class.php');
testlinkInitPage($db);
print "KL - 20061025 - work in progress <BR>";

// collect results for Test Plan
//$arrBuilds = getBuilds($db,$_SESSION['testPlanId'], " ORDER BY builds.name ");
$arrBuilds = null;
//$total = getPlanTCNumber($db,$_SESSION['testPlanId']);
$total = null;
$arrData = array();
///SCHLUNDUS
/**
foreach ($arrBuilds as $myBuild=>$name)
{
	// get results for the build
	$buildResults = getPlanStatus($db,$_SESSION['testPlanId'], $myBuild);
	$notRun = $total - ($buildResults['passed'] + $buildResults['failed'] + $buildResults['blocked']);
	
	if ($total)
	{
	///SCHLUNDUS
		array_push($arrData, 
		           array($name, $total, 
			               $buildResults['passed'], round((100 * ($buildResults['passed']/$total)),2),
			               $buildResults['failed'], round((100 * ($buildResults['failed']/$total)),2),
			               $buildResults['blocked'], round((100 * ($buildResults['blocked']/$total)),2),
			               $notRun, round((100 * ($notRun/$total)),2) ));
	}
	else
	{
	///SCHLUNDUS
		array_push($arrData, array($name, 0, 0,0,0,$notRun, 0) );
	
	}
}
*/

$smarty = new TLSmarty;
$smarty->assign('tcs_color', $g_tc_sd_color);
$smarty->assign('title', $_SESSION['testPlanName'] . lang_get('title_metrics_x_build'));
$smarty->assign('arrData', $arrData);
$smarty->display('resultsAllBuilds.tpl');
?>

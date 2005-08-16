<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsAllBuilds.php,v 1.2 2005/08/16 18:00:58 franciscom Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* 
* This page show Test Results over all Builds.
*
*/
require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');	
require_once('results.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

// collect results for Test Plan
$arrBuilds = getBuilds($_SESSION['testPlanId']);
$total = getPlanTCNumber($_SESSION['testPlanId']);

$arrData = array();
///SCHLUNDUS
foreach ($arrBuilds as $myBuild=>$name)
{
	// get results for the build
	$buildResults = getPlanStatus($_SESSION['testPlanId'], $myBuild);
	$notRun = $total - ($buildResults[0] + $buildResults[1] + $buildResults[2]);
	
	if ($total)
	{
	///SCHLUNDUS
		array_push($arrData, array($name, $total, 
			$buildResults[0], round((100 * ($buildResults[0]/$total)),2),
			$buildResults[1], round((100 * ($buildResults[1]/$total)),2),
			$buildResults[2], round((100 * ($buildResults[2]/$total)),2),
			$notRun, round((100 * ($notRun/$total)),2) ));
	}
	else
	{
	///SCHLUNDUS
		array_push($arrData, array($name, 0, 
			0,0,0,$notRun, 0) );
	
	}
}

$smarty = new TLSmarty;
$smarty->assign('tcs_color', $g_tc_sd_color);
$smarty->assign('title', $_SESSION['testPlanName'] . lang_get('title_metrics_x_build'));
$smarty->assign('arrData', $arrData);
$smarty->display('resultsAllBuilds.tpl');
?>

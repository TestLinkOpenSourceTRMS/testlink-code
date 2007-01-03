<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsAllBuilds.php,v 1.11 2007/01/03 02:38:31 kevinlevy Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* 
* This page show Test Results over all Builds.
*
* @author Kevin Levy 20061029 - 1.7 upgrate
*/

require('../../config.inc.php');
require_once('common.php');
require_once('../functions/results.class.php');
testlinkInitPage($db);

$tp = new testplan($db);
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0 ;

$re = new results($db, $tp, 'all', 'a');
$arrDataBuilds = $re->getAggregateBuildResults();

$arrData = null;

$i = 0;
if ($arrDataBuilds != null) {
  while ($buildId = key($arrDataBuilds)) {
   $arr = $arrDataBuilds[$buildId];
   $arrData[$i] = $arr;
   $i++;
   next($arrDataBuilds);
  }
}
$smarty = new TLSmarty;
$smarty->assign('tcs_color', $g_tc_sd_color);
$smarty->assign('title', $_SESSION['testPlanName'] . lang_get('title_metrics_x_build'));
$smarty->assign('arrData', $arrData);
$smarty->display('resultsAllBuilds.tpl');
?>

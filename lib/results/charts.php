<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: charts.php,v $
 * @version $Revision: 1.18 $
 * @modified $Date: 2008/09/20 21:02:54 $ by $Author: schlundus $
 * @author kevin
 *
 * Revisions:
 *	20080812 - havlatm - simplyfied, polite
 *
 **/
require_once('../../config.inc.php');
require_once('common.php');
require_once('../../third_party/charts/charts.php');

define('TL_CHART_WIDTH', 600);
define('TL_CHART_HEIGHT', 400);
define('TL_CHART_BG', '888888');

testlinkInitPage($db);
$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$tplan_id=$_REQUEST['tplan_id'];
$tproject_id=$_SESSION['testprojectID'];

$tplan_info = $tplan_mgr->get_by_id($tplan_id);
$tproject_info = $tproject_mgr->get_by_id($tproject_id);

$tplan_name = $tplan_info['name'];
$tproject_name = $tproject_info['name'];

$pathToCharts = "third_party/charts";
$pathToScripts = "lib/results";
$charts_swf= $pathToCharts . "/charts.swf";
$charts_library= $pathToCharts . "/charts_library";

$chartsUrl=new stdClass();

$chartsUrl->overallPieChart = "{$pathToScripts}/overallPieChart.php?tplan_id={$tplan_id}";
$chartsUrl->keywordBarChart = "{$pathToScripts}/keywordBarChart.php?tplan_id={$tplan_id}";
$chartsUrl->ownerBarChart = "{$pathToScripts}/ownerBarChart.php?tplan_id={$tplan_id}";
$chartsUrl->topLevelSuitesBarChart = "{$pathToScripts}/topLevelSuitesBarChart.php?tplan_id={$tplan_id}";

$charts = array(
	lang_get('overall_metrics') => InsertChart($charts_swf, $charts_library, 
				$chartsUrl->overallPieChart, TL_CHART_WIDTH, TL_CHART_HEIGHT, 
				TL_CHART_BG ),
	lang_get('results_by_keyword') => InsertChart($charts_swf, $charts_library,
	            $chartsUrl->keywordBarChart,TL_CHART_WIDTH, TL_CHART_HEIGHT, TL_CHART_BG ),
	lang_get('results_by_tester') => InsertChart($charts_swf, $charts_library,
	            $chartsUrl->ownerBarChart, TL_CHART_WIDTH, TL_CHART_HEIGHT, TL_CHART_BG),
	lang_get('results_top_level_suites') => InsertChart($charts_swf, $charts_library,
				$chartsUrl->topLevelSuitesBarChart, TL_CHART_WIDTH, TL_CHART_HEIGHT,
				TL_CHART_BG),  
);
                 
                 
$smarty = new TLSmarty();
$smarty->assign("tplan_name",$tplan_name);
$smarty->assign('tproject_name', $tproject_name);

$smarty->assign("charts",$charts);
$smarty->display("charts.tpl");	                   
?>

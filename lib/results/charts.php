<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: charts.php,v $
 * @version $Revision: 1.10 $
 * @modified $Date: 2007/03/25 20:07:19 $  $Author: schlundus $
 * @author kevin
 *
 *
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../../third_party/charts/charts.php');
testlinkInitPage($db);
$testPlanName = $_SESSION['testPlanName']; 

$pathToCharts = "third_party/charts";
$pathToScripts = "lib/results";
$charts = array(
	lang_get('overall_metrics') => InsertChart("{$pathToCharts}/charts.swf", "{$pathToCharts}/charts_library", "{$pathToScripts}/overallPieChart.php", 400, 250 ),
	lang_get('results_by_keyword') => InsertChart("{$pathToCharts}/charts.swf","{$pathToCharts}/charts_library", "{$pathToScripts}/keywordBarChart.php", 800, 600 ),
	lang_get('results_by_tester') => InsertChart("{$pathToCharts}/charts.swf", "{$pathToCharts}/charts_library","{$pathToScripts}/ownerBarChart.php", 800, 600),
	lang_get('results_top_level_suites') => InsertChart("{$pathToCharts}/charts/charts.swf", "{$pathToCharts}/charts_library", "{$pathToScripts}/topLevelSuitesBarChart.php", 800, 600),  
);
                 
$smarty = new TLSmarty();
$smarty->assign("tpname",$testPlanName);
$smarty->assign("charts",$charts);
$smarty->display("charts.tpl");	                   
?>

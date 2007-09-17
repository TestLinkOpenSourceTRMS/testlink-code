<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: charts.php,v $
 * @version $Revision: 1.15 $
 * @modified $Date: 2007/09/17 06:29:07 $  $Author: franciscom $
 * @author kevin
 *
 *
 *
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../../third_party/charts/charts.php');

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


$charts = array(
	lang_get('overall_metrics') => InsertChart($charts_swf,$charts_library, 
	                                           "{$pathToScripts}/overallPieChart.php?tplan_id={$tplan_id}", 
	                                           400, 250 ),
	lang_get('results_by_keyword') => InsertChart($charts_swf,$charts_library,
	                                              "{$pathToScripts}/keywordBarChart.php?tplan_id={$tplan_id}", 
	                                              800, 600 ),
	lang_get('results_by_tester') => InsertChart($charts_swf,$charts_library,
	                                             "{$pathToScripts}/ownerBarChart.php?tplan_id={$tplan_id}", 800, 600),
	lang_get('results_top_level_suites') => 
	InsertChart($charts_swf,$charts_library,
	            "{$pathToScripts}/topLevelSuitesBarChart.php?tplan_id={$tplan_id}", 800, 600),  
);
                 
$smarty = new TLSmarty();
$smarty->assign("tplan_name",$tplan_name);
$smarty->assign('tproject_name', $tproject_name);

$smarty->assign("charts",$charts);
$smarty->display("charts.tpl");	                   
?>

<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: timeCharts.php,v $
 * @version $Revision: 1.1 $
 * @modified $Date: 2007/07/28 23:21:33 $  $Author: kevinlevy $
 * @author kevin
 *
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
$charts_swf= $pathToCharts . "/charts.swf";
$charts_library= $pathToCharts . "/charts_library";

$charts = array(
	lang_get('time_chart') => InsertChart($charts_swf,$charts_library, 
	                                           "{$pathToScripts}/executionsPerIntervalGraph.php", 600, 400));
          
$smarty = new TLSmarty();
$smarty->assign("tplan_name",$testPlanName);
$smarty->assign('tproject_name', $_SESSION['testprojectName'] );

$smarty->assign("charts",$charts);
//print "hello world <BR>";

$smarty->display("charts.tpl");	                   
?>

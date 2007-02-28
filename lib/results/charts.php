<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: charts.php,v $
 * @version $Revision: 1.8 $
 * @modified $Date: 2007/02/28 07:16:22 $  $Author: kevinlevy $
 * @author kevin
 *
 *
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
testlinkInitPage($db);
$testPlanName = $_SESSION['testPlanName']; 

echo "<h2>",$_SESSION['testPlanName']," ",lang_get('graphical_reports'),"</h2><h6>",lang_get('maani_copyright'),"</h6>";
echo "<h3>",lang_get('overall_metrics'),"</h3>";
	

//include charts.php to access the InsertChart function
include "../../third_party/charts/charts.php";
echo InsertChart ( "../../third_party/charts/charts.swf", 
                   "../../third_party/charts/charts_library", "overallPieChart.php", 400, 250 );

echo "<h3>",lang_get('results_by_keyword'),"</h3>";

echo InsertChart ( "../../third_party/charts/charts.swf", 
                   "../../third_party/charts/charts_library", "keywordBarChart.php", 800, 600 );

echo "<h3>",lang_get('results_by_tester'),"</h3>";
echo InsertChart ( "../../third_party/charts/charts.swf", "../../third_party/charts/charts_library", 
                   "ownerBarChart.php", 800, 600);

echo "<h3>",lang_get('results_top_level_suites'),"</h3>";
echo InsertChart ( "../../third_party/charts/charts.swf", "../../third_party/charts/charts_library", 
                   "topLevelSuitesBarChart.php", 800, 600);
?>

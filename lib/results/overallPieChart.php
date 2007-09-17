<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: overallPieChart.php,v 1.4 2007/09/17 06:29:07 franciscom Exp $ 
*
* @author	Kevin Levy
*/
require_once('../functions/results.class.php');
require_once('../functions/testplan.class.php');
require_once("../../third_party/charts/charts.php");
testlinkInitPage($db);

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$tplan_id=$_REQUEST['tplan_id'];
$tproject_id=$_SESSION['testprojectID'];

$tplan_info = $tplan_mgr->get_by_id($tplan_id);
$tproject_info = $tproject_mgr->get_by_id($tproject_id);

$re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,
                  ALL_TEST_SUITES,ALL_BUILDS);


$totals = $re->getTotalsForPlan();

$totalPass = $totals['pass'];
$totalFail = $totals['fail'];
$totalBlocked = $totals['blocked'];
$totalNotRun = $totals['notRun'];

$chart[ 'chart_data' ] = array ( 
								array("", 
									  lang_get($g_tc_status_verbose_labels["passed"]), 
									  lang_get($g_tc_status_verbose_labels["failed"]), 
									  lang_get($g_tc_status_verbose_labels["blocked"]), 
									  lang_get($g_tc_status_verbose_labels["not_run"]),
									 ), 
								array ( "", $totalPass, $totalFail, $totalBlocked, $totalNotRun ) );
$chart[ 'chart_grid_h' ] = array ( 'alpha'=>20, 'color'=>"000000", 'thickness'=>1, 'type'=>"solid" );
$chart[ 'chart_rect' ] = array ( 'positive_color'=>"ffffff", 'positive_alpha'=>20, 'negative_color'=>"ff0000", 'negative_alpha'=>10 );
$chart[ 'chart_type' ] = "pie";
$chart[ 'chart_value' ] = array ( 'color'=>"ffffff", 'alpha'=>90, 'font'=>"arial", 'bold'=>true, 'size'=>10, 'position'=>"inside", 'prefix'=>"", 'suffix'=>"", 'decimals'=>0, 'separator'=>"", 'as_percentage'=>true );

$chart[ 'draw' ] = array ( array ( 'type'=>"text", 'color'=>"000000", 'alpha'=>10, 'font'=>"arial", 'rotation'=>0, 'bold'=>true, 'size'=>30, 'x'=>0, 'y'=>230, 'width'=>400, 'height'=>250, 'text'=>"|||||||||||||||||||||||||||||||||||||||||||||||", 'h_align'=>"center", 'v_align'=>"bottom" )) ;

$chart[ 'legend_label' ] = array ( 'layout'=>"horizontal", 'bullet'=>"circle", 'font'=>"arial", 'bold'=>true, 'size'=>13, 'color'=>"ffffff", 'alpha'=>85 ); 
$chart[ 'legend_rect' ] = array ( 'fill_color'=>"ffffff", 'fill_alpha'=>10, 'line_color'=>"000000", 'line_alpha'=>0, 'line_thickness'=>0 ); 

$chart[ 'series_color' ] = array ( "00FF00", "FF0000", "0000FF", "000000"); 
$chart[ 'series_explode' ] = array ( 20, 0, 50 );

SendChartData($chart);
?>

<?php

//require('../../config.inc.php');
//require_once('common.php');
//require_once('builds.inc.php');
//require_once('timer.php');
require_once('../functions/results.class.php');
require_once('../functions/testplan.class.php');
//require_once('displayMgr.php');
testlinkInitPage($db);
$tpID = $_SESSION['testPlanId']; 
$tp = new testplan($db);
$builds_to_query = 'a';
$suitesSelected = 'all';
$re = new results($db, $tp, $suitesSelected, $builds_to_query);
$totals = $re->getTotalsForPlan();

//include charts.php to access the SendChartData function
include "../../third_party/charts/charts.php";

$totalPass = $totals['pass'];
$totalFail = $totals['fail'];
$totalBlocked = $totals['blocked'];
$totalNotRun = $totals['notRun'];


$chart[ 'chart_data' ] = array ( array ( "", "Pass", "Fail", "Blocked", "Not Run"), array ( "", $totalPass, $totalFail, $totalBlocked, $totalNotRun ) );
$chart[ 'chart_grid_h' ] = array ( 'alpha'=>20, 'color'=>"000000", 'thickness'=>1, 'type'=>"solid" );
$chart[ 'chart_rect' ] = array ( 'positive_color'=>"ffffff", 'positive_alpha'=>20, 'negative_color'=>"ff0000", 'negative_alpha'=>10 );
$chart[ 'chart_type' ] = "pie";
$chart[ 'chart_value' ] = array ( 'color'=>"ffffff", 'alpha'=>90, 'font'=>"arial", 'bold'=>true, 'size'=>10, 'position'=>"inside", 'prefix'=>"", 'suffix'=>"", 'decimals'=>0, 'separator'=>"", 'as_percentage'=>true );

$chart[ 'draw' ] = array ( array ( 'type'=>"text", 'color'=>"000000", 'alpha'=>10, 'font'=>"arial", 'rotation'=>0, 'bold'=>true, 'size'=>30, 'x'=>0, 'y'=>140, 'width'=>400, 'height'=>150, 'text'=>"|||||||||||||||||||||||||||||||||||||||||||||||", 'h_align'=>"center", 'v_align'=>"bottom" )) ;

$chart[ 'legend_label' ] = array ( 'layout'=>"horizontal", 'bullet'=>"circle", 'font'=>"arial", 'bold'=>true, 'size'=>13, 'color'=>"ffffff", 'alpha'=>85 ); 
$chart[ 'legend_rect' ] = array ( 'fill_color'=>"ffffff", 'fill_alpha'=>10, 'line_color'=>"000000", 'line_alpha'=>0, 'line_thickness'=>0 ); 

$chart[ 'series_color' ] = array ( "00FF00", "FF0000", "0000FF", "000000"); 
$chart[ 'series_explode' ] = array ( 20, 0, 50 );

SendChartData($chart);

?>
<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: executionsPerIntervalGraph.php,v 1.1 2007/07/28 23:23:13 kevinlevy Exp $ 
*
* @author	Kevin Levy
*/
require_once('../functions/results.class.php');
require_once('../functions/testplan.class.php');
require_once("../../third_party/charts/charts.php");
testlinkInitPage($db);


/** ***************************************** */

$arrayOfDates = array();
$ts = time();

// intervals can be days, weeks, or months
$intervalType = "weeks";
// # of intervals the report will span 

// I want the last 2 weeks of data
$numberOfIntervals = 2;

// today, 1 week ago, 2 weeks ago
$numberOfDates = $numberOfIntervals + 1; 

# figure out what 1 day is in seconds
$seconds_in_day = 24 * 60 * 60;
$interval_time = 0;
if ($intervalType == "days") {
	$interval_time = $seconds_in_day;
}
elseif ($intervalType == "weeks") {
			$interval_time = $seconds_in_day * 7;
}
elseif ($intervalType == "months") {
	$interval_time = $seconds_in_day * 30;
}

for ($i = 0; $i < $numberOfDates; $i++) {
	$dateWithoutTime = date( "Y-m-d", ( $ts - ($interval_time * $i)));
	$date = $dateWithoutTime . " 23:59:59";
	array_push($arrayOfDates, $date);
}

// I want the array to store the dates 
// in increasing order
$arrayOfDates = array_reverse($arrayOfDates);
// END CREATE DATES ARRAY
$tpID = $_SESSION['testPlanId']; 
$tp = new testplan($db);
$tcsExecuted = array();
array_push($tcsExecuted, "total");
$startTime = $arrayOfDates[0];
// query all builds and all suites
$suitesSelected = 'all';
$buildsToQuery = 'a';

// do not query by keyword, owner, lastResult or executor
$lastResultSelected = 'a';
$keywordIdSelected = 0;
$ownerSelected = null;

$re = new results($db, $tp, $suitesSelected, $buildsToQuery, $lastResultSelected, $keywordIdSelected, $ownerSelected);
$suiteStructure = $re->getSuiteStructure();
$flatArray = $re->getFlatArray();
$linked_tcversions = $re->getLinkedTCVersions();

for ($i = 1; $i < sizeof($arrayOfDates); $i++) {
	$startTime = $arrayOfDates[0];
	$endTime = $arrayOfDates[$i];
	$re = new results($db, $tp, $suitesSelected, $buildsToQuery, $lastResultSelected, $keywordIdSelected, $ownerSelected, $startTime, $endTime, null, null, null, $suiteStructure, $flatArray, $linked_tcversions);
	$totals = $re->getTotalsForPlan();
	$totalPass = $totals['pass'];
	$totalFail = $totals['fail'];
	$totalBlocked = $totals['blocked'];
	$totalNotRun = $totals['notRun'];
	$totalExecuted = $totalPass + $totalFail + $totalBlocked;
	array_push($tcsExecuted, $totalExecuted);
}

//$xAxisTicks = array_values($arrayOfDates);
/**
$xAxisTicks = array("A","B","C");
$x_length = sizeof($xAxisTicks);
$maxY = 10;
$chart[ 'axis_category' ] = array ( 'size'=>$x_length, 'color'=>"000000", 'alpha'=>0, 'font'=>"arial", 'bold'=>true, 'skip'=>0 ,'orientation'=>"horizontal" ); 
$chart[ 'axis_ticks' ] = array ( 'value_ticks'=>true, 'category_ticks'=>true, 'major_thickness'=>2, 'minor_thickness'=>1, 'minor_count'=>1, 'major_color'=>"000000", 'minor_color'=>"222222" ,'position'=>"outside" );
$chart[ 'axis_value' ] = array (  'min'=>0, 'max'=>$maxY, 'font'=>"arial", 'bold'=>true, 'size'=>10, 'color'=>"ffffff", 'alpha'=>50, 'steps'=>6, 'prefix'=>"", 'suffix'=>"", 'decimals'=>0, 'separator'=>"", 'show_min'=>true );
$chart[ 'chart_border' ] = array ( 'color'=>"000000", 'top_thickness'=>2, 'bottom_thickness'=>2, 'left_thickness'=>2, 'right_thickness'=>2 );
$chart[ 'chart_data' ] = array ($xAxisTicks, $tcsExecuted);
$chart[ 'chart_grid_h' ] = array ( 'alpha'=>10, 'color'=>"000000", 'thickness'=>1, 'type'=>"solid" );
$chart[ 'chart_grid_v' ] = array ( 'alpha'=>10, 'color'=>"000000", 'thickness'=>1, 'type'=>"solid" );
$chart[ 'chart_pref' ] = array ( 'line_thickness'=>2, 'point_shape'=>"circle", 'fill_shape'=>false );
$chart[ 'chart_rect' ] = array ( 'x'=>40, 'y'=>25, 'width'=>400, 'height'=>300, 'positive_color'=>"000000", 'positive_alpha'=>30, 'negative_color'=>"ff0000",  'negative_alpha'=>10 );
$chart[ 'chart_type' ] = "Line";
$chart[ 'chart_value' ] = array ( 'prefix'=>"", 'suffix'=>"", 'decimals'=>0, 'separator'=>"", 'position'=>"cursor", 'hide_zero'=>true, 'as_percentage'=>false, 'font'=>"arial", 'bold'=>true, 'size'=>12, 'color'=>"ffffff", 'alpha'=>75 );
$chart[ 'draw' ] = array ( array ( 'type'=>"text", 'color'=>"ffffff", 'alpha'=>15, 'font'=>"arial", 'rotation'=>-90, 'bold'=>true, 'size'=>20, 'x'=>0, 'y'=>348, 'width'=>300, 'height'=>150, 'text'=>"TCs Executed", 'h_align'=>"center", 'v_align'=>"top" ),
                           array ( 'type'=>"text", 'color'=>"000000", 'alpha'=>15, 'font'=>"arial", 'rotation'=>0, 'bold'=>true, 'size'=>20, 'x'=>50, 'y'=>50, 'width'=>320, 'height'=>300, 'text'=>"time", 'h_align'=>"left", 'v_align'=>"bottom" ) );
$chart[ 'legend_rect' ] = array ( 'x'=>-100, 'y'=>-100, 'width'=>10, 'height'=>10, 'margin'=>10 ); 
$chart[ 'series_color' ] = array ( "77bb11", "cc5511" ); 
SendChartData($chart);
*/

$chart[ 'axis_category' ] = array (  'size'=>16, 'color'=>"000000", 'alpha'=>75, 'skip'=>0 ,'orientation'=>"horizontal" ); 
$chart[ 'axis_ticks' ] = array ( 'value_ticks'=>false, 'category_ticks'=>true, 'major_thickness'=>2, 'minor_thickness'=>1, 'minor_count'=>1, 'major_color'=>"000000", 'minor_color'=>"222222" ,'position'=>"inside" );
$chart[ 'axis_value' ] = array ( 'min'=>0, 'size'=>10, 'color'=>"ffffff", 'alpha'=>50, 'steps'=>6, 'prefix'=>"", 'suffix'=>"", 'decimals'=>0, 'separator'=>"", 'show_min'=>false );		
$chart[ 'chart_data' ] = array ( array ( "", "week1", "week2"), $tcsExecuted );
$chart[ 'chart_grid_h' ] = array ( 'alpha'=>10, 'color'=>"000000", 'thickness'=>1 );
$chart[ 'chart_pref' ] = array ( 'line_thickness'=>2, 'point_shape'=>"circle", 'fill_shape'=>false );
$chart[ 'chart_rect' ] = array ( 'x'=>50, 'y'=>100, 'width'=>320, 'height'=>150, 'positive_color'=>"ffffff", 'positive_alpha'=>50, 'negative_color'=>"000000", 'negative_alpha'=>10 );
$chart[ 'chart_transition' ] = array ( 'type'=>"slide_left", 'delay'=>.5, 'duration'=>.5, 'order'=>"series" );
$chart[ 'chart_type' ] = "Line";
$chart[ 'chart_value' ] = array ( 'position'=>"cursor", 'size'=>12, 'color'=>"000000", 'background_color'=>"aaff00", 'alpha'=>80 );
//$chart[ 'draw' ] = array ( array ( 'transition'=>"dissolve", 'delay'=>0, 'duration'=>.5, 'type'=>"text", 'color'=>"000000", 'alpha'=>8, 'font'=>"Arial", 'rotation'=>0, 'bold'=>true, 'size'=>48, 'x'=>8, 'y'=>7, 'width'=>400, 'height'=>75, 'text'=>"Executions", 'h_align'=>"center", 'v_align'=>"bottom" ) );
$chart[ 'legend_label' ] = array ( 'layout'=>"horizontal", 'bullet'=>"line", 'font'=>"arial", 'bold'=>true, 'size'=>13, 'color'=>"ffffff", 'alpha'=>65 ); 
$chart[ 'legend_rect' ] = array ( 'x'=>50, 'y'=>75, 'width'=>320, 'height'=>5, 'margin'=>5, 'fill_color'=>"000000", 'fill_alpha'=>7, 'line_color'=>"000000", 'line_alpha'=>0, 'line_thickness'=>0 ); 
$chart[ 'legend_transition' ] = array ( 'type'=>"dissolve", 'delay'=>0, 'duration'=>.5 );
$chart[ 'series_color' ] = array ( "ff4444", "ffff00", "8844ff" ); 
$chart [ 'series_explode' ] = array ( 400 );

SendChartData ( $chart );

?>

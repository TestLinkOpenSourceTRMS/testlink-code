<?php
include "../../third_party/charts/charts.php";

$chart[ 'axis_value' ] = array ( 'font'=>"arial", 'bold'=>true, 'size'=>10, 'color'=>"000000", 'alpha'=>50, 'steps'=>6, 'prefix'=>"", 'suffix'=>"", 'decimals'=>0, 'separator'=>"", 'show_min'=>true );

$chart[ 'chart_border' ] = array ( 'color'=>"000000", 'top_thickness'=>0, 'bottom_thickness'=>3, 'left_thickness'=>0, 'right_thickness'=>0 );
$chart[ 'chart_data' ] = array ( array ( "", "P1", "P2", "P3" ), array ( "pass", 1, 5, 10), array ( "fail", 1, 5, 10), array ("blocked", 1, 5, 10), array ("not run", 1, 5, 10));
$chart[ 'chart_grid_h' ] = array ( 'alpha'=>20, 'color'=>"000000", 'thickness'=>1, 'type'=>"solid" );
$chart[ 'chart_grid_v' ] = array ( 'alpha'=>20, 'color'=>"000000", 'thickness'=>1, 'type'=>"dashed" );
$chart[ 'chart_rect' ] = array ( 'x'=>125, 'y'=>65, 'width'=>250, 'height'=>200, 'positive_color'=>"ffffff", 'negative_color'=>"000000", 'positive_alpha'=>75, 'negative_alpha'=>15 );
$chart[ 'chart_transition' ] = array ( 'type'=>"drop", 'delay'=>0, 'duration'=>2, 'order'=>"series" );
$chart[ 'chart_type' ] = "stacked column"; 

$chart[ 'draw' ] = array ( array ( 'transition'=>"slide_up", 'delay'=>1, 'duration'=>.5, 'type'=>"text", 'color'=>"000033", 'alpha'=>15, 'font'=>"arial", 'rotation'=>-90, 'bold'=>true, 'size'=>64, 'x'=>0, 'y'=>295, 'width'=>300, 'height'=>50, 'text'=>"PRIORITY", 'h_align'=>"right", 'v_align'=>"middle" ),
                           array ( 'transition'=>"slide_up", 'delay'=>1, 'duration'=>.5, 'type'=>"text", 'color'=>"ffffff", 'alpha'=>40, 'font'=>"arial", 'rotation'=>-90, 'bold'=>true, 'size'=>25, 'x'=>30, 'y'=>300, 'width'=>300, 'height'=>50, 'text'=>"report", 'h_align'=>"right", 'v_align'=>"middle" ) );

$chart[ 'legend_label' ] = array ( 'layout'=>"horizontal", 'font'=>"arial", 'bold'=>true, 'size'=>13, 'color'=>"444466", 'alpha'=>90 ); 
$chart[ 'legend_rect' ] = array ( 'x'=>125, 'y'=>10, 'width'=>250, 'height'=>10, 'margin'=>5, 'fill_color'=>"ffffff", 'fill_alpha'=>35, 'line_color'=>"000000", 'line_alpha'=>0, 'line_thickness'=>0 ); 
$chart[ 'legend_transition' ] = array ( 'type'=>"slide_left", 'delay'=>0, 'duration'=>1 );

$chart[ 'series_color' ] = array ("00FF00", "FF0000", "0000FF", "000000");

SendChartData ( $chart );
?>
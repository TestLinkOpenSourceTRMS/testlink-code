<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: overallPieChart.php,v 1.5 2008/05/11 16:56:37 franciscom Exp $ 
*
* @author	Kevin Levy
*
* - PHP autoload feature is used to load classes on demand
*/
require_once('../../config.inc.php');
require_once('results.class.php');
require_once("../../third_party/charts/charts.php");

testlinkInitPage($db);

// Important:
// Elements order in chart array is CRITIC
//
// Good strings to send to chart rendering engine
// 
// Passed  Failed  Blocked  Not Run   0  0  0  3  pie    |||||||||||||||||||||||||||||||||||||||||||||||   00FF00  FF0000  0000FF  000000    20  0  50  
// Not Run  Passed  Failed  Blocked   1  1  0  1  pie    |||||||||||||||||||||||||||||||||||||||||||||||   000000  00FF00  FF0000  0000FF    20  0  50  
//

$cdata = getChartData($db);
$chart['chart_data'] = $cdata->labels_values;
$chart['chart_grid_h'] = array ( 'alpha'=>20, 'color'=>"000000", 'thickness'=>1, 'type'=>"solid" );
$chart['chart_rect'] = array ( 'positive_color'=>"ffffff", 'positive_alpha'=>20, 'negative_color'=>"ff0000", 'negative_alpha'=>10 );
$chart['chart_type'] = "pie";
$chart['chart_value'] = array ( 'color'=>"ffffff", 'alpha'=>90, 'font'=>"arial", 'bold'=>true, 'size'=>10, 'position'=>"inside", 'prefix'=>"", 'suffix'=>"", 'decimals'=>0, 'separator'=>"", 'as_percentage'=>true );
$chart['draw'] = array ( array ( 'type'=>"text", 'color'=>"000000", 'alpha'=>10, 'font'=>"arial", 
                                 'rotation'=>0, 'bold'=>true, 'size'=>30, 'x'=>0, 'y'=>230, 'width'=>400, 'height'=>250, 
                                 'text'=>"|||||||||||||||||||||||||||||||||||||||||||||||", 'h_align'=>"center", 'v_align'=>"bottom" )) ;
$chart['legend_label'] = array ( 'layout'=>"horizontal", 'bullet'=>"circle", 'font'=>"arial", 'bold'=>true, 'size'=>13, 'color'=>"ffffff", 'alpha'=>85 ); 
$chart['legend_rect'] = array ( 'fill_color'=>"ffffff", 'fill_alpha'=>10, 'line_color'=>"000000", 'line_alpha'=>0, 'line_thickness'=>0 ); 
$chart['series_color'] = $cdata->series_color; 
$chart['series_explode'] = array ( 20, 0, 50 );

SendChartData($chart);




/*
  function: getChartData

  args :
  
  returns: 

*/
function getChartData(&$dbHandler)
{
    $obj=new stdClass();   
    $resultsCfg=config_get('results');
   
    $tplan_mgr = new testplan($dbHandler);
    $tproject_mgr = new testproject($dbHandler);
    
    $tplan_id=$_REQUEST['tplan_id'];
    $tproject_id=$_SESSION['testprojectID'];
    
    $tplan_info = $tplan_mgr->get_by_id($tplan_id);
    $tproject_info = $tproject_mgr->get_by_id($tproject_id);
    
    $re = new results($dbHandler, $tplan_mgr, $tproject_info, $tplan_info,
                      ALL_TEST_SUITES,ALL_BUILDS);
    
    $totals = $re->getTotalsForPlan();
    
    // Will exclude 'total' key
    unset($totals['total']);
    
    $values=array('');
    $labels=array('');
    foreach( $totals as $key => $value)
    {
        $values[]=$value;
        $labels[]=lang_get($resultsCfg['status_label'][$key]);
        $obj->series_color[]=$resultsCfg['charts']['status_colour'][$key];
    }
    
    $obj->labels_values=array($labels,$values);
    
    return $obj;
}
?>
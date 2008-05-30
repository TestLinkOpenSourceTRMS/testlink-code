<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: keywordBarChart.php,v 1.8 2008/05/30 09:31:25 franciscom Exp $ 
*
* @author	Kevin Levy
*
* - PHP autoload feature is used to load classes on demand
*
* rev: 20080511 - franciscom - refactored to manage automatically new user defined status
*                              Removed fancy transistion
*
*/
require_once('../../config.inc.php');
require_once('../../third_party/charts/charts.php');
require_once('results.class.php');

testlinkInitPage($db);

// Output string to chart engine
// BLUE GREEN RED  Passed  0 0 0  Failed  0 0 0  Blocked  0 1 1  Frio  1 1 0  Not Run  1 1 1   
// stacked column  Keywords  report     00FF00  FF0000  0000FF  CC0000 000000  

$cdata = getChartData($db);
$chart['chart_data'] = $cdata->chart_data;

$chart['axis_value'] = array ( 'font'=>"arial", 'bold'=>true, 'size'=>10, 'color'=>"000000", 'alpha'=>50, 'steps'=>6, 'prefix'=>"", 'suffix'=>"", 'decimals'=>0, 'separator'=>"", 'show_min'=>true );

$chart['chart_border'] = array ( 'color'=>"000000", 'top_thickness'=>0, 'bottom_thickness'=>3, 'left_thickness'=>0, 'right_thickness'=>0 );


$chart['chart_grid_h'] = array ( 'alpha'=>20, 'color'=>"000000", 'thickness'=>1, 'type'=>"solid" );
$chart['chart_grid_v'] = array ( 'alpha'=>20, 'color'=>"000000", 'thickness'=>1, 'type'=>"dashed" );
$chart['chart_rect'] = array ( 'x'=>125, 'y'=>75, 'width'=>500, 'height'=>400, 'positive_color'=>"ffffff", 'negative_color'=>"000000", 'positive_alpha'=>75, 'negative_alpha'=>15 );

// Removed fancy transistion
// $chart['chart_transition'] = array ( 'type'=>"drop", 'delay'=>0, 'duration'=>2, 'order'=>"series" );
$chart['draw'] = array();

$chart['chart_type'] = "stacked column"; 

$chart['axis_category'] = array ('orientation'=>"diagonal_down");

// Removed fancy transistion
// $chart['draw'] = array ( array ( 'transition'=>"slide_up", 'delay'=>1, 'duration'=>.5, 'type'=>"text", 'color'=>"000033", 'alpha'=>15, 'font'=>"arial", 'rotation'=>-90, 'bold'=>true, 'size'=>64, 'x'=>0, 'y'=>295, 'width'=>300, 'height'=>60, 'text'=>"Keywords", 'h_align'=>"right", 'v_align'=>"middle" ),
//                          array ( 'transition'=>"slide_up", 'delay'=>1, 'duration'=>.5, 'type'=>"text", 'color'=>"ffffff", 'alpha'=>40, 'font'=>"arial", 'rotation'=>-90, 'bold'=>true, 'size'=>25, 'x'=>35, 'y'=>300, 'width'=>300, 'height'=>50, 'text'=>"report", 'h_align'=>"right", 'v_align'=>"middle" ) );
$labels = new stdClass();
$labels->report=lang_get('chart_report');
$labels->keywords=lang_get('chart_keywords');

$chart['draw'] = array ( array ('type'=>"text", 'color'=>"000033", 'alpha'=>15, 'font'=>"arial", 'rotation'=>-90, 
                                'bold'=>true, 'size'=>40, 'x'=>0, 'y'=>400, 'width'=>300, 'height'=>60, 
                                'text'=> $labels->keywords, 'h_align'=>"right", 'v_align'=>"middle" ),
                         array ('type'=>"text", 'color'=>"ffffff", 'alpha'=>40, 'font'=>"arial", 'rotation'=>-90, 
                                'bold'=>true, 'size'=>25, 'x'=>35, 'y'=>300, 'width'=>300, 'height'=>50, 
                                'text'=>$labels->report, 'h_align'=>"right", 'v_align'=>"middle" ) );


$chart['legend_label'] = array ( 'layout'=>"horizontal", 'font'=>"arial", 'bold'=>true, 'size'=>13, 'color'=>"444466", 'alpha'=>90 ); 
$chart['legend_rect'] = array ( 'x'=>125, 'y'=>10, 'width'=>250, 'height'=>10, 'margin'=>5, 'fill_color'=>"ffffff", 'fill_alpha'=>35, 'line_color'=>"000000", 'line_alpha'=>0, 'line_thickness'=>0 ); 

// Removed fancy transistion
// $chart['legend_transition'] = array ( 'type'=>"slide_left", 'delay'=>0, 'duration'=>1 );
$chart['legend_transition'] = array();

$chart['series_color'] = $cdata->series_color;

SendChartData ( $chart );

/*
  function: getChartData

  args :
  
  returns: 

*/
function getChartData(&$dbHandler)
{
    $keywordNames=array('');
    $totals=null; 
    $tplan_mgr = new testplan($dbHandler);
    $tproject_mgr = new testproject($dbHandler);
    
    $tplan_id=$_REQUEST['tplan_id'];
    $tproject_id=$_SESSION['testprojectID'];
    
    $tplan_info = $tplan_mgr->get_by_id($tplan_id);
    $tproject_info = $tproject_mgr->get_by_id($tproject_id);
    
    $re = new results($dbHandler, $tplan_mgr, $tproject_info, $tplan_info,
                      ALL_TEST_SUITES,ALL_BUILDS);
    
    
    $keywordResults = $re->getAggregateKeywordResults();
    
    if( !is_null($keywordResults) )
    {
        // all array must have same number of items
        // keywordNames: used to dsplay name in X axis
        //               first element must be leave clear
        //
        $keywordNames=array('');
        foreach($keywordResults as $keyword_id => $elem)
        {
            $keywordNames[] = $elem['keyword_name'];   
            foreach($elem['details'] as $status => $value)
            {
                $totals[$status][]=$value['qty'];  
            }    
        }  
    }

    // all array must have same number of items
    // keywordNames: used to dsplay name in X axis
    //               first element must be leave clear
    //
    // results array: first element is status label
    
    $obj = new stdClass();
    $obj->chart_data = array($keywordNames);
    $obj->series_color=null;
    $resultsCfg=config_get('results');
    if(!is_null($totals) )
    {
        foreach( $totals as $status => $values)
        {
            array_unshift($values,lang_get($resultsCfg['status_label'][$status]));
            $obj->chart_data[]=$values;
            $obj->series_color[]=$resultsCfg['charts']['status_colour'][$status];
        }
    }
    return $obj;    
    
}

?>

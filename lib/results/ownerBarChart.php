<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: ownerBarChart.php,v 1.7 2008/08/12 22:17:46 havlat Exp $ 
*
* @author	Kevin Levy
*
* - PHP autoload feature is used to load classes on demand
*
* rev: 20080511 - franciscom - refactored to manage automatically new user defined status
*                              Removed fancy transistion
*/
require_once('../../config.inc.php');
require_once("../../third_party/charts/charts.php");
require_once('results.class.php');


testlinkInitPage($db);
$cdata = getChartData($db);
$chart['chart_data'] = $cdata->chart_data;
$chart['chart_type'] = "stacked column"; 

$chart['axis_value'] = array ( 'font'=>"arial", 'bold'=>true, 'size'=>10, 
		'color'=>"000000", 'alpha'=>50, 'steps'=>6, 'prefix'=>"", 'suffix'=>"", 
		'decimals'=>0, 'separator'=>"", 'show_min'=>true );
$chart['axis_category'] = array ('orientation'=>"diagonal_down");

$chart['chart_rect'] = array ( 'x'=>30, 'y'=>20, 'height'=>250, 
	'positive_color'=>"EEEEEE", 'negative_color'=>"000000", 'positive_alpha'=>20, 'negative_alpha'=>15 );



$chart['legend_label'] = array ( 'layout'=>"horizontal", 'font'=>"arial", 
		'bold'=>true, 'size'=>13, 'color'=>"444466", 'alpha'=>90 ); 
$chart['legend_rect'] = array ( 'x'=>50, 'y'=>360, 'width'=>350,  
		'margin'=>5, 'fill_color'=>"ffffff", 'fill_alpha'=>35); 

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
    $tplan_mgr = new testplan($dbHandler);
    $tproject_mgr = new testproject($dbHandler);
    
    $tplan_id=$_REQUEST['tplan_id'];
    $tproject_id=$_SESSION['testprojectID'];
    
    $tplan_info = $tplan_mgr->get_by_id($tplan_id);
    $tproject_info = $tproject_mgr->get_by_id($tproject_id);
    
    $re = new results($dbHandler, $tplan_mgr, $tproject_info, $tplan_info,
                      ALL_TEST_SUITES,ALL_BUILDS);
    
    $testerResults = $re->getAggregateOwnerResults();
    
    if( !is_null($testerResults) )
    {
        // all array must have same number of items
        // keywordNames: used to dsplay name in X axis
        //               first element must be leave clear
        //
        $testerNames=array('');
        foreach($testerResults as $tester_id => $elem)
        {
            $testerNames[] = $elem['tester_name'];   
            foreach($elem['details'] as $status => $value)
            {
                $totals[$status][]=$value['qty'];  
            }    
        }  
    }


    // all array must have same number of items
    // testerNames: used to dsplay name in X axis
    //               first element must be leave clear
    //
    // results array: first element is status label
    //
    $obj = new stdClass();
    $obj->chart_data = array($testerNames);
    $resultsCfg=config_get('results');
    foreach( $totals as $status => $values)
    {
        array_unshift($values,lang_get($resultsCfg['status_label'][$status]));
        $obj->chart_data[]=$values;
        $obj->series_color[]=$resultsCfg['charts']['status_colour'][$status];
    }
    return $obj;
}
?>

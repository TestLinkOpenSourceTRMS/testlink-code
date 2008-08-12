<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * $Id: overallPieChart.php,v 1.6 2008/08/12 22:17:46 havlat Exp $ 
 *
 * @author	Kevin Levy
 *
 * - PHP autoload feature is used to load classes on demand
 * 
 * Revisions:
 *	20080812 - havlatm - simplyfied, polite
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
$chart['chart_type'] = "pie";
$chart['chart_data'] = $cdata->labels_values;
$chart['chart_value'] = array ( 'color'=>"ffffff", 'alpha'=>90, 'font'=>"arial", 
		'bold'=>true, 'size'=>10, 'position'=>"inside", 'prefix'=>"", 'suffix'=>"", 
		'decimals'=>0, 'separator'=>"", 'as_percentage'=>true );
$chart['legend_label'] = array ( 'layout'=>"horizontal",  
		'font'=>"arial", 'bold'=>true, 'size'=>13, 'color'=>"ffffff" ); 
$chart['legend_rect'] = array ( 'fill_color'=>"AAAAAA", 'margin'=>30 ); 
$chart['series_color'] = $cdata->series_color; 

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
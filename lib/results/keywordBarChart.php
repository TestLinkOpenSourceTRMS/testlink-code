<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * $Id: keywordBarChart.php,v 1.10 2008/09/20 21:02:54 schlundus Exp $ 
 *
 * @author	Kevin Levy
 *
 * - PHP autoload feature is used to load classes on demand
 *
 * rev: 20080511 - franciscom - refactored to manage automatically new user defined status
 *                              Removed fancy transistion
 *	20080812 - havlatm - simplyfied, polite
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

$chart['chart_type'] = "stacked column"; 
$chart['axis_category'] = array ('orientation'=>"diagonal_down");
$chart['axis_value'] = array ( 'font'=>"arial", 'bold'=>true, 'size'=>10, 
		'color'=>"000000", 'alpha'=>50, 'steps'=>6, 'prefix'=>"", 'suffix'=>"", 
		'decimals'=>0, 'separator'=>"", 'show_min'=>true );

$chart['chart_grid_h'] = array ( 'alpha'=>20, 'color'=>"000000", 'thickness'=>1, 'type'=>"solid" );
$chart['chart_rect'] = array ( 'x'=>30, 'y'=>20, 'height'=>250, 
		'positive_color'=>"ffffff", 'negative_color'=>"000000", 'positive_alpha'=>75, 'negative_alpha'=>15 );


$chart['legend_label'] = array ( 'layout'=>"horizontal", 'font'=>"arial", 
		'bold'=>true, 'size'=>13, 'color'=>"444466", 'alpha'=>90 ); 
$chart['legend_rect'] = array ( 'x'=>50, 'y'=>360, 'width'=>350,  
		'margin'=>5, 'fill_color'=>"ffffff", 'fill_alpha'=>35); 

$chart['series_color'] = $cdata->series_color;

SendChartData ( $chart );

/*
  function: getChartData

  args :
  
  returns: 

*/
function getChartData(&$dbHandler)
{
    $keywordNames = array('');
    $totals = null; 
    $tplan_mgr = new testplan($dbHandler);
    $tproject_mgr = new testproject($dbHandler);
    
    $tplan_id = $_REQUEST['tplan_id'];
    $tproject_id = $_SESSION['testprojectID'];
    
    $tplan_info = $tplan_mgr->get_by_id($tplan_id);
    $tproject_info = $tproject_mgr->get_by_id($tproject_id);
    
    $re = new results($dbHandler, $tplan_mgr, $tproject_info, $tplan_info,
                      ALL_TEST_SUITES,ALL_BUILDS);
    
    $keywordResults = $re->getAggregateKeywordResults();
    if(!is_null($keywordResults))
    {
        // all array must have same number of items
        // keywordNames: used to dsplay name in X axis
        //               first element must be leave clear
        //
        foreach($keywordResults as $keyword_id => $elem)
        {
            $keywordNames[] = htmlspecialchars($elem['keyword_name']);
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
    $obj->series_color = null;
    $resultsCfg = config_get('results');
    if(!is_null($totals))
    {
        foreach($totals as $status => $values)
        {
            array_unshift($values,lang_get($resultsCfg['status_label'][$status]));
            $obj->chart_data[] = $values;
            $obj->series_color[] = $resultsCfg['charts']['status_colour'][$status];
        }
    }
    return $obj;    
}
?>

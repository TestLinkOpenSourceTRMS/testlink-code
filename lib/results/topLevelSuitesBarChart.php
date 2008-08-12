<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: topLevelSuitesBarChart.php,v $
 * @version $Revision: 1.8 $
 * @modified $Date: 2008/08/12 22:17:46 $ by $Author: havlat $
 *
 * @author	Kevin Levy
 *
 * - PHP autoload feature is used to load classes on demand
 *
 * rev: 20080511 - franciscom - refactored to manage automatically new user defined status
 *                              Removed fancy transistion
 *	20080812 - havlatm - simplyfied, polite
 *
 */
require_once('../../config.inc.php');
require_once("../../third_party/charts/charts.php");
require_once('results.class.php');

testlinkInitPage($db);

$cdata = getChartData($db);

$chart['chart_data'] = $cdata->chart_data;

$chart['chart_type'] = "stacked column"; 
$chart['axis_category'] = array ('orientation'=>"diagonal_down");
$chart['axis_value'] = array ( 'font'=>"arial", 'bold'=>true, 'size'=>10, 'color'=>"000000", 
		'alpha'=>50, 'steps'=>6, 'prefix'=>"", 'suffix'=>"", 'decimals'=>0, 'separator'=>"", 
		'show_min'=>true );

$chart['chart_grid_h'] = array ( 'alpha'=>20, 'color'=>"000000", 'thickness'=>1, 
		'type'=>"solid" );
$chart['chart_rect'] = array ( 'x'=>30, 'y'=>20, 'height'=>250, 
		'positive_color'=>"ffffff", 'negative_color'=>"000000", 'positive_alpha'=>75, 
		'negative_alpha'=>15 );

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
    $tplan_mgr = new testplan($dbHandler);
    $tproject_mgr = new testproject($dbHandler);
    
    $tplan_id=$_REQUEST['tplan_id'];
    $tproject_id=$_SESSION['testprojectID'];
    
    $tplan_info = $tplan_mgr->get_by_id($tplan_id);
    $tproject_info = $tproject_mgr->get_by_id($tproject_id);
    
    $re = new results($dbHandler, $tplan_mgr, $tproject_info, $tplan_info,
                      ALL_TEST_SUITES,ALL_BUILDS);
    
    
    $topLevelSuites = $re->getTopLevelSuites();
    $mapOfAggregate = $re->getAggregateMap();
   
    // data structures:
    //
    // An array with test suites names ($tsuiteNames) where position 0 is ''
    // A map with key= test case status.
    //       value -> array
    //                position 0 = human description (label) for status
    //                other position are related to $tsuiteNames, and contain total figures.
    //
    // Example:
    //
    // test suite names: [0] => 
    //                   [1] => Communications
    //                   [2] => Transportation
    //
    // totals['passed'] => Array([0] => Passed
    //                           [1] => 1
    //                           [2] => 0)
    //
    if (is_array($topLevelSuites)) 
    {
        $tsuiteNames=array('');
        foreach($topLevelSuites as $tsuite )
        {
            $rmap=$mapOfAggregate[$tsuite['id']];
        	  $tsuiteNames[]=$tsuite['name'];

            unset($rmap['total']);
        	  foreach( $rmap as $key => $value)
        	  {
        	      $totals[$key][]=$value;  
        	  }
        } 
    } // end if 
    
    // all array must have same number of items
    // testerNames: used to dsplay name in X axis
    //               first element must be leave clear
    //
    // results array: first element is status label
    //
    $obj = new stdClass();
    $obj->chart_data = array($tsuiteNames);
    $resultsCfg=config_get('results');
    foreach( $totals as $status => $values)
    {
        // $values[0]=lang_get($resultsCfg['status_label'][$status]);
        array_unshift($values,lang_get($resultsCfg['status_label'][$status]));
        $obj->chart_data[]=$values;
        $obj->series_color[]=$resultsCfg['charts']['status_colour'][$status];
    }
    return $obj;
}
?>
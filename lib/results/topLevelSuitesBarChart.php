<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: topLevelSuitesBarChart.php,v $
 * @version $Revision: 1.14 $
 * @modified $Date: 2009/05/27 18:42:07 $ by $Author: schlundus $
 *
 * @author	Kevin Levy
 *
* rev: 20081116 - franciscom - refactored to display X axis ordered (alphabetical).
*      20081113 - franciscom - BUGID 1848
 *
 */
require_once('../../config.inc.php');
require_once('charts.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$cfg = new stdClass();
$cfg->chartTitle = lang_get('results_top_level_suites');
$cfg->XSize = 700;
$cfg->YSize = 275;
$cfg->scale = new stdClass();
$cfg->scale->legendXAngle = 35;

$info = getDataAndScale($db);
createChart($info,$cfg);


/*
  function: getDataAndScale

  args :
  
  returns: 

*/
function getDataAndScale(&$dbHandler)
{
    $obj = new stdClass(); 
    $totals = null; 
    $resultsCfg = config_get('results');

    $dataSet = $_SESSION['statistics']['getTopLevelSuites'];
    $mapOfAggregate = $_SESSION['statistics']['getAggregateMap'];
     
    $obj->canDraw = !is_null($dataSet);
    if($obj->canDraw) 
    {
        // Process to enable alphabetical order
        foreach($dataSet as $tsuite)
        {
            $item_descr[$tsuite['name']] = $tsuite['id'];
        }  
        ksort($item_descr);
        
        foreach($item_descr as $name => $tsuite_id)
        {
            $items[]=htmlspecialchars($name);
            $rmap = $mapOfAggregate[$tsuite_id];
             
            unset($rmap['total']);
        	foreach($rmap as $key => $value)
        	{
        		$totals[$key][]=$value;  
        	}
        }
    }
    
    $obj->xAxis = new stdClass();
    $obj->xAxis->values = $items;
    $obj->xAxis->serieName = 'Serie8';
    $obj->series_color = null;
    
    foreach($totals as $status => $values)
    {
       $obj->chart_data[] = $values;
       $obj->series_label[] = lang_get($resultsCfg['status_label'][$status]);
       $obj->series_color[] = $resultsCfg['charts']['status_colour'][$status];
    }
 
    return $obj;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>
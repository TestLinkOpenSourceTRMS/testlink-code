<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: topLevelSuitesBarChart.php,v $
 * @version $Revision: 1.15.2.1 $
 * @modified $Date: 2010/12/10 15:52:23 $ by $Author: franciscom $
 *
 * @author	Kevin Levy
 *
 * @internal revisions
 *
 * 20101210 - franciscom - BUGID 4090 
 * 20100912 - franciscom - BUGID 2215
 * 20081116 - franciscom - refactored to display X axis ordered (alphabetical).
 * 20081113 - franciscom - BUGID 1848
 *
 */
require_once('../../config.inc.php');
require_once('charts.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$cfg = new stdClass();
$cfg->scale = new stdClass();

$chart_cfg = config_get('results');
$chart_cfg = $chart_cfg['charts']['dimensions']['topLevelSuitesBarChart'];

$cfg->chartTitle = lang_get($chart_cfg['chartTitle']);
$cfg->XSize = $chart_cfg['XSize'];
$cfg->YSize = $chart_cfg['YSize'];
$cfg->beginX = $chart_cfg['beginX'];
$cfg->beginY = $chart_cfg['beginY'];
$cfg->scale->legendXAngle = $chart_cfg['legendXAngle'];

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
       // BUGID 4090
 	   if( isset($resultsCfg['charts']['status_colour'][$status]) )
       {	
			$obj->series_color[] = $resultsCfg['charts']['status_colour'][$status];
       }	
    }
 
    return $obj;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>
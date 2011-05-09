<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	topLevelSuitesBarChart.php
 * 
 * @internal revisions
 * 20101210 - franciscom - BUGID 4090 
 * 20100912 - franciscom - BUGID 2215
 *
 */
require_once('../../config.inc.php');
require_once('charts.inc.php');
testlinkInitPage($db);

$args = init_args($db);
checkRights($db,$_SESSION['currentUser'],$args);


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



function init_args(&$dbHandler)
{
	$iParams = array("tproject_id" => array(tlInputParameter::INT_N),
					 "tplan_id" => array(tlInputParameter::INT_N));
	
	$args = new stdClass();
	R_PARAMS($iParams,$args);
	
    $treeMgr = new tree($dbHandler);
    
    $args->tproject_name = '';
    if($args->tproject_id > 0)
    {
		$dummy = $treeMgr->get_node_hierarchy_info($args->tproject_id);
    	$args->tproject_name = $dummy['name'];
    }

    $args->tplan_name = '';
    if($args->tplan_id > 0)
    {
		$dummy = $treeMgr->get_node_hierarchy_info($args->tplan_id);
		$args->tplan_name = $dummy['name'];  
    }
    
    return $args;
}

/**
 * 
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = $argsObj->tproject_id;
	$env['tplan_id'] = $argsObj->tplan_id;
	checkSecurityClearance($db,$userObj,$env,array('testplan_metrics'),'and');
}
?>
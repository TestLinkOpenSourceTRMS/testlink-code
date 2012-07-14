<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * $Id: keywordBarChart.php,v 1.16.2.1 2010/12/10 15:52:23 franciscom Exp $ 
 *
 * @author	Kevin Levy
 *
 * - PHP autoload feature is used to load classes on demand
 *
 * @internal revisions
 *
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('charts.inc.php');
testlinkInitPage($db,true,false,"checkRights");

$cfg = new stdClass();
$cfg->scale = new stdClass();

$chart_cfg = config_get('results');
$chart_cfg = $chart_cfg['charts']['dimensions']['keywordBarChart'];

$cfg->chartTitle = lang_get($chart_cfg['chartTitle']);
$cfg->XSize = $chart_cfg['XSize'];
$cfg->YSize = $chart_cfg['YSize'];
$cfg->beginX = $chart_cfg['beginX'];
$cfg->beginY = $chart_cfg['beginY'];
$cfg->scale->legendXAngle = $chart_cfg['legendXAngle'];


$args = init_args();
$info = getDataAndScale($db,$args);

createChart($info,$cfg);


/*
  function: getDataAndScale

  args: dbHandler
  
  returns: object

*/
function getDataAndScale(&$dbHandler,$argsObj)
{
	$resultsCfg = config_get('results');
	$obj = new stdClass(); 
	$items = array();
	$totals = null; 

	$metricsMgr = new tlTestPlanMetrics($dbHandler);
    $dummy = $metricsMgr->getStatusTotalsByKeywordForRender($argsObj->tplan_id);
    
    $obj->canDraw = false;
	if( !is_null($dummy) )    
	{
    	$dataSet = $dummy->info;
		$obj->canDraw = !is_null($dataSet) && (count($dataSet) > 0);
	}
	
	if($obj->canDraw)
	{
	   	// Process to enable alphabetical order
	    foreach($dataSet as $keyword_id => $elem)
	    {
	        $item_descr[$elem['name']] = $keyword_id;
	    }  
	    ksort($item_descr);
	    
	    foreach($item_descr as $name => $keyword_id)
	    {
	        $items[] = htmlspecialchars($name);
	       	foreach($dataSet[$keyword_id]['details'] as $status => $value)
	        {
	            $totals[$status][] = $value['qty'];  
	        }    
	    }
	} 
	
	$obj->xAxis = new stdClass();
	$obj->xAxis->values = $items;
	$obj->xAxis->serieName = 'Serie8';
	
	$obj->series_color = null;
	$obj->scale = new stdClass();
	$obj->scale->maxY = 0;
	$obj->scale->minY = 0;
	$obj->scale->divisions = 0;
	
	if(!is_null($totals))
	{
	    // in this array position we will find minimun value after an rsort
	    $minPos = count($dataSet)-1;
	
	    $obj->scale->maxY = 0;
	    $obj->scale->minY = 0;
	    
	    foreach($totals as $status => $values)
	    {
	        $obj->chart_data[] = $values;
	        $obj->series_label[] = lang_get($resultsCfg['status_label'][$status]);
	        if( isset($resultsCfg['charts']['status_colour'][$status]) )
            {	
	        	$obj->series_color[] = $resultsCfg['charts']['status_colour'][$status];
	        }	
	    }
	}
	    
	return $obj;
}


function init_args()
{
	$argsObj = new stdClass();
	// $argsObj->tproject_id = intval($_REQUEST['tproject_id']);
	$argsObj->tplan_id = intval($_REQUEST['tplan_id']);
	if( isset($_REQUEST['debug']) )
	{
		$argsObj->debug = 'yes';
	}
	return $argsObj;
}


function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>
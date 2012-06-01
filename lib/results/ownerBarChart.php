<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: ownerBarChart.php,v 1.15.2.1 2010/12/10 15:52:23 franciscom Exp $ 
*
* @author	Kevin Levy
*
* rev:
* 	20101210 - franciscom - BUGID 4090 
*	20081116 - franciscom - refactored to display X axis ordered (alphabetical).
*   20081113 - franciscom - BUGID 1848
* 
*/
require_once('../../config.inc.php');
require_once('charts.inc.php');
testlinkInitPage($db,true,false,"checkRights");

$cfg = new stdClass();
$cfg->scale = new stdClass();

$chart_cfg = config_get('results');
$chart_cfg = $chart_cfg['charts']['dimensions']['ownerBarChart'];

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

  args :
  
  returns: 

*/
function getDataAndScale(&$dbHandler,$argsObj)
{
    $obj = new stdClass(); 
    $totals = null; 
    $resultsCfg = config_get('results');

	$metricsMgr = new tlTestPlanMetrics($dbHandler);
    $dummy = $metricsMgr->getStatusTotalsByAssignedUserForRender($argsObj->tplan_id);
    $dataSet = $dummy->info;

    $obj->canDraw = !is_null($dataSet) && (count($dataSet) > 0);
    if($obj->canDraw)
    {
        // Process to enable alphabetical order
        foreach($dataSet as $assignedUser => $elem)
        {
            $item_descr[$elem['name']] = $assignedUser;
        }  
        ksort($item_descr);
	
        foreach($item_descr as $name => $assignedUser)
        {
            $items[] = htmlspecialchars($name);
            foreach($dataSet[$assignedUser]['details'] as $status => $value)
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
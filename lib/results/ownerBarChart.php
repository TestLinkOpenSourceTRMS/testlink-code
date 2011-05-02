<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* @internal filename: ownerBarChart.php
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
testlinkInitPage($db);

$args = init_args();
checkRights($db,$_SESSION['currentUser'],$args);


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

    $dataSet = $_SESSION['statistics']['getAggregateOwnerResults'];
    $obj->canDraw = !is_null($dataSet);
    if($obj->canDraw)
    {
        // Process to enable alphabetical order
        foreach($dataSet as $tester_id => $elem)
        {
            $item_descr[$elem['tester_name']] = $tester_id;
        }  
        ksort($item_descr);

        foreach($item_descr as $name => $tester_id)
        {
            $items[] = htmlspecialchars($name);
            foreach($dataSet[$tester_id]['details'] as $status => $value)
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
   	        // BUGID 4090
	        if( isset($resultsCfg['charts']['status_colour'][$status]) )
            {	
            	$obj->series_color[] = $resultsCfg['charts']['status_colour'][$status];
            }	
        }
    }
    return $obj;
}

/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('testplan_metrics'),'and');
}

?>
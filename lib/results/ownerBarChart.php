<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: ownerBarChart.php,v 1.12 2008/11/13 14:22:37 franciscom Exp $ 
*
* @author	Kevin Levy
*
* rev: 20081113 - franciscom - BUGID 1848
* 
*/
require_once('../../config.inc.php');
require_once('charts.inc.php');

testlinkInitPage($db);
$cfg = new stdClass();
$cfg->chartTitle=lang_get('results_by_tester'); 
$cfg->XSize=700;
$cfg->YSize=300;
$cfg->scale=new stdClass();
$cfg->scale->legendXAngle=35;

$info=getDataAndScale($db);
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
    $resultsCfg=config_get('results');

    $dataSet = $_SESSION['statistics']['getAggregateOwnerResults'];
    $obj->canDraw=!is_null($dataSet);
    if($obj->canDraw)
    {
        foreach($dataSet as $tester_id => $elem)
        {
            $items[] = htmlspecialchars($elem['tester_name']);   
            foreach($elem['details'] as $status => $value)
            {
                $totals[$status][]=$value['qty'];  
            }    
        }  
    }
    $obj->xAxis=new stdClass();
    $obj->xAxis->values = $items;
    $obj->xAxis->serieName = 'Serie8';
    $obj->series_color = null;
    $obj->scale = new stdClass();
    $obj->scale->maxY=0;
    $obj->scale->minY=0;
    $obj->scale->divisions=0;

    if(!is_null($totals))
    {
        foreach($totals as $status => $values)
        {
            $obj->chart_data[] = $values;
            $obj->series_label[] =lang_get($resultsCfg['status_label'][$status]);
            $obj->series_color[] = $resultsCfg['charts']['status_colour'][$status];
        }
    }
    return $obj;
}
?>
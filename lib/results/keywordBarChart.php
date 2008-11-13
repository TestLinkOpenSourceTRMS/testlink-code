<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * $Id: keywordBarChart.php,v 1.12 2008/11/13 14:22:37 franciscom Exp $ 
 *
 * @author	Kevin Levy
 *
 * - PHP autoload feature is used to load classes on demand
 *
 * rev: 20081113 - franciscom - BUGID 1848
 */
require_once('../../config.inc.php');
require_once('charts.inc.php');

testlinkInitPage($db);
$cfg = new stdClass();
$cfg->chartTitle=lang_get('results_by_keyword'); 
$cfg->XSize=650;
$cfg->YSize=250;
$cfg->scale=new stdClass();
$cfg->scale->legendXAngle=0;

$info=getDataAndScale($db);
createChart($info,$cfg);


/*
  function: getDataAndScale

  args: dbHandler
  
  returns: object

*/
function getDataAndScale(&$dbHandler)
{
$resultsCfg = config_get('results');
$obj = new stdClass(); 
$items = array();
$dataSet = $_SESSION['statistics']['getAggregateKeywordResults'];
$obj->canDraw=!is_null($dataSet);
$totals = null; 

if($obj->canDraw)
{
    foreach($dataSet as $keyword_id => $elem)
    {
        $items[] = htmlspecialchars($elem['keyword_name']);
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
    // in this array position we will find minimun value after an rsort
    $minPos=count($dataSet)-1;

    $obj->scale->maxY=0;
    $obj->scale->minY=0;
    
    foreach($totals as $status => $values)
    {
        $obj->chart_data[] = $values;
        $obj->series_label[] =lang_get($resultsCfg['status_label'][$status]);
        $obj->series_color[] = $resultsCfg['charts']['status_colour'][$status];
  
        // needed to get values to set scale
        rsort($values);
        if( $values[0] > $obj->scale->maxY )
        {
           $obj->scale->maxY = $values[0];
        }
        if( $values[$minPos] < $obj->scale->minY )
        {
           $obj->scale->minY = $values[$minPos];
        }
    }
    $obj->scale->divisions=$obj->scale->maxY;
}
    
    
return $obj;
}
?>
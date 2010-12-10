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
 * 20101210 - franciscom - BUGID 4090
 * 20100912 - franciscom - BUGID 2215
 * 20081116 - franciscom - refactored to display X axis ordered (alphabetical).
 * 20081113 - franciscom - BUGID 1848
 */
require_once('../../config.inc.php');
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


$info = getDataAndScale($db);
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
	$obj->canDraw = !is_null($dataSet);
	$totals = null; 
	
	if($obj->canDraw)
	{
	   	// Process to enable alphabetical order
	    foreach($dataSet as $keyword_id => $elem)
	    {
	        $item_descr[$elem['keyword_name']] = $keyword_id;
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
	        
	        // BUGID 4090
	        if( isset($resultsCfg['charts']['status_colour'][$status]) )
            {	
	        	$obj->series_color[] = $resultsCfg['charts']['status_colour'][$status];
	        }	
	    }
	}
	    
	return $obj;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>
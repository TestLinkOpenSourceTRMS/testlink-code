<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @filesource  keywordBarChart.php
 *
 * @author  Francisco Mancardi
 *
 *
 * @internal revisions
 * @since 1.9.10
 *
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('charts.inc.php');

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


$args = init_args($db);
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

/**
 *
 */
function init_args(&$dbHandler)
{
  $iParams = array("apikey" => array(tlInputParameter::STRING_N,0,64),
                   "tproject_id" => array(tlInputParameter::INT_N), 
                   "tplan_id" => array(tlInputParameter::INT_N));

  $args = new stdClass();
  R_PARAMS($iParams,$args);
  
  if( !is_null($args->apikey) )
  {
    $cerbero = new stdClass();
    $cerbero->args = new stdClass();
    $cerbero->args->tproject_id = $args->tproject_id;
    $cerbero->args->tplan_id = $args->tplan_id;

    if(strlen($args->apikey) == 32)
    {
      $cerbero->args->getAccessAttr = true;
      $cerbero->method = 'checkRights';
      $cerbero->redirect_target = "../../login.php?note=logout";
      setUpEnvForRemoteAccess($dbHandler,$args->apikey,$cerbero);
    }
    else
    {
      $args->addOpAccess = false;
      $cerbero->method = null;
      $cerbero->args->getAccessAttr = false;
      setUpEnvForAnonymousAccess($dbHandler,$args->apikey,$cerbero);
    }  
  }
  else
  {
    testlinkInitPage($dbHandler,false,false,"checkRights");  
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  }

  if( isset($_REQUEST['debug']) )
  {
    $args->debug = 'yes';
  }
  return $args;
}

/**
 *
 */
function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'testplan_metrics');
}
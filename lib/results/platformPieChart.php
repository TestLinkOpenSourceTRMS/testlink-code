<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  platformPieChart.php
 * @package     TestLink
 * @author      franciscom
 * @copyright   2005-2013, TestLink community
 * @link        http://www.testlink.org
 *
 * @internal revisions
 * @since 1.9.10
 *
**/
require_once('../../config.inc.php');
require_once('common.php');
include("../../third_party/pchart/pChart/pData.class");   
include("../../third_party/pchart/pChart/pChart.class");   

$resultsCfg = config_get('results');
$chart_cfg = $resultsCfg['charts']['dimensions']['platformPieChart'];

$args = init_args($db);
$metricsMgr = new tlTestPlanMetrics($db);
$dummy = $metricsMgr->getStatusTotalsByPlatformForRender($args->tplan_id);

// if platform has no test case assigned $dummy->info[$args->platform_id] does not exists
if( isset($dummy->info[$args->platform_id]) )
{
  $totals = $dummy->info[$args->platform_id]['details'];
}
else
{
  // create empty set
  $status = $metricsMgr->getStatusForReports();
  foreach($status as $statusVerbose)
  {
    $totals[$statusVerbose] = array('qty' => 0, 'percentage' => 0);
  }
  unset($status);
}

unset($dummy);
unset($metricsMgr);

$values = array();
$labels = array();
$series_color = array();
foreach($totals as $key => $value)
{
    $value = $value['qty'];
    $values[] = $value;
    $labels[] = lang_get($resultsCfg['status_label'][$key]) . " ($value)";
    if( isset($resultsCfg['charts']['status_colour'][$key]) )
    {
      $series_color[] = $resultsCfg['charts']['status_colour'][$key];
    }  
}

// Dataset definition    
$DataSet = new pData;   
$DataSet->AddPoint($values,"Serie1");   
$DataSet->AddPoint($labels,"Serie8");   
$DataSet->AddAllSeries();   
$DataSet->SetAbsciseLabelSerie("Serie8");   

// Initialise the graph
$pChartCfg = new stdClass(); 
$pChartCfg->XSize = $chart_cfg['XSize'];
$pChartCfg->YSize = $chart_cfg['YSize'];                    
$pChartCfg->radius = $chart_cfg['radius'];
$pChartCfg->legendX = $chart_cfg['legendX'];                    
$pChartCfg->legendY = $chart_cfg['legendY'];

$pChartCfg->centerX = intval($pChartCfg->XSize/2);                    
$pChartCfg->centerY = intval($pChartCfg->YSize/2);

$graph = new stdClass();
$graph->data = $DataSet->GetData();
$graph->description = $DataSet->GetDataDescription();

$Test = new pChart($pChartCfg->XSize,$pChartCfg->YSize);
foreach($series_color as $key => $hexrgb)
{
  $rgb = str_split($hexrgb,2);
  $Test->setColorPalette($key,hexdec($rgb[0]),hexdec($rgb[1]),hexdec($rgb[2]));  
}
 
// Draw the pie chart   
$Test->setFontProperties(config_get('charts_font_path'),config_get('charts_font_size'));
$Test->AntialiasQuality = 0;
$Test->drawBasicPieGraph($graph->data,$graph->description,
                         $pChartCfg->centerX,$pChartCfg->centerY,$pChartCfg->radius,PIE_PERCENTAGE,255,255,218);   
$Test->drawPieLegend($pChartCfg->legendX,$pChartCfg->legendY,$graph->data,$graph->description,250,250,250);                                
$Test->Stroke();


function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'testplan_metrics');
}


/**
 * 
 *
 */
function init_args(&$dbHandler)
{
  //  $_REQUEST = strings_stripSlashes($_REQUEST);
  //  $args = new stdClass();
  //  $args->tplan_id = $_REQUEST['tplan_id'];
  //  $args->tproject_id = $_SESSION['testprojectID'];
  //  $args->platform_id = $_REQUEST['platform_id'];
  $iParams = array("apikey" => array(tlInputParameter::STRING_N,0,64),
                   "platform_id" => array(tlInputParameter::INT_N), 
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
    testlinkInitPage($dbHandler,true,false,"checkRights");  
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  }
  

  return $args;
}
<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filespurce	platformPieChart.php
 * @package 	TestLink
 * @author 		franciscom
 * @copyright 	2005-2009, TestLink community
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 * 20100922 - Julian - BUGID 3798
 *
**/
require_once('../../config.inc.php');
require_once('common.php');
define('PCHART_PATH','../../third_party/pchart');
include(PCHART_PATH . "/pChart/pData.class");   
include(PCHART_PATH . "/pChart/pChart.class");   
testlinkInitPage($db);

$resultsCfg = config_get('results');
$chart_cfg = $resultsCfg['charts']['dimensions']['platformPieChart'];

$args = init_args();
checkRights($db,$_SESSION['currentUser'],$args);

$tplan_mgr = new testplan($db);
$totalsByPlatform = $tplan_mgr->getStatusTotalsByPlatform($args->tplan_id);

$totals=$totalsByPlatform[$args->platform_id]['details'];
unset($totals['total']);

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

// BUGID 3798
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

/**
 * 
 *
 */
function init_args()
{
    $_REQUEST = strings_stripSlashes($_REQUEST);
    $args = new stdClass();
    $args->tplan_id = intval($_REQUEST['tplan_id']);
    $args->tproject_id = intval($_REQUEST['tproject_id']);
    $args->platform_id = $_REQUEST['platform_id'];
    return $args;
}
?>
<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * $Id: overallPieChart.php,v 1.9 2009/06/04 03:08:36 tosikawa Exp $ 
 *
 * @author	Kevin Levy
 *
 * - PHP autoload feature is used to load classes on demand
 * 
 * Revisions:
 *  20081028 - franciscom - refactored to use pChart
 *	20080812 - havlatm - simplyfied, polite
 */
require_once('../../config.inc.php');
require_once('results.class.php');
define('PCHART_PATH','../../third_party/pchart');
include(PCHART_PATH . "/pChart/pData.class");   
include(PCHART_PATH . "/pChart/pChart.class");   

testlinkInitPage($db);
$resultsCfg=config_get('results');
$totals = $_SESSION['statistics']['getTotalsForPlan'];

unset($totals['total']);
$values=array();
$labels=array();
foreach( $totals as $key => $value)
{
    $values[]=$value;
    $labels[]=lang_get($resultsCfg['status_label'][$key]);
    $series_color[]=$resultsCfg['charts']['status_colour'][$key];
}

// Dataset definition    
$DataSet = new pData;   
$DataSet->AddPoint($values,"Serie1");   
$DataSet->AddPoint($labels,"Serie8");   
$DataSet->AddAllSeries();   
$DataSet->SetAbsciseLabelSerie("Serie8");   

// Initialise the graph
$pChartCfg=new stdClass(); 
$pChartCfg->XSize=400;
$pChartCfg->YSize=400;                    
$pChartCfg->centerX=intval($pChartCfg->XSize/2);                    
$pChartCfg->centerY=intval($pChartCfg->YSize/2);
$pChartCfg->radius=150;
$pChartCfg->legendX=10;                    
$pChartCfg->legendY=15;

$graph=new stdClass();
$graph->data=$DataSet->GetData();
$graph->description=$DataSet->GetDataDescription();

$Test = new pChart($pChartCfg->XSize,$pChartCfg->YSize);
foreach( $series_color as $key => $hexrgb)
{
    $rgb=str_split($hexrgb,2);
    $Test->setColorPalette($key,hexdec($rgb[0]),hexdec($rgb[1]),hexdec($rgb[2]));  
}
 
// Draw the pie chart   
$Test->setFontProperties(config_get('charts_font_path'),config_get('charts_font_size'));
$Test->AntialiasQuality = 0;
$Test->drawBasicPieGraph($graph->data,$graph->description,
                         $pChartCfg->centerX,$pChartCfg->centerY,$pChartCfg->radius,PIE_PERCENTAGE,255,255,218);   
$Test->drawPieLegend($pChartCfg->legendX,$pChartCfg->legendY,$graph->data,$graph->description,250,250,250);                                
$Test->Stroke();   
?>
<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * $Id: overallPieChart.php,v 1.7 2008/10/28 09:54:49 franciscom Exp $ 
 *
 * @author	Kevin Levy
 *
 * - PHP autoload feature is used to load classes on demand
 * 
 * Revisions:
 *	20080812 - havlatm - simplyfied, polite
 */
require_once('../../config.inc.php');
require_once('results.class.php');
define('PCHART_PATH','../../third_party/pchart');
include(PCHART_PATH . "/pChart/pData.class");   
include(PCHART_PATH . "/pChart/pChart.class");   

testlinkInitPage($db);
$resultsCfg=config_get('results');
// $tplan_mgr = new testplan($db);
// $tproject_mgr = new testproject($db);
// $tplan_id=$_REQUEST['tplan_id'];
// $tproject_id=$_SESSION['testprojectID'];
// 
// $tplan_info = $tplan_mgr->get_by_id($tplan_id);
// $tproject_info = $tproject_mgr->get_by_id($tproject_id);
// 
// $re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,ALL_TEST_SUITES,ALL_BUILDS);

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
$Test->setFontProperties(PCHART_PATH . "/Fonts/tahoma.ttf",8);   
$Test->AntialiasQuality = 0;
$Test->drawBasicPieGraph($graph->data,$graph->description,
                         $pChartCfg->centerX,$pChartCfg->centerY,$pChartCfg->radius,PIE_PERCENTAGE,255,255,218);   
$Test->drawPieLegend($pChartCfg->legendX,$pChartCfg->legendY,$graph->data,$graph->description,250,250,250);                                
$Test->Stroke();   
?>
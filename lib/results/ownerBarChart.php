<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: ownerBarChart.php,v 1.11 2008/10/29 07:58:25 franciscom Exp $ 
*
* @author	Kevin Levy
*
* - PHP autoload feature is used to load classes on demand
*
* rev: 20080511 - franciscom - refactored to manage automatically new user defined status
*                              Removed fancy transistion
*/
require_once('../../config.inc.php');
require_once('results.class.php');
define('PCHART_PATH','../../third_party/pchart');
include(PCHART_PATH . "/pChart/pData.class");   
include(PCHART_PATH . "/pChart/pChart.class");   


testlinkInitPage($db);
createChart($db);


/*
  function: createChart

  args :
  
  returns: 

*/
function createChart(&$dbHandler)
{
    $pChartCfg=new stdClass(); 
    $pChartCfg->XSize=700;
    $pChartCfg->YSize=275;
    $pChartCfg->legendXAngle=75;                    
    
    $testerResults = $_SESSION['statistics']['getAggregateOwnerResults']; //$re->getAggregateOwnerResults();
    
    if( !is_null($testerResults) )
    {
        foreach($testerResults as $tester_id => $elem)
        {
            $testerNames[] = htmlspecialchars($elem['tester_name']);   
            foreach($elem['details'] as $status => $value)
            {
                $totals[$status][]=$value['qty'];  
            }    
        }  
    }
    $obj = new stdClass();
    $obj->xAxis=new stdClass();
    $obj->xAxis->values = $testerNames;
    $obj->xAxis->serieName = 'Serie8';
    $obj->series_color = null;

    $resultsCfg=config_get('results');
    if(!is_null($totals))
    {
        foreach($totals as $status => $values)
        {
            $obj->chart_data[] = $values;
            $obj->series_label[] =lang_get($resultsCfg['status_label'][$status]);
            $obj->series_color[] = $resultsCfg['charts']['status_colour'][$status];
        }
    }

    $DataSet = new pData;
    foreach($obj->chart_data as $key => $values)
    {
        $id=$key+1;
        $DataSet->AddPoint($values,"Serie{$id}");  
        $DataSet->SetSerieName($obj->series_label[$key],"Serie{$id}");
        
    }
    $DataSet->AddPoint($obj->xAxis->values,$obj->xAxis->serieName);
    $DataSet->AddAllSeries();
    $DataSet->RemoveSerie($obj->xAxis->serieName);
    $DataSet->SetAbsciseLabelSerie($obj->xAxis->serieName);
    
    $graph=new stdClass();
    $graph->data=$DataSet->GetData();
    $graph->description=$DataSet->GetDataDescription();

           
    // Initialise the graph
    $Test = new pChart($pChartCfg->XSize,$pChartCfg->YSize);
    foreach( $obj->series_color as $key => $hexrgb)
    {
        $rgb=str_split($hexrgb,2);
        $Test->setColorPalette($key,hexdec($rgb[0]),hexdec($rgb[1]),hexdec($rgb[2]));  
    }
    $Test->drawGraphAreaGradient(132,173,131,50,TARGET_BACKGROUND);
    $Test->setFontProperties(PCHART_PATH . "/Fonts/tahoma.ttf",8);
    $Test->setGraphArea(120,20,675,190);
    $Test->drawGraphArea(213,217,221,FALSE);
    $Test->drawScale($graph->data,$graph->description,SCALE_ADDALL,
                     213,217,221,TRUE,$pChartCfg->legendXAngle,2,TRUE);
  
    // Draw the bar chart
    $Test->drawStackedBarGraph($graph->data,$graph->description,70);
    
    // Draw the title
    $Title = lang_get('results_by_tester');
    $Test->drawTextBox(0,0,50,$pChartCfg->YSize,$Title,90,255,255,255,ALIGN_BOTTOM_CENTER,TRUE,0,0,0,30);
    
    // Draw the legend
    $Test->setFontProperties(PCHART_PATH . "/Fonts/tahoma.ttf",8);
    $Test->drawLegend(610,10,$graph->description,236,238,240,52,58,82);
    
    // Render the picture
    $Test->addBorder(2);
    $Test->Stroke();
}
?>
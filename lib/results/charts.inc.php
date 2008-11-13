<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * $Id: charts.inc.php,v 1.1 2008/11/13 14:22:37 franciscom Exp $ 
 *
 * @author	Francisco Mancardi - francisco.mancardi@gmail.com
 *
 * rev: 20081113 - franciscom - BUGID 1848
 *
 */
require_once('../../config.inc.php');
require_once('results.class.php');
define('PCHART_PATH','../../third_party/pchart');
include_once(PCHART_PATH . "/pChart/pData.class");   
include_once(PCHART_PATH . "/pChart/pChart.class");   

/*
  function: createChart

  args :
  
  returns: 

*/
function createChart(&$info,&$cfg)
{
    $backgndColor=array('R' => 255, 'G' => 255, 'B' => 255);
    $chartCfg=new stdClass();
    $chartCfg->XSize=$info->canDraw ? $cfg->XSize : 600;
    $chartCfg->YSize=$info->canDraw ? $cfg->YSize : 50;                    
    
    $chartCfg->border = new stdClass();
    $chartCfg->border->width = 2;
    $chartCfg->border->color = array('R' => 0, 'G' => 0, 'B' => 0);

    $chartCfg->graphArea = new stdClass();
    $chartCfg->graphArea->color=array('R' => 213, 'G' => 217, 'B' => 221);
    $chartCfg->graphArea->beginX = 40; 
    $chartCfg->graphArea->beginY = 40;
    $chartCfg->graphArea->endX = $chartCfg->XSize - $chartCfg->graphArea->beginX;
    $chartCfg->graphArea->endY = $chartCfg->YSize - $chartCfg->graphArea->beginY;

    $chartCfg->scale=new stdClass();
    $chartCfg->scale->mode=SCALE_ADDALL;
    $chartCfg->scale->color = array('R' => 0, 'G' => 0, 'B' => 0);
    $chartCfg->scale->drawTicks = TRUE;
    $chartCfg->scale->angle=$cfg->scale->legendXAngle;
    $chartCfg->scale->decimals=1;
    $chartCfg->scale->withMargin=TRUE;
        
    $chartCfg->legend=new stdClass();
    $chartCfg->legend->X=$chartCfg->XSize-80;                    
    $chartCfg->legend->Y=15;
    $chartCfg->legend->color=array('R' => 236, 'G' => 238, 'B' => 240);

    $chartCfg->title=new stdClass();
    $chartCfg->title->value=$cfg->chartTitle; 
    
    $chartCfg->title->X=2*$chartCfg->graphArea->beginX;                    
    $chartCfg->title->Y=$chartCfg->legend->Y;
    $chartCfg->title->color=array('R' => 0, 'G' => 0, 'B' => 255);
    
    $Test = new pChart($chartCfg->XSize,$chartCfg->YSize);
    $Test->drawBackground($backgndColor['R'],$backgndColor['G'],$backgndColor['B']);
    $Test->drawGraphArea($chartCfg->graphArea->color['R'],
                         $chartCfg->graphArea->color['G'],$chartCfg->graphArea->color['B']);
    $Test->setGraphArea($chartCfg->graphArea->beginX,$chartCfg->graphArea->beginY,
                        $chartCfg->graphArea->endX,$chartCfg->graphArea->endY);
    
    $Test->setFontProperties(PCHART_PATH . "/Fonts/tahoma.ttf",8);
       
    if($info->canDraw)
    {
        $DataSet = new pData;
        foreach($info->chart_data as $key => $values)
        {
            $id=$key+1;
            $DataSet->AddPoint($values,"Serie{$id}");  
            $DataSet->SetSerieName($info->series_label[$key],"Serie{$id}");
            
        }
        $DataSet->AddPoint($info->xAxis->values,$info->xAxis->serieName);
        $DataSet->AddAllSeries();
        $DataSet->RemoveSerie($info->xAxis->serieName);
        $DataSet->SetAbsciseLabelSerie($info->xAxis->serieName);
        $chartData=$DataSet->GetData();
        $chartLegend=$DataSet->GetDataDescription();
        
           
        foreach( $info->series_color as $key => $hexrgb)
        {
            $rgb=str_split($hexrgb,2);
            $Test->setColorPalette($key,hexdec($rgb[0]),hexdec($rgb[1]),hexdec($rgb[2]));  
        }
        // $Test->setFixedScale($info->scale->minY,$info->scale->maxY,$info->scale->divisions);
        $Test->drawScale($chartData,$chartLegend,$chartCfg->scale->mode,
                         $chartCfg->scale->color['R'],$chartCfg->scale->color['G'],$chartCfg->scale->color['B'],
                         $chartCfg->scale->drawTicks,$chartCfg->scale->angle,$chartCfg->scale->decimals,
                         $chartCfg->scale->withMargin);
  
        $Test->drawStackedBarGraph($chartData,$chartLegend,70);
        
        // Draw the legend
        $Test->setFontProperties(PCHART_PATH . "/Fonts/tahoma.ttf",8);
        $Test->drawLegend($chartCfg->legend->X,$chartCfg->legend->Y,$chartLegend,
                          $chartCfg->legend->color['R'],$chartCfg->legend->color['G'],
                          $chartCfg->legend->color['B']);
 
        $Test->addBorder($chartCfg->border->width,
                         $chartCfg->border->color['R'],$chartCfg->border->color['G'],
                         $chartCfg->border->color['B']);
    }
    else
    {
        $chartCfg->title->value .= '/' . lang_get('no_data_available'); 
    }

    $Test->drawTitle($chartCfg->title->X,$chartCfg->title->Y,$chartCfg->title->value,
                     $chartCfg->title->color['R'],$chartCfg->title->color['G'],$chartCfg->title->color['B']);
    $Test->Stroke();
}
?>

<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * @filesourece	charts.inc.php
 * @author	Francisco Mancardi - francisco.mancardi@gmail.com
 * @internal revisions
 *
 *
 */
define("SCALE_ADDALLSTART0", 4);

require_once '../../config.inc.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use pChart\pChart;
use pChart\pData;

/**
 *
 * @param stdClass $info
 * @param stdClass $cfg
 */
function createChart(&$info, &$cfg)
{
    $backgndColor = array(
        'R' => 255,
        'G' => 255,
        'B' => 254
    );
    $chartCfg = new stdClass();
    $chartCfg->XSize = $info->canDraw ? $cfg->XSize : 600;
    $chartCfg->YSize = $info->canDraw ? $cfg->YSize : 50;

    $chartCfg->border = new stdClass();
    $chartCfg->border->width = 1;
    $chartCfg->border->color = array(
        'R' => 0,
        'G' => 0,
        'B' => 0
    );

    $chartCfg->graphArea = new stdClass();
    $chartCfg->graphArea->color = array(
        'R' => 213,
        'G' => 217,
        'B' => 221
    );

    $chartCfg->graphArea->beginX = property_exists($cfg, 'beginX') ? $cfg->beginX : 40;
    $chartCfg->graphArea->beginY = property_exists($cfg, 'beginY') ? $cfg->beginY : 100;

    $chartCfg->graphArea->endX = $chartCfg->XSize - $chartCfg->graphArea->beginX;
    $chartCfg->graphArea->endY = $chartCfg->YSize - $chartCfg->graphArea->beginY;

    $chartCfg->scale = new stdClass();

    // 20100914 - franciscom
    // After reading documentation
    // drawScale
    // Today there is four way of computing scales :
    //
    // - Getting Max & Min values per serie : ScaleMode = SCALE_NORMAL
    // - Like the previous one but setting the min value to 0 : ScaleMode = SCALE_START0
    // - Getting the series cumulative Max & Min values : ScaleMode = SCALE_ADDALL
    // - Like the previous one but setting the min value to 0 : ScaleMode = SCALE_ADDALLSTART0
    //
    // This will depends on the kind of graph you are drawing, today only the stacked bar chart
    // can use the SCALE_ADDALL mode.
    // Drawing graphs were you want to fix the min value to 0 you must use the SCALE_START0 option.
    //
    $chartCfg->scale->mode = SCALE_ADDALLSTART0;
    $chartCfg->scale->color = array(
        'R' => 0,
        'G' => 0,
        'B' => 0
    );
    $chartCfg->scale->drawTicks = true;
    $chartCfg->scale->angle = $cfg->scale->legendXAngle;
    $chartCfg->scale->decimals = 1;
    $chartCfg->scale->withMargin = true;

    $chartCfg->legend = new stdClass();
    $chartCfg->legend->X = 15;
    $chartCfg->legend->Y = 20;
    $chartCfg->legend->color = array(
        'R' => 236,
        'G' => 238,
        'B' => 240
    );

    $chartCfg->title = new stdClass();
    $chartCfg->title->value = $cfg->chartTitle;
    $chartCfg->title->X = ($chartCfg->XSize / 2) -
        (strlen($chartCfg->title->value) * 2.5);
    $chartCfg->title->Y = 15;
    $chartCfg->title->color = array(
        'R' => 0,
        'G' => 0,
        'B' => 255
    );

    $test = new pChart($chartCfg->XSize, $chartCfg->YSize);
    $test->drawBackground($backgndColor['R'], $backgndColor['G'],
        $backgndColor['B']);
    $test->drawGraphArea($chartCfg->graphArea->color['R'],
        $chartCfg->graphArea->color['G'], $chartCfg->graphArea->color['B']);
    $test->setGraphArea($chartCfg->graphArea->beginX,
        $chartCfg->graphArea->beginY, $chartCfg->graphArea->endX,
        $chartCfg->graphArea->endY);

    $test->setFontProperties(config_get('charts_font_path'),
        config_get('charts_font_size'));

    if ($info->canDraw) {
        $dataSet = new pData();
        foreach ($info->chart_data as $key => $values) {
            $id = $key + 1;
            $dataSet->AddPoint($values, "Serie{$id}");
            $dataSet->SetSerieName($info->series_label[$key], "Serie{$id}");
        }
        $dataSet->AddPoint($info->xAxis->values, $info->xAxis->serieName);
        $dataSet->AddAllSeries();
        $dataSet->RemoveSerie($info->xAxis->serieName);
        $dataSet->SetAbsciseLabelSerie($info->xAxis->serieName);
        $chartData = $dataSet->GetData();
        $chartLegend = $dataSet->GetDataDescription();

        foreach ($info->series_color as $key => $hexrgb) {
            $rgb = str_split($hexrgb, 2);
            $test->setColorPalette($key, hexdec($rgb[0]), hexdec($rgb[1]),
                hexdec($rgb[2]));
        }
        $test->drawScale($chartData, $chartLegend, $chartCfg->scale->mode,
            $chartCfg->scale->color['R'], $chartCfg->scale->color['G'],
            $chartCfg->scale->color['B'], $chartCfg->scale->drawTicks,
            $chartCfg->scale->angle, $chartCfg->scale->decimals,
            $chartCfg->scale->withMargin);
        $test->drawStackedBarGraph($chartData, $chartLegend, 70);

        // Draw the legend
        $test->setFontProperties(config_get('charts_font_path'),
            config_get('charts_font_size'));
        $test->drawLegend($chartCfg->legend->X, $chartCfg->legend->Y,
            $chartLegend, $chartCfg->legend->color['R'],
            $chartCfg->legend->color['G'], $chartCfg->legend->color['B']);

        $test->addBorder($chartCfg->border->width, $chartCfg->border->color['R'],
            $chartCfg->border->color['G'], $chartCfg->border->color['B']);
    } else {
        $chartCfg->title->value .= '/' . lang_get('no_data_available');
    }

    $test->drawTitle($chartCfg->title->X, $chartCfg->title->Y,
        $chartCfg->title->value, $chartCfg->title->color['R'],
        $chartCfg->title->color['G'], $chartCfg->title->color['B']);
    $test->Stroke();
}
?>

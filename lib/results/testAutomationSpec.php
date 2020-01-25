<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource testAutomationSpec.php
 * 
 */
require('../../config.inc.php');

// Must be included BEFORE common.php
require_once('../../third_party/codeplex/PHPExcel.php');

require_once('common.php');
require_once('displayMgr.php');
require_once("specview.php");

$timerOn = microtime(true);
$tplCfg = templateConfiguration();

list($tplan_mgr,$args) = initArgsForReports($db);

$gui = initializeGui($db,$args,$tplan_mgr);
$mailCfg = buildMailCfg($gui);

// 
$gui->do_report['status_ok'] = 1;
$gui->do_report['msg'] = '';

$ll = null;
$filters = array('exec_type' => TESTCASE_EXECUTION_TYPE_AUTO);

$opt = array('onlyLatestTCV' => true);
$out = genSpecViewFlat($db,'testproject',
                      $args->tproject_id,$args->tproject_id,
                      '',$ll,0,$filters,$opt);

$gui->items = $out['spec_view'];

// useful to avoid error messages on smarty template.
$gui->items_qty = is_null($gui->items) ? 0 : count($gui->items);
$gui->has_tc = $out['num_tc'] > 0 ? 1:0;
$gui->support_array = array_keys($gui->items);

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($tplCfg->tpl);


/**
 * 
 *
 */
function buildMailCfg(&$guiObj) {
	$labels = array('testplan' => lang_get('testplan'), 'testproject' => lang_get('testproject'));
	$cfg = new stdClass();
	$cfg->cc = ''; 
	$cfg->subject = $guiObj->title . ' : ' . 
                  $labels['testproject'] . ' : ' . 
                  $guiObj->tproject_name . 
	                ' : ' . $labels['testplan'] . ' : ' . $guiObj->tplan_name;
	                 
	return $cfg;
}

/**
 *
 */
function initializeGui(&$dbHandler,$argsObj,&$tplanMgr) {
 
  list($add2args,$gui) = initUserEnv($dbHandler,$argsObj);

  $gui->title = lang_get('report_test_automation');
  $gui->do_report = array();
  $gui->columnsDefinition = new stdClass();
  $gui->statistics = new stdClass();
  $gui->elapsed_time = 0; 
  $gui->displayBuildMetrics = false;
  $gui->buildMetricsFeedback = lang_get('buildMetricsFeedback');

  $gui->tproject_name = testproject::getName($dbHandler,$argsObj->tproject_id);

  return $gui;
}


/**
 *
 *
 */
function createSpreadsheet($gui,$args,&$tplanMgr) {

  // N sections
  // Always same format
  // Platform 
  // Build Assigned Not Run [%] Passed [%] Failed [%] Blocked [%]
  //                Completed [%]

  // Results by Platform
  // Overall Build Status
  // Results by Build
  // Results by Top Level Test Suite
  // Results by priority
  // Results by Keyword


  $lbl = initLblSpreadsheet();
  $cellRange = setCellRangeSpreadsheet();
  $style = initStyleSpreadsheet();

  // Common
  $execStatusDomain = $tplanMgr->getStatusForReports();
  $dataHeaderMetrics = array();
  // $cellPosForExecStatus = array();
  $ccc = 0;
  foreach( $execStatusDomain as $code => $human ) {
    $dataHeaderMetrics[] = lang_get('test_status_' . $human);
    $ccc++;    
    $dataHeaderMetrics[] = '[%]';
    $ccc++;    
    //$cellPosForExecStatus[$human] = $ccc;
  }
  $dataHeaderMetrics[] = $lbl['completed_perc'];

  $objPHPExcel = new PHPExcel();
  $lines2write = xlsStepOne($objPHPExcel,$style,$lbl,$gui);

  $oneLevel = array();

  // NO PLATFORM => ID=0
  if( $gui->hasPlatforms ) {
    $oneLevel[] = array('entity' => 'platform', 
                        'dimension' => 'testcase_qty',
                        'nameKey' => 'name', 'tcQtyKey' => 'total_tc',
                        'source' => &$gui->statistics->platform);
  }

  $oneLevel[] = array('entity' => 'build', 'dimension' => 'testcase_qty',
                      'nameKey' => 'build_name', 
                      'tcQtyKey' => 'total_assigned',
                      'source' => &$gui->statistics->overallBuildStatus);

  $startingRow = count($lines2write); // MAGIC
  foreach( $oneLevel as $target ) {
    $entity = $target['entity'];
    $dimension = $target['dimension'];
    $dataHeader = array($lbl[$entity],$lbl[$dimension]);

    // intermediate column qty is dynamic because it depends
    // of status configuration.
    foreach( $dataHeaderMetrics as $val ) {
      $dataHeader[] = $val;
    }
  
    $startingRow++;
    $startingRow++;
    $cellArea = "A{$startingRow}:";
    foreach($dataHeader as $zdx => $field) {
      $cellID = $cellRange[$zdx] . $startingRow; 
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);
      $cellAreaEnd = $cellRange[$zdx];
    }
    $cellArea .= "{$cellAreaEnd}{$startingRow}";
    $objPHPExcel->getActiveSheet()
              ->getStyle($cellArea)
              ->applyFromArray($style['DataHeader']);  

    $startingRow++;
    $infoSet = $target['source'];
    $nameKey = $target['nameKey'];
    $tcQtyKey = $target['tcQtyKey'];

    foreach($infoSet as $itemID => $fieldSet) {

      $whatCell = 0;
      $cellID = $cellRange[$whatCell] . $startingRow; 
      $field = $fieldSet[$nameKey];
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);

      $whatCell++;
      $cellID = $cellRange[$whatCell] . $startingRow; 
      $field = $fieldSet[$tcQtyKey];
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);

      foreach($fieldSet['details'] as $human => $metrics) {
        $whatCell++;        
        $cellID = $cellRange[$whatCell] . $startingRow; 
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($cellID, $metrics['qty']);

        $whatCell++;
        $cellID = $cellRange[$whatCell] . $startingRow; 
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($cellID, $metrics['percentage']);
      }
      $whatCell++;
      $cellID = $cellRange[$whatCell] . $startingRow; 
      $objPHPExcel->setActiveSheetIndex(0)
                  ->setCellValue($cellID, 
                       $fieldSet['percentage_completed']);
      $startingRow++;
    }
  }

  // The first column will be the platform
  $twoLevels = array();

  if( $gui->hasPlatforms ) {
    $twoLevels[] = 
      array('entity' => 'build', 'dimension' => 'testcase_qty',
            'nameKey' => 'build_name', 
            'tcQtyKey' => 'total_assigned',
            'source' => &$gui->statistics->buildByPlatMetrics);
  }

  $twoLevels[] = 
    array('entity' => 'testsuite', 'dimension' => 'testcase_qty',
          'nameKey' => 'name', 
          'tcQtyKey' => 'total_tc',
          'source' => &$gui->statistics->testsuites);

  $twoLevels[] = array('entity' => 'priority', 
                       'dimension' => 'testcase_qty',
                       'nameKey' => 'name', 'tcQtyKey' => 'total_tc',
                       'source' => &$gui->statistics->priorities);

  $twoLevels[] = 
    array('entity' => 'keyword', 'dimension' => 'testcase_qty',
          'nameKey' => 'name', 
          'tcQtyKey' => 'total_tc',
          'source' => &$gui->statistics->keywords);

  foreach( $twoLevels as $target ) {
    $startingRow++;
    $startingRow++;

    $entity = $target['entity'];
    $dimension = $target['dimension'];
    $nameKey = $target['nameKey'];
    $tcQtyKey = $target['tcQtyKey'];

    if( count($target['source']) == 0 ) {
      continue;
    }

    // Just ONE HEADER ?
    $dataHeader = array($lbl['platform'],$lbl[$entity],$lbl[$dimension]);
    if( $gui->hasPlatforms == false ) {
      array_shift($dataHeader);      
    }

    // intermediate column qty is dynamic because it depends
    // of status configuration.
    foreach( $dataHeaderMetrics as $val ) {
      $dataHeader[] = $val;
    }
  
    $cellArea = "A{$startingRow}:";
    foreach($dataHeader as $zdx => $field) {
      $cellID = $cellRange[$zdx] . $startingRow; 
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);
      $cellAreaEnd = $cellRange[$zdx];
    }
    $cellArea .= "{$cellAreaEnd}{$startingRow}";
    $objPHPExcel->getActiveSheet()
                ->getStyle($cellArea)
                ->applyFromArray($style['DataHeader']);  
    // END ONE HEADER
    $startingRow++;

    $idr = '';
    foreach( $gui->platformSet as $platID => $platName ) {
      $idr = ('' == $idr || 'rowB' == $idr ) ? 'rowA' : 'rowB';

      $infoSet = isset($target['source'][$platID]) ? 
                 $target['source'][$platID] : array();

      foreach($infoSet as $itemID => $fieldSet) {
        $whatCell=0;
        
        if( $gui->hasPlatforms ) {
          $cellID = $cellRange[$whatCell] . $startingRow; 
          $field = $platName;
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);          
          
          $whatCell++;
        }

        $cellID = $cellRange[$whatCell] . $startingRow; 
        $field = $fieldSet[$nameKey];
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);

        $whatCell++;
        $cellID = $cellRange[$whatCell] . $startingRow; 
        $field = $fieldSet[$tcQtyKey];
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);

        foreach($fieldSet['details'] as $human => $metrics) {
          $whatCell++;
          $cellID = $cellRange[$whatCell] . $startingRow; 
          $objPHPExcel->setActiveSheetIndex(0)
                      ->setCellValue($cellID, $metrics['qty']);

          $whatCell++;
          $cellID = $cellRange[$whatCell] . $startingRow; 
          $objPHPExcel->setActiveSheetIndex(0)
                      ->setCellValue($cellID, $metrics['percentage']);
        }
        $whatCell++;
        $cellID = $cellRange[$whatCell] . $startingRow; 
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($cellID, 
                         $fieldSet['percentage_completed']);
        
        $cellZone = "A{$startingRow}:" . $cellRange[$whatCell] . 
                    "$startingRow";

        $objPHPExcel->getActiveSheet()
                    ->getStyle($cellZone)
                    ->applyFromArray($style[$idr]);  

        $startingRow++;
      }
    }   
  } // on container ? 

  // Just to add some final empty row
  $cellID = $cellRange[0] . $startingRow; 
  $field = '';
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);          



  // Final step
  $tmpfname = tempnam(config_get('temp_dir'),"TestLink_GTMP.tmp");
  $objPHPExcel->setActiveSheetIndex(0);
  $xlsType = 'Excel5';                               
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $xlsType);  
  $objWriter->save($tmpfname);
  
  downloadXls($tmpfname,$xlsType,$gui,'TestLink_GTMP_');
}


/**
 *
 */
function xlsStepOne(&$oj,$style,&$lbl,&$gui) {
  $dummy = '';
  $lines2write = array(array($lbl['testproject'],$gui->tproject_name),
                       array($lbl['testplan'],$gui->tplan_name),
                       array($lbl['generated_by_TestLink_on'],
                       localize_dateOrTimeStamp(null,$dummy,'timestamp_format',time())));

  $cellArea = "A1:"; 
  foreach($lines2write as $zdx => $fields) {
    $cdx = $zdx+1;
    $oj->setActiveSheetIndex(0)
       ->setCellValue("A{$cdx}", current($fields))
       ->setCellValue("B{$cdx}", end($fields));
  }

  $cellArea .= "A{$cdx}";
  $oj->getActiveSheet()
     ->getStyle($cellArea)
     ->applyFromArray($style['ReportContext']); 

  return $lines2write;
}  

/**
 *
 */
function initLblSpreadsheet() {
  $lbl = init_labels(
           array('testsuite' => null,
                 'testcase_qty' => null,'keyword' => null, 
                 'platform' => null,'priority' => null,
                 'priority_level' => null,
                 'build' => null,'testplan' => null, 
                 'testproject' => null,'not_run' => null,
                 'completed_perc' => 'trep_comp_perc',
                 'generated_by_TestLink_on' => null));
  return $lbl;
} 

/**
 *
 */  
function initStyleSpreadsheet() {
  $style = array();
  $style['ReportContext'] = array('font' => array('bold' => true));
  $style['DataHeader'] = 
    array('font' => array('bold' => true),
          'borders' => 
             array('outline' => 
              array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
          'vertical' => 
              array('style' => PHPExcel_Style_Border::BORDER_THIN)),
          'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'argb' => 'FF9999FF'))
    );

  $style['rowA'] = 
    array('borders' => 
            array(
              'outline' => 
               array('style' => PHPExcel_Style_Border::BORDER_THIN),
              'vertical' => 
               array('style' => PHPExcel_Style_Border::BORDER_THIN)),
          'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'argb' => 'FFFFFFFF'))
    );

  $style['rowB'] = 
    array('borders' => 
            array(
              'outline' => 
               array('style' => PHPExcel_Style_Border::BORDER_THIN),
              'vertical' => 
               array('style' => PHPExcel_Style_Border::BORDER_THIN)),
          'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
          'startcolor' => array( 'argb' => 'DCDCDCDC'))
    );

  return $style;
}  

/**
 *
 */
function setCellRangeSpreadsheet() {
  $cr = range('A','Z');
  $crLen = count($cr);
  for($idx = 0; $idx < $crLen; $idx++) {
    for($jdx = 0; $jdx < $crLen; $jdx++) {
      $cr[] = $cr[$idx] . $cr[$jdx];
    }
  }
  return $cr;
}  



/**
 *
 */
function checkRights(&$db,&$user,$context = null) {
  if(is_null($context)) {
    $context = new stdClass();
    $context->tproject_id = $context->tplan_id = null;
    $context->getAccessAttr = false; 
  }

  $check = $user->hasRight($db,'testplan_metrics',$context->tproject_id,$context->tplan_id,$context->getAccessAttr);
  return $check;
}

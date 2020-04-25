<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource baselinel1l2.php
 * 
 */
require('../../config.inc.php');

// Must be included BEFORE common.php
require_once('../../third_party/codeplex/PHPExcel.php');

require_once('common.php');
require_once('displayMgr.php');

$timerOn = microtime(true);
$tplCfg = templateConfiguration();

list($tplan_mgr,$args) = initArgsForReports($db);
if( null == $tplan_mgr ) {
  $tplan_mgr = new testplan($db);
}

$gui = initializeGui($db,$args,$tplan_mgr);
$mailCfg = buildMailCfg($gui);

// 
  $gui->do_report['status_ok'] = 1;
  $gui->do_report['msg'] = '';

$tables = tlObject::getDBTables(array('baseline_l1l2_context',
                                      'baseline_l1l2_details',
                                      'nodes_hierarchy'));

// Get virtual columns
$sql = "SELECT distinct(status) as status_col
        FROM {$tables['baseline_l1l2_context']} BLC
        JOIN  {$tables['baseline_l1l2_details']} BLDT
        ON BLDT.context_id = BLC.id
        WHERE BLC.testplan_id = $args->tplan_id";

$statusRS = $db->fetchRowsIntoMap($sql,'status_col');
$cfg = config_get('results');
$codeToStatus = array_flip($cfg['status_code']);
$statusToLabel = $cfg['status_label'];
$statusDisplayOrder = $cfg['status_order'];
$statusCols = array();
$gui->columnsDefinition = array();

foreach ($statusDisplayOrder as $x => $code) {
  $statusCols[$code] = $statusToLabel[$codeToStatus[$code]];
  $gui->columnsDefinition[$codeToStatus[$code]] =  
          array('qty' => lang_get($statusCols[$code]), 
                'percentage' => "[%]");

  $data_tpl[$codeToStatus[$code]] = array('qty' => 0, 'percentage' => 0);
}


//foreach ($gui->platformSet as $plat_id => $plat_name) {
  $sql = "SELECT context_id,BLDT.id AS detail_id, 
          testplan_id,platform_id,
          begin_exec_ts,end_exec_ts,creation_ts,
          top_tsuite_id,child_tsuite_id,status,qty,total_tc,
          TS_TOP.name AS top_name, TS_CHI.name AS child_name
          FROM {$tables['baseline_l1l2_context']} BLC
          JOIN  {$tables['baseline_l1l2_details']} BLDT
          ON BLDT.context_id = BLC.id
          JOIN {$tables['nodes_hierarchy']} AS TS_TOP 
          ON TS_TOP.id = top_tsuite_id
          JOIN {$tables['nodes_hierarchy']} AS TS_CHI 
          ON TS_CHI.id = child_tsuite_id
          WHERE BLC.testplan_id = $args->tplan_id
          ORDER BY BLC.creation_ts DESC, top_name ASC,child_name ASC";


  $keyCols = array('platform_id','context_id',
                   'top_tsuite_id','child_tsuite_id');
  $rsu = $db->fetchRowsIntoMap4l($sql,$keyCols,true);



// Generate statistics for each platform
// Platforms are ordered by name  
foreach ($rsu as $plat_id => $dataByContext) {
  $gui->statistics = array();

  $gui->statistics[$plat_id] = array();
  $gui->span[$plat_id] = array();
  
  $rx = 0;
  foreach ($dataByContext as $context_id => $dataByTop) {
    $gui->statistics[$plat_id][$rx] = array();
    $gui->span[$plat_id][$rx] = null;

    $rrr = current(current($dataByTop))[0];
    reset($dataByTop);
    $gui->span[$plat_id][$rx] = 
      array('begin' => $rrr['begin_exec_ts'],
            'end' => $rrr['end_exec_ts'],
            'baseline_ts' => $rrr['creation_ts']);

    foreach ($dataByTop as $top_id => $dataByChild) {
      foreach ($dataByChild as $child_id => $dataX) {
        $gui->statistics[$plat_id][$rx][$child_id] = array();
        $hand = &$gui->statistics[$plat_id][$rx][$child_id];

        $dfx = $dataX[0];
        $hand['name'] = $dfx['top_name'] . ':' . $dfx['child_name'];
        $hand['total_tc'] = $dfx['total_tc'];
        $hand['percentage_completed'] = -1;
        $hand['details'] = $data_tpl;
        $hand['parent_id'] = $top_id;

        foreach ($dataX as $xx => $xmen) {
          $pp = ($hand['total_tc'] > 0) ? 
                  (round(($xmen['qty']/$hand['total_tc']) * 100,1)) : 0;
          $hand['details'][$codeToStatus[$xmen['status']]] = 
                array('qty' => $xmen['qty'],'percentage' => $pp);
        }

        // Calculate percentage completed, using all exec status
        // other than not run
        if ($hand['total_tc'] > 0) {
          $hand['percentage_completed'] =  
            $hand['total_tc'] - $hand['details']['not_run']['qty'];
          $hand['percentage_completed'] = 
            round(($hand['percentage_completed']/$hand['total_tc']) * 100,1);
        }
      }
    }
    $rx++;
  }
}


/*
array(1) {
  [187]=>
  array(10) {
    [33984]=>
    array(6) {
      ["type"]=> string(6) "tsuite"
      ["name"]=>
      string(55) "PT/08/TMS/Costing:PT/08.01/Price List & Rate Management"
      ["parent_id"]=> string(5) "33953"
      ["total_tc"]=> int(218)
      ["percentage_completed"]=> string(4) "95.4"
      ["details"]=>
      array(4) {
        ["not_run"]=> array(2) {["qty"]=>int(10)
                                ["percentage"]=> string(3) "4.6"}
        ["passed"]=>
      }
    }
*/



$timerOff = microtime(true);
$gui->elapsed_time = round($timerOff - $timerOn,2);

if ($args->spreadsheet) {
  createSpreadsheet($gui,$args,$tplan_mgr);
}

$smarty = new TLSmarty;
$smarty->assign('gui', $gui);
displayReport($tplCfg->tpl, $smarty, $args->format,$mailCfg);


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

  $gui->fakePlatform = array('');
  $gui->title = lang_get('baseline_l1l2');
  $gui->do_report = array();
  $gui->showPlatforms = true;
  $gui->columnsDefinition = new stdClass();
  $gui->statistics = new stdClass();
  $gui->elapsed_time = 0; 
  $gui->displayBuildMetrics = false;
  $gui->buildMetricsFeedback = lang_get('buildMetricsFeedback');

  $gui->tproject_name = testproject::getName($dbHandler,$argsObj->tproject_id);

  $info = $tplanMgr->get_by_id($argsObj->tplan_id);
  $gui->tplan_name = $info['name'];
  $gui->tplan_id = intval($argsObj->tplan_id);

  $gui->platformSet = $tplanMgr->getPlatforms($argsObj->tplan_id,array('outputFormat' => 'map'));
  if( is_null($gui->platformSet) ) {
  	$gui->platformSet = array('');
  	$gui->showPlatforms = false;
  } else {
    natsort($gui->platformSet);
  }

  $gui->hasPlatforms = count($gui->platformSet) >= 1 && 
                       !isset($gui->platformSet[0]);

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

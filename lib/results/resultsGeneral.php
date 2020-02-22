<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource resultsGeneral.php
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
$metricsMgr = new tlTestPlanMetrics($db);


$tsInf = $metricsMgr->getStatusTotalsByTopLevelTestSuiteForRender($args->tplan_id,null,array('groupByPlatform' => 1));

if(is_null($tsInf)) {
	// no test cases -> no report
	$gui->do_report['status_ok'] = 0;
	$gui->do_report['msg'] = lang_get('report_tspec_has_no_tsuites');
	tLog('Overall Metrics page: no test cases defined');
} else {

	// do report
	$gui->statistics->testsuites = $tsInf->info;
	$gui->do_report['status_ok'] = 1;
	$gui->do_report['msg'] = '';


  $keywordsMetrics = 
    $metricsMgr->getStatusTotalsByKeywordForRender($args->tplan_id,null, array('groupByPlatform' => 1) );

	$gui->statistics->keywords = !is_null($keywordsMetrics) ? $keywordsMetrics->info : null; 
              
  $items2loop = array();
	if( $gui->showPlatforms ) {
		$items2loop[] = 'platform';
		$platformMetrics = $metricsMgr->getStatusTotalsByPlatformForRender($args->tplan_id);
		$gui->statistics->platform = !is_null($platformMetrics) ? $platformMetrics->info : null; 
	}

	if($gui->tprojOpt->testPriorityEnabled) {
		$filters = null;
		$opt = array('getOnlyAssigned' => false, 
                 'groupByPlatform' => 1);
		$priorityMetrics = $metricsMgr->getStatusTotalsByPriorityForRender($args->tplan_id,$filters,$opt);
		$gui->statistics->priorities = !is_null($priorityMetrics) ? $priorityMetrics->info : null; 
	}


	foreach($items2loop as $item) {
    if( !is_null($gui->statistics->$item) ) {
      $gui->columnsDefinition->$item = array();
      
     	// Get labels
     	$dummy = current($gui->statistics->$item);
     	if(isset($dummy['details'])) {  
        foreach($dummy['details'] as $status_verbose => $value) {
          $dummy['details'][$status_verbose]['qty'] = lang_get($tlCfg->results['status_label'][$status_verbose]);
          $dummy['details'][$status_verbose]['percentage'] = "[%]";
        }
        $gui->columnsDefinition->$item = $dummy['details'];
      }
    }
  } 

  $doubleItemToLoop = array('priorities','keywords','testsuites');
  foreach( $doubleItemToLoop as $item ) {
    if( !is_null($gui->statistics->$item) ) {
      $gui->columnsDefinition->$item = array();
   
      // Get labels
      // !!double current because main key is PLATFORM
      $dummy = current(current($gui->statistics->$item));
      if(isset($dummy['details'])) {  
        foreach($dummy['details'] as $status_verbose => $value) {
          $dummy['details'][$status_verbose]['qty'] = lang_get($tlCfg->results['status_label'][$status_verbose]);
          $dummy['details'][$status_verbose]['percentage'] = "[%]";
        }
        $gui->columnsDefinition->$item = $dummy['details'];
      }
    }    
  }


 	/* BUILDS REPORT */
	$colDefinition = null;
	$results = null;
	if($gui->do_report['status_ok']) {
		
    $o = $metricsMgr->getOverallBuildStatusForRender($args->tplan_id);
    $gui->statistics->overallBuildStatus = $o->info;
    $gui->columnsDefinition->overallBuildStatus = $o->colDefinition;
		$gui->displayBuildMetrics = !is_null($gui->statistics->overallBuildStatus);
	}  

  // Build by Platform
  $colDefinition = null;
  $results = null;
  if($gui->do_report['status_ok']) {     
    $o = $metricsMgr->getBuildByPlatStatusForRender($args->tplan_id);

    $gui->statistics->buildByPlatMetrics = new stdClass();
    $gui->statistics->buildByPlatMetrics = $o->info; 
    $gui->columnsDefinition->buildByPlatMetrics = $o->colDefinition;

    $gui->displayBuildByPlatMetrics = 
      !is_null($gui->statistics->buildByPlatMetrics); 
  }  


	
  /* MILESTONE & PRIORITY REPORT */
  // Need to be refactored ???
	$milestonesList = $tplan_mgr->get_milestones($args->tplan_id);
	if (!empty($milestonesList)) {
		$gui->statistics->milestones = $metricsMgr->getMilestonesMetrics($args->tplan_id,$milestonesList);
  }

} 

$timerOff = microtime(true);
$gui->elapsed_time = round($timerOff - $timerOn,2);

if( $args->spreadsheet ) {
  createSpreadsheet($gui,$args,$tplan_mgr);
}


$smarty = new TLSmarty;
$smarty->assign('gui', $gui);
displayReport($tplCfg->tpl, $smarty, $args->format,$mailCfg);



/*
  function: init_args 
  args: none
  returns: array 
*/
function init_args(&$dbHandler) {
  $tplanMgr = null;
  $iParams = array("apikey" => array(tlInputParameter::STRING_N,32,64),
                   "tproject_id" => array(tlInputParameter::INT_N), 
	                 "tplan_id" => array(tlInputParameter::INT_N),
                   "format" => array(tlInputParameter::INT_N),
                   "sendByMail" => array(tlInputParameter::INT_N),
                   "spreadsheet" => array(tlInputParameter::INT_N));

	$args = new stdClass();
	$pParams = G_PARAMS($iParams,$args);

  $args->spreadsheet = intval($args->spreadsheet);
  if( !is_null($args->apikey) ) {
    $cerbero = new stdClass();
    $cerbero->args = new stdClass();
    $cerbero->args->tproject_id = $args->tproject_id;
    $cerbero->args->tplan_id = $args->tplan_id;
    
    if(strlen($args->apikey) == 32) {
      $cerbero->args->getAccessAttr = true;
      $cerbero->method = 'checkRights';
      $cerbero->redirect_target = "../../login.php?note=logout";
      setUpEnvForRemoteAccess($dbHandler,$args->apikey,$cerbero);
    } else {
      $args->addOpAccess = false;
      $cerbero->method = null;
      setUpEnvForAnonymousAccess($dbHandler,$args->apikey,$cerbero);
    }  
  } else {
    testlinkInitPage($dbHandler,true,false,"checkRights");

    $tplanMgr = new testplan($dbHandler);
    $tplan = $tplanMgr->get_by_id($args->tplan_id);
	  $args->tproject_id = $tplan['testproject_id'];
  }

  if($args->tproject_id <= 0) {
  	$msg = __FILE__ . '::' . __FUNCTION__ . " :: Invalid Test Project ID ({$args->tproject_id})";
  	throw new Exception($msg);
  }

  if (is_null($args->format)) {
		tlog("Parameter 'format' is not defined", 'ERROR');
		exit();
	}
	
	$args->user = $_SESSION['currentUser'];
  $args->format = $args->sendByMail ? FORMAT_MAIL_HTML : $args->format;

  return array($tplanMgr,$args);
}


/**
 * 
 *
 */
function buildMailCfg(&$guiObj) {
	$labels = array('testplan' => lang_get('testplan'), 'testproject' => lang_get('testproject'));
	$cfg = new stdClass();
	$cfg->cc = ''; 
	$cfg->subject = $guiObj->title . ' : ' . $labels['testproject'] . ' : ' . $guiObj->tproject_name . 
	                ' : ' . $labels['testplan'] . ' : ' . $guiObj->tplan_name;
	                 
	return $cfg;
}

/**
 *
 */
function initializeGui(&$dbHandler,$argsObj,&$tplanMgr) {
 
  list($add2args,$gui) = initUserEnv($dbHandler,$argsObj);

  $gui->fakePlatform = array('');
  $gui->title = lang_get('title_gen_test_rep');
  $gui->do_report = array();
  $gui->showPlatforms=true;
  $gui->columnsDefinition = new stdClass();
  $gui->columnsDefinition->keywords = null;
  $gui->columnsDefinition->testers = null;
  $gui->columnsDefinition->platform = null;
  $gui->statistics = new stdClass();
  $gui->statistics->keywords = null;
  $gui->statistics->testers = null;
  $gui->statistics->milestones = null;
  $gui->statistics->overalBuildStatus = null;
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

  $gui->basehref = $_SESSION['basehref'];
  $gui->actionSendMail = $gui->basehref . 
          "lib/results/resultsGeneral.php?format=" . 
          FORMAT_MAIL_HTML . "&tplan_id={$gui->tplan_id}" .
          "&tproject_id={$gui->tproject_id}";

  $gui->actionSpreadsheet = $gui->basehref . 
          "lib/results/resultsGeneral.php?format=" . 
          FORMAT_XLS . "&tplan_id={$gui->tplan_id}&spreadsheet=1".
          "&tproject_id={$gui->tproject_id}";

  $gui->mailFeedBack = new stdClass();
  $gui->mailFeedBack->msg = '';
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
  $hasPlatforms = count($gui->platformSet) >= 1 && 
                  !isset($gui->platformSet[0]);
  if( $hasPlatforms ) {
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

  if( $hasPlatforms ) {
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
    if( $hasPlatforms == false ) {
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
        
        if( $hasPlatforms ) {
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

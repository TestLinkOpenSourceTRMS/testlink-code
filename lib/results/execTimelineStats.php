<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource execTimelineStats.php
 * 
 */
require('../../config.inc.php');

// Must be included BEFORE common.php
require_once('../../third_party/codeplex/PHPExcel.php');

require_once('common.php');
require_once('displayMgr.php');

$timerOn = microtime(true);
$tplCfg = templateConfiguration();

testlinkInitPage($db,'init_project' == 'dont_init_project',
                     'doNotCheckSession' == 'doNotCheckSession');

list($tplan_mgr,$args) = initArgsForReports($db);
if( null == $tplan_mgr ) {
  $tplan_mgr = new testplan($db);
}

$gui = initializeGui($db,$args,$tplan_mgr);
$mailCfg = buildMailCfg($gui);
$mgr = new tlTestPlanMetrics($db);

$statsBy = array();
$statsBy['month'] = array('timeline' => 'month');
$statsBy['day'] = array('timeline' => 'day');
$statsBy['day_hour'] = array('timeline' => 'day_hour');

$gui->statsBy = $statsBy;
$gui->group = $group = 'day';
$stats = $mgr->getExecTimelineStats($args->tplan_id,null,$statsBy[$group]);

if ($stats != null) {
  $gui->do_report['status_ok'] = 1;
  $gui->do_report['msg'] = '';
  $gui->statistics->exec = $stats;

  if( !is_null($gui->statistics->exec) ) {
    switch ($group) {
      case 'day':
       $gui->columnsDefinition->exec = 
             array(lang_get('qty'),lang_get('yyyy_mm_dd'));
      break;  

      case 'month':
       $gui->columnsDefinition->exec = 
             array(lang_get('qty'),lang_get('yyyy_mm'));
      break;  

      case 'day_hour':
       $gui->columnsDefinition->exec = 
             array(lang_get('qty'),lang_get('yyyy_mm_dd'),lang_get('hh'));
      break;  
    } 
  }    
}

if( $args->spreadsheet ) {
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
function initializeGui(&$dbHandler,$argsObj,&$tplanMgr) 
{
 
  $gui = new stdClass();
  $gui->tproject_id = $argsObj->tproject_id;
 
  if ($argsObj->accessType == 'gui') {
    list($add2args,$gui) = initUserEnv($dbHandler,$argsObj);
  } 

  $gui->apikey = $argsObj->apikey;
  $gui->accessType = $argsObj->accessType;
  $gui->fakePlatform = array('');
  $gui->title = lang_get('execTimelineStats_report');
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
          "lib/results/execTimelineStats.php?format=" . 
          FORMAT_MAIL_HTML . "&tplan_id={$gui->tplan_id}" .
          "&tproject_id={$gui->tproject_id}";

  $gui->actionSpreadsheet = $gui->basehref . 
          "lib/results/execTimelineStats.php?format=" . 
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
function createSpreadsheet($gui,$args,&$tplanMgr) 
{
  $lbl = initLblSpreadsheet();
  $cellRange = setCellRangeSpreadsheet();
  $style = initStyleSpreadsheet();

  // Common
  $dataHeaderMetrics = array();
  $dataHeaderMetrics[] = $lbl['qty_of_executions'];
  switch ($gui->group) {
    case 'day':
      $dataHeaderMetrics[] = $lbl['yyyy_mm_dd'];
    break;

    case 'month':
      $dataHeaderMetrics[] = $lbl['yyyy_mm'];
    break;

    case 'day_hour':
      $dataHeaderMetrics[] = $lbl['yyyy_mm_dd'];
      $dataHeaderMetrics[] = $lbl['hh'];
    break;
  }

  $objPHPExcel = new PHPExcel();
  $lines2write = xlsStepOne($objPHPExcel,$style,$lbl,$gui);
  $startingRow = count($lines2write); // MAGIC
  $dataHeader = array();
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

  // 'The meat!!'
  foreach ($gui->statistics->exec as $timestamp => $elem) {
    $ldx = 0;
    foreach ($elem as $field) {
      $cellID = $cellRange[$ldx++] . $startingRow; 
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);
    }   
    $startingRow++;
  }
  $startingRow++;

  // Just to add some final empty row
  $cellID = $cellRange[0] . $startingRow; 
  $field = '';
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);          

  // Final step
  $tmpfname = tempnam(config_get('temp_dir'),"TL_ExecTimelineStats.tmp");
  $objPHPExcel->setActiveSheetIndex(0);
  $xlsType = 'Excel5';                               
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $xlsType);  
  $objWriter->save($tmpfname);
  
  downloadXls($tmpfname,$xlsType,$gui,'TL_ExecTimelineStats_');
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
           array('qty' => null,'yyyy_mm_dd' => null, 
                 'qty_of_executions' => null,
                 'yyyy_mm' => null, 'hh' => null, 
                 'platform' => null,
                 'testplan' => null, 
                 'testproject' => null,
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

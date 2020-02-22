<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Never Run, means on ALL ACTIVE BUILDS by Test Plan and Platform
 *
 * @filesource  neverRunByPP.php
 * @package     TestLink
 * @copyright   2007-2019, TestLink community 
 * @link        http://www.testlink.org
 *
 * 
 */
require('../../config.inc.php');

// Must be included BEFORE common.php
require_once('../../third_party/codeplex/PHPExcel.php');   

require_once('common.php');
require_once('displayMgr.php');
require_once('users.inc.php');
require_once('exttable.class.php');
require_once('exec.inc.php'); // used for bug string lookup

// IMPORTANT NOTICE/WARNING about XLS generation
// Seams that \n are not liked 
// http://stackoverflow.com/questions/5960242/how-to-make-new-lines-in-a-cell-using-phpexcel
//

$tplCfg = templateConfiguration();

list($tplan_mgr,$args) = initArgsForReports($db);
if( null == $tplan_mgr ) {
  $tplan_mgr = new testplan($db);
}
$tcase_mgr = new testcase($db);

$gui = initializeGui($db,$args,$tplan_mgr);
$labels = &$gui->labels;

$testCaseCfg = config_get('testcase_cfg');

// done here in order to get some config about images
$smarty = new TLSmarty();

$doIt = false;
$doChoice = true;

$metrics = null;

/*  
file_put_contents('/development/tmp/ty.txt', 
  json_encode(array('$args->platSet' => $args->platSet))  . "\n",
  FILE_APPEND);  
*/
if( $args->doAction == 'result' ) {
  $metrics = getMetrics($db,$args,$gui);
}

if( $args->doAction == 'result' && 
  !is_null($metrics) and count($metrics) > 0 ) {              

  $doIt = true;
  $doChoice = false;

  $tpl = $tplCfg->default_template;

  $urlSafeString = array();  
  $urlSafeString['tprojectPrefix'] = urlencode($gui->tproject_info['prefix']);
  $urlSafeString['basehref'] = str_replace(" ", "%20", $args->basehref);  
    
  $out = array();
  $pathCache = $topCache = $levelCache = null;
  $nameCache = initNameCache($gui);

  $odx = 0;
  foreach($metrics as &$elem) {  
    // -------------------------------------------
    // do some decode work, using caches
    if( !isset($pathCache[$elem['tcase_id']]) ) {
      $du = $tcase_mgr->getPathLayered(array($elem['tcase_id']));  
      $pathCache[$elem['tcase_id']] = $du[$elem['tsuite_id']]['value'];
      $levelCache[$elem['tcase_id']] = $du[$elem['tsuite_id']]['level'];
      $ky = current(array_keys($du)); 
      $topCache[$elem['tcase_id']] = $ky;
    }
   
    // -----------------------------------------------------------
    // IMPORTANT NOTICE:
    //
    // Column ORDER IS CRITIC                       
    // testTitle   CCA-15708: RSRSR-150
    // platformName XXXX  <<< ONlY is platforms have been used on 
    //                        Test plan under analisys
    //
    // $out[$odx]['suiteName'] =  $pathCache[$exec['tcase_id']];

    // -------------------------------------------------------------
    $zipper = '';
    switch($args->format) {
      case FORMAT_HTML:
        $out[$odx]['testTitle'] = "<!-- " . 
          sprintf("%010d", $elem['external_id']) . " -->";
        $zipper = '';
      break;

      case FORMAT_XLS:
        $out[$odx]['testTitle'] = '';
      break;

      default:
        $out[$odx]['testTitle'] = '<a href="' . 
          $urlSafeString['basehref'] . 
            'linkto.php?tprojectPrefix=' . 
            $urlSafeString['tprojectPrefix'] . '&item=testcase&id=' . 
            urlencode($exec['full_external_id']) .'">';
        $zipper = '</a>';
      break;
    }

    // See IMPORTANT NOTICE/WARNING about XLS generation
    $out[$odx]['testTitle'] .= $elem['full_external_id'] . ':' . 
                               $elem['name'] . $zipper;

    // Insert order on out is CRITIC, because order is used on buildMatrix
    if($gui->show_platforms) {
      $out[$odx]['platformName'] = 
        $nameCache['platform'][$elem['platform_id']];
    }
    // ---------------------------------------------------------
    $odx++;
  }
  $gui->dataSet = $out;
  unset($out);
} 

$gui->urlSendExcelByEmail = $args->basehref .
      "lib/results/neverRunByPP.php?" .
      "format=" . FORMAT_XLS . "&tplan_id=$gui->tplan_id" .
      "&tproject_id=$gui->tproject_id&doAction=result";

if( $doIt ) {  
  switch($args->format) {
    case FORMAT_XLS:
      createSpreadsheet($gui,$args,$args->getSpreadsheetBy,$cfSet);
    break;  

    default:
      $tableOpt = 
        array('format' => $args->format,
              'show_platforms' => $gui->show_platforms);

      $gui->tableSet[] = buildMatrix($gui->dataSet, $args, $tableOpt ,
                                     $gui->platformSet,$cfSet);
    break;
  } 
}

$smarty = new TLSmarty();
$smarty->assign('gui', $gui );

if( $doChoice ) {
  $tpl = 'neverRunByPPLauncher.tpl';
  $gui->url2call = $args->basehref .
    "lib/results/neverRunByPP.php?tplan_id=$gui->tplan_id" .
    "&tproject_id=$gui->tproject_id&doAction=result";
}

displayReport($tplCfg->template_dir . $tpl, 
              $smarty, $args->format, $gui->mailCfg);


/**
 * 
 *
 */
function init_args(&$dbHandler) {
  $iP = array("apikey" => array(tlInputParameter::STRING_N,32,64),
              "tproject_id" => array(tlInputParameter::INT_N), 
              "tplan_id" => array(tlInputParameter::INT_N),
              "format" => array(tlInputParameter::INT_N),
              "type" => array(tlInputParameter::STRING_N,0,1),
              "platSet" => array(tlInputParameter::ARRAY_INT),
              "doAction" => array(tlInputParameter::STRING_N,5,10));

  $args = new stdClass();
  R_PARAMS($iP,$args);

  $cx = 'sendSpreadSheetByMail_x';
  $args->getSpreadsheetBy = isset($_REQUEST[$cx]) ? 'email' : null;
  if( is_null($args->getSpreadsheetBy) ) {
    $cx = 'exportSpreadSheet_x';
    $args->getSpreadsheetBy = isset($_REQUEST[$cx]) ? 'download' : null;
  }  

  if ($args->tproject_id == 0 && $args->tplan_id >0) {
    $tplan = new testplan($dbHandler);
    $nn = $tplan->get_by_id($args->tplan_id);
    $args->tproject_id = $nn['testproject_id'];    
  }

  $args->addOpAccess = true;  
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
  }
  
  $args->user = $_SESSION['currentUser'];
  $args->basehref = $_SESSION['basehref'];
  
  return $args;
}

/**
 * initializeGui
 *
 */
function initializeGui(&$dbh,&$argsObj,&$tplanMgr) {
  $tprojectMgr = new testproject($dbh);

  $guiObj = new stdClass();

  $guiObj->labels = init_labels(
    array('deleted_user' => null, 'design' => null, 
          'execution' => null,'nobody' => null,
          'execution_history' => null,
          'info_notrun_tc_report' => null,
          'title' => 'neverRunByPP_title'));

  $guiObj->title = $guiObj->labels['title'];
  $guiObj->pageTitle = $guiObj->title;
  $guiObj->report_context = '';
  $guiObj->info_msg = '';

  $guiObj->tplan_info = $tplanMgr->get_by_id($argsObj->tplan_id);
  $guiObj->tproject_info = $tprojectMgr->get_by_id($argsObj->tproject_id);
  $guiObj->tplan_name = $guiObj->tplan_info['name'];
  $guiObj->tproject_name = $guiObj->tproject_info['name'];

  $guiObj->format = $argsObj->format; 
  $guiObj->tproject_id = $argsObj->tproject_id; 
  $guiObj->tplan_id = $argsObj->tplan_id; 
  $guiObj->apikey = $argsObj->apikey;

  $guiObj->dataSet = null;
  $guiObj->type = $argsObj->type;
  $guiObj->warning_msg = '';

  $reportCfg = config_get('reports_list');

  // needed to decode
  $getOpt = array('outputFormat' => 'map', 'addIfNull' => true);
  $guiObj->platformSet = $tplanMgr->getPlatforms($argsObj->tplan_id,$getOpt);
  
  $guiObj->show_platforms = true;
  $pqy = count($guiObj->platformSet);
  if( $pqy == 0 || ($pqy == 1) && isset($guiObj->platformSet[0])){
    $guiObj->show_platforms = false;
  }

  // will be used when sending mail o creating spreadsheet
  $guiObj->platSet = array();
  $pp = (array)array_flip($argsObj->platSet);
  if( !isset($pp[0]) ) {
    // we have platforms
    foreach( $argsObj->platSet as $pk ) {
      $guiObj->platSet[$pk] = $pk;
    }
  }

  $guiObj->mailCfg = buildMailCfg($guiObj);

  return $guiObj;    
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
  $check = $user->hasRight($db,'testplan_metrics',
    $context->tproject_id,$context->tplan_id,$context->getAccessAttr);
  return $check;
}


/**
 * 
 *
 */
function buildMailCfg(&$guiObj) {
  $labels = array('testplan' => lang_get('testplan'), 
                  'testproject' => lang_get('testproject'));
  $cfg = new stdClass();
  $cfg->cc = ''; 
  $cfg->subject = $guiObj->title . ' : ' . $labels['testproject'] . ' : ' . $guiObj->tproject_name . 
                  ' : ' . $labels['testplan'] . ' : ' . $guiObj->tplan_name;
                   
  return $cfg;
}

/**
 * Builds ext-js rich table to display matrix results
 *
 * @param map dataSet: data to be displayed on matrix
 *
 * return tlExtTable
 *
 */
function buildMatrix($dataSet, &$args, $options = array(), $platforms,$customFieldColumns=null) {
  $default_options = 
    array('show_platforms' => false,'format' => FORMAT_HTML);
  $options = array_merge($default_options, $options);

  $l18n = init_labels(array('platform' => null));
  $columns = array();
  $columns[] = array('title_key' => 'title_test_case_title', 'width' => 80, 'type' => 'text');

  if ($options['show_platforms']) {
    $columns[] = array('title_key' => 'platform', 'width' => 60, 'filter' => 'list', 'filterOptions' => $platforms);
  }

  if ($options['format'] == FORMAT_HTML) {

    // IMPORTANT DEVELOPMENT NOTICE
    // columns and dataSet are deeply related this means that inside
    // dataSet order has to be identical that on columns or table will be a disaster
    //
    $matrix = new tlExtTable($columns, $dataSet, 'tl_table_results_by_status');
    
    //if not run report: sort by test suite
    //blocked, failed report: sort by platform (if enabled) else sort by date
    $sort_name = 0;
    $sort_name = $options['show_platforms'] ? $l18n['platform'] : '';
    
    $matrix->setSortByColumnName($sort_name);
    $matrix->addCustomBehaviour('text', array('render' => 'columnWrap'));
    
    //define table toolbar
    $matrix->showToolbar = true;
    $matrix->toolbarExpandCollapseGroupsButton = true;
    $matrix->toolbarShowAllColumnsButton = true;
  } else {
    $matrix = new tlHTMLTable($columns, $dataSet, 'tl_table_results_by_status');
  }
  return $matrix;
}


/**
 *
 */
function initNameCache($guiObj) {
  $safeItems = array('platform' => null);

  if($guiObj->show_platforms) {
    foreach($guiObj->platformSet as $id => $name) {
      $safeItems['platform'][$id] = htmlspecialchars($name);  
    }
  }  
  
  return $safeItems;
}

/**
 *
 */
function createSpreadsheet($gui,$args,$media) {
  $lbl = initLblSpreadsheet();
  $cellRange = range('A','Z');
  $style = initStyleSpreadsheet();

  $objPHPExcel = new PHPExcel();
  $lines2write = xlsStepOne($objPHPExcel,$style,$lbl,$gui);

  // Step 2
  // data is organized with following columns$dataHeader[]
  // Test case
  // [Platform]
  //
  // This is HOW gui->dataSet is organized
  // THIS IS CRITIC ??
  //
  // testTitle   PTRJ-76:Create issue tracker - no conflict
  // [platformName]
  //
  $dataHeader = array($lbl['title_test_case_title']);
  if( $showPlatforms = ( property_exists($gui,'platformSet') && 
      !is_null($gui->platformSet) && !isset($gui->platformSet[0])) ) {
    $dataHeader[] = $lbl['platform'];
  }

  $startingRow = count($lines2write) + 2; // MAGIC
  $cellArea = "A{$startingRow}:";
  foreach($dataHeader as $zdx => $field) {
    $cellID = $cellRange[$zdx] . $startingRow; 
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);
    $cellAreaEnd = $cellRange[$zdx];
  }
  $cellArea .= "{$cellAreaEnd}{$startingRow}";
  $objPHPExcel->getActiveSheet()->getStyle($cellArea)
              ->applyFromArray($style['DataHeader']);  

  // Now process data  
  $colorChangeCol = 1;
  $startingRow++;
  $qta_loops = count($gui->dataSet);
  $val4color = $gui->dataSet[0][$colorChangeCol];
  for($idx = 0; $idx < $qta_loops; $idx++) {
    $line2write = $gui->dataSet[$idx];
    $colCounter = 0; 
    foreach($line2write as $ldx => $field) {
      $cellID = $cellRange[$colCounter] . $startingRow; 
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, html_entity_decode($field) );
      $colCounter++;
    }
    $cellEnd = $cellRange[$colCounter-1] . $startingRow;
    $startingRow++;
  }
  
  // Final step
  $objPHPExcel->setActiveSheetIndex(0);
  
  $xlsType = 'Excel5';                               
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $xlsType);

  $codex = 'neverRunByPP';   
  $tmpfname = tempnam(config_get('temp_dir'),"$codex.tmp");
  $objWriter->save($tmpfname);

  if($args->getSpreadsheetBy == 'email') {
    require_once('email_api.php');

    $ema = new stdClass();
    $ema->from_address = config_get('from_email');
    $ema->to_address = $args->user->emailAddress;;
    $ema->subject = $gui->mailCfg->subject;
    $ema->message = $gui->mailCfg->subject;
    
    $dum = uniqid("$codex_") . '.xls';
    $oops = array('attachment' => 
                  array('file' => $tmpfname, 'newname' => $dum),
                  'exit_on_error' => true, 'htmlFormat' => true);
    $email_op = email_send_wrapper($ema,$oops);
    unlink($tmpfname);
    exit(); 
  } else {
    downloadXls($tmpfname,$xlsType,$gui,"$codex_");
  } 
}


/**
 *
 */
function getMetrics(&$dbh,&$args,&$gui) {
  $metricsMgr = new tlTestPlanMetrics($dbh);

  $opt = array('output' => 'array');
  $met = $metricsMgr->getNeverRunByPlatform($args->tplan_id,$args->platSet);
 
  $gui->notRunReport = true;
  $gui->info_msg = $gui->labels['info_notrun_tc_report'];
  $gui->notesAccessKey = 'summary';
  $gui->userAccessKey = 'user_id';

  return $met; 
}

/**
 *
 */
function initLblSpreadsheet() {
  $lbl = init_labels(array('title_test_suite_name' => null,'platform' => null,'build' => null,'th_bugs_id_summary' => null,
                           'title_test_case_title' => null,'version'  => null,
                           'testproject' => null,'generated_by_TestLink_on' => null,'testplan' => null,
                           'title_execution_notes' => null, 'th_date' => null, 'th_run_by' => null,
                           'assigned_to' => null,'summary' => null));
  return $lbl;  
}

/**
 *
 */
function initStyleSpreadsheet() {
  $sty = array();
  $sty['ReportContext'] = array('font' => array('bold' => true));
  $sty['DataHeader'] = array('font' => array('bold' => true),
                           'borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                              'vertical' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
                           'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
                                           'startcolor' => array( 'argb' => 'FF9999FF'))
                           );

  return $sty;
}

/**
 *
 */
function xlsStepOne($oj,$style,$lbl,$gui) {
  $dummy = '';
  $lines2write = array(array($gui->title,''),
                       array($lbl['testproject'],$gui->tproject_name),
                       array($lbl['testplan'],$gui->tplan_name),
                       array($lbl['generated_by_TestLink_on'],
                             localize_dateOrTimeStamp(null,$dummy,'timestamp_format',time())),
                       array($gui->report_context,''));

  $cellArea = "A1:"; 
  foreach($lines2write as $zdx => $fields) {
    $cdx = $zdx+1;
    $oj->setActiveSheetIndex(0)->setCellValue("A{$cdx}", current($fields))
       ->setCellValue("B{$cdx}", end($fields));
  }
  $cellArea .= "A{$cdx}";
  $oj->getActiveSheet()->getStyle($cellArea)
     ->applyFromArray($style['ReportContext']); 

  return $lines2write;
}
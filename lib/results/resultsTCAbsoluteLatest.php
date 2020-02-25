<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
*
* @filesource resultsTCAbsoluteLatest.php
* @author     Francisco Mancardi <francisco.mancardi@gmail.com>
* 
* Absolute Latest Execution Results on Test Plan & ONE Platform
* Builds ARE IGNORED
*
*/
require('../../config.inc.php');

// Must be included BEFORE common.php
require_once('../../third_party/codeplex/PHPExcel.php');   

require_once('common.php');
require_once('displayMgr.php');
require_once('exttable.class.php');

$timerOn = microtime(true);   // will be used to compute elapsed time
$templateCfg = templateConfiguration();

// to init $db
testlinkInitPage($db,false,false);  
$smarty = new TLSmarty;
$metricsMgr = new tlTestPlanMetrics($db);
$tplan_mgr  = &$metricsMgr; 


$args = init_args($db);

list($gui,$tproject_info,$labels,$cfg) = 
  initializeGui($db,$args,$smarty->getImages(),$tplan_mgr);
$args->cfg = $cfg;


$renderHTML = true;

// Because we will try to send via email xls, we need to be careful
// with logic regarding args->format.
// may be we need to add in logic the media => email, download, etc
//
// We have faced a performance block due to an environment with
// 700 Builds and 1300 Test Cases on Test Plan
// This created a block on NOT RUN QUERY, 
// but anyway will produce an enormous and unmanageable matrix on screen
//

switch ($args->doAction) {
  case 'result':
    $tpl = $templateCfg->default_template;
    doProcess($db,$args,$gui,$metricsMgr);
  break;

  case 'choose':
  default:
    $tpl = 'resultsTCAbsoluteLatestLauncher.tpl';
    $gui->url2call = $args->basehref .
      "lib/results/resultsTCAbsoluteLatest.php?tplan_id=$gui->tplan_id" .
      "&tproject_id=$gui->tproject_id&doAction=result";
  break;
}

$smarty->assign('gui',$gui);
displayReport($templateCfg->template_dir . $tpl, $smarty, $args->format, 
              $gui->mailCfg,$renderHTML);


/**
 * 
 *
 */
function init_args(&$dbHandler)
{
  $iParams = array("apikey" => array(tlInputParameter::STRING_N,32,64),
                   "tproject_id" => array(tlInputParameter::INT_N), 
                   "tplan_id" => array(tlInputParameter::INT_N),
                   "platform_id" => array(tlInputParameter::INT_N),
                   "doAction" => array(tlInputParameter::STRING_N,5,10),
                   "format" => array(tlInputParameter::INT_N));

  $args = new stdClass();
  R_PARAMS($iParams,$args);

  $args->format = intval($args->format);

  $args->getSpreadsheetBy = isset($_REQUEST['sendSpreadSheetByMail_x']) ? 'email' : null;

  if( is_null($args->getSpreadsheetBy) ) {
    $args->getSpreadsheetBy = isset($_REQUEST['exportSpreadSheet_x']) ? 'download' : null;
  }  


  $args->addOpAccess = true;
  if( !is_null($args->apikey) ) {
    $cerbero = new stdClass();
    $cerbero->args = new stdClass();
    $cerbero->args->tproject_id = $args->tproject_id;
    $cerbero->args->tplan_id = $args->tplan_id;

    if( strlen($args->apikey) == 32) {
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
    testlinkInitPage($dbHandler,false,false,"checkRights");  
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  }

  if ($args->tproject_id <= 0) {
    $msg = __FILE__ . '::' . __FUNCTION__ . " :: Invalid Test Project ID ({$args->tproject_id})";
    throw new Exception($msg);
  }

  if ($args->tplan_id <= 0) {
    $msg = __FILE__ . '::' . __FUNCTION__ . " :: Invalid Test PLAN ID ({$args->tproject_id})";
    throw new Exception($msg);
  }

  if ($args->doAction == 'result') {
    if ($args->platform_id <= 0) {
      $msg = __FILE__ . '::' . __FUNCTION__ . " :: Invalid PLATFORM ID ({$args->tproject_id})";
      throw new Exception($msg);
    }    
  }

  $args->user = $_SESSION['currentUser'];
  $args->basehref = $_SESSION['basehref'];
  
  return $args;
}

/**
 * 
 *
 */
function checkRights(&$db,&$user,$context = null)
{
  if (is_null($context)) {
    $context = new stdClass();
    $context->tproject_id = $context->tplan_id = null;
    $context->getAccessAttr = false; 
  }

  $check = $user->hasRight($db,'testplan_metrics',$context->tproject_id,$context->tplan_id,$context->getAccessAttr);
  return $check;
}


/**
 * Builds ext-js rich table to display matrix results
 *
 *
 * return tlExtTable
 *
 */
function buildMatrix(&$guiObj,&$argsObj,$forceFormat=null) {  

  $columns = array(array('title_key' => 'title_test_suite_name', 
                         'width' => 100),
                   array('title_key' => 'title_test_case_title', 
                         'width' => 150));

  $lbl = init_labels(array('title_test_suite_name' => null,
                           'platform' => null,
                           'priority' => null, 
                           'result_on_last_build' => null, 
                           'title_test_case_title' => null,
                           'latest_exec_notes' => null));
  
  $group_name = $lbl['title_test_suite_name'];

  if (!is_null($guiObj->platforms)) {
    $columns[] = array('title_key' => 'platform', 
                       'width' => 60, 'filter' => 'list', 
                       'filterOptions' => $guiObj->platforms);
    $group_name = $lbl['platform'];
  }

  if ($guiObj->options->testPriorityEnabled) {
    $columns[] = array('title_key' => 'priority', 
                       'type' => 'priority', 'width' => 40);
  }
  
  // --------------------------------------------------------------------
  $columns[] = array('title_key' => 'latest_execution', 
                     'type' => 'status', 'width' => 100);

  $columns[] = array('title_key' => 'latest_exec_notes', 
                     'type' => 'status', 'width' => 100);

  $fo = !is_null($forceFormat) ? $forceFormat : $argsObj->format; 
  if ($fo == FORMAT_HTML) {
    $matrix = new tlExtTable($columns, $guiObj->matrix, 'tl_table_results_tc');
    
    // if platforms feature is enabled group by platform 
    // otherwise group by test suite
    $matrix->setGroupByColumnName($group_name);
    $matrix->sortDirection = 'DESC';

    if ($guiObj->options->testPriorityEnabled) {
      // Developer Note:
      // To understand 'filter' => 'Priority' => 
      // see exttable.class.php => buildColumns()
      $matrix->addCustomBehaviour('priority', array('render' => 'priorityRenderer', 'filter' => 'Priority'));
      $matrix->setSortByColumnName($lbl['priority']);
    } else {
      $matrix->setSortByColumnName($lbl['title_test_case_title']);
    }
    
    // define table toolbar
    $matrix->showToolbar = true;
    $matrix->toolbarExpandCollapseGroupsButton = true;
    $matrix->toolbarShowAllColumnsButton = true;
  } else {
    $matrix = new tlHTMLTable($columns, $guiObj->matrix, 'tl_table_results_tc');
  }
  unset($columns);
  
  return $matrix;
}


/**
 * 
 *
 */
function buildMailCfg(&$guiObj)
{
  $labels = array('testplan' => lang_get('testplan'), 'testproject' => lang_get('testproject'));
  $cfg = new stdClass();
  $cfg->cc = ''; 
  $cfg->subject = $guiObj->title . ' : ' . $labels['testproject'] . ' : ' . $guiObj->tproject_name . 
                  ' : ' . $labels['testplan'] . ' : ' . $guiObj->tplan_name;
                   
  return $cfg;
}

/**
 * 
 *
 */
function initializeGui(&$dbHandler,&$argsObj,$imgSet,&$tplanMgr)
{
  
  $cfg = array('results' => config_get('results'), 
               'urgency' => config_get('urgency'),
               'tcase' => config_get('testcase_cfg'));

  $guiObj = new stdClass();
  $guiObj->map_status_css = null;
  $guiObj->title = lang_get('resultsTCAbsoluteLatest_title');
  $guiObj->pageTitle = $guiObj->title;

  $guiObj->printDate = '';
  $guiObj->matrix = array();
  $guiObj->platform_id = $argsObj->platform_id; 

  $guiObj->platforms = 
    $tplanMgr->getPlatforms($argsObj->tplan_id,array('outputFormat' => 'map'));
  $guiObj->show_platforms = !is_null($guiObj->platforms);

  $guiObj->img = new stdClass();
  $guiObj->img->exec = $imgSet['exec_icon'];
  $guiObj->img->edit = $imgSet['edit_icon'];
  $guiObj->img->history = $imgSet['history_small'];

  $guiObj->tproject_id = $argsObj->tproject_id;
  $guiObj->tplan_id = $argsObj->tplan_id;

  $guiObj->apikey = $argsObj->apikey;


  $tproject_mgr = new testproject($dbHandler);
  $tproject_info = $tproject_mgr->get_by_id($argsObj->tproject_id);
  $argsObj->prefix = $tproject_info['prefix']; 
  $argsObj->tcPrefix = $tproject_info['prefix'] . $cfg['tcase']->glue_character;
  $argsObj->tprojectOpt = $tproject_info['opt'];

  $guiObj->options = new stdClass();
  $guiObj->options->testPriorityEnabled = $tproject_info['opt']->testPriorityEnabled;
  unset($tproject_mgr); 

  $tplan_info = $tplanMgr->get_by_id($argsObj->tplan_id);
  $guiObj->tplan_name = $tplan_info['name'];
  $guiObj->tproject_name = $tproject_info['name'];

  $L10N = init_labels(array('design' => null, 
                            'execution' => null, 
                            'latest_execution' => null, 
                            'history' => 'execution_history',
                            'test_result_matrix_filters' => null, 
                            'too_much_data' => null,
                            'too_much_builds' => null,
                            'result_on_last_build' => null, 
                            'versionTag' => 'tcversion_indicator') );

  $L10N['not_run'] = lang_get($cfg['results']['status_label']['not_run']);

  $guiObj->report_details = lang_get(basename(__FILE__, '.php')); 

  $guiObj->matrixCfg  = config_get('resultMatrixReport');
  $guiObj->buildInfoSet = 
    $tplanMgr->get_builds($argsObj->tplan_id, testplan::ACTIVE_BUILDS,null,
                          array('orderBy' => $guiObj->matrixCfg->buildOrderByClause)); 
  $guiObj->activeBuildsQty = count($guiObj->buildInfoSet);


  foreach($cfg['results']['code_status'] as $code => $verbose) {
    if( isset($cfg['results']['status_label'][$verbose])) {
      $L10N[$code] = lang_get($cfg['results']['status_label'][$verbose]);
      $guiObj->map_status_css[$code] = $cfg['results']['code_status'][$code] . '_text';
    }
  }

  $xxx = config_get('urgency');
  foreach ($xxx['code_label'] as $code => $label) {
    $cfg['priority'][$code] = lang_get($label);
  } 
 
  $guiObj->mailCfg = buildMailCfg($guiObj);

  $guiObj->labels = $L10N;
  return array($guiObj,$tproject_info,$L10N,$cfg);
}

/**
 *
 *
 */
function createSpreadsheet($gui,$args,$media) {
  $lbl = initLblSpreadsheet();
  $cellRange = setCellRangeSpreadsheet();
  $style = initStyleSpreadsheet();

  $objPHPExcel = new PHPExcel();
  $lines2write = xlsStepOne($objPHPExcel,$style,$lbl,$gui);

  // Step 2
  // data is organized with following columns $dataHeader[]
  // Test suite
  // Test case
  // [Platform]  => if any exists
  //
  // Priority   ===>  Just discovered that we have choosen to make this column
  //                  displayabled or not according test project configuration
  //                  IMHO has no sense work without priority
  // 
  // Latest Execution result (Hmm need to explain better)
  // Latest Execution notes
  // 
  $dataHeader = array($lbl['title_test_suite_name'],
                      $lbl['title_test_case_title']);

  if( $showPlatforms = !is_null($gui->platforms) ) {
    $dataHeader[] = $lbl['platform'];
  }

  if ($gui->options->testPriorityEnabled) {  
    $dataHeader[] = $lbl['priority'];
  }


  $dataHeader[] = $lbl['latest_execution'];
  $dataHeader[] = $lbl['latest_exec_notes'];

  $startingRow = count($lines2write) + 2; // MAGIC
  $cellArea = "A{$startingRow}:";
  foreach ($dataHeader as $zdx => $field) {
    $cellID = $cellRange[$zdx] . $startingRow; 
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);
    $cellAreaEnd = $cellRange[$zdx];
  }

  $cellArea .= "{$cellAreaEnd}{$startingRow}";
  $objPHPExcel->getActiveSheet()->getStyle($cellArea)->applyFromArray($style['DataHeader']);	

  $startingRow++;
  $qta_loops = count($gui->matrix);
  for($idx = 0; $idx < $qta_loops; $idx++) {
		foreach($gui->matrix[$idx] as $ldx => $field) {
			$cellID = $cellRange[$ldx] . $startingRow; 
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);
		}
		$startingRow++;
  }
  
  // Final step
  $fname = basename(__FILE__, '.php') . '_'; 
  $tmpfname = tempnam(config_get('temp_dir'), $fname . ".tmp");
  $objPHPExcel->setActiveSheetIndex(0);
  $xlsType = 'Excel5';                               
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $xlsType);  
  $objWriter->save($tmpfname);
  
  if ($args->getSpreadsheetBy == 'email') {
    require_once('email_api.php');

    $ema = new stdClass();
    $ema->from_address = config_get('from_email');
    $ema->to_address = $args->user->emailAddress;;
    $ema->subject = $gui->mailCfg->subject;
    $ema->message = $gui->mailCfg->subject;
    
    $dum = uniqid($fname) . '.xls';
    $oops = array('attachment' => 
                  array('file' => $tmpfname, 'newname' => $dum),
                  'exit_on_error' => true, 'htmlFormat' => true);
    $email_op = email_send_wrapper($ema,$oops);
    unlink($tmpfname);
    exit(); 
  } else {
    downloadXls($tmpfname,$xlsType,$gui,$fname);
  } 
}


/**
 *
 */
function setUpBuilds(&$args,&$gui) { 
  $args->builds = new stdClass();

  if( is_null($args->build_set) ) {
    $args->builds->idSet = null;
    
    $gui->buildListForExcel = '';
    $gui->filterApplied = false;
    if( !is_null($gui->buildInfoSet) ) {
      $args->builds->idSet = array_keys($gui->buildInfoSet);
    }
  } else {
    $args->builds->idSet = array_keys(array_flip($args->build_set));
    $gui->filterApplied = true;
    $gui->buildListForExcel = implode(',',$args->builds->idSet); 
  }

  $args->builds->latest = new stdClass();
  $args->builds->latest->id = end($args->builds->idSet);
  $args->builds->latest->name = $gui->buildInfoSet[$args->builds->latest->id]['name'];
}




/**
 *
 *
 */
function buildDataSet(&$db,&$args,&$gui,&$metrics,$labels,$forceFormat=null)
{
  $userSet = getUsersForHtmlOptions($db,null,null,null,null,
                                    array('userDisplayFormat' => '%first% %last%'));

  // invariant pieces  => avoid wasting time on loops
  $dlink = '<a href="' . str_replace(" ", "%20", $args->basehref) . 
           'linkto.php?tprojectPrefix=' . urlencode($args->prefix) . '&item=testcase&id=';  

  $hist_img_tag = '<img title="';
  $edit_img_tag = '<img title="';
  $hist_img_tag .= $labels['history'] . '"' . ' src="' . $gui->img->history;
  $edit_img_tag .= $labels['design'] . '"' . ' src="' . $gui->img->edit;
  $hist_img_tag .= '" /></a> ';
  $edit_img_tag .= '" /></a> ';
  

  $cols = $args->cols;
  $priorityCfg = config_get('urgencyImportance');
  $execVerboseCode = config_get('results');
  $execVerboseCode = $execVerboseCode['status_code'];
  $execCodeVerbose = array_flip($execVerboseCode);

  $itemSet = array_keys($metrics);
  $tsuiteCache = array(); 
  $treeMgr = new tree($db);

  foreach($itemSet as $iidx) {
    $tcaseSet = array_keys($metrics[$iidx]);
    foreach($tcaseSet as $tcaseID) {
      $platformSet = array_keys($metrics[$iidx][$tcaseID]);
      foreach($platformSet as $platformID) {
        $rf = &$metrics[$iidx][$tcaseID][$platformID][0];

        $tsuiteID = $rf['tsuite_id'];
        if (!isset($tsuiteCache[$tsuiteID])) {
          // Get full path
          $tsuiteCache[$tsuiteID] = 
            implode("/",$treeMgr->get_path($tsuiteID,null,'name'));
        }

        $rows = null;
        $external_id = $args->tcPrefix . $rf['external_id'];
        $rows[$cols['tsuite']] = $tsuiteCache[$tsuiteID];

        $name = htmlspecialchars("{$external_id}:{$rf['name']}",ENT_QUOTES);

        $fo = !is_null($forceFormat) ? $forceFormat : $args->format;
        if ($fo == FORMAT_HTML) {
          $rows[$cols['link']] = "<!-- " . 
            sprintf("%010d", $rf['external_id']) . " -->";

          if($args->addOpAccess) {  
            $rows[$cols['link']] .= 
              "<a href=\"javascript:openExecHistoryWindow({$tcaseID});\">" .
              $hist_img_tag .
              "<a href=\"javascript:openTCEditWindow({$tcaseID});\">" .
              $edit_img_tag; 
          }                       
          $rows[$cols['link']] .= $name;
        } else {
          $rows[$cols['link']] = "{$external_id}:{$rf['name']}";
        }

        $rows[$cols['platform']] = $gui->platforms[$platformID];

        if ($gui->options->testPriorityEnabled) {
          if ($rf['urg_imp'] >= $priorityCfg->threshold['high']) {            
            $rf['priority_level'] = HIGH;
          } else if( $rf['urg_imp'] < $priorityCfg->threshold['low']) {
            $rf['priority_level'] = LOW;
          } else {
            $rf['priority_level'] = MEDIUM;
          }

          switch($fo) {
            case FORMAT_XLS:
              // We need the human readable value, not the code
              $rows[$cols['priority']] = 
                $args->cfg['priority'][$rf['priority_level']];
            break;

            default:
              // Raw Code the human readable value will be 
              // constructed while rendering.
              $rows[$cols['priority']] = $rf['priority_level']; 
            break;
          }  
        }

        $statusVerbose = $labels[$rf['status']] .
                        sprintf($labels['versionTag'],$rf['version']);

        if ($fo == FORMAT_HTML) {
          $execOut = array('text' => '', 'value' => '', 'cssClass' => '');
          $execOut['text'] = $statusVerbose;
          $execOut['value'] = $rf['status'];
          $execOut['cssClass'] = $gui->map_status_css[$rf['status']];
          $rows[] = $execOut;
        }
        else {
          $rows[] = $statusVerbose;
        }
        $nv = '';
        if (isset($rf['execution_notes'])) {
          $nv = is_null($rf['execution_notes']) ? '' : 
                        $rf['execution_notes'];
        }
        if( $fo == FORMAT_XLS) {
          $rows[] = $nv;
        } 
        if( $fo == FORMAT_HTML) {
          $rows[] = ['text' => $nv];
        }
        $gui->matrix[] = $rows;
      } // $platformSet
    }  // $tcaseSet
  } // $tsuiteSet
}


/**
 *
 */
function initLblSpreadsheet() {
  $lbl = init_labels(array('title_test_suite_name' => null,
                           'platform' => null,
                           'priority' => null,
                           'title_test_case_title' => null,
                           'test_exec_by' => null,
                           'notes' => null, 
                           'date_time_run' => null, 
                           'execution_duration' => null,
                           'testproject' => null,
                           'generated_by_TestLink_on' => null,
                           'testplan' => null,
                           'result_on_last_build' => null,
                           'latest_execution' => null,
                           'assigned_to' => null,
                           'latest_exec_notes' => null,
                           'important_notice' => null));
  return $lbl;
} 

/**
 *
 */  
function initStyleSpreadsheet() {
  $style = array();
  $style['ReportContext'] = array('font' => array('bold' => true));
  $style['DataHeader'] = array('font' => array('bold' => true),
                           'borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                              'vertical' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
                           'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
                                           'startcolor' => array( 'argb' => 'FF9999FF'))
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
function xlsStepOne(&$oj,$style,&$lbl,&$gui) {
  $dummy = '';
  $lines2write = array(array($lbl['testproject'],$gui->tproject_name),
                       array($lbl['testplan'],$gui->tplan_name),
                       array($lbl['important_notice'],$gui->report_details),
                       array($lbl['generated_by_TestLink_on'],
                       localize_dateOrTimeStamp(null,$dummy,'timestamp_format',time())));

  $cellArea = "A1:"; 
  foreach($lines2write as $zdx => $fields) {
    $cdx = $zdx+1;
    $oj->setActiveSheetIndex(0)->setCellValue("A{$cdx}", current($fields))
                  ->setCellValue("B{$cdx}", end($fields));
  }
  $cellArea .= "A{$cdx}";
  $oj->getActiveSheet()->getStyle($cellArea)->applyFromArray($style['ReportContext']); 

  return $lines2write;
}  

/**
 *
 */
function initCols()
{
  $tcols = array('tsuite','link','platform','priority',
                 'latest_exec','latest_exec_notes');
  $cols = array_flip($tcols);
  return $cols;  
}  

/**
 *
 */
function doProcess(&$dbH,&$args,&$gui,&$metricsMgr) 
{
  $opt = array('getExecutionNotes' => true);

  $opt = array('output' => 'array');
  $neverRunOnPP = (array)$metricsMgr->getNeverRunOnSinglePlatform($args->tplan_id,$args->platform_id);

  $execStatus = (array)$metricsMgr->getLatestExecOnSinglePlatformMatrix($args->tplan_id,$args->platform_id,$opt);

  $allExec = array();
  foreach ($neverRunOnPP as $elem) {
    $allExec[] = $elem;  
  }
  foreach ($execStatus as $elem) {
    $allExec[] = $elem;  
  }

  // Every Test suite a row on matrix to display will be created
  $args->cols = initCols();
  if( !is_null($neverRunOnPP) || !is_null($execStatus)) {
    buildDataSet($dbH,$args,$gui,$allExec,$gui->labels);
  }

  $renderHTML = false;

  switch($args->format) {
    case FORMAT_XLS:
      createSpreadsheet($gui,$args,$args->getSpreadsheetBy);
    break;  

    default:
      $renderHTML = true;
      $gui->tableSet[] = buildMatrix($gui, $args);
    break;
  }
}


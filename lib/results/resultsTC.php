<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
*
* @filesource   resultsTC.php
* @author       Francisco Mancardi <francisco.mancardi@gmail.com>
* 
* Test Results Matrix
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

$smarty = new TLSmarty;

$args = init_args($db);

$metricsMgr = new tlTestPlanMetrics($db);
$tplan_mgr  = &$metricsMgr; 

list($gui,$tproject_info,$labels,$cfg) = initializeGui($db,$args,$smarty->getImages(),$tplan_mgr);
$args->cfg = $cfg;

// Because we will try to send via email xls, we need to be careful
// with logic regarding args->format.
// may be we need to add in logic the media => email, download, etc
//
// We have faced a performance block due to an environment with
// 700 Builds and 1300 Test Cases on Test Plan
// This created a block on NOT RUN QUERY, but anyway will produce an enormous and
// unmanageable matrix on screen
//
// New way to process:
// ACTIVE Build Qty > 20 => Ask user to select builds he/she wants to use
// Cell Qty = (ACTIVE Build Qty x Test Cases on Test plan) > 2000 => said user I'm sorry
//
if( ($gui->activeBuildsQty <= $gui->matrixCfg->buildQtyLimit) || $args->do_action == 'result')
{
  setUpBuilds($args,$gui);

  $tpl = $templateCfg->default_template;
  $opt = null;
  $buildSet = array('buildSet' => $args->builds->idSet);

  if($args->format == FORMAT_XLS)
  {
    $opt = array('getExecutionNotes' => true, 'getTester' => true,
                 'getUserAssignment' => true, 
                 'getExecutionTimestamp' => true, 'getExecutionDuration' => true);
  }    
  $execStatus = $metricsMgr->getExecStatusMatrix($args->tplan_id,$buildSet,$opt);
  $metrics = $execStatus['metrics'];
  $latestExecution = $execStatus['latestExec']; 

  // Every Test suite a row on matrix to display will be created
  // One matrix will be created for every platform that has testcases
  $args->cols = initCols($gui->show_platforms);
  if( !is_null($execStatus['metrics']) )
  {
    buildDataSet($db,$args,$gui,$execStatus,$labels);
  }

  $renderHTML = false;
  switch($args->format)
  {
    case FORMAT_XLS:
      createSpreadsheet($gui,$args,$args->getSpreadsheetBy);
    break;  

    default:
      $renderHTML = true;
      $gui->tableSet[] = buildMatrix($gui, $args);
    break;
  }
}  
else
{
  // We need to ask user to do a choice
  $tpl = 'resultsTCLauncher.tpl';
  $gui->pageTitle = $labels['test_result_matrix_filters'];
  if($gui->matrixCfg->buildQtyLimit > 0)
  {  
    $gui->userFeedback = $labels['too_much_data'] . '<br>' .
                         sprintf($labels['too_much_builds'],$gui->activeBuildsQty,$gui->matrixCfg->buildQtyLimit);
  }
}  


$timerOff = microtime(true);
$gui->elapsed_time = round($timerOff - $timerOn,2);

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
                   "do_action" => array(tlInputParameter::STRING_N,5,10),
                   "build_set" => array(tlInputParameter::ARRAY_INT),
                   "buildListForExcel" => array(tlInputParameter::STRING_N,0,100),
                   "format" => array(tlInputParameter::INT_N));

  
  $args = new stdClass();
  R_PARAMS($iParams,$args);

  $args->getSpreadsheetBy = isset($_REQUEST['sendSpreadSheetByMail_x']) ? 'email' : null;
  if( is_null($args->getSpreadsheetBy) )
  {
    $args->getSpreadsheetBy = isset($_REQUEST['exportSpreadSheet_x']) ? 'download' : null;
  }  


  $args->addOpAccess = true;
  if( !is_null($args->apikey) )
  {
    //var_dump($args);
    $cerbero = new stdClass();
    $cerbero->args = new stdClass();
    $cerbero->args->tproject_id = $args->tproject_id;
    $cerbero->args->tplan_id = $args->tplan_id;

    if(strlen($args->apikey) == 32)
    {
      $cerbero->args->getAccessAttr = true;
      $cerbero->method = 'checkRights';
      $cerbero->redirect_target = "../../login.php?note=logout";
      setUpEnvForRemoteAccess($dbHandler,$args->apikey,$cerbero);
    }
    else
    {
      $args->addOpAccess = false;
      $cerbero->method = null;
      setUpEnvForAnonymousAccess($dbHandler,$args->apikey,$cerbero);
    }  
  }
  else
  {
    testlinkInitPage($dbHandler,false,false,"checkRights");  
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  }

  if($args->tproject_id <= 0)
  {
    $msg = __FILE__ . '::' . __FUNCTION__ . " :: Invalid Test Project ID ({$args->tproject_id})";
    throw new Exception($msg);
  }

  switch($args->format)
  {
    case FORMAT_XLS:
      if($args->buildListForExcel != '')
      {  
        $args->build_set = explode(',',$args->buildListForExcel);
      }  
    break;
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
  if(is_null($context))
  {
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
function buildMatrix(&$guiObj,&$argsObj,$forceFormat=null)
{  
  $buildIDSet = $argsObj->builds->idSet;
  $latestBuild = $argsObj->builds->latest;

  $columns = array(array('title_key' => 'title_test_suite_name', 'width' => 100),
                   array('title_key' => 'title_test_case_title', 'width' => 150));

  $lbl = init_labels(array('title_test_suite_name' => null,'platform' => null,'priority' => null,
                           'result_on_last_build' => null, 'title_test_case_title' => null));
  
  $group_name = $lbl['title_test_suite_name'];

  if(!is_null($guiObj->platforms))
  {
    $columns[] = array('title_key' => 'platform', 'width' => 60, 'filter' => 'list', 
                       'filterOptions' => $guiObj->platforms);
    $group_name = $lbl['platform'];
  }
  if($guiObj->options->testPriorityEnabled) 
  {
    $columns[] = array('title_key' => 'priority', 'type' => 'priority', 'width' => 40);
  }
  
  // --------------------------------------------------------------------
  $guiObj->filterFeedback = null;
  foreach($buildIDSet as $iix)
  {
    $buildSet[] = $guiObj->buildInfoSet[$iix];
    if($guiObj->filterApplied)
    {
      $guiObj->filterFeedback[] = $guiObj->buildInfoSet[$iix]['name'];
    }
  }  

  if( $guiObj->matrixCfg->buildColumns['showStatusLastExecuted'] )
  {
    $buildSet[] = array('name' => $lbl['result_on_last_build'] . ' ' . $latestBuild->name);
  }
  
  foreach($buildSet as $build) 
  {
    $columns[] = array('title' => $build['name'], 'type' => 'status', 'width' => 100);
  }
  // --------------------------------------------------------------------
  
  
  $columns[] = array('title_key' => 'last_execution', 'type' => 'status', 'width' => 100);

  $fo = !is_null($forceFormat) ? $forceFormat : $argsObj->format; 
  if ($fo == FORMAT_HTML) 
  {
    $matrix = new tlExtTable($columns, $guiObj->matrix, 'tl_table_results_tc');
    
    //if platforms feature is enabled group by platform otherwise group by test suite
    $matrix->setGroupByColumnName($group_name);
    $matrix->sortDirection = 'DESC';

    if($guiObj->options->testPriorityEnabled) 
    {
      // Developer Note:
      // To understand 'filter' => 'Priority' => see exttable.class.php => buildColumns()
      $matrix->addCustomBehaviour('priority', array('render' => 'priorityRenderer', 'filter' => 'Priority'));
      $matrix->setSortByColumnName($lbl['priority']);
    } 
    else 
    {
      $matrix->setSortByColumnName($lbl['title_test_case_title']);
    }
    
    // define table toolbar
    $matrix->showToolbar = true;
    $matrix->toolbarExpandCollapseGroupsButton = true;
    $matrix->toolbarShowAllColumnsButton = true;

  } 
  else 
  {
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
  
  $cfg = array('results' => config_get('results'), 'urgency' => config_get('urgency'),
               'tcase' => config_get('testcase_cfg'));

  $guiObj = new stdClass();
  $guiObj->map_status_css = null;
  $guiObj->title = lang_get('title_test_report_all_builds');
  $guiObj->printDate = '';
  $guiObj->matrix = array();

  $guiObj->platforms = $tplanMgr->getPlatforms($argsObj->tplan_id,array('outputFormat' => 'map'));
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

  $l18n = init_labels(array('design' => null, 'execution' => null, 'history' => 'execution_history',
                            'test_result_matrix_filters' => null, 'too_much_data' => null,'too_much_builds' => null,
                            'result_on_last_build' => null, 'versionTag' => 'tcversion_indicator') );

  $l18n['not_run']=lang_get($cfg['results']['status_label']['not_run']);


  $guiObj->matrixCfg  = config_get('resultMatrixReport');
  $guiObj->buildInfoSet = $tplanMgr->get_builds($argsObj->tplan_id, testplan::ACTIVE_BUILDS,null,
                                                array('orderBy' => $guiObj->matrixCfg->buildOrderByClause)); 
  $guiObj->activeBuildsQty = count($guiObj->buildInfoSet);


  // hmm need to understand if this can be removed
  if ($guiObj->matrixCfg->buildColumns['latestBuildOnLeft'])
  {
    $guiObj->buildInfoSet = array_reverse($guiObj->buildInfoSet);
  }
  // -------------------------------------------------------------------------------


  foreach($cfg['results']['code_status'] as $code => $verbose)
  {
    if( isset($cfg['results']['status_label'][$verbose]))
    {
      $l18n[$code] = lang_get($cfg['results']['status_label'][$verbose]);
      $guiObj->map_status_css[$code] = $cfg['results']['code_status'][$code] . '_text';
    }
  }

  $xxx = config_get('urgency');
  foreach ($xxx['code_label'] as $code => $label) 
  {
    $cfg['priority'][$code] = lang_get($label);
  } 
 
  $guiObj->mailCfg = buildMailCfg($guiObj);

  return array($guiObj,$tproject_info,$l18n,$cfg);
}

/**
 *
 *
 */
function createSpreadsheet($gui,$args,$media)
{
  $buildIDSet = $args->builds->idSet;
  $latestBuild = $args->builds->latest;

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
  // Exec result on Build 1
  // Assigned To
  // Date
  // Tester
  // Notes
  // Duration
  //
  // Exec result on Build 2
  // Assigned To
  // ...
  // ...
  // Exec result on Build N
  // Exec result on ON LATEST CREATED Build
  // Latest Execution result (Hmm need to explain better)
  // 
  $dataHeader = array($lbl['title_test_suite_name'],$lbl['title_test_case_title']);

  if( $showPlatforms = !is_null($gui->platforms) )
  {
    $dataHeader[] = $lbl['platform'];
  }

  if($gui->options->testPriorityEnabled)
  {  
    $dataHeader[] = $lbl['priority'];
  }


  $gui->filterFeedback = null;
  foreach($buildIDSet as $iix)
  {
    $dataHeader[] = $lbl['build'] . ' ' . $gui->buildInfoSet[$iix]['name'];
    $dataHeader[] = $lbl['assigned_to'];
    $dataHeader[] = $lbl['date_time_run'];
    $dataHeader[] = $lbl['test_exec_by'];
    $dataHeader[] = $lbl['notes'];
    $dataHeader[] = $lbl['execution_duration'];

    if($gui->filterApplied)
    {
      $gui->filterFeedback[] = $gui->buildInfoSet[$iix]['name'];
    }
  }  


  // Now the magic
  if( $gui->matrixCfg->buildColumns['showStatusLastExecuted'] )
  {  
    $dataHeader[] = $lbl['result_on_last_build'];
  }
  $dataHeader[] = $lbl['last_execution'];

  $startingRow = count($lines2write) + 2; // MAGIC
  $cellArea = "A{$startingRow}:";
  foreach($dataHeader as $zdx => $field)
  {
    $cellID = $cellRange[$zdx] . $startingRow; 
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);
    $cellAreaEnd = $cellRange[$zdx];
  }

  $cellArea .= "{$cellAreaEnd}{$startingRow}";
  $objPHPExcel->getActiveSheet()->getStyle($cellArea)->applyFromArray($style['DataHeader']);	
  
  $startingRow++;
  $qta_loops = count($gui->matrix);
  for($idx = 0; $idx < $qta_loops; $idx++)
  {
		foreach($gui->matrix[$idx] as $ldx => $field)
		{
			$cellID = $cellRange[$ldx] . $startingRow; 
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);
		}
		$startingRow++;
  }
  
  // Final step
  $tmpfname = tempnam(config_get('temp_dir'),"resultsTC.tmp");
  $objPHPExcel->setActiveSheetIndex(0);
  $xlsType = 'Excel5';                               
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $xlsType);  
  $objWriter->save($tmpfname);
  
  if($args->getSpreadsheetBy == 'email')
  {
    require_once('email_api.php');

    $ema = new stdClass();
    $ema->from_address = config_get('from_email');
    $ema->to_address = $args->user->emailAddress;;
    $ema->subject = $gui->mailCfg->subject;
    $ema->message = $gui->mailCfg->subject;
    
    $dum = uniqid("resultsTC_") . '.xls';
    $oops = array('attachment' => 
                  array('file' => $tmpfname, 'newname' => $dum),
                  'exit_on_error' => true, 'htmlFormat' => true);
    $email_op = email_send_wrapper($ema,$oops);
    unlink($tmpfname);
    exit(); 
  } 
  else
  {
    downloadXls($tmpfname,$xlsType,$gui,'resultsTC_');
  } 
}


/**
 *
 */
function setUpBuilds(&$args,&$gui)
{ 
  $args->builds = new stdClass();

  if( is_null($args->build_set) )
  {
    $args->builds->idSet = null;
    
    $gui->buildListForExcel = '';
    $gui->filterApplied = false;
    if( !is_null($gui->buildInfoSet) )
    {
      $args->builds->idSet = array_keys($gui->buildInfoSet);
    }
  }  
  else
  {
    $args->builds->idSet = array_keys(array_flip($args->build_set));
    $gui->filterApplied = true;
    $gui->buildListForExcel = implode(',',$buildIDSet); 
  }

  $args->builds->latest = new stdClass();
  $args->builds->latest->id = end($args->builds->idSet);
  $args->builds->latest->name = $gui->buildInfoSet[$args->builds->latest->id]['name'];
}




/**
 *
 *
 */
function buildDataSet(&$db,&$args,&$gui,&$exec,$labels,$forceFormat=null)
{
  $userSet = getUsersForHtmlOptions($db,null,null,null,null,
                                    array('userDisplayFormat' => '%first% %last%'));

  // invariant pieces  => avoid wasting time on loops
  $dlink = '<a href="' . str_replace(" ", "%20", $args->basehref) . 
           'linkto.php?tprojectPrefix=' . urlencode($args->prefix) . '&item=testcase&id=';  

  $hist_img_tag = '<img title="' . $labels['history'] . '"' . ' src="' . $gui->img->history . '" /></a> ';
  $edit_img_tag = '<img title="' . $labels['design'] . '"' . ' src="' . $gui->img->edit . '" /></a> ';

  $metrics = $exec['metrics'];
  $latestExecution = $exec['latestExec'];
  $cols = $args->cols;

  $tsuiteSet = array_keys($metrics);
  foreach($tsuiteSet as $tsuiteID)
  {
    $tcaseSet = array_keys($metrics[$tsuiteID]);
    foreach($tcaseSet as $tcaseID)
    {
      $platformSet = array_keys($metrics[$tsuiteID][$tcaseID]);
      foreach($platformSet as $platformID)
      {
        $rf = &$metrics[$tsuiteID][$tcaseID][$platformID];
        $rows = null;

        // some info does not change on different executions
        $build2loop = array_keys($rf);
        $top = current($build2loop);
        $external_id = $args->tcPrefix . $rf[$top]['external_id'];
        $rows[$cols['tsuite']] = $rf[$top]['suiteName'];


        $name = htmlspecialchars("{$external_id}:{$rf[$top]['name']}",ENT_QUOTES);

        $fo = !is_null($forceFormat) ? $forceFormat : $args->format;
        if($fo == FORMAT_HTML)
        {
          $rows[$cols['link']] = "<!-- " . sprintf("%010d", $rf[$top]['external_id']) . " -->";
          if($args->addOpAccess)
          {  
            $rows[$cols['link']] .= "<a href=\"javascript:openExecHistoryWindow({$tcaseID});\">" .
                                    $hist_img_tag .
                                    "<a href=\"javascript:openTCEditWindow({$tcaseID});\">" .
                                    $edit_img_tag; 
          }                       
          $rows[$cols['link']] .= $name;
        }
        else
        {
          $rows[$cols['link']] = "{$external_id}:{$rf[$top]['name']}";
        }

        if ($gui->show_platforms)
        {
          $rows[$cols['platform']] = $gui->platforms[$platformID];
        }

        if($gui->options->testPriorityEnabled) 
        {
          switch($fo)
          {
            case FORMAT_XLS:
              $rows[$cols['priority']] = $args->cfg['priority'][$rf[$top]['priority_level']];
            break;
              
            default:
              // is better to use code to do reorder instead of localized string ???
              $rows[$cols['priority']] = $rf[$top]['priority_level'];
            break;
          }  
        }

        // Now loop on result on each build, but following order
        $buildExecStatus = null;  
        $execOnLastBuild = null;
        foreach($args->builds->idSet as $buildID)
        {
          $r4build['text'] = "";

          if( $fo == FORMAT_XLS)
          {
            $r4build = $labels[$rf[$buildID]['status']] .
                       sprintf($labels['versionTag'],$rf[$buildID]['version']);

            $tester = '';           
            if(isset($userSet,$rf[$buildID]['tester_id']))
            {
              $tester = $userSet[$rf[$buildID]['tester_id']];
            }

            $assignee = '';
            if(isset($userSet,$rf[$buildID]['user_id']))
            {
              $assignee = $userSet[$rf[$buildID]['user_id']];
            }

            $bella = array($r4build,$assignee,
                           $rf[$buildID]['execution_ts'],$tester,
                           $rf[$buildID]['execution_notes'],
                           $rf[$buildID]['execution_duration']);            
            $buildExecStatus = array_merge((array)$buildExecStatus, $bella);
          }
          else
          {
            $r4build['text'] = "";
          }  

          if ($fo == FORMAT_HTML ) 
          {
            if ($args->addOpAccess)
            {
              $r4build['text'] = "<a href=\"javascript:openExecutionWindow(" .
                                 "{$tcaseID}, {$rf[$buildID]['tcversion_id']}, {$buildID}, " .
                                 "{$args->tplan_id}, {$platformID});\">" .
                                  "<img title=\"{$labels['execution']}\" src=\"{$gui->img->exec}\" /></a> ";
            }  
    
            $r4build['text'] .= $labels[$rf[$buildID]['status']] .
                                sprintf($labels['versionTag'],$rf[$buildID]['version']);
    
            $r4build['value'] = $rf[$buildID]['status'];
            $r4build['cssClass'] = $gui->map_status_css[$rf[$buildID]['status']];
            $buildExecStatus[] = $r4build;
          }

          if($gui->matrixCfg->buildColumns['showStatusLastExecuted'] && 
             $args->builds->latest->id == $buildID)
          {
            $execOnLastBuild = $r4build;  
          }              

          // why we do special reasoning on NOT RUN ???
          if( ($latestExecution[$platformID][$tcaseID]['status'] == 
               $args->cfg['results']['status_code']['not_run']) ||
              ( ($latestExecution[$platformID][$tcaseID]['build_id'] == $buildID) &&                             
                ($latestExecution[$platformID][$tcaseID]['id'] == $rf[$buildID]['executions_id']) ) 
            )                  
          {
            $lexec = $r4build;
          }
        } // foreach buildIDSet
        
        // Ok, now the specials
        // If configured, add column with Exec result on Latest Created Build
        if ($gui->matrixCfg->buildColumns['showStatusLastExecuted'])
        {
          $buildExecStatus[] = $execOnLastBuild;
        }
        
        if ($gui->matrixCfg->buildColumns['latestBuildOnLeft']) 
        {
          $buildExecStatus = array_reverse($buildExecStatus);
        }

        $rows = array_merge($rows, $buildExecStatus);
          
        // Always righmost column will display lastest execution result
        $rows[] = $lexec;
         
        $gui->matrix[] = $rows;
        unset($r4build);
        unset($rows);
        unset($buildExecStatus);
      } // $platformSet
    }  // $tcaseSet
  } // $tsuiteSet
}


/**
 *
 */
function initLblSpreadsheet()
{
  $lbl = init_labels(array('title_test_suite_name' => null,
                           'platform' => null,'priority' => null,
                           'build' => null, 'title_test_case_title' => null,'test_exec_by' => null,
                           'notes' => null, 'date_time_run' => null, 'execution_duration' => null,
                           'testproject' => null,'generated_by_TestLink_on' => null,'testplan' => null,
                           'result_on_last_build' => null,'last_execution' => null,'assigned_to' => null));
  return $lbl;
} 

/**
 *
 */  
function initStyleSpreadsheet()
{
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
function setCellRangeSpreadsheet()
{
  $cr = range('A','Z');
  $crLen = count($cr);
  for($idx = 0; $idx < $crLen; $idx++)
  {
    for($jdx = 0; $jdx < $crLen; $jdx++) 
    {
      $cr[] = $cr[$idx] . $cr[$jdx];
    }
  }
  return $cr;
}  

/**
 *
 */
function xlsStepOne(&$oj,$style,&$lbl,&$gui)
{
  $dummy = '';
  $lines2write = array(array($lbl['testproject'],$gui->tproject_name),
                       array($lbl['testplan'],$gui->tplan_name),
                       array($lbl['generated_by_TestLink_on'],
                       localize_dateOrTimeStamp(null,$dummy,'timestamp_format',time())));

  $cellArea = "A1:"; 
  foreach($lines2write as $zdx => $fields)
  {
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
function initCols($showPlat)
{
  $tcols = array('tsuite', 'link');
  if($showPlat)
  {
    $tcols[] = 'platform';
  }
  $tcols[] = 'priority';
  $cols = array_flip($tcols);
  return $cols;  
}  

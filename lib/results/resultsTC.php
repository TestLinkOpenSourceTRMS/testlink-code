<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
*
* @filesource   resultsTC.php
* @author       Francisco Mancardi <francisco.mancardi@gmail.com>
* @author       Martin Havlat <havlat@users.sourceforge.net>
* @author       Chad Rosen
* 
* Show Test Report by individual test case.
*
* @internal revisions
* @since 1.9.10
*/
require('../../config.inc.php');
require_once('../../third_party/codeplex/PHPExcel.php');   // Must be included BEFORE common.php
require_once('common.php');
require_once('displayMgr.php');
require_once('exttable.class.php');

$timerOn = microtime(true);   // will be used to compute elapsed time
$templateCfg = templateConfiguration();

$smarty = new TLSmarty;
$args = init_args($db);

$metricsMgr = new tlTestPlanMetrics($db);
$tplan_mgr  = &$metricsMgr; // displayMemUsage('START' . __FILE__);

list($gui,$tproject_info,$labels,$cfg) = initializeGui($db,$args,$smarty->getImages(),$tplan_mgr);

$tprojectOpt = $_SESSION['testprojectOptions'];
$testCaseCfg = config_get('testcase_cfg');
$testCasePrefix = $tproject_info['prefix'] . $testCaseCfg->glue_character;;
unset($testCaseCfg);

$mailCfg = buildMailCfg($gui); //displayMemUsage('Before getExecStatusMatrix()');

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
  if( is_null($args->build_set) )
  {
    $buildIDSet = null;
    $gui->buildListForExcel = '';
    $gui->filterApplied = false;
    if( !is_null($gui->buildInfoSet) )
    {
      $buildIDSet = array_keys($gui->buildInfoSet);
    }
  }  
  else
  {
    $buildIDSet = array_keys(array_flip($args->build_set));
    $gui->filterApplied = true;
    $gui->buildListForExcel = implode(',',$buildIDSet); 
  }  
  $lastestBuild = new stdClass();
  $lastestBuild->id = end($buildIDSet);
  $lastestBuild->name = $gui->buildInfoSet[$lastestBuild->id]['name'];

  $tpl = $templateCfg->default_template;

  $opt = null;
  if($args->format == FORMAT_XLS)
  {
    $opt = array('getExecutionNotes' => true, 'getTester' => true,
                 'getExecutionTimestamp' => true, 'getExecutionDuration' => true);
  }

  $execStatus = $metricsMgr->getExecStatusMatrix($args->tplan_id,array('buildSet' => $buildIDSet), $opt);
  $metrics = $execStatus['metrics'];

  $latestExecution = $execStatus['latestExec']; //displayMemUsage('Before UNSET');
  unset($execStatus); // displayMemUsage('AFTER UNSET');

  // Every Test suite a row on matrix to display will be created
  // One matrix will be created for every platform that has testcases
  $tcols = array('tsuite', 'link');
  if( ($show_platforms = !is_null($gui->platforms)) )
  {
    $tcols[] = 'platform';
  }
  $tcols[] = 'priority';
  $cols = array_flip($tcols);

  if( !is_null($metrics) )
  {
    $userSet = getUsersForHtmlOptions($db,null,null,null,null,array('userDisplayFormat' => '%first% %last%'));

    // invariant pieces  => avoid wasting time on loops
    $dlink = '<a href="' . str_replace(" ", "%20", $args->basehref) . 'linkto.php?tprojectPrefix=' . 
             urlencode($tproject_info['prefix']) . '&item=testcase&id=';  

    $hist_img_tag = '<img title="' . $labels['history'] . '"' . ' src="' . $gui->img->history . '" /></a> ';
    $edit_img_tag = '<img title="' . $labels['design'] . '"' . ' src="' . $gui->img->edit . '" /></a> ';
 
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
          $external_id = $testCasePrefix . $rf[$top]['external_id'];
          $rows[$cols['tsuite']] = $rf[$top]['suiteName'];
        
          // build HTML Links to test case
          switch($args->format)
          {
            case FORMAT_XLS:
              $rows[$cols['link']] = "{$external_id}:{$rf[$top]['name']}";
            break;
            
            default:
              $name = htmlspecialchars("{$external_id}:{$rf[$top]['name']}",ENT_QUOTES);
              if($args->format == FORMAT_HTML)
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
                $rows[$cols['link']] = $dlink . urlencode($external_id) . '">' . "{$name}</a> ";
              }
            break;
          }

          if ($show_platforms)
          {
            $rows[$cols['platform']] = $gui->platforms[$platformID];
          }

          if($gui->options->testPriorityEnabled) 
          {
            
            switch($args->format)
            {
              case FORMAT_XLS:
                $rows[$cols['priority']] = $cfg['priority'][$rf[$top]['priority_level']];
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
          foreach($buildIDSet as $buildID)
          {
            // new dBug($rf);
            if( $args->format == FORMAT_XLS)
            {
              $r4build = $labels[$rf[$buildID]['status']] .
                         sprintf($labels['versionTag'],$rf[$buildID]['version']);

              $tester = '';           
              if(isset($userSet,$rf[$buildID]['tester_id']))
              {
                $tester = $userSet[$rf[$buildID]['tester_id']];
              }

              $bella = array($r4build,$rf[$buildID]['execution_ts'],$tester,
                             $rf[$buildID]['execution_notes'],$rf[$buildID]['execution_duration']);            
              $buildExecStatus = array_merge((array)$buildExecStatus, $bella);
            }
            else
            {
              $r4build['text'] = "";

              if ($args->format == FORMAT_HTML && $args->addOpAccess) 
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
          
            if($gui->matrixCfg->buildColumns['showStatusLastExecuted'] && $lastestBuild->id == $buildID)
            {
              $execOnLastBuild = $r4build;  
            }              

            // why we do special reasoning on NOT RUN ???
            if( ($latestExecution[$platformID][$tcaseID]['status'] == $cfg['results']['status_code']['not_run']) ||
                ( ($latestExecution[$platformID][$tcaseID]['build_id'] == $buildID) &&                             
                  ($latestExecution[$platformID][$tcaseID]['id'] == $rf[$buildID]['executions_id']) ) 
              )                  
            {
              $lexec = $r4build;
            }
          }

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

          // new dBug($rows);
          // new dBug($buildExecStatus);
          
          $rows = array_merge($rows, $buildExecStatus);
          
          // Always righmost column will display lastest execution result
          $rows[] = $lexec;
         
          $gui->matrix[] = $rows;

          // new dBug($gui->matrix);

          unset($r4build);
          unset($rows);
          unset($buildExecStatus);
        } // $platformSet
        
      }  // $tcaseSet
  
    } // $tsuiteSet
  }  
  unset($metrics);
  unset($latestExecution);

  switch($args->format)
  {
    case FORMAT_XLS:
      // new dBug($gui->matrix);      die();
      createSpreadsheet($gui,$args,$buildIDSet);
    break;  

    default:
      // new dBug($gui->matrix);
      // die();

     $gui->tableSet[] =  buildMatrix($gui, $args, $buildIDSet, $lastestBuild);
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
displayReport($templateCfg->template_dir . $tpl, $smarty, $args->format, $mailCfg);

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
function buildMatrix(&$guiObj,&$argsObj,$buildIDSet,$latestBuild)
{
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
  if ($argsObj->format == FORMAT_HTML) 
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
  
  $cfg = array('results' => config_get('results'), 'urgency' => config_get('urgency'));
  
  $guiObj = new stdClass();
  $guiObj->map_status_css = null;
  $guiObj->title = lang_get('title_test_report_all_builds');
  $guiObj->printDate = '';
  $guiObj->matrix = array();

  $guiObj->platforms = $tplanMgr->getPlatforms($argsObj->tplan_id,array('outputFormat' => 'map'));

  $guiObj->img = new stdClass();
  $guiObj->img->exec = $imgSet['exec_icon'];
  $guiObj->img->edit = $imgSet['edit_icon'];
  $guiObj->img->history = $imgSet['history_small'];

  $guiObj->tproject_id = $argsObj->tproject_id;
  $guiObj->tplan_id = $argsObj->tplan_id;

  $guiObj->apikey = $argsObj->apikey;


  $tproject_mgr = new testproject($dbHandler);
  $tproject_info = $tproject_mgr->get_by_id($argsObj->tproject_id);
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


  $guiObj->buildInfoSet = $tplanMgr->get_builds($argsObj->tplan_id, testplan::ACTIVE_BUILDS); 
  $guiObj->activeBuildsQty = count($guiObj->buildInfoSet);

  $guiObj->matrixCfg  = config_get('resultMatrixReport');

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
 
  return array($guiObj,$tproject_info,$l18n,$cfg);
}

/**
 *
 *
 */
function createSpreadsheet($gui,$args,$buildIDSet)
{
  $lbl = init_labels(array('title_test_suite_name' => null,'platform' => null,'priority' => null,
                           'build' => null, 'title_test_case_title' => null,'test_exec_by' => null,
                           'notes' => null, 'date_time_run' => null, 'execution_duration' => null,
                           'testproject' => null,'generated_by_TestLink_on' => null,'testplan' => null,
                           'result_on_last_build' => null,'last_execution' => null));

  $cellRange = range('A','Z');
  // $colors4cell = array('font' => array('color' => PHPExcel_Style_Color::COLOR_RED));

  $styleReportContext = array('font' => array('bold' => true));
  $styleDataHeader = array('font' => array('bold' => true),
                           'borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                              'vertical' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
                           'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
                                           'startcolor' => array( 'argb' => 'FF9999FF'))
                           );
  $dummy = '';
  $lines2write = array(array($lbl['testproject'],$gui->tproject_name),
                       array($lbl['testplan'],$gui->tplan_name),
                       array($lbl['generated_by_TestLink_on'],
                       localize_dateOrTimeStamp(null,$dummy,'timestamp_format',time())));

  $objPHPExcel = new PHPExcel();
  $cellArea = "A1:"; 
  foreach($lines2write as $zdx => $fields)
  {
    $cdx = $zdx+1;
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A{$cdx}", current($fields))
                ->setCellValue("B{$cdx}", end($fields));
  }
  $cellArea .= "A{$cdx}";
  $objPHPExcel->getActiveSheet()->getStyle($cellArea)->applyFromArray($styleReportContext);	


  // Step 2
  // data is organized with following columns$dataHeader[]
  // Test suite
  // Test case
  // [Platform]
  // Priority   ===>  Just discovered that we have choosen to make this column
  //                  displayabled or not according test project configuration
  //                  IMHO has no sense work without priority
  // 
  // Exec result on Build 1
  // Date
  // Tester
  // Notes
  // Duration
  //
  // Exec result on Build 2
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
  $objPHPExcel->getActiveSheet()->getStyle($cellArea)->applyFromArray($styleDataHeader);	
  
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
  $objPHPExcel->setActiveSheetIndex(0);
  $settings = array();
  $settings['Excel2007'] = array('ext' => '.xlsx', 
                                 'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  $settings['Excel5'] = array('ext' => '.xls', 
                              'Content-Type' => 'application/vnd.ms-excel');
  
  $xlsType = 'Excel5';                               
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $xlsType);
  
  $tmpfname = tempnam(config_get('temp_dir'),"resultsTC.tmp");
  $objWriter->save($tmpfname);
  
  $content = file_get_contents($tmpfname);
  unlink($tmpfname);
  $f2d = 'resultsTC_'. $gui->tproject_name . '_' . $gui->tplan_name . $settings[$xlsType]['ext'];
  downloadContentsToFile($content,$f2d,array('Content-Type' =>  $settings[$xlsType]['Content-Type']));
  exit();
}
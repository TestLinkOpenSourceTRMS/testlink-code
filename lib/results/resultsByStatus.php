<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * IMPORTANT NOTICE:
 * Only test cases that HAVE TESTER ASSIGNED will be considered.
 *
 * @filesource  resultsByStatus.php
 * @package     TestLink
 * @copyright   2007-2017, TestLink community 
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

$args = init_args($db);
$statusCode = $args->statusCode;

$tplan_mgr = new testplan($db);

$tcase_mgr = new testcase($db);

$gui = initializeGui($db,$args,$tplan_mgr);
$its = &$gui->its;
$labels = &$gui->labels;

$testCaseCfg = config_get('testcase_cfg');
$metrics = getMetrics($db,$args,$gui);

$cfOnExec = $cfSet = null;

// done here in order to get some config about images
$smarty = new TLSmarty();
if( !is_null($metrics) and count($metrics) > 0 )
{              
  if($args->addOpAccess)
  {  
    $links = featureLinks($labels,$smarty->getImages());
  }  


  $userAccessKey = $gui->userAccessKey;
  $notesAccessKey = $gui->notesAccessKey;

  $urlSafeString = array();  
  $urlSafeString['tprojectPrefix'] = urlencode($gui->tproject_info['prefix']);
  $urlSafeString['basehref'] = str_replace(" ", "%20", $args->basehref);  
    
  $out = array();
  $users = getUsersForHtmlOptions($db);
  $pathCache = $topCache = $levelCache = null;
  $nameCache = initNameCache($gui);

  $odx = 0;

  if( $args->type != $statusCode['not_run'] )
  {
    // get Custom fields definition to understand columns to be added
    $cfSet = $tcase_mgr->cfield_mgr->get_linked_cfields_at_execution($args->tproject_id,true,'testcase');
    $execSet = array_keys($metrics);


    // go for Custom fields values of all executions on ONE SHOT!
    $cfOnExec = $tcase_mgr->cfield_mgr->get_linked_cfields_at_execution($args->tproject_id,true,'testcase',null,$execSet);
  }


  foreach($metrics as $execID => &$exec)
  {  
    // --------------------------------------------------------------------------
    // do some decode work, using caches
    if( !isset($pathCache[$exec['tcase_id']]) )
    {
      $dummy = $tcase_mgr->getPathLayered(array($exec['tcase_id']));  
      $pathCache[$exec['tcase_id']] = $dummy[$exec['tsuite_id']]['value'];
      $levelCache[$exec['tcase_id']] = $dummy[$exec['tsuite_id']]['level'];
      $ky = current(array_keys($dummy)); 
      $topCache[$exec['tcase_id']] = $ky;
    }
    
   
    // -------------------------------------------------------------------------
    // IMPORTANT NOTICE:
    // When test case has been runned, version must be get from
    // executions.tcversion_number 
    //
    // Column ORDER IS CRITIC                       
    // suiteName
    // testTitle   CCA-15708: RSRSR-150
    // testVersion   1
    // platformName XXXX  <<< ONlY is platforms have been used on 
    //                        Test plan under analisys
    //
    // buildName   2.0  <<< At least when platforms ARE NOT USED, 
    //                  <<< BY DEFAULT build is not displayed as 
    //                      column but used to group results.
    // testerName   yyyyyy
    // localizedTS   2012-04-25 12:14:55   <<<< ONLY if executed
    // notes   [empty string]  (execution notes)
    // bugString   [empty string]        <<<< ONLY if executed 
    //  
    $out[$odx]['suiteName'] =  $pathCache[$exec['tcase_id']];

    // -------------------------------------------------------------------------
    $zipper = '';
    switch($args->format)
    {
      case FORMAT_HTML:
        $out[$odx]['testTitle'] = "<!-- " . sprintf("%010d", $exec['external_id']) . " -->";
        $zipper = '';
        if($args->addOpAccess)
        {  
          $out[$odx]['testTitle'] .= sprintf($links['full'],
                                     $exec['tcase_id'],$exec['tcase_id'],$exec['tcversion_id'],
                                     $exec['build_id'],$args->tplan_id,$exec['platform_id'],$exec['tcase_id']);
          $zipper = '</a>';
        }
      break;

      case FORMAT_XLS:
        $out[$odx]['testTitle'] = '';
      break;

      default:
        $out[$odx]['testTitle'] = '<a href="' . $urlSafeString['basehref'] . 
                                  'linkto.php?tprojectPrefix=' . 
                                  $urlSafeString['tprojectPrefix'] . '&item=testcase&id=' . 
                                  urlencode($exec['full_external_id']) .'">';
        $zipper = '</a>';
      break;

    }

    // See IMPORTANT NOTICE/WARNING about XLS generation
    $out[$odx]['testTitle'] .= $exec['full_external_id'] . ':' . $exec['name'] . 
                               $zipper;

    $out[$odx]['testVersion'] =  $exec['tcversion_number'];
    
    // Insert order on out is CRITIC, because order is used on buildMatrix
    if($gui->show_platforms)
    {
      $out[$odx]['platformName'] = $nameCache['platform'][$exec['platform_id']];
    }

    $out[$odx]['buildName'] = $nameCache['build'][$exec['build_id']];

    // ------------------------------------------------------------------------
    // verbose user  
    if( $args->type == $statusCode['not_run'] )
    {
      natsort($exec[$userAccessKey]);
      $zux = array();
      foreach ($exec[$userAccessKey] as $vux) 
      {
        if(isset($users,$vux))
        {
           $zux[] = htmlspecialchars($users[$vux]);
        }
        else
        {
          // user id has been disable/deleted
          $zux[] = sprintf($labels['deleted_user'],$vux);
        }
      }
      $out[$odx]['testerName'] = implode(',',$zux);
    }  
    else
    {  
      if($exec[$userAccessKey] == 0 )
      {
        $out[$odx]['testerName'] = $labels['nobody'];
      }
      else
      {
        if(isset($users,$exec[$userAccessKey]))
        {
           $out[$odx]['testerName'] = htmlspecialchars($users[$exec[$userAccessKey]]);
        }
        else
        {
          // user id has been disable/deleted
          $out[$odx]['testerName'] = sprintf($labels['deleted_user'],$exec[$userAccessKey]);
        }
      }
    }
    $out[$odx]['testerName'] = htmlspecialchars($out[$odx]['testerName']);
    
    // -------------------------------------------------------------------------
    if( $args->type != $statusCode['not_run'] )
    {
      $out[$odx]['localizedTS'] = $exec['execution_ts'];
    }
    $out[$odx]['notes'] = strip_tags($exec[$notesAccessKey]);

    if( $args->type != $statusCode['not_run'] )
    {
      if(!is_null($cfSet))
      {
        // Need to document how important is value of second index on  
        // $out[$odx][SECOND INDEX] 
        foreach($cfSet as $cfID => $cfValue)
        {
          if(isset($cfOnExec[$execID][$cfID]) && !is_null($cfOnExec[$execID][$cfID]))
          {  
            $out[$odx][$cfID] = $tcase_mgr->cfield_mgr->string_custom_field_value($cfOnExec[$execID][$cfID],null);
          }  
          else
          {
            $out[$odx][$cfID] = '';
          }  
        }  
      }  

      // ------------------------------------------------------------------------
      // Bug processing. 
      // Remember that bugs are linked to executions NOT test case.
      // When using Platforms a Test Case can have multiple executions
      // (N on each platform).
      // ------------------------------------------------------------------------
      $bugString = '';
      if($gui->bugInterfaceOn && $exec['status'] != $statusCode['not_run']) 
      {
        $bugSet = get_bugs_for_exec($db, $its, $exec['executions_id'],array('id','summary'));
        if (count($bugSet) == 0) 
        {
          $gui->without_bugs_counter += 1;
        }

        switch($args->format)
        {
          case FORMAT_XLS:
            // See IMPORTANT NOTICE/WARNING about XLS generation
            foreach($bugSet as $bug) 
            {
              $bugString .= $bug['id'] . ':' . $bug['summary'] . "\r";
            }
          break;  

          default:
            foreach($bugSet as $bug) 
            {
              $bugString .= $bug['link_to_bts'] . '<br/>';
            }
          break;
        }
        unset($bugSet);    
      }
      $out[$odx]['bugString'] = $bugString;
    }
    $odx++;
  }
  $gui->dataSet = $out;
  unset($out);
}
else
{
  $gui->warning_msg = getWarning($args->type,$statusCode);
}  

switch($args->format)
{
  case FORMAT_XLS:
    createSpreadsheet($gui,$args,$args->getSpreadsheetBy,$cfSet);
  break;  

  default:
    $tableOpt = array('status_not_run' => ($args->type == $statusCode['not_run']),
                      'bugInterfaceOn' => $gui->bugInterfaceOn,
                      'format' => $args->format,
                      'show_platforms' => $gui->show_platforms);

    $gui->tableSet[] = buildMatrix($gui->dataSet, $args, $tableOpt ,
                                   $gui->platformSet,$cfSet);
  break;
} 

$smarty = new TLSmarty();
$smarty->assign('gui', $gui );
displayReport($tplCfg->template_dir . $tplCfg->default_template, 
              $smarty, $args->format, $gui->mailCfg);


/**
 * 
 *
 */
function init_args(&$dbHandler)
{
  $iParams = array("apikey" => array(tlInputParameter::STRING_N,32,64),
                   "tproject_id" => array(tlInputParameter::INT_N), 
                   "tplan_id" => array(tlInputParameter::INT_N),
                   "format" => array(tlInputParameter::INT_N),
                   "type" => array(tlInputParameter::STRING_N,0,1));

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
    testlinkInitPage($dbHandler,true,false,"checkRights");  
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  }
  

  $rCfg = config_get('results');
  $args->statusCode = $rCfg['status_code'];

  $args->user = $_SESSION['currentUser'];
  $args->basehref = $_SESSION['basehref'];
  return $args;
}

/**
 * initializeGui
 *
 */
function initializeGui(&$dbh,&$argsObj,&$tplanMgr)
{
  $tprojectMgr = new testproject($dbh);

  $guiObj = new stdClass();

  $guiObj->labels = init_labels(array('deleted_user' => null, 'design' => null, 
                                      'execution' => null,'nobody' => null,
                                      'execution_history' => null,
                                      'info_only_with_tester_assignment'  => null,
                                      'th_bugs_not_linked' => null,
                                      'info_notrun_tc_report' => null));

  $guiObj->report_context = $guiObj->labels['info_only_with_tester_assignment'];
  $guiObj->info_msg = '';
  $guiObj->bugs_msg = '';

  $guiObj->tplan_info = $tplanMgr->get_by_id($argsObj->tplan_id);
  $guiObj->tproject_info = $tprojectMgr->get_by_id($argsObj->tproject_id);
  $guiObj->tplan_name = $guiObj->tplan_info['name'];
  $guiObj->tproject_name = $guiObj->tproject_info['name'];


  $guiObj->format = $argsObj->format; 
  $guiObj->tproject_id = $argsObj->tproject_id; 
  $guiObj->tplan_id = $argsObj->tplan_id; 
  $guiObj->apikey = $argsObj->apikey;

  // Count for the Failed Issues whose bugs have to be raised/not linked. 
  $guiObj->without_bugs_counter = 0; 
  $guiObj->dataSet = null;
  $guiObj->title = null;
  $guiObj->type = $argsObj->type;
  $guiObj->warning_msg = '';


  // Implementation based on convention, will use only keys starting with 'list_tc_'
  // see reports.cfg.php
  $reportCfg = config_get('reports_list');

  $lbl_th_bugs_not_linked = lang_get('th_bugs_not_linked');
  $needle = 'list_tc_';
  $nl = strlen($needle);
  foreach( $reportCfg as $key => $val )
  {
    $checkIt = false;
    if( $checkIt = (strpos($key,$needle) !== FALSE) )
    {
      // now get the verbose status
      // list_tc_[verbose_status], example list_tc_not_run
      $verbose_status = substr($key, $nl);

      // if( $verbose_status != 'not_run' || $verbose_status != 'passed' )
      $guiObj->bugs_msg = $lbl_th_bugs_not_linked;
      if( isset($reportCfg[$key]['misc']) )
      {
        if( isset($reportCfg[$key]['misc']['bugs_not_linked']) &&  
            $reportCfg[$key]['misc']['bugs_not_linked'] == false ) 
        {
          $guiObj->bugs_msg = '';
        }  
      }  
    }

    if( $checkIt )
    {  
      if($argsObj->type == $argsObj->statusCode[$verbose_status])
      {
        $guiObj->title = lang_get('list_of_' . $verbose_status);
        break;
      }  
    }
  }    

  if(is_null($guiObj->title))
  {
    tlog('wrong value of GET type');
    exit();
  }

 
  // needed to decode
  $getOpt = array('outputFormat' => 'map');
  $guiObj->platformSet = $tplanMgr->getPlatforms($argsObj->tplan_id,$getOpt);
  if( !($guiObj->show_platforms = !is_null($guiObj->platformSet)) )
  {
    $guiObj->platformSet = array('');
  }

  $guiObj->buildSet = $tplanMgr->get_builds_for_html_options($argsObj->tplan_id);


  $guiObj->its = null;
  $info = $tprojectMgr->get_by_id($argsObj->tproject_id);
  $guiObj->bugInterfaceOn = $info['issue_tracker_enabled'];
  if($info['issue_tracker_enabled'])
  {
    $it_mgr = new tlIssueTracker($dbh);
    $guiObj->its = $it_mgr->getInterfaceObject($argsObj->tproject_id);
    unset($it_mgr);
  }  

  $guiObj->mailCfg = buildMailCfg($guiObj);

  return $guiObj;    

}



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
 * Builds ext-js rich table to display matrix results
 *
 * @param map dataSet: data to be displayed on matrix
 *
 * return tlExtTable
 *
 */
function buildMatrix($dataSet, &$args, $options = array(), $platforms,$customFieldColumns=null)
{
  $default_options = array('bugInterfaceOn' => false,'show_platforms' => false,
                           'status_not_run' => false,'format' => FORMAT_HTML);
  $options = array_merge($default_options, $options);

  $l18n = init_labels(array('assigned_to' => null,'platform' => null, 'th_date' => null,
                            'th_build' => null));



  $columns = array();
  $columns[] = array('title_key' => 'title_test_suite_name', 'width' => 80, 'type' => 'text');
  $columns[] = array('title_key' => 'title_test_case_title', 'width' => 80, 'type' => 'text');
  $columns[] = array('title_key' => 'version', 'width' => 30);
  if ($options['show_platforms'])
  {
    $columns[] = array('title_key' => 'platform', 'width' => 60, 'filter' => 'list', 'filterOptions' => $platforms);
  }
  if( $options['status_not_run'] )
  {
    $columns[] = array('title_key' => 'th_build', 'width' => 35);
    $columns[] = array('title_key' => 'assigned_to', 'width' => 60);
    $columns[] = array('title_key' => 'summary', 'width' => 150, 'type' => 'text');
  }
  else
  {
    $columns[] = array('title_key' => 'th_build', 'width' => 35);
    $columns[] = array('title_key' => 'th_run_by', 'width' => 60);
    $columns[] = array('title_key' => 'th_date', 'width' => 60);
    $columns[] = array('title_key' => 'title_execution_notes', 'width' => 150, 'type' => 'text');

    // 20130325
    if(!is_null($customFieldColumns))
    {
      foreach($customFieldColumns as $id => $def)
      {
        $columns[] = array('title' => $def['label'], 'width' => 60);
      }  
    }  

    if ($options['bugInterfaceOn'])
    {
      $columns[] = array('title_key' => 'th_bugs_id_summary', 'type' => 'text');
    }
  }


  if ($options['format'] == FORMAT_HTML)
  {

    // IMPORTANT DEVELOPMENT NOTICE
    // columns and dataSet are deeply related this means that inside
    // dataSet order has to be identical that on columns or table will be a disaster
    //
    $matrix = new tlExtTable($columns, $dataSet, 'tl_table_results_by_status');
    
    //if not run report: sort by test suite
    //blocked, failed report: sort by platform (if enabled) else sort by date
    $sort_name = 0;
    if ($options['status_not_run']) 
    {
      $sort_name = $l18n['assigned_to'];
    } 
    else 
    {
      $sort_name = $options['show_platforms'] ? $l18n['platform'] : $l18n['th_date'];
    }
    
    $matrix->setSortByColumnName($sort_name);
    $matrix->setGroupByColumnName($l18n['th_build']);

    $matrix->addCustomBehaviour('text', array('render' => 'columnWrap'));
    
    //define table toolbar
    $matrix->showToolbar = true;
    $matrix->toolbarExpandCollapseGroupsButton = true;
    $matrix->toolbarShowAllColumnsButton = true;
  }
  else
  {
    $matrix = new tlHTMLTable($columns, $dataSet, 'tl_table_results_by_status');
  }
  return $matrix;
}


/**
 *
 */
function featureLinks($lbl,$img)
{
  $links = array();

  // %s => test case id
  $links['exec_history'] = '<a href="javascript:openExecHistoryWindow(%s);" >' .
                       '<img title="' . $lbl['execution_history'] . '" ' .
                       'src="' . $img['history_small'] . '" /></a> ';

  // tcase_id,tcversion_id,build_id,tplan_id,platform_id
  $links['exec'] = '<a href="javascript:openExecutionWindow(%s,%s,%s,%s,%s);" >' .
             '<img title="' . $lbl['execution'] .'" ' .
             'src="' . $img['exec_icon'] . '" /></a> ';

  // %s => test case id
  $links['edit'] = '<a href="javascript:openTCEditWindow(%s);" >' .
          '<img title="' . $lbl['design'] . '" '. 
            'src="' . $img['edit_icon'] . '" /></a> ';


  $links['full'] = $links['exec_history'] . $links['exec'] . $links['edit'];

  return $links;
}


/**
 *
 */
function initNameCache($guiObj)
{
  $safeItems = array('build' => null, 'platform' => null);

  foreach($guiObj->buildSet as $id => $name)
  {
    $safeItems['build'][$id] = htmlspecialchars($name);  
  }

  if($guiObj->show_platforms)
  {
    foreach($guiObj->platformSet as $id => $name)
    {
      $safeItems['platform'][$id] = htmlspecialchars($name);  
    }
  }  
  
  return $safeItems;
}

/**
 *
 */
function getWarning($targetStatus,$statusCfg)
{
  $msg = ''; 
  $key2check = array('not_run','failed','blocked');
  foreach($key2check as $statusVerbose)
  {
    if( $targetStatus == $statusCfg[$statusVerbose] )
    {         
      $msg = lang_get('no_' . $statusVerbose . '_with_tester');
      break;
    }
  }
  return $msg;
} 

/**
 *
 */
function createSpreadsheet($gui,$args,$media,$customFieldColumns=null)
{
  $lbl = initLblSpreadsheet();
  $cellRange = range('A','Z');
  $style = initStyleSpreadsheet();

  $objPHPExcel = new PHPExcel();
  $lines2write = xlsStepOne($objPHPExcel,$style,$lbl,$gui);

  // Step 2
  // data is organized with following columns$dataHeader[]
  // Test suite
  // Test case
  // Version
  // [Platform]
  // Build
  // Tester
  // Date 
  // Execution notes
  // [Custom Field ENABLED ON EXEC 1]
  // [Custom Field ENABLED ON EXEC 1]
  // [Custom Field ENABLED ON EXEC 1]
  // [bugString]   only if bugtracking integration exists for this test project

  // This is HOW gui->dataSet is organized
  // THIS IS CRITIC ??
  //
  // suiteName   Issue Tracker Management
  // testTitle   PTRJ-76:Create issue tracker - no conflict
  // testVersion   1
  // [platformName]
  // buildName   1.0
  // testerName  admin
  // localizedTS   2013-03-28 20:15:06
  // notes   [empty string]
  // bugString   [empty string]  

  //
  $dataHeader = array($lbl['title_test_suite_name'],$lbl['title_test_case_title'],$lbl['version']);
  if( $showPlatforms = ( property_exists($gui,'platformSet') && !is_null($gui->platformSet) && 
                         !isset($gui->platformSet[0])) )
  {
    $dataHeader[] = $lbl['platform'];
  }

  $dataHeader[] = $lbl['build'];

  if( $gui->notRunReport )
  {
    $dataHeader[] = $lbl['assigned_to'];
    $dataHeader[] = $lbl['summary'];
  }
  else
  {
    $dataHeader[] = $lbl['th_run_by'];
    $dataHeader[] = $lbl['th_date'];
    $dataHeader[] = $lbl['title_execution_notes'];
  }


  if(!is_null($customFieldColumns))
  {
    foreach($customFieldColumns as $id => $def)
    {
      $dataHeader[] = $def['label'];
    }  
  }  

  // ATTENTION logic regarding NOT RUN IS MISSING
  // For not run this column and also columns regarding CF on exec are not displayed
  if( $gui->bugInterfaceOn && !$gui->notRunReport)  
  {
    $dataHeader[] = $lbl['th_bugs_id_summary'];
  }  

  $startingRow = count($lines2write) + 2; // MAGIC
  $cellArea = "A{$startingRow}:";
  foreach($dataHeader as $zdx => $field)
  {
    $cellID = $cellRange[$zdx] . $startingRow; 
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);
    $cellAreaEnd = $cellRange[$zdx];
  }
  $cellArea .= "{$cellAreaEnd}{$startingRow}";
  $objPHPExcel->getActiveSheet()->getStyle($cellArea)
              ->applyFromArray($style['DataHeader']);  


  // Now process data  
  $startingRow++;
  $qta_loops = count($gui->dataSet);
  for($idx = 0; $idx < $qta_loops; $idx++)
  {
    $line2write = $gui->dataSet[$idx];
    $colCounter = 0; 
    foreach($gui->dataSet[$idx] as $ldx => $field)
    {
      if( $ldx != 'bugString' || ($ldx == 'bugString' && $gui->bugInterfaceOn) )
      {  
        $cellID = $cellRange[$colCounter] . $startingRow; 
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, html_entity_decode($field) );
        $colCounter++;
      }
      
      // May be same processing can be applied to execution otes
      if(($ldx == 'bugString' && $gui->bugInterfaceOn))
      {
        // To manage new line
        // http://stackoverflow.com/questions/5960242/how-to-make-new-lines-in-a-cell-using-phpexcel
        // http://stackoverflow.com/questions/6054444/how-to-set-auto-height-in-phpexcel
        $objPHPExcel->setActiveSheetIndex(0)->getStyle($cellID)->getAlignment()->setWrapText(true);  
      }  
    }
    $cellEnd = $cellRange[$colCounter-1] . $startingRow;
    $startingRow++;
  }
  
  // Final step
  $objPHPExcel->setActiveSheetIndex(0);
  
  $xlsType = 'Excel5';                               
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $xlsType);
  
  $tmpfname = tempnam(config_get('temp_dir'),"resultsByStatus.tmp");
  $objWriter->save($tmpfname);

  if($args->getSpreadsheetBy == 'email')
  {
    require_once('email_api.php');

    $ema = new stdClass();
    $ema->from_address = config_get('from_email');
    $ema->to_address = $args->user->emailAddress;;
    $ema->subject = $gui->mailCfg->subject;
    $ema->message = $gui->mailCfg->subject;
    
    $dum = uniqid("resultsByStatus_") . '.xls';
    $oops = array('attachment' => 
                  array('file' => $tmpfname, 'newname' => $dum),
                  'exit_on_error' => true, 'htmlFormat' => true);
    $email_op = email_send_wrapper($ema,$oops);
    unlink($tmpfname);
    exit(); 
  } 
  else
  {
    downloadXls($tmpfname,$xlsType,$gui,'resultsByStatus_');
  } 
}


/**
 *
 */
function getMetrics(&$dbh,&$args,&$gui)
{
  $resultsCfg = config_get('results');
  $statusCode = $resultsCfg['status_code'];
  $metricsMgr = new tlTestPlanMetrics($dbh);

  if( $args->type == $statusCode['not_run'] )
  {
    $opt = array('output' => 'array');
    $met = $metricsMgr->getNotRunWithTesterAssigned($args->tplan_id,null,$opt);
 
    $gui->notRunReport = true;
    $gui->info_msg = $gui->labels['info_notrun_tc_report'];
    $gui->notesAccessKey = 'summary';
    $gui->userAccessKey = 'user_id';
  }
  else
  {
    $opt = array('output' => 'mapByExecID', 'getOnlyAssigned' => true);
    $met = $metricsMgr->getExecutionsByStatus($args->tplan_id,$args->type,null,$opt);

    $gui->notRunReport = false;
    $gui->info_msg = lang_get('info_' . $resultsCfg['code_status'][$args->type] .'_tc_report');
    
    $gui->notesAccessKey = 'execution_notes';
    $gui->userAccessKey='tester_id';
  } 

  return $met; 
}

/**
 *
 */
function initLblSpreadsheet()
{
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
function initStyleSpreadsheet()
{
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
function xlsStepOne($oj,$style,$lbl,$gui)
{

  $dummy = '';
  $lines2write = array(array($gui->title,''),
                       array($lbl['testproject'],$gui->tproject_name),
                       array($lbl['testplan'],$gui->tplan_name),
                       array($lbl['generated_by_TestLink_on'],
                             localize_dateOrTimeStamp(null,$dummy,'timestamp_format',time())),
                       array($gui->report_context,''));

  $cellArea = "A1:"; 
  foreach($lines2write as $zdx => $fields)
  {
    $cdx = $zdx+1;
    $oj->setActiveSheetIndex(0)->setCellValue("A{$cdx}", current($fields))
       ->setCellValue("B{$cdx}", end($fields));
  }
  $cellArea .= "A{$cdx}";
  $oj->getActiveSheet()->getStyle($cellArea)
     ->applyFromArray($style['ReportContext']); 

  return $lines2write;
}


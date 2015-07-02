<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource testCasesWithoutTester.php
 * 
 * For a test plan, list test cases that HAS NOT BEEN RUN AND HAS NO TESTER ASSIGNED
 *
 * @internal revisions
 * @since 1.9.12
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$tplan_mgr = new testplan($db);
$args = init_args($tplan_mgr);
$gui = initializeGui($db,$args);

// create it here, in order to be able to get tlImages
$smarty = new TLSmarty();

$msg_key = 'no_linked_tcversions';
if($tplan_mgr->count_testcases($args->tplan_id) > 0)
{
  $platformCache = null;
  $msg_key = 'all_testcases_have_tester';
  $cfg = config_get('results');

  $metricsMgr = new tlTestPlanMetrics($db);
  $metrics = $metricsMgr->getNotRunWoTesterAssigned($args->tplan_id,null,null,
                                                    array('output' => 'array', 'ignoreBuild' => true));

  if(($gui->row_qty = count($metrics)) > 0)
  {
    $msg_key = '';
    $links = featureLinks($gui->labels,$smarty->getImages());
    $gui->pageTitle .= " - " . $gui->labels['match_count'] . ":" . $gui->row_qty;


    if ($args->show_platforms)
    {
      $platformCache = $tplan_mgr->getPlatforms($args->tplan_id,array('outputFormat' => 'mapAccessByID'));
    }
    
    // Collect all tcases id and get all test suite paths
    $targetSet = array();

    foreach ($metrics as &$item) 
    {
      $targetSet[] = $item['tcase_id'];
    }
    $tree_mgr = new tree($db);
    $path_info = $tree_mgr->get_full_path_verbose($targetSet);
    unset($tree_mgr);
    unset($targetSet);

    $data = array();
    foreach ($metrics as &$item)
    {
      $row = array();
      $row[] = join(" / ", $path_info[$item['tcase_id']]);
      
      $row[] = "<!-- " . sprintf("%010d", $item['external_id']) . " -->" . 
               sprintf($links['full'],$item['tcase_id'],$item['tcase_id']) .
               $item['full_external_id'] . ': ' . $item['name'];
      
      if ($args->show_platforms)
      {
        $row[] = $platformCache[$item['platform_id']]['name'];
      }

      if($gui->options->testPriorityEnabled)
      {
        // THIS HAS TO BE REFACTORED, because we can no do lot of calls
        // because performance will be BAD
        $row[] = $tplan_mgr->urgencyImportanceToPriorityLevel($item['urg_imp']);
      }
      
      $row[] = strip_tags($item['summary']);
      $data[] = $row;
    }

    $gui->tableSet[] = buildTable($data, $args->tproject_id, $args->show_platforms,
                                  $gui->options->testPriorityEnabled);
  }
}

$gui->warning_msg = lang_get($msg_key);
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * 
 *
 */
function buildTable($data, $tproject_id, $show_platforms, $priorityMgmtEnabled) 
{
  $key2search = array('testsuite','testcase','platform','priority','summary');
  foreach($key2search as $key)
  {
    $labels[$key] = lang_get($key);
  }        
  $columns[] = array('title_key' => 'testsuite', 'width' => 20);
  
  $columns[] = array('title_key' => 'testcase', 'width' => 25);
  
  if ($show_platforms){
    $columns[] = array('title_key' => 'platform', 'width' => 10);
  }
  
  if ($priorityMgmtEnabled) {
    $columns[] = array('title_key' => 'priority', 'type' => 'priority', 'width' => 5);
  }
  
  $columns[] = array('title_key' => 'summary', 'type' => 'text', 'width' => 40);
  
  $matrix = new tlExtTable($columns, $data, 'tl_table_tc_without_tester');
  
  $matrix->setGroupByColumnName($labels['testsuite']);
  $matrix->setSortByColumnName($labels['testcase']);
  $matrix->addCustomBehaviour('text', array('render' => 'columnWrap'));
  
  if($priorityMgmtEnabled) 
  {
    $matrix->addCustomBehaviour('priority', array('render' => 'priorityRenderer', 'filter' => 'Priority'));
    $matrix->setSortByColumnName($labels['priority']);
  }
  return $matrix;
}

/*
  function: 

  args :
  
  returns: 

*/
function init_args(&$tplan_mgr)
{
  $iParams = array("format" => array(tlInputParameter::INT_N),
                   "tplan_id" => array(tlInputParameter::INT_N));

  $args = new stdClass();
  R_PARAMS($iParams,$args);
    
  $args->show_platforms = false;
  $args->tproject_id = intval(isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0);

  $args->tplan_name = '';
  if(!$args->tplan_id)
  {
    $args->tplan_id = intval(isset($_SESSION['testplanID']) ? $_SESSION['testplanID'] : 0);
  }
  
  if($args->tplan_id > 0)
  {
    $tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
    $args->tplan_name = $tplan_info['name'];  
    $args->show_platforms = $tplan_mgr->hasLinkedPlatforms($args->tplan_id);
  }
 
  return $args;
}

/**
 *
 *
 */
function featureLinks($lbl,$img)
{
  $links = array();

  // %s => test case id
  $links['exec_history'] = '<a href="javascript:openExecHistoryWindow(%s);" >' .
                       '<img title="' . $lbl['execution_history'] . '" ' .
                       'src="' . $img['history_small'] . '" /></a> ';

  // %s => test case id
  $links['edit'] = '<a href="javascript:openTCEditWindow(%s);" >' .
          '<img title="' . $lbl['design'] . '" '. 'src="' . $img['edit_icon'] . '" /></a> ';


  $links['full'] = $links['exec_history'] . $links['edit'];
  return $links;
}

/**
 *
 */
function initializeGui(&$dbHandler,&$argsObj)
{
  $gui = new stdClass();
  $gui->pageTitle = lang_get('caption_testCasesWithoutTester');
  $gui->warning_msg = '';
  $gui->tplan_name = $argsObj->tplan_name;
  
  $mgr = new testproject($dbHandler);
  $dummy = $mgr->get_by_id($argsObj->tproject_id);

  $gui->tproject_name = $argsObj->tproject_name = $dummy['name'];
  
  $gui->options = new stdClass();
  $gui->options->testPriorityEnabled = $dummy['opt']->testPriorityEnabled;
  $gui->labels = init_labels(array('design' => null, 'execution' => null, 'execution_history' => null,
                                    'match_count' => null));

  $gui->tableSet = null;
  return $gui;
}




function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'testplan_metrics');
}
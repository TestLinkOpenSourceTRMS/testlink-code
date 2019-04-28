<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  metricsDashboard.php
 * @package     TestLink
 * @copyright   2007-2017, TestLink community 
 * @author      franciscom
 *
 *
 **/
require('../../config.inc.php');
require_once('common.php');
require_once('exttable.class.php');
$templateCfg = templateConfiguration();

list($args,$gui) = initEnv($db);


$result_cfg = config_get('results');
$show_all_status_details = config_get('metrics_dashboard')->show_test_plan_status;
$round_precision = config_get('dashboard_precision');

$labels = init_labels(array('overall_progress' => null, 'test_plan' => null, 'progress' => null,
                            'href_metrics_dashboard' => null, 'progress_absolute' => null,
                            'no_testplans_available' => null, 'not_aplicable' => null,
                            'platform' => null, 'th_active_tc' => null, 'in_percent' => null));

list($gui->tplan_metrics,$gui->show_platforms, $platforms) = getMetrics($db,$_SESSION['currentUser'],$args,$result_cfg, $labels);


// new dBug($gui->tplan_metrics);
if(count($gui->tplan_metrics) > 0) 
{
  $statusSetForDisplay = $result_cfg['status_label_for_exec_ui']; 
  $gui->warning_msg = '';
  $columns = getColumnsDefinition($gui->show_platforms, $statusSetForDisplay, $labels, $platforms);

  $matrixData = array();
  if(isset($gui->tplan_metrics['testplans']))
  {  
    foreach ($gui->tplan_metrics['testplans'] as $tplan_metrics)
    {
      foreach($tplan_metrics['platforms'] as $key => $platform_metric) 
      {
        $rowData = array();
        
        // if test plan does not use platforms a overall status is not necessary
        $tplan_string = strip_tags($platform_metric['tplan_name']);
        if ($show_all_status_details) 
        {
          // add information for all exec statuses
          $tplan_string .= "<br>";
          foreach( $statusSetForDisplay as $status_verbose => &$status_label)
          {
            $tplan_string .= lang_get($status_label). ": " .
                     $tplan_metrics['overall'][$status_verbose] .
                             " [" . getPercentage($tplan_metrics['overall'][$status_verbose], 
                                                  $tplan_metrics['overall']['active'],
                                                  $round_precision) . "%], ";
          }
        } 
        else 
        {
          $tplan_string .= " - ";
        }
        
        $tplan_string .= $labels['overall_progress'] . ": " . 
                         getPercentage($tplan_metrics['overall']['executed'],
                                       $tplan_metrics['overall']['active'],
                                       $round_precision) . "%";
        
        $rowData[] = $tplan_string;
        if ($gui->show_platforms) 
        {
          $rowData[] = strip_tags($platform_metric['platform_name']);
        }
        
        $rowData[] = $platform_metric['total'];
        foreach ($statusSetForDisplay as $status_verbose => $status_label)
        {
          if( isset($platform_metric[$status_verbose]) )
          {
            $rowData[] = $platform_metric[$status_verbose];
            $rowData[] = getPercentage($platform_metric[$status_verbose], $platform_metric['active'],
                                         $round_precision);
          }
          else
          {
            $rowData[] = 0;
            $rowData[] = 0;
          }
        }

        $rowData[] = getPercentage($platform_metric['executed'], $platform_metric['active'],
                                   $round_precision);
          
        $matrixData[] = $rowData;
      }
    }
  }
  
  $table = new tlExtTable($columns, $matrixData, 'tl_table_metrics_dashboard');

  // if platforms are to be shown -> group by test plan
  // if no platforms are to be shown -> no grouping
  if($gui->show_platforms) 
  {
    $table->setGroupByColumnName($labels['test_plan']);
  }

  $table->setSortByColumnName($labels['progress']);
  $table->sortDirection = 'DESC';

  $table->showToolbar = true;
  $table->toolbarExpandCollapseGroupsButton = true;
  $table->toolbarShowAllColumnsButton = true;
  $table->toolbarResetFiltersButton = true;
  $table->title = $labels['href_metrics_dashboard'];
  $table->showGroupItemsCount = true;

  $gui->tableSet = array($table);
  
  // get overall progress, collect test project metrics
  $gui->project_metrics = collectTestProjectMetrics($gui->tplan_metrics,
                            array('statusSetForDisplay' => $statusSetForDisplay,
                                'round_precision' => $round_precision));
}


$smarty = new TLSmarty;
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 *  only active builds has to be used
 *
 *  @internal revisions
 *
 *  
 */
function getMetrics(&$db,$userObj,$args, $result_cfg, $labels)
{
  $user_id = $args->currentUserID;
  $tproject_id = $args->tproject_id;
  $linked_tcversions = array();
  $metrics = array();
  $tplan_mgr = new testplan($db);
  $show_platforms = false;
  $platforms = array();

  // get all tesplans accessibles  for user, for $tproject_id
  $options = array('output' => 'map');
  $options['active'] = $args->show_only_active ? ACTIVE : TP_ALL_STATUS; 
  $test_plans = $userObj->getAccessibleTestPlans($db,$tproject_id,null,$options);

  // Get count of testcases linked to every testplan
  // Hmm Count active and inactive ?
  $linkedItemsQty = $tplan_mgr->count_testcases(array_keys($test_plans),null,array('output' => 'groupByTestPlan'));
  
  
  $metricsMgr = new tlTestPlanMetrics($db);
  $show_platforms = false;
  
  $metrics = array('testplans' => null, 'total' => null);
  $mm = &$metrics['testplans'];
  $metrics['total'] = array('active' => 0,'total' => 0, 'executed' => 0);
  foreach($result_cfg['status_label_for_exec_ui'] as $status_code => &$dummy)
  {
    $metrics['total'][$status_code] = 0; 
  } 
  
  $codeStatusVerbose = array_flip($result_cfg['status_code']);
  foreach($test_plans as $key => &$dummy)
  {
    // We need to know if test plan has builds, if not we can not call any method 
    // that try to get exec info, because you can only execute if you have builds.
    //
    // 20130909 - added active filter
    $buildSet = $tplan_mgr->get_builds($key,testplan::ACTIVE_BUILDS);
    if( is_null($buildSet) )
    {
      continue;
    }

    $platformSet = $tplan_mgr->getPlatforms($key);
    if (isset($platformSet)) 
    {
      $platforms = array_merge($platforms, $platformSet);
    } 
    $show_platforms_for_tplan = !is_null($platformSet);
    $show_platforms = $show_platforms || $show_platforms_for_tplan;
    if( !is_null($platformSet) )
    {
      $neurus = $metricsMgr->getExecCountersByPlatformExecStatus($key,null,
                                                                 array('getPlatformSet' => true,
                                                                       'getOnlyActiveTCVersions' => true));
                                     
      $mm[$key]['overall']['active'] = $mm[$key]['overall']['executed'] = 0;
      foreach($neurus['with_tester'] as $platform_id => &$pinfo)
      {
        $xd = &$mm[$key]['platforms'][$platform_id];
        $xd['tplan_name'] = $dummy['name'];
        $xd['platform_name'] = $neurus['platforms'][$platform_id];
        $xd['total'] = $xd['active'] = $neurus['total'][$platform_id]['qty'];
        $xd['executed'] = 0;
        
        foreach($pinfo as $code => &$elem)
        {
          $xd[$codeStatusVerbose[$code]] = $elem['exec_qty'];
          if($codeStatusVerbose[$code] != 'not_run')
          {
            $xd['executed'] += $elem['exec_qty'];
          }
          if( !isset($mm[$key]['overall'][$codeStatusVerbose[$code]]) )
          {
            $mm[$key]['overall'][$codeStatusVerbose[$code]] = 0;
          }
          $mm[$key]['overall'][$codeStatusVerbose[$code]] += $elem['exec_qty'];
          $metrics['total'][$codeStatusVerbose[$code]] += $elem['exec_qty']; 
        }
        $mm[$key]['overall']['executed'] += $xd['executed'];
        $mm[$key]['overall']['active'] += $xd['active'];
      } 
      unset($neurus);
      $mm[$key]['overall']['total'] = $mm[$key]['overall']['active'];                             
      $metrics['total']['executed'] += $mm[$key]['overall']['executed'];
      $metrics['total']['active'] += $mm[$key]['overall']['active'];
    }
    else
    {
      $mm[$key]['overall'] = $metricsMgr->getExecCountersByExecStatus($key,null,
                                                                      array('getOnlyActiveTCVersions' => true));

      $mm[$key]['overall']['active'] = $mm[$key]['overall']['total'];

      // compute executed
      $mm[$key]['overall']['executed'] = 0;
      foreach($mm[$key]['overall'] as $status_code => $qty)
      {
        if( $status_code != 'not_run' && $status_code != 'total' && $status_code != 'active' ) 
        {
          $mm[$key]['overall']['executed'] += $qty;
        }

        if( $status_code != 'total' && $status_code != 'active' ) 
        {
          if(!isset($metrics['total'][$status_code]))
          {
            $metrics['total'][$status_code] = 0;
          }
          $metrics['total'][$status_code] += $qty;
        }
      }
      $metrics['total']['executed'] += $mm[$key]['overall']['executed'];
      $metrics['total']['active'] += $mm[$key]['overall']['active'];
    
      $mm[$key]['platforms'][0] = $mm[$key]['overall'];
      $mm[$key]['platforms'][0]['tplan_name'] = $dummy['name'];
      $mm[$key]['platforms'][0]['platform_name'] = $labels['not_aplicable'];
    } 
  }
    
  // remove duplicate platform names
  $platformsUnique = array();
  foreach($platforms as $platform) 
  {
    if(!in_array($platform['name'], $platformsUnique)) 
    {
      $platformsUnique[] = $platform['name'];
    }
  }
  
  return array($metrics, $show_platforms, $platformsUnique);
}

/**
 * 
 *
 */
function getPercentage($denominator, $numerator, $round_precision)
{
  $percentage = ($numerator > 0) ? (round(($denominator / $numerator) * 100,$round_precision)) : 0;

  return $percentage;
}

/**
 * get Columns definition for table to display
 *
 */
function getColumnsDefinition($showPlatforms, $statusLbl, $labels, $platforms)
{
  $colDef = array();

  $colDef[] = array('title_key' => 'test_plan', 'width' => 60, 'type' => 'text', 'sortType' => 'asText',
                    'filter' => 'string');

  if ($showPlatforms)
  {
    $colDef[] = array('title_key' => 'platform', 'width' => 60, 'sortType' => 'asText',
                      'filter' => 'list', 'filterOptions' => $platforms);
  }

  $colDef[] = array('title_key' => 'th_active_tc', 'width' => 40, 'sortType' => 'asInt',
                    'filter' => 'numeric');
  
  // create 2 columns for each defined status
  foreach($statusLbl as $lbl)
  {
    $colDef[] = array('title_key' => $lbl, 'width' => 40, 'hidden' => true, 'type' => 'int',
                      'sortType' => 'asInt', 'filter' => 'numeric');
    
    $colDef[] = array('title' => lang_get($lbl) . " " . $labels['in_percent'], 'width' => 40,
                      'col_id' => 'id_'. $lbl .'_percent', 'type' => 'float', 'sortType' => 'asFloat',
                      'filter' => 'numeric');
  }
  
  $colDef[] = array('title_key' => 'progress', 'width' => 40, 'sortType' => 'asFloat', 'filter' => 'numeric');

  return $colDef;
}

function initEnv(&$dbHandler)
{
  $args = new stdClass();
  $gui = new stdClass();

  $iParams = array("apikey" => array(tlInputParameter::STRING_N,32,64),
                   "tproject_id" => array(tlInputParameter::INT_N), 
                   "tplan_id" => array(tlInputParameter::INT_N),
                   "show_only_active" => array(tlInputParameter::CB_BOOL),
                   "show_only_active_hidden" => array(tlInputParameter::CB_BOOL));

  R_PARAMS($iParams,$args);
  
  if( !is_null($args->apikey) )
  {
    
    $args->show_only_active = true;
    $cerbero = new stdClass();
    $cerbero->args = new stdClass();
    $cerbero->redirect_target = "../../login.php?note=logout";

    if(strlen($args->apikey) == 32)
    {
      $cerbero->args->tproject_id = $args->tproject_id;
      $cerbero->args->tplan_id = $args->tplan_id;
      $cerbero->args->getAccessAttr = true;
      $cerbero->method = 'checkRights';
    
      setUpEnvForRemoteAccess($dbHandler,$args->apikey,$cerbero);
    }
    else
    {
      // Have got OBJECT KEY
      $cerbero->method = null;
      $cerbero->args->getAccessAttr = false;

      setUpEnvForAnonymousAccess($dbHandler,$args->apikey,$cerbero);
    
      // if we are here => everything seems OK
      $tprojMgr = new testproject($dbHandler);
      $dj = $tprojMgr->getByAPIKey($args->apikey);
      $args->tproject_id = $dj['id'];
    }  
  }
  else
  {
    testlinkInitPage($dbHandler,false,false,"checkRights");  
    $args->tproject_id = isset($_SESSION['testprojectID']) ? 
                         intval($_SESSION['testprojectID']) : 0;
  }
  
  if($args->tproject_id <= 0)
  {
    $msg = __FILE__ . '::' . __FUNCTION__ . " :: Invalid Test Project ID ({$args->tproject_id})";
    throw new Exception($msg);
  }
  $mgr = new tree($dbHandler);
  $dummy = $mgr->get_node_hierarchy_info($args->tproject_id);
  $args->tproject_name = $dummy['name'];

  $args->user = $_SESSION['currentUser'];
  $args->currentUserID = $args->user->dbID;
  
  // I'm sorry for MAGIC
  $args->direct_link_ok = true;
  $ak = testproject::getAPIkey($dbHandler,$args->tproject_id);
  $args->direct_link = $_SESSION['basehref'] . 
                       "lnl.php?type=metricsdashboard&" .
                       "apikey={$ak}";

  if ($args->show_only_active) 
  {
    $selection = true;
  } 
  else if ($args->show_only_active_hidden) 
  {
    $selection = false;
  } 
  else if (isset($_SESSION['show_only_active'])) 
  {
    $selection = $_SESSION['show_only_active'];
  } 
  else 
  {
    $selection = true;
  }
  $args->show_only_active = $_SESSION['show_only_active'] = $selection;
  

  $gui->tproject_name = $args->tproject_name;
  $gui->show_only_active = $args->show_only_active;
  $gui->direct_link = $args->direct_link;
  $gui->direct_link_ok = $args->direct_link_ok;
  $gui->warning_msg = lang_get('no_testplans_available');

  return array($args,$gui);
}


/**
 *
 */
function collectTestProjectMetrics($tplanMetrics,$cfg)
{
  $mm = array();
  $mm['executed']['value'] = getPercentage($tplanMetrics['total']['executed'], 
                                           $tplanMetrics['total']['active'], $cfg['round_precision']);
  $mm['executed']['label_key'] = 'progress_absolute';

  foreach ($cfg['statusSetForDisplay'] as $status_verbose => $label_key)
  {
    $mm[$status_verbose]['value'] = getPercentage($tplanMetrics['total'][$status_verbose], 
                                                    $tplanMetrics['total']['active'], $cfg['round_precision']);
    $mm[$status_verbose]['label_key'] = $label_key;
  }
  return $mm;
}

/**
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
  $checkOrMode = array('testplan_metrics','testplan_execute');
  foreach($checkOrMode as $right)
  {
    if( $user->hasRight($db,$right,$context->tproject_id,$context->tplan_id,$context->getAccessAttr) )
    {
      return true;  
    }
  }  
  return false;
}
?>
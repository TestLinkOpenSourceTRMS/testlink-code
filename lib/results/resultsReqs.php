<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * Report requirement based results
 *
 * @filesource  resultsReqs.php
 * @package     TestLink
 * @author      Martin Havlat
 * 
 * 
 * internal revisions
 * @since 1.9.16
 */

require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
require_once('exttable.class.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$smarty = new TLSmarty();


$tproject_mgr = new testproject($db);
$tplan_mgr = new testplan($db);
$req_mgr = new requirement_mgr($db);
$req_spec_mgr = new requirement_spec_mgr($db);

$title_sep = config_get('gui_title_separator_1');
$charset = config_get('charset');


$req_cfg = config_get('req_cfg');
list($req_spec_type_labels,$req_type_labels,$status_labels,$labels) = setUpLabels($req_cfg);
list($results_cfg,$status_code_map,$code_status_map,$eval_status_map) = setUpReqStatusCfg();

$args = init_args($tproject_mgr,$tplan_mgr,$req_cfg);

$images = $smarty->getImages();
$gui = init_gui($args,$tplan_mgr);
$i2u = array('edit_icon','exec_icon','history_small');


$reqContext = array('tproject_id' => $args->tproject_id, 'tplan_id' => $args->tplan_id, 
                    'platform_id' => $args->platform);

$reqSetX = (array)$req_mgr->getAllByContext($reqContext);
$req_ids = array_keys($reqSetX);

$prefix = $tproject_mgr->getTestCasePrefix($args->tproject_id) . (config_get('testcase_cfg')->glue_character);

$rspecSet = array();
$testcases = array();

// first step: get the requirements and linked testcases with which we have to work,
// order them into $rspecSet by spec
$gui->total_reqs = 0;
if (count($req_ids)) 
{   
  list($gui->total_reqs,$rspecSet,$testcases) = buildReqSpecMap($req_ids,$req_mgr,$req_spec_mgr,$tplan_mgr,
                                                                    $args->states_to_show->selected,$args);
  if (!count($rspecSet)) 
  {
    $gui->warning_msg = $labels['no_matching_reqs'];
  }
}
else 
{
  $gui->warning_msg = $labels['no_srs_defined'];
}


// second step: walk through req spec map, count/calculate, store results
if(count($rspecSet)) 
{

  foreach ($rspecSet as $rspec_id => $req_spec_info) 
  {
    $rspecSet[$rspec_id]['req_counters'] = array('total' => 0);
    foreach ($req_spec_info['requirements'] as $req_id => $req_info) 
    {
      // Test Plan Test Case Version (TPTCV)
      $rspecSet[$rspec_id]['requirements'][$req_id]['tc_counters'] = array('total' => 0,'totalTPTCV' => 0);
      
      // add coverage for more detailed evaluation
      $rspecSet[$rspec_id]['requirements'][$req_id]['tc_counters']['expected_coverage'] = 
      $rspecSet[$rspec_id]['requirements'][$req_id]['expected_coverage'];
    
      foreach ($req_info['linked_testcases'] as $key => $tc_info) 
      {
        $tc_id = $tc_info['id'];
        $plat2loop = array_keys($testcases[$tc_id]);
        $rspecSet[$rspec_id]['requirements'][$req_id]['tc_counters']['total']++;
 
        foreach($plat2loop as $plat_id)
        {
          $rspecSet[$rspec_id]['requirements'][$req_id]['tc_counters']['totalTPTCV']++;
          if (isset($testcases[$tc_id][$plat_id]['exec_status'])) 
          {
            $status = $testcases[$tc_id][$plat_id]['exec_status'];
         
            // if the counters for this status don't exist yet, initialize them with 0
            if (!isset($rspecSet[$rspec_id]['requirements'][$req_id]['tc_counters'][$status])) 
            {
              $rspecSet[$rspec_id]['requirements'][$req_id]['tc_counters'][$status] = 0;
            }
            
            $rspecSet[$rspec_id]['requirements'][$req_id]['tc_counters'][$status] ++;
          }
        }  
      }
      
      // evaluate this requirement by configured coverage algorithm
      $eval = evaluate_req($status_code_map, $req_cfg->coverageStatusAlgorithm,
                           $rspecSet[$rspec_id]['requirements'][$req_id]['tc_counters']);

      $rspecSet[$rspec_id]['requirements'][$req_id]['evaluation'] = $eval;
      
      if (!isset($rspecSet[$rspec_id]['req_counters'][$eval])) 
      {
        $rspecSet[$rspec_id]['req_counters'][$eval] = 0;
      }
      
      $rspecSet[$rspec_id]['req_counters'][$eval] ++;
      $rspecSet[$rspec_id]['req_counters']['total'] ++;
    }
  }
}

// last step: build the table
if (count($rspecSet)) 
{
  $allStatusCode = config_get('results');

  // headers
  $columns = array();
  $columns[] = array('title_key' => 'req_spec_short',
                     'groupable' => 'true', 'hideable' => 'false', 'hidden' => 'true');
  $columns[] = array('title_key' => 'title', 'width' => 100,
                     'groupable' => 'false', 'type' => 'text');
  $columns[] = array('title_key' => 'version', 'width' => 20, 'groupable' => 'false');
  
  if ($req_cfg->expected_coverage_management) 
  {
    $columns[] = array('title_key' => 'th_coverage', 'width' => 60, 'groupable' => 'false');
  }
  
  $evaluation_for_filter = array();
  foreach($eval_status_map as $eval) 
  {
    $evaluation_for_filter[] = $eval['label'];
  }
  $columns[] = array('title_key' => 'evaluation', 'width' => 80, 'groupable' => 'false',
                     'filter' => 'ListSimpleMatch', 'filterOptions' => $evaluation_for_filter);
  $columns[] = array('title_key' => 'type', 'width' => 60, 'groupable' => 'false', 
                     'filter' => 'list', 'filterOptions' => $req_type_labels);
  $columns[] = array('title_key' => 'status', 'width' => 60, 'groupable' => 'false',
                     'filter' => 'list', 'filterOptions' => $status_labels);
  
  foreach ($code_status_map as $status) 
  {
    $columns[] = array('title_key' => $results_cfg['status_label'][$status['status']], 
                       'width' => 60, 'groupable' => 'false');
  }
  
  // complete progress
  $columns[] = array('title_key' => 'progress', 'width' => 60, 'groupable' => 'false');
  $columns[] = array('title_key' => 'linked_tcs', 'groupable' => 'false', 'width' => 250, 'type' => 'text');
  
  // data for rows
  $rows = array();
  foreach ($rspecSet as $rspec_id => $req_spec_info) 
  {
    
    // build the evaluation data string and attache it to req spec name for table group feature
    $req_spec_description = build_req_spec_description($eval_status_map, $req_spec_info,
                                                       $req_cfg->external_req_management, $labels,
                                                       $req_spec_type_labels);
                                                       
        
    foreach ($req_spec_info['requirements'] as $req_id => $req_info) 
    {
      $single_row = array();
      
      // first column (grouped, not shown) is req spec information
      $path = $req_mgr->tree_mgr->get_path($req_info['srs_id']);
      foreach ($path as $key => $val) 
      {
        $path[$key] = $val['name'];
      }
      $path = implode("/", $path);
      $single_row[] = htmlentities($path, ENT_QUOTES, $charset) . $req_spec_description;
      
      // create the linked title to display
      $edit_link = '<a href="javascript:openLinkedReqWindow(' . $req_id . ')>' .
                   "<img title=\"{$labels['requirement']}\" src=\"{$images['edit_icon']}\" /></a> ";

      $single_row[] = $edit_link .
                      htmlentities($req_info['req_doc_id'], ENT_QUOTES, $charset) . $title_sep . 
                      htmlentities($req_info['title'], ENT_QUOTES, $charset);
        
      $single_row[] = "<!-- " . sprintf("%010d", $req_info['version']) . " -->" . $req_info['version'];
        
      // coverage
      if ($req_cfg->expected_coverage_management) 
      {
        $expected_coverage = $req_info['expected_coverage'];
        $current = count($req_info['linked_testcases']);
        if ($expected_coverage) 
        {
          $coverage_string = "<!-- -1 -->" . $labels['na'] . " ($current/0)";
          if ($expected_coverage) 
          {
            $percentage = 100 / $expected_coverage * $current;
            $coverage_string = comment_percentage($percentage) .
                               " ({$current}/{$expected_coverage})";
          }
          $single_row[] = $coverage_string;
        } 
        else 
        {
          // no expected value, no percentage, just absolute number
          $single_row[] = $current;
        }
      }
      
      $eval = $req_info['evaluation'];
      
      // add the count of each evaluation
      $eval_status_map[$eval]['count'] += 1;

      $single_row[] = '<span class="' . $eval_status_map[$eval]['css_class'] . '">' . 
                      $eval_status_map[$eval]['label'] . '</span>';
      
      $single_row[] = isset($req_type_labels[$req_info['type']]) ? $req_type_labels[$req_info['type']] : 
                      sprintf($labels['no_label_for_req_type'],$req_info['type']);
      
      $single_row[] = $status_labels[$req_info['status']];
      
      // add count and percentage for each possible status and progress
      $progress_percentage = 0;
      
      $total_count = ($req_cfg->expected_coverage_management && $expected_coverage > 0) ?
                     $expected_coverage : $req_info['tc_counters']['total'];
      
      foreach ($status_code_map as $status => $code) 
      {
        $count = isset($req_info['tc_counters'][$code]) ? $req_info['tc_counters'][$code] : 0;
        $value = 0;
        
        if ($total_count) 
        {
          $percentage = (100 / $total_count) * $count;
            $percentage_string = comment_percentage($percentage) . " ({$count}/{$total_count})";
          
            $value = $percentage_string;
          
          // if status is not "not run", add it to progress percentage
          if ($code != $status_code_map['not_run']) 
          {
            $progress_percentage += $percentage;
          }
        } 
        else 
        {
          $value = $labels['na'];
        }
        
        $single_row[] = $value;
      }
      
      // complete progress
      $single_row[] = $total_count ? comment_percentage($progress_percentage) : $labels['na'];

      // show all linked tcversions incl exec result
      $linked_tcs_with_status = '';
      if (count($req_info['linked_testcases']) > 0 ) 
      {
        // ATTENTION HERE IS WHERE PLATFORMS AFFECTS
        foreach($req_info['linked_testcases'] as $ltcase) 
        {
          $tc_id = $ltcase['id'];
          foreach($testcases[$tc_id] as $pelem)
          {
            $status = $status_code_map['not_run'];
            if(isset($pelem['exec_status'])) 
            {
              $status = $pelem['exec_status'];
              $status_l10n = $eval_status_map[$status]['label'];
            }
            else
            {
              $not_run = $allStatusCode['status_code']['not_run'];
              $status_l10n = $labels['not_in_testplan'];
            }  
            $pname = ($pelem['platform_id'] > 0 ? ($pelem['platform_name'] . '/') : '' );
            $colored_status = '<span class="' . $eval_status_map[$status]['css_class'] . '">' . 
                              '[' . $pname . $status_l10n . ']</span>';

            
            $tc_name = $prefix . $ltcase['tc_external_id'] . $title_sep . $ltcase['name'];
            
            $exec_history_link = "<a href=\"javascript:openExecHistoryWindow({$tc_id});\">" .
                                 "<img title=\"{$labels['execution_history']}\" " .
                                 " src=\"{$images['history_small']}\" /></a> ";
            $edit_link = "<a href=\"javascript:openTCEditWindow({$tc_id});\">" .
                         "<img title=\"{$labels['design']}\" src=\"{$images['edit_icon']}\" /></a> ";
            
            $exec_link = "";


            if(isset($pelem['exec_status']) && $pelem['exec_status'] != $status_code_map['not_run']) 
            {
              $exec_link = "<a href=\"javascript:openExecutionWindow(" .
                           "{$tc_id}, {$pelem['tcversion_id']}, {$pelem['exec_on_build']} , " .
                           "{$pelem['exec_on_tplan']}, {$pelem['platform_id']});\">" .
                           "<img title=\"{$labels['execution']}\" src=\"{$images['exec_icon']}\" /></a> ";
            }

            $linked_tcs_with_status .= "{$exec_history_link} {$edit_link} {$exec_link} {$colored_status} {$tc_name}<br>";
          }  

        }
      } 
      else  
      {
        $linked_tcs_with_status = $labels['no_linked_tcs'];
      }
      
      $single_row[] = $linked_tcs_with_status;
      
      $rows[] = $single_row;
    }
  }
  
  $matrix = new tlExtTable($columns, $rows, 'tl_table_results_reqs');
  $matrix->title = $gui->pageTitle;
  
  // group by Req Spec and hide that column
  $matrix->setGroupByColumnName($labels['req_spec_short']);
  
  $matrix->setSortByColumnName($labels['progress']);
  $matrix->sortDirection = 'DESC';
  
  // show long text content in multiple lines
  $matrix->addCustomBehaviour('text', array('render' => 'columnWrap'));
  
  // define toolbar
  $matrix->toolbarShowAllColumnsButton = true;
  $matrix->showGroupItemsCount = false;
  
  $gui->tableSet = array($matrix);
}

$gui->summary = $eval_status_map;

$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * Builds a descriptive string which will be added to the grouping column of the ExtJS table
 * for each req spec to see information about the requirements in this spec and their status.
 * 
 * @author Andreas Simon
 * @param array $evalcode_status_map
 * @param array $spec_info
 * @param bool $ext_mgmt_enabled
 * @param array $labels
 * @param array $spec_types
 * @return string description
 */
function build_req_spec_description(&$evalcode_status_map, &$spec_info, $ext_mgmt_enabled, 
                                    &$labels, &$spec_types) 
{
  
  $description = "";
  $space = "&nbsp;&nbsp;&nbsp;&nbsp;";
  
  $req_count = $spec_info['req_counters']['total'];
  $external_count = isset($spec_info['total_req']) ? $spec_info['total_req'] : 0;
  $description .= "<br/>" . $space . $labels['type'] . ": " . $spec_types[$spec_info['type']];
  
  if ($ext_mgmt_enabled && $external_count) 
  {
    $description .= "<br/>{$space}{$labels['req_availability']}: " . 
                    "({$req_count}/{$external_count})";
  } 
  else 
  {
    $description .= "<br/>" . $space . $labels['requirements'] . ": " . $req_count;
  }
  
  foreach ($evalcode_status_map as $status_code => $status_info) 
  {
    $count = isset($spec_info['req_counters'][$status_code]) ?
             $spec_info['req_counters'][$status_code] : 0;
    if ($count) 
    {
      $description .= "<br/>" . $space . $status_info['long_label'] . ": " . $count;
    }
  }
  
  return $description;
}


/**
 * Get the evaluation status of a single requirement.
 * 
 * @author Andreas Simon
 * @param array $status_code
 * @param array $algorithm_cfg
 * @param array $counters
 * @return string evaluation
 */
function evaluate_req(&$status_code, &$algorithm_cfg, &$counters) 
{
  // init
  $evaluation = null;
  $is_fully_covered = ($counters['total'] >= $counters['expected_coverage']) ? true : false;

  if( !isset($counters[$status_code['not_run']]) )
  {
    $counters[$status_code['not_run']] = 0;
  }

  $doIt = true;
  if ($counters['total'] == 0) 
  {
    // Zero test cases linked to Requirement => uncovered
    // Do we really display this req on report?
    $evaluation = 'uncovered';
    $doIt = false;
  }
  
  
  // because we can have a situation where NOT ALL test cases assigned to req
  // are linked to test plan, we need to compute how many results we have
  // because this figure is equal to qty of test cases linked to test plan
  // ATTENTION: recheck logic when there are PLATFORMS.
  
  // if there are linked test cases and ALL are not run => Req. takes status 'not run'
  // how many status counters are set ?
  $hmc = 0;
  foreach($status_code as $verbose => $code)
  {
    $hmc += isset($counters[$code]);
  }  

  if( ($counters['total'] > 0) ) 
  {
    list($evaluation,$doIt) = doNotRunAnalysis($hmc,$counters,$status_code['not_run']);
    if(!$doIt)
    {
      $evaluation .= ($is_fully_covered ? '' : '_nfc');
    }  
  }

  if($doIt) 
  {
    $evaluation = null;
    $analisysDone = false;
    foreach($algorithm_cfg['checkOrder'] as $checkKey) 
    {
      $analisysDone = true;
      $doOuterBreak = false;
      foreach($algorithm_cfg['checkType'][$checkKey] as $status2check) 
      {
        $code = $status_code[$status2check];
        $count = isset($counters[$code]) ? $counters[$code] : 0;
        if ($checkKey == 'atLeastOne' && $count) 
        {
          $evaluation = $is_fully_covered ? $code : $code . "_nfc";
          $doOuterBreak = true;
          break;
        }
        
        if($checkKey == 'all' && ($count == $counters['totalTPTCV']) )
        {
          $evaluation = $is_fully_covered ? $code : $code . "_nfc";
          $doOuterBreak = true;
          break;
        }
      }  
      
      if($doOuterBreak) 
      {
        break;
      }
    }

    if($analisysDone && is_null($evaluation))
    {
      $evaluation = 'partially_passed';
      if($counters[$status_code['not_run']] == 0)
      {
        $evaluation = $status_code['passed'];
      }  
      $evaluation .= ($is_fully_covered ? '' : '_nfc');
    }  
  }
  return $evaluation;
}


/**
 * Transform a numerical value to a string with its value as a padded html comment 
 * to make sorting on ExtJS table object easier
 * 
 * @author Andreas Simon
 * @param int $percentage
 * @return string
 */
function comment_percentage($percentage) 
{
  $percentage = round($percentage, 2);
  $padded_percentage = sprintf("%010d", $percentage);
  $string = "<!-- {$padded_percentage} --> {$percentage}% ";
  return $string;
}


/**
 * initialize user input
 * 
 * @author Andreas Simon
 * @param resource &$tproject_mgr reference to testproject manager
 * @return array $args array with user input information
 */
function init_args(&$tproject_mgr, &$tplan_mgr, &$req_cfg)
{
  $args = new stdClass();

  $states_to_show = array(0 => "0");
  if (isset($_REQUEST['states_to_show'])) 
  {
    $states_to_show = $_REQUEST['states_to_show'];
  } 
  else if (isset($_SESSION['states_to_show'])) 
  {
    $states_to_show = $_SESSION['states_to_show'];
  }
    
  $args->states_to_show = new stdClass();
  $args->states_to_show->selected = $_SESSION['states_to_show'] = $states_to_show;
  
  // get configured statuses and add "any" string to menu
  $args->states_to_show->items = array(0 => "[" . lang_get('any') . "]") + 
                                (array) init_labels($req_cfg->status_labels);
  
  $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
  $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;

  $args->tplan_id = intval($_SESSION['resultsNavigator_testplanID']);
  
  $args->format = $_SESSION['resultsNavigator_format'];

  // remember platform selection too
  //
  $platform = 0;
  $gui_open = config_get('gui_separator_open');
  $gui_close = config_get('gui_separator_close');
  $dummy = $tplan_mgr->platform_mgr->getLinkedToTestplanAsMap($args->tplan_id);
  $args->platformSet = $dummy ? array(0 => $gui_open . lang_get('any') . $gui_close) + $dummy : null;
  
  if (isset($_REQUEST['platform'])) 
  {
    $platform = $_REQUEST['platform'];
  } 
  else if ( isset($_SESSION['platform']) && isset($args->platforms[$_SESSION['platform']]) ) 
  {
    // ATTENTION: 
    // This can be ONLY done if: 
    // the platform we remember linked to current test plan 
    $platform = intval($_SESSION['platform']);
  }
  
  $args->platform = $_SESSION['platform'] = $platform;


  // $dummy = $tplan_mgr->get_builds_for_html_options($id,$active=null,$open=null,$opt=null)
  $dummy = $tplan_mgr->get_builds_for_html_options($args->tplan_id, 1); //Only active builds should be available to choose
  $args->buildSet = $dummy ? array(0 => $gui_open . lang_get('any') . $gui_close) + $dummy : null;
  $args->build = 0;
  if (isset($_REQUEST['build'])) 
  {
    $args->build = $_REQUEST['build'];
  } 
  
  return $args;
}


/**
 * initialize GUI object
 * 
 * @author Andreas Simon
 * @param stdClass $argsObj reference to user input
 * @return stdClass $gui gui data
 */
function init_gui(&$argsObj,&$tplanMgr) 
{
  $gui = new stdClass();
  
  $gui->pageTitle = lang_get('title_result_req_testplan');
  $gui->warning_msg = '';
  $gui->tproject_name = $argsObj->tproject_name;
  $gui->states_to_show = $argsObj->states_to_show;
  $gui->tableSet = null;
  $gui->selected_platform = $argsObj->platform;
  $gui->platforms = $argsObj->platformSet;

  $gui->builds = $argsObj->buildSet;
  $gui->selected_build = $argsObj->build;
  
  $dummy = $tplanMgr->get_by_id($argsObj->tplan_id);
  $gui->tplan_name = $dummy['name'];

  $gui->baseHref = $_SESSION['basehref'];

  return $gui;
}


function setUpLabels($reqCfg)
{
  $dummy = config_get('req_spec_cfg');
  $rsptlbl = init_labels($dummy->type_labels);

  $rtlbl = init_labels($reqCfg->type_labels);
  $slbl = init_labels($reqCfg->status_labels);

  $labels = init_labels( array('requirement' => null,'requirements' => null,
                               'type' => null,'req_availability' => null,
                               'linked_tcs' => null,'no_linked_tcs' => null,
                               'goto_testspec' => null,'design' => null,
                               'no_label_for_req_type'  => null, 'progress' => null,
                               'na' => 'not_aplicable', 'no_matching_reqs' => null,
                               'execution' => null,'no_srs_defined' => null,
                               'not_in_testplan' => null,'platform' => null,
                               'execution_history' => null, 'req_spec_short' => null));

  return array($rsptlbl,$rtlbl,$slbl,$labels);
}

/**
 *
 */
function setUpReqStatusCfg()
{
  $results_cfg = config_get('results');
  
  $status_code_map = array();
  foreach ($results_cfg['status_label_for_exec_ui'] as $status => $label) 
  {
    $status_code_map[$status] = $results_cfg['status_code'][$status];
  }
  
  $code_status_map = array_flip($status_code_map);
  foreach ($code_status_map as $code => $status) 
  {
    $code_status_map[$code] = array('label' => lang_get($results_cfg['status_label'][$status]),
                                    'long_label' => lang_get("req_title_" . $status),
                                    'status' => $status,
                                    'css_class' => $status . '_text');
  }


  $eva = $code_status_map;
  
  // add additional states for requirement evaluation
  $evalbl = init_labels(array('partially_passed' => null, 'partially_passed_reqs' => null,
                              'uncovered' => null, 'uncovered_reqs' => null,
                              'passed_nfc' => null, 'passed_nfc_reqs' => null,
                              'failed_nfc' => null, 'failed_nfc_reqs' => null,
                              'blocked_nfc' => null, 'blocked_nfc_reqs' => null,
                              'not_run_nfc' => null, 'not_run_nfc_reqs' => null,
                              'passed' => null,
                              'partially_passed_nfc' => null, 'partially_passed_nfc_reqs' => null));

  $eva['partially_passed'] = array('label' => $evalbl['partially_passed'],
                                   'long_label' => $evalbl['partially_passed_reqs'],
                                   'css_class' => 'passed_text');

  $eva['uncovered'] = array('label' => $evalbl['uncovered'],
                            'long_label' => $evalbl['uncovered_reqs'],
                            'css_class' => 'not_run_text');

  $eva['p_nfc'] = array('label' => $evalbl['passed_nfc'],
                        'long_label' => $evalbl['passed_nfc_reqs'],
                        'css_class' => 'passed_text');
                        
  $eva['f_nfc'] = array('label' => $evalbl['failed_nfc'],
                        'long_label' => $evalbl['failed_nfc_reqs'],
                        'css_class' => 'failed_text');
                                    
  $eva['b_nfc'] = array('label' => $evalbl['blocked_nfc'],
                        'long_label' => $evalbl['blocked_nfc_reqs'],
                        'css_class' => 'blocked_text');
                        
  $eva['n_nfc'] = array('label' => $evalbl['not_run_nfc'],
                        'long_label' => $evalbl['not_run_nfc_reqs'],
                        'css_class' => 'not_run_text');
                        
  $eva['partially_passed_nfc'] = array('label' => $evalbl['partially_passed_nfc'],
                                       'long_label' => $evalbl['partially_passed_nfc_reqs'],
                                       'css_class' => 'passed_text');
  

  // add count for each status to show test progress
  foreach ($eva as $key => $status) 
  {
    $eva[$key]['count'] = 0;
  }

  return array($results_cfg,$status_code_map,$code_status_map,$eva);
}


/**
 *
 */
function buildReqSpecMap($reqSet,&$reqMgr,&$reqSpecMgr,&$tplanMgr,$reqStatusFilter,&$argsObj)
{   
  $rspec = array();
  $total = 0;
  $tc_ids = array();

  $coverageContext = null;
  if ($argsObj->platform != 0) 
  {
    $coverageContext['tplan_id'] = $argsObj->tplan_id;
    $coverageContext['platform_id'] = $argsObj->platform;
  }
  
  // Test case linked to test plan
  // $itemsInTestPlan = $tplanMgr->get_linked_items_id($argsObj->tplan_id);
  $itemsInTestPlan = $tplanMgr->getLinkedItems($argsObj->tplan_id);
  foreach($reqSet as $id) 
  {
    // get the information for this requirement
    $req = $reqMgr->get_by_id($id, requirement_mgr::LATEST_VERSION);
    $req = $req[0];
    
    // if req is "usable" (has one of the selected states) add it
    if( in_array($req['status'], $reqStatusFilter, true) || 
        in_array("0", $reqStatusFilter, true) ) 
    {
      $total++;
      
      // some sort of Caching
      if (!isset($rspec[$req['srs_id']])) 
      {
        $rspec[$req['srs_id']] = $reqSpecMgr->get_by_id($req['srs_id']);
        $rspec[$req['srs_id']]['requirements'] = array();
      }

      $req['linked_testcases'] = (array)$reqMgr->get_coverage($id,$coverageContext,array('accessKey' => 'tcase_id'));

      // Now loop to mark test cases ASSIGNED to requirements as LINKED OR NOT to Test plan under analisys.
      foreach($req['linked_testcases'] as $itemID => $dummy)
      {
        $req['linked_testcases'][$itemID]['in_testplan'] = isset($itemsInTestPlan[$itemID]);  
      }  

      $rspec[$req['srs_id']]['requirements'][$id] = $req;

      foreach ($req['linked_testcases'] as $tc) 
      {
        $tc_ids[] = $tc['id'];
      }
    }
  }

  // Get test case data from test case version LINKED TO TEST PLAN, 
  // using as FILTER test cases ASSIGNED (linked) TO requirements
  //
  // ATTENTION:
  // What can happens is this
  // Test spec has TC1,TC2,TC3,TC4
  // REQ 1 has TC1,TC2.TC3 assigned
  // TEST PLAN A has only TC1, TC2
  //
  // It will be impossibile to provide a CLEAR INDICATION of 
  // relation between REQ status and Test case exec status, because
  // TC3 is NOT PART OF TEST PLAN under analisys
  //
  $tcaseSet = array();
  if (count($tc_ids)) 
  {
    $filters = array('tcase_id' => $tc_ids);
    $f2a = array('platform','build');
    foreach($f2a as $fk)
    {
      if ($argsObj->$fk != 0) 
      {
        $filters[$fk . '_id'] = $argsObj->$fk;
      }

    }  
    
    $filterOnly = array();
    $filterOnly['platform_id'] = isset($filters['platform_id']) &&  !isset($filters['build_id']);
    $filterOnly['build_id'] = !isset($filters['platform_id']) &&  isset($filters['build_id']);
    $noFilter = !isset($filters['platform_id']) && !isset($filters['build_id']);
    $allFilters = isset($filters['platform_id']) && isset($filters['build_id']);

    // $options = array('addExecInfo' => true,'accessKeyType' => 'tcase');
    $options = array('addExecInfo' => true,'accessKeyType' => 'tcase+platform', 'build_is_active' => true);

    if($noFilter || $filterOnly['platform_id'])  
    {
      $tcaseSet = $tplanMgr->getLTCVOnTestPlanPlatform($argsObj->tplan_id, $filters, $options);
    }  
    else if ($allFilters || $filterOnly['build_id'])
    {
      $tcaseSet = $tplanMgr->getLTCVNewGeneration($argsObj->tplan_id, $filters, $options);
    }  
  }

  return array($total,$rspec,$tcaseSet);
}

/**
 *
 */
function doNotRunAnalysis($tcaseQty,$execStatusCounter,$notRunCode)
{
  $evaluation = null;
  $doIt = true;

  if($tcaseQty == 1)
  {
    if( $execStatusCounter[$notRunCode] != 0 )
    {
      $evaluation = $notRunCode;
      $doIt = false;
    }  
  }  
  else
  {
    if(($execStatusCounter['totalTPTCV'] == $execStatusCounter[$notRunCode]))
    {
      $evaluation = $notRunCode;
      $doIt = false;
    }  
  }
  return array($evaluation,$doIt);
}



/**
 * Check if the user has the needed rights to view this page (testplan metrics).
 * 
 * @author Andreas Simon
 * @param Database $db reference to database object
 * @param tlUser $user reference to user object
 */
function checkRights(&$db, &$user)
{
  return $user->hasRight($db,'testplan_metrics');
}
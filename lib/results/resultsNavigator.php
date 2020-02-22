<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Scope: Launcher for Test Results and Metrics.
 *
 * @filesource	resultsNavigator.php
 * 
 * 
 **/
require('../../config.inc.php');
require_once('common.php');
require_once('reports.class.php');
testlinkInitPage($db,true,false,"checkRights");

$smarty = new TLSmarty();
$templateCfg = templateConfiguration();

$args = init_args($db);
$gui = initializeGui($db,$args);
$reports_mgr = new tlReports($db, $gui->tplan_id);

// --------------------------------------------------------------------
// Do some checks to understand if reports make sense

// Check if there are linked test cases to the choosen test plan.
$tc4tp_count = $reports_mgr->get_count_testcase4testplan();
tLog('TC in TP count = ' . $tc4tp_count);
if( $tc4tp_count == 0) {
  // Test plan without test cases
  $gui->do_report['status_ok'] = 0;
  $gui->do_report['msg'] = lang_get('report_tplan_has_no_tcases');       
}

// Build qty
$build_count = $reports_mgr->get_count_builds();
tLog('Active Builds count = ' . $build_count);
if( $build_count == 0) {
  // Test plan without builds can have execution data
  $gui->do_report['status_ok'] = 0;
  $gui->do_report['msg'] = lang_get('report_tplan_has_no_build');       
}

// -----------------------------------------------------------------
// get navigation data
$gui->menuItems = array();
if($gui->do_report['status_ok']) {
  // create a list or reports
  $context = new stdClass();
  $context->tproject_id = $args->tproject_id;
  $context->tplan_id = $args->tplan_id;

  $tplan_mgr = new testplan($db);
  $dmy = $tplan_mgr->get_by_id($context->tplan_id);
  unset($tplan_mgr);

  $context->apikey = $dmy['api_key'];
  $context->imgSet = $smarty->getImages();
  $gui->menuItems = 
    $reports_mgr->get_list_reports($context,$gui->btsEnabled,$args->optReqs, 
                                   $tlCfg->reports_formats[$args->format]);
}

$gui->selectedReportType = $args->format;
$gui->reportTypes = localize_array($tlCfg->reports_formats);


$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * 
 *
 */
function init_args(&$dbH) {
  $iParams = array("format" => array(tlInputParameter::INT_N),
                   "tplan_id" => array(tlInputParameter::INT_N),
                   "tproject_id" => array(tlInputParameter::INT_N),
                   "show_inactive_tplans" => array(tlInputParameter::CB_BOOL));
  $args = new stdClass();
  R_PARAMS($iParams,$args);
  
  if (is_null($args->format)) {
    $reports_formats = config_get('reports_formats');
    $args->format = sizeof($reports_formats) ? key($reports_formats) : null;
  }

  $args->userID = $_SESSION['userID'];
  $args->user = $_SESSION['currentUser'];

  $args->tproject_id = intval($args->tproject_id);
  if ( $args->tproject_id <= 0) {
   throw new Exception("Invalid Test Project ID", 1);
  }  
  
  $args->tplan_id = intval($args->tplan_id);
  if ( $args->tplan_id <= 0) {
    // Get First testplan in the availables
    $tplanSet = $args->user->getAccessibleTestPlans($dbH,
                               $args->tproject_id);
    if( count($tplanSet) > 0 ) {
      $cu = current($tplanSet);
      $args->tplan_id = intval($cu['id']);
    } else {
      throw new Exception("Invalid Test Plan ID", 1);
    } 
  }
  
  $args->checked_show_inactive_tplans = 
    $args->show_inactive_tplans ? 'checked="checked"' : 0;
  $args->show_only_active_tplans = !$args->show_inactive_tplans;
    
  return $args;
}

/**
 *
 */
function initializeGui(&$dbHandler,$argsObj) {

  list($add2args,$gui) = initUserEnv($dbHandler,$argsObj);

  $gui->workframe = $_SESSION['basehref'] . "lib/general/staticPage.php?key=showMetrics";
  $gui->do_report = array('status_ok' => 1, 'msg' => '');
  $gui->tplan_id = $argsObj->tplan_id;
  $gui->tproject_id = $argsObj->tproject_id;
  $gui->checked_show_inactive_tplans = $argsObj->checked_show_inactive_tplans;
  
  $tprojMgr = new testproject($dbHandler);
  $gui->btsEnabled = $tprojMgr->isIssueTrackerEnabled($gui->tproject_id);

  $tprjOpt = $tprojMgr->getOptions($gui->tproject_id);
  $argsObj->optReqs = $tprjOpt->requirementsEnabled;

  // get Accessible Test Plans for combobox
  $activeAttr = $argsObj->show_only_active_tplans ? 1 : null;
  $gui->tplans = 
    $argsObj->user->getAccessibleTestPlans($dbHandler,$argsObj->tproject_id,null,
                                           array('output' =>'combo', 'active' => $activeAttr));
  
  return $gui;
}



/**
 * 
 *
 */
function checkRights(&$db,&$user) {
  return $user->hasRight($db,'testplan_metrics');
}
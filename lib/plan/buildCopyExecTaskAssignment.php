<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package     TestLink
 * @author      Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright   2005-2016, TestLink community 
 * @filesource  buildCopyExecTaskAssignment.php
 * @link        http://www.testlink.org
 *
 */
         
require_once(dirname(__FILE__)."/../../config.inc.php");
require_once("common.php");

// TODO understand the new model for rights check
testlinkInitPage($db);

$tplan_mgr = new testplan($db);
$assignment_mgr = &$tplan_mgr->assignment_mgr;
$build_mgr = new build($db);

$templateCfg = templateConfiguration();

$args = init_args($db,$tplan_mgr,$build_mgr);
$gui = init_gui($db, $args, $tplan_mgr);

switch( $args->doAction )
{
  case 'copy':
    // Step 1 - remove old assignments
    $assignment_mgr->delete_by_build_id($args->build_id);

    // Step 2 - copy assignments
    $assignment_mgr->copy_assignments($args->source_build_id,$args->build_id,
                                      $args->user_id);
    $gui->message = lang_get('copy_done');
  break;

  default:
  break;
}


$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 *
 */
function init_args(&$dbH,&$tplanMgr,&$buildMgr) 
{
  $args = new stdClass();
  
  $_REQUEST = strings_stripSlashes($_REQUEST);
  
  $k2g = array('build_id','source_build_id');
  foreach($k2g as $key) {
    $args->$key = isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : 0;
  }  

  $args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : null;
  switch( $args->doAction )
  {
    case 'copy':
    break;

    default:
     $args->doAction = null;
    break;
  }

  // Fatal checks
  if( $args->build_id <= 0 ) {
    throw new Exception("Error Processing Request - Target build is not set", 1);
  }  

  // Get test plan id from build
  $bi = $buildMgr->get_by_id($args->build_id);
  $args->tplan_id = $bi['testplan_id'];

  // Get test project id from test plan
  $pli = $tplanMgr->get_by_id($args->plan_id);
  $args->tproject_id = $pli['testproject_id'];

  $args->confirmed = isset($_REQUEST['confirmed']) 
                     && $_REQUEST['confirmed'] == 'yes' ? true : false;
  
  
  $args->refreshTree = false;
  $args->currentUser = $_SESSION['currentUser'];
  $args->user_id = $_SESSION['userID'];

  // ----------------------------------------------------------------
  // Feature Access Check
  $env = array()
  $env['script'] = basename(__FILE__);
  $env['tproject_id'] = $args->tproject_id;
  $env['tplan_id'] = $args->tplan_id;
  $args->user->checkGUISecurityClearance(dbHandler,$env,
                    array('testplan_planning'),'and');
  // ----------------------------------------------------------------

  return $args;
}


/**
 *
 */
function init_gui(&$dbHandler, &$argsObj, &$tplanMgr) 
{
  $gui = new stdClass();
  
  $gui->build_id = $argsObj->build_id;
  $gui->source_build_id = $argsObj->source_build_id;
  $gui->source_build = getBuildDomainForGUI($tplanMgr, $argsObj);
  $gui->message = "";

  if( is_null($gui->source_build->items) )
  {
    $gui->message = lang_get('no_builds_available_for_tester_copy');
  }  

  $gui->draw_tc_unassign_button = false;
  $gui->refreshTree = false;
  
  $gui->title = lang_get('copy_tester_assignments_title');

  
  $gui->popup_title = "";
  $gui->popup_message = "";
  
  return $gui;
}

/**
 * Initialize the HTML select box for selection of a source build when
 * user wants to copy the user assignments.
 * @internal revisions
 */
function getBuildDomainForGUI(&$tplanMgr, &$argsObj) 
{

  $htmlMenu = array('items' => null, 'selected' => null, 'build_count' => 0, 'testers' => null);
  $opt = array('orderByDir' => 'id:DESC', 'excludeBuild' => $argsObj->build_id);
  
  $htmlMenu['items'] = $tplanMgr->get_builds_for_html_options($argsObj->tplan_id,testplan::ACTIVE_BUILDS,testplan::OPEN_BUILDS,$opt);

  // get the number of existing execution assignments with each build
  if( !is_null($htmlMenu['items']) )
  {
    $lblCount = lang_get('assignments'); 
    $htmlMenu['build_count'] = count($htmlMenu['items']);
    foreach ($htmlMenu['items'] as $key => $name) 
    {
      $count = $tplanMgr->assignment_mgr->get_count_of_assignments_for_build_id($key);
      $htmlMenu['items'][$key] = $name . " ($lblCount" . $count . ")"; 
      $htmlMenu['testers'][$key] = $count; 
    }
    
    // if no build has been chosen yet, select the newest build by default
    reset($htmlMenu['items']);
    if( !$argsObj->source_build_id )
    {
      $htmlMenu['selected'] = key($htmlMenu['items']);
    } 
  }   
  
  return $htmlMenu;
} 
<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Platform link/unlink from a test plan
 * 
 * @package     TestLink
 * @copyright   2005-2020, TestLink community 
 * @filesource  platformsAssign.php
 * @link        http://www.testlink.org
 *
 **/
require_once("../../config.inc.php");
require_once("common.php");
require_once("opt_transfer.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();

$opt_cfg = opt_transf_empty_cfg();
$opt_cfg->js_ot_name = 'ot';
$args = init_args($opt_cfg);
$tplan_mgr = new testplan($db);
$platform_mgr = new tlPlatform($db, $args->tproject_id);
checkPageAccess($db,$args);  // Will exit if check failed
$gui = initializeGui($db,$args,$platform_mgr); 

$smarty = new TLSmarty();

if (isset($args->tplan_id) && $args->tplan_id >0) {
  // do following check to give warning to user
  // if test plan has test case versions with platform_id=0
  // this means that right now there are not platforms linked to test plan.
  // Give message to user with following info:
  // Till you are not going to assign a platform to this linked tcversions
  // and it's execution results he/she will not be able to execute
  //
  $qtyByPlatform = $tplan_mgr->countLinkedTCVersionsByPlatform($args->tplan_id);


  $qtyLinked2Unknown = isset($qtyByPlatform[0]['qty']) ? $qtyByPlatform[0]['qty'] : 0;
 
  if( ($fix_needed = ($qtyLinked2Unknown > 0)) ) {
    $gui->warning = lang_get('unknown_platform');
  }
  $opt_cfg->global_lbl = '';
  $opt_cfg->additional_global_lbl = null;
  $opt_cfg->from->lbl = lang_get('available_platforms');
  $opt_cfg->to->lbl = lang_get('assigned_platforms');
  $gui->platform_count_js = init_option_panels($tplan_mgr, $platform_mgr, $opt_cfg, $args);

  $tplanData = $tplan_mgr->get_by_id($args->tplan_id);
  if (isset($tplanData)) {
    $gui->mainTitle = sprintf($gui->mainTitle,$tplanData['name']);
  }

  // -------------------------------------------------------------------------------------------------
  if ($args->doAction == 'doAssignPlatforms' || $args->doAction == 'doAssignAndLinkTCV') {
    $platform_mgr->linkToTestplan($args->platformsToAdd,$args->tplan_id);
    $platform_mgr->unlinkFromTestplan($args->platformsToRemove,$args->tplan_id);
  }	

  if ($args->doAction == 'doAssignPlatforms') {
    if( $fix_needed && count($args->platformsToAdd) == 1) {
      reset($args->platformsToAdd);
      $tplan_mgr->changeLinkedTCVersionsPlatform($args->tplan_id,0,current($args->platformsToAdd));
    }
    // Update option panes with newly updated config
    $gui->platform_count_js = init_option_panels($tplan_mgr, $platform_mgr, $opt_cfg, $args);
  }

  if ($args->doAction == 'doAssignAndLinkTCV') {

    // Get all linked platforms and filter out the platforms WITH TESTCASES
     $platWithZeroTCV = [];
     if ($args->onRightSide != null && count($args->onRightSide)) {
      foreach($args->onRightSide as $platID) {
        if (!isset($qtyByPlatform[$platID])) {
          $platWithZeroTCV[$platID] = $platID;  
        }
      }
     }

    // get all linked to the first of selected platforms
    $fromPlat = intval($_REQUEST["to_select_box"]);
    if ($fromPlat == 0) {
      // try to get first on set
      if ($gui->hasTCVWithPlat) {
        $platSetLink = array_keys($qtyByPlatform);
        $fromPlat = current($platSetLink);
      }	
    }

    // test again
    if ($fromPlat != 0) {
       // does this platform linked TCV? if not -> nothing can be done
       if (isset($qtyByPlatform[$fromPlat])) {
         foreach($platWithZeroTCV as $toPlat) {
           $tplan_mgr->copyLinkFromPlatformToPlatform($args->tplan_id,$fromPlat,$toPlat,$args->user_id);
         }	
       }
      // Update option panes with newly updated config
      $gui->platform_count_js = init_option_panels($tplan_mgr, $platform_mgr, $opt_cfg, $args);
    }
  }
  // -------------------------------------------------------------------------------------------------



}


$opt_cfg->from->desc_field = 'platform';
$opt_cfg->to->desc_field = 'platform';
item_opt_transf_cfg($opt_cfg, null);

$smarty->assign('gui', $gui);
$smarty->assign('opt_cfg', $opt_cfg);

$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * Initializes option transfer items, by appending a text with number linked TC:s 
 * for every assigned platform.
 * It also builds a js map platform_name => linked_count. 
 * This map is used to show warning dialog only when trying to unlink 
 * platforms with assigned TCs
 */
function init_option_panels(&$tplan_mgr, &$platform_mgr, 
                            &$opt_cfg, &$args)
{

  $opt_cfg->from->map = $platform_mgr->getAllAsMap(config_get('platforms')->allowedOnAssign);

  $optLTT = null;
  $map = $platform_mgr->getLinkedToTestplanAsMap($args->tplan_id,
                                                 $optLTT);
  $platform_count_js = "platform_count_map = new Array();\n";
  if (!is_null($map)) {     
    foreach ($map as $plat_id => &$plat_name) {
      $count = $tplan_mgr->count_testcases($args->tplan_id,
                                           $plat_id);
      $plat_name .= sprintf(lang_get('platform_linked_count'), 
                            $count);
      $platform_count_js .= 
        "platform_count_map['$plat_name'] = $count;\n";

      // Removal of duplicates is NOT handled 
      // automatically since we just have modified 
      // their names adding a usage counter.
      unset($opt_cfg->from->map[$plat_id]);
    }
  }

  $opt_cfg->to->map = $map;
  return $platform_count_js;
}

/**
 * 
 *
 */
function init_args(&$opt_cfg)
{
  $added = $opt_cfg->js_ot_name . "_addedRight";
  $removed = $opt_cfg->js_ot_name . "_removedRight";

  $iParams = [
    "tproject_id" => [tlInputParameter::INT_N],
    "tplan_id" => [tlInputParameter::INT_N],
    "edit" => [tlInputParameter::STRING_N,0,100],
    "doAction" => [tlInputParameter::STRING_N,0,20],
    $added => [tlInputParameter::STRING_N],
    $removed => [tlInputParameter::STRING_N]
  ];

  $pParams = R_PARAMS($iParams);

  $args = new stdClass();
  $args->tproject_id = $pParams["tproject_id"];
  $args->tplan_id = $pParams["tplan_id"];
  $args->edit = $pParams["edit"];
  $args->doAction = $pParams["doAction"];
  $args->currentUser = $_SESSION['currentUser'];
  
  $args->platformsToAdd = [];
  if ($pParams[$added] != "") {
     $args->platformsToAdd = (array)explode(",", $pParams[$added]);
  }
  
  $args->platformsToRemove = [];
  if( $pParams[$removed] != "" ) 
  {
    $args->platformsToRemove = (array)explode(",", $pParams[$removed]);
  }

  return $args;
}


/**
 * 
 */
function initializeGui(&$dbH,&$argsObj,&$platformMgr) 
{
  $gui = $platformMgr->initViewGui($argsObj->currentUser,$argsObj);
  $gui->activeMenu['projects'] = 'inactive'; // done in initViewGui
  $gui->activeMenu['plans'] = 'active';

  $gui->platform_assignment_subtitle = null;
  $gui->warning = '';
  $gui->tplan_id = $argsObj->tplan_id;
  $gui->tproject_id = $argsObj->tproject_id;
  $gui->can_do = isset($argsObj->tplan_id);
  $gui->mainTitle = lang_get('add_remove_platforms');

  return $gui;
}

/**
 *
 */
function checkPageAccess(&$db,&$argsObj) {
  $env['script'] = basename(__FILE__);
  $env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
  $env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
  $argsObj->currentUser->checkGUISecurityClearance($db,$env,array('testplan_add_remove_platforms'),'and');
}

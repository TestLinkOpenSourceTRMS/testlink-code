<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Platform link/unlink from a test plan
 * 
 * @package     TestLink
 * @author      eloff
 * @copyright   2005-2014, TestLink community 
 * @filesource  platformsAssign.php
 * @link        http://www.testlink.org
 *
 * @internal revisions
 * @since 1.9.11
 *
 **/
require_once("../../config.inc.php");
require_once("common.php");
require_once("opt_transfer.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$opt_cfg = opt_transf_empty_cfg();
$opt_cfg->js_ot_name = 'ot';
$args = init_args($opt_cfg);

if ($args->edit == 'testproject')
{
  show_instructions('platformAssign');
  exit();
}


$smarty = new TLSmarty();
$tplan_mgr = new testplan($db);
$platform_mgr = new tlPlatform($db, $args->testproject_id);

$gui = new stdClass();
$gui->platform_assignment_subtitle = null;
$gui->tplan_id = $args->tplan_id;
$gui->can_do = isset($args->tplan_id);
$gui->mainTitle = lang_get('add_remove_platforms');
$gui->warning = '';

if (isset($args->tplan_id))
{
  // do following check to give warning to user
  // if test plan has test case versions with platform_id=0
  // this means that right now there are not platforms linked to test plan.
  // Give message to user with following info:
  // Till you are not going to assign a platform to this linked tcversions
  // and it's execution results he/she will not be able to execute
  //
  $qtyByPlatform = $tplan_mgr->countLinkedTCVersionsByPlatform($args->tplan_id);
  $qtyLinked2Unknown = isset($qtyByPlatform[0]['qty']) ? $qtyByPlatform[0]['qty'] : 0;
  if( ($fix_needed = ($qtyLinked2Unknown > 0)) )
  {
    
    $gui->warning = lang_get('unknown_platform');
  }
  $opt_cfg->global_lbl = '';
  $opt_cfg->additional_global_lbl = null;
  $opt_cfg->from->lbl = lang_get('available_platforms');
  $opt_cfg->to->lbl = lang_get('assigned_platforms');
  $gui->platform_count_js = init_option_panels($tplan_mgr, $platform_mgr, $opt_cfg, $args);

  $tplanData = $tplan_mgr->get_by_id($args->tplan_id);
  if (isset($tplanData))
  {
    $gui->mainTitle = sprintf($gui->mainTitle,$tplanData['name']);
  }

    
  if($args->doAction == 'doAssignPlatforms')
  {
    $platform_mgr->linkToTestplan($args->platformsToAdd,$args->tplan_id);
    $platform_mgr->unlinkFromTestplan($args->platformsToRemove,$args->tplan_id);
    if( $fix_needed && count($args->platformsToAdd) == 1)
    {
      reset($args->platformsToAdd);
      $tplan_mgr->changeLinkedTCVersionsPlatform($args->tplan_id,0,current($args->platformsToAdd));
    }
    // Update option panes with newly updated config
    $gui->platform_count_js = init_option_panels($tplan_mgr, $platform_mgr, $opt_cfg, $args);
  }
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
function init_option_panels(&$tplan_mgr, &$platform_mgr, &$opt_cfg, &$args)
{
  $opt_cfg->from->map = $platform_mgr->getAllAsMap();
  $map = $platform_mgr->getLinkedToTestplanAsMap($args->tplan_id);
  $platform_count_js = "platform_count_map = new Array();\n";
  if(!is_null($map))
  {     
    foreach ($map as $platform_id => &$platform_name) 
    {
      $count = $tplan_mgr->count_testcases($args->tplan_id,$platform_id);
      $platform_name .= sprintf(lang_get('platform_linked_count'), $count);
      $platform_count_js .= "platform_count_map['$platform_name'] = $count;\n";
      
      // Removal of duplicates is NOT handles automatically since we just
      // modified their names.
      unset($opt_cfg->from->map[$platform_id]);
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

  $iParams = array( "tplan_id" => array(tlInputParameter::INT_N),
                    "edit" => array(tlInputParameter::STRING_N,0,100),
                    "doAction" => array(tlInputParameter::STRING_N,0,20),
                    $added => array(tlInputParameter::STRING_N),
                    $removed => array(tlInputParameter::STRING_N));

  $pParams = R_PARAMS($iParams);

  $args = new stdClass();
  $args->tplan_id = $pParams["tplan_id"];
  $args->platformsToAdd = null;
  $args->platformsToRemove = null;
  $args->edit = $pParams["edit"];
  $args->doAction = $pParams["doAction"];
  $args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
  
  if( $pParams[$added] != "" ) 
  {
     $args->platformsToAdd = explode(",", $pParams[$added]);
  }
  
  if( $pParams[$removed] != "" ) 
  {
    $args->platformsToRemove = explode(",", $pParams[$removed]);
  }

  return $args;
}


function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'testplan_add_remove_platforms');
}

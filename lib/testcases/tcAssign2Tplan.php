<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * While in test specification feature, assign TEST CASE version to multiple
 * ACTIVE test plans
 *
 * @package     TestLink
 * @author      Amit Khullar - amkhullar@gmail.com
 * @copyright   2007-2014, TestLink community 
 * @filesource  tcAssign2Tplan.php,v 1.8 2010/05/20 18:20:46 franciscom Exp $
 *
 *
 * @internal revisions
 **/

require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tcase_mgr=new testcase($db);
$tplan_mgr=new testplan($db);
$tproject_mgr=new testproject($db);

$glue = config_get('testcase_cfg')->glue_character;
$args = init_args();
$gui = initializeGui($args);
$getOpt = array('outputFormat' => 'map', 'addIfNull' => true);
$gui->platformSet = $tplan_mgr->getPlatforms($args->tplan_id,$getOpt);  

$options['output'] = 'essential';
$tcase_all_info = $tcase_mgr->get_by_id($args->tcase_id,testcase::ALL_VERSIONS,null,$options);

if( !is_null($tcase_all_info) )
{
  foreach($tcase_all_info as $tcversion_info)
  {
    if($tcversion_info['id'] == $args->tcversion_id )
    {
      $version = $tcversion_info['version'];
      $gui->pageTitle=lang_get('test_case') . ':' . $tcversion_info['name'];
      $gui->tcaseIdentity = $tproject_mgr->getTestCasePrefix($args->tproject_id);
      $gui->tcaseIdentity .= $glue . $tcversion_info['tc_external_id'] . ':' . $tcversion_info['name'];
      break;      
    }   
  } 
}

$link_info = $tcase_mgr->get_linked_versions($args->tcase_id);
if( !is_null($tplanSet = $tproject_mgr->get_all_testplans($args->tproject_id,array('plan_status' => 1))) )
{
  $has_links = array_fill_keys(array_keys($tplanSet),false);
  $linked_tplans = null;
  if( !is_null($link_info) )
  {
    foreach($link_info as $tcversion_id => $info)
    {
      foreach($info as $tplan_id => $platform_info)
      {
        $has_links[$tplan_id] = true;
        foreach($platform_info as $platform_id => $value)
        {
          $linked_tplans[$tplan_id][$platform_id]['tcversion_id']=$value['tcversion_id'];                            
          $linked_tplans[$tplan_id][$platform_id]['version']=$value['version'];
          $linked_tplans[$tplan_id][$platform_id]['draw_checkbox'] = false;
        }
      }
    }  
  }

  // Initial situation, enable link of target test case version to all test plans
  $getOpt = array('outputFormat' => 'map', 'addIfNull' => true);
  foreach($tplanSet as $tplan_id => $value)  
  {
    $gui->tplans[$tplan_id] = array();
    $platformSet = $tplan_mgr->getPlatforms($tplan_id,$getOpt);

    // $target_version_number = 0;
    // $target_version_id = 0;
    $target_version_number = $version;
    $target_version_id = $args->tcversion_id;
    $linked_platforms = null;

    // if a version of this Test Case has been linked to test plan, get it.
    if( $has_links[$tplan_id] )
    {
      $linked_platforms = array_flip(array_keys($linked_tplans[$tplan_id]));
      $dummy = current($linked_tplans[$tplan_id]);
      $target_version_number = $dummy['version'];
      $target_version_id = $dummy['tcversion_id'];
    }

    // do logic on test plan linked platforms to understand what to display
    // For situation like
    // Test Plan TPX - Platforms: P1,P2,P3
    // Test Case A - version 1 -> Test Plan TPX - Platform P1
    // 
    // Create Test Case A - version 2
    //
    // Add to test plan on version 2
    // We CAN NOT DISPLAY Platforms P2 and P3, because P1 has been linked to version 1
    // and we DO NOT ALLOW different test case versions to be linked to ONE TEST PLAN.
    // Then we need to display only
    // [x](read only)  version 1 - test plan TPX - platform P1
    //
    // But if we go to version 1 and choose add to test plan, will display:
    // [x](read only)  version 1 - test plan TPX - platform P1
    // [ ]  version 1 - test plan TPX - platform P2
    // [ ]  version 1 - test plan TPX - platform P3
    //
    // Then we can add version 1 to other platform
    // Following logic try to implement this.
    //
    foreach($platformSet as $platform_id => $platform_info)
    {
      $doAdd = true;
      $draw_checkbox = true;
      if( $has_links[$tplan_id] )
      {
        if( isset($linked_platforms[$platform_id]) )
        {
          $draw_checkbox = false;
        }
        else if($target_version_number == $version)
        {
          $draw_checkbox = true;
        }
        else
        {
          $doAdd = false;
        }
      } 
      if( $doAdd )
      {
        $gui->tplans[$tplan_id][$platform_id] = $value;
        $gui->tplans[$tplan_id][$platform_id]['tcversion_id'] = $target_version_id;
        $gui->tplans[$tplan_id][$platform_id]['version'] = $target_version_number;
        $gui->tplans[$tplan_id][$platform_id]['draw_checkbox'] = $draw_checkbox;
        $gui->tplans[$tplan_id][$platform_id]['platform'] = $platform_info;                            
      }
          
    }
  }

  // Check if submit button can be displayed.
  // Condition there is at least one test plan where no version of
  // target test cases has been linked.
  $gui->can_do=false;  // because an OR logic will be used
  foreach($gui->tplans as $tplan_id => $platform_info)  
  {
    foreach($platform_info as $platform_id => $value)
    {
      $gui->can_do = $gui->can_do || $gui->tplans[$tplan_id][$platform_id]['draw_checkbox'];
    }
        
  }    
}
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * init_args
 * creates a sort of namespace
 *
 * @return  object with some REQUEST and SESSION values as members.
 */
function init_args()
{
  $_REQUEST = strings_stripSlashes($_REQUEST);

  // if any piece of context is missing => we will display nothing instead of crashing WORK TO BE DONE
  $args = new stdClass();
  $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testplanID'];
  $args->tproject_id = isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : $_SESSION['testprojectID'];
  $args->tcase_id = isset($_REQUEST['tcase_id']) ? $_REQUEST['tcase_id'] : 0;
  $args->tcversion_id = isset($_REQUEST['tcversion_id']) ? $_REQUEST['tcversion_id'] : 0;
  return $args; 
}


/**
 * 
 *
 */
function initializeGui($argsObj)
{
  $guiObj = new stdClass();
  $guiObj->pageTitle='';
  $guiObj->tcaseIdentity='';
  $guiObj->mainDescription=lang_get('add_tcversion_to_plans');;
  $guiObj->tcase_id=$argsObj->tcase_id;
  $guiObj->tcversion_id=$argsObj->tcversion_id;
  $guiObj->can_do=false;
  $guiObj->item_sep=config_get('gui')->title_separator_2;
  $guiObj->cancelActionJS = 'location.href=fRoot+' . "'" . "lib/testcases/archiveData.php?" .
                            'edit=testcase&id=' . intval($argsObj->tcase_id) . "'"; 
  return $guiObj;
}
<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Test navigator for Test Plan for following features
 *
 * - Test case execution
 *
 * @filesource  execNavigator.php
 * @package     TestLink
 * @copyright   2007-2017, TestLink community
 * @link        http://www.testlink.org
 *
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');
require_once("users.inc.php");
require_once('treeMenu.inc.php');
require_once('exec.inc.php');

testlinkInitPage($db);

$templateCfg = templateConfiguration();

$chronos[] = $tstart = microtime(true);
$control = new tlTestCaseFilterControl($db, 'execution_mode');
$control->formAction = '';

$gui = initializeGui($db,$control);


$control->build_tree_menu($gui);


$smarty = new TLSmarty();
if( $gui->execAccess )
{
  $smarty->assign('gui',$gui);
  $smarty->assign('control', $control);
  $smarty->assign('menuUrl',$gui->menuUrl);
  $smarty->assign('args', $gui->args);
  $tpl = $templateCfg->template_dir . $templateCfg->default_template;
}  
else
{
  $tpl = 'noaccesstofeature.tpl';
}

$smarty->display($tpl);


/**
 * 
 *
 */
function initializeGui(&$dbH,&$control) 
{
  $gui = new stdClass();
  
  // This logic is managed from execSetResults.php
  $gui->loadExecDashboard = true;
  if( isset($_SESSION['loadExecDashboard'][$control->form_token]) || 
      $control->args->loadExecDashboard == 0 
    ) 
  {
    $gui->loadExecDashboard = false;  
    unset($_SESSION['loadExecDashboard'][$control->form_token]);      
  }  

  $gui->menuUrl = 'lib/execute/execSetResults.php';
  $gui->args = $control->get_argument_string();
  if($control->args->loadExecDashboard == false)
  {
    $gui->src_workframe = '';
  } 
  else
  {
    $gui->src_workframe = $control->args->basehref . $gui->menuUrl .
                          "?edit=testproject&id={$control->args->testproject_id}" . 
                          $gui->args;
  } 
  
  $control->draw_export_testplan_button = true;
  $control->draw_import_xml_results_button = true;
  
  
  $dummy = config_get('results');
  $gui->not_run = $dummy['status_code']['not_run'];
  
  $dummy = config_get('execution_filter_methods');
  $gui->lastest_exec_method = $dummy['status_code']['latest_execution'];
  $gui->pageTitle = lang_get('href_execute_test');

  $grants = checkAccessToExec($dbH,$control);

  // feature to enable/disable
  $gui->features = array('export' => false,'import' => false);
  $gui->execAccess = false;
  if($grants['testplan_execute'])
  {
    $gui->features['export'] = true;
    $gui->features['import'] = true;
    $gui->execAccess = true;
  }  

  if($grants['exec_ro_access'])
  {
    $gui->execAccess = true;
  }  


  $control->draw_export_testplan_button = $gui->features['export'];
  $control->draw_import_xml_results_button = $gui->features['import'];

  return $gui;
}


/**
 *
 */
function checkAccessToExec(&$dbH,&$ct)
{
  $tplan_id = intval($ct->args->testplan_id);
  $sch = tlObject::getDBTables(array('testplans'));
  $sql = "SELECT testproject_id FROM {$sch['testplans']} " .
         "WHERE id=" . $tplan_id;
  $rs = $dbH->get_recordset($sql);
  if(is_null($rs))
  {
    throw new Exception("Can not find Test Project For Test Plan - ABORT", 1);
    
  }  
  $rs = current($rs);
  $tproject_id = $rs['testproject_id'];

  $user = $_SESSION['currentUser'];
  $grants = null;
  $k2a = array('testplan_execute','exec_ro_access');
  foreach($k2a as $r2c)
  {
    $grants[$r2c] = false;
    if( $user->hasRight($dbH,$r2c,$tproject_id,$tplan_id,true) || $user->globalRoleID == TL_ROLES_ADMIN )
    {
      $grants[$r2c] = true;
    }    
  }  

  return $grants;
} 

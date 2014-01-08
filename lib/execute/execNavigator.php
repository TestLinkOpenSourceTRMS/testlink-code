<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * link/unlink test cases to a test plan
 *
 * @filesource  execNavigator.php
 * @package   TestLink
 * @copyright   2007-2011, TestLink community
 * @link    http://www.teamst.org/index.php
 *
 * @internal revisions
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

$gui = initializeGui($control);
$control->build_tree_menu($gui);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->assign('control', $control);
$smarty->assign('menuUrl',$gui->menuUrl);
$smarty->assign('args', $gui->args);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * 
 *
 */
function initializeGui(&$control) 
{
  $gui = new stdClass();

  // This logic is managed from execSetResults.php
  $gui->loadExecDashboard = true;
  if( isset($_SESSION['loadExecDashboard'][$control->form_token]) )
  {
    $gui->loadExecDashboard = false;  
    unset($_SESSION['loadExecDashboard'][$control->form_token]);      
  }  

  $gui->menuUrl = 'lib/execute/execSetResults.php';
  $gui->args = $control->get_argument_string();
  $gui->src_workframe = $control->args->basehref . $gui->menuUrl .
                  "?edit=testproject&id={$control->args->testproject_id}" . $gui->args;
  
  $control->draw_export_testplan_button = true;
  $control->draw_import_xml_results_button = true;
  
  
  $dummy = config_get('results');
  $gui->not_run = $dummy['status_code']['not_run'];
  
  $dummy = config_get('execution_filter_methods');
  $gui->lastest_exec_method = $dummy['status_code']['latest_execution'];

  return $gui;
}
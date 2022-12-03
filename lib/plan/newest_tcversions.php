<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @filesource newest_tcversions.php
 * 
 */         
require('../../config.inc.php');
require_once("common.php");

testlinkInitPage($db);

$templateCfg = templateConfiguration();

$testcase_cfg = config_get('testcase_cfg');
$tree_mgr = new tree($db); 
$tsuite_mgr = new testsuite($db); 
$tplan_mgr = new testplan($db); 
$tcase_mgr = new testcase($db); 


$args = init_args($db);
$gui = new stdClass();
$gui->can_manage_testplans = $_SESSION['currentUser']->hasRight($db,"mgt_testplan_create");
$gui->tplans = [];
$gui->show_details = 0;
$gui->user_feedback = '';
$gui->tplan_id = $args->tplan_id;
$gui->tproject_id = $args->tproject_id;

$gui->tcasePrefix = $tcase_mgr->tproject_mgr->getTestCasePrefix($args->tproject_id) .
                    $testcase_cfg->glue_character;

$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$gui->tplan_name = $tplan_info['name'];
$gui->tproject_name = $args->tproject_name;

$linked_tcases = (array)$tplan_mgr->get_linked_items_id($args->tplan_id);
$qty_linked = count($linked_tcases);
$gui->testcases = (array)$tplan_mgr->get_linked_and_newest_tcversions($args->tplan_id);

if ($qty_linked) {
  $qty_newest = count($gui->testcases);
  $gui->user_feedback = lang_get('no_newest_version_of_linked_tcversions');  
  if ($qty_newest) {
    $gui->user_feedback = '';
    $gui->show_details = 1;
    
    // get path
    $tcaseSet = array_keys($gui->testcases);
    $path_info=$tree_mgr->get_full_path_verbose($tcaseSet);
    foreach ($gui->testcases as $tcase_id => $value) {
      $path = $path_info[$tcase_id];
      unset($path[0]);
      $path[] = '';
      $gui->testcases[$tcase_id]['path']=implode(' / ',$path);
    }
  }
} else {
  $gui->user_feedback = lang_get('no_linked_tcversions');  
}

$tplans = $_SESSION['currentUser']->getAccessibleTestPlans($db,$args->tproject_id);
foreach($tplans as $key => $value) {
	$gui->tplans[$value['id']] = $value['name'];
}

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



/**
 * init_args
 *
 */
function init_args(&$dbH)
{
	$_REQUEST = strings_stripSlashes($_REQUEST);
  list($args,$env) = initContext();

  $args->user_id = $_SESSION['userID'];
  $args->tproject_name = testproject::getName($dbH,$args->tproject_id);
  
  
  $args->id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
  $args->version_id = isset($_REQUEST['version_id']) ? 
                      intval($_REQUEST['version_id']) : 0;
                      
  $args->level = isset($_REQUEST['level']) ? $_REQUEST['level'] : null;
  
  // Can be a list (string with , (comma) has item separator), 
  $args->keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;

  $args->user = $_SESSION['currentUser'];
  // ---------------------------------------------------------------------------------
  // Feature Access Check
  $env = [
    'script' => basename(__FILE__),
    'tproject_id' => $args->tproject_id,
    'tplan_id' => $args->tplan_id
  ];
  $args->user->checkGUISecurityClearance($dbH,$env,['testplan_planning'],'and');
  // --------------------------------------------------------------------------------

  return $args;  
}
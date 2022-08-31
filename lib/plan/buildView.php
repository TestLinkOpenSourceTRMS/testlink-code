<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource buildView.php
 *
 *       
 *
 */
require('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db);

$gui = initEnv($db);

$tplCfg = templateConfiguration();
$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($tplCfg->template_dir . $tplCfg->default_template);


/**
 *
 */
function initEnv(&$dbHandler) {

  list($context,$env) = initContext();
  list($args,$gui) = initUserEnv($dbHandler,$context);
  $gui->activeMenu['plans'] = 'active';

  if( $gui->tplan_id == 0 ) {
    throw new Exception("Abort Test Plan ID == 0", 1);
  }  

  $tplan_mgr = new testplan($dbHandler);
  $info = $tplan_mgr->tree_manager->
            get_node_hierarchy_info($gui->tplan_id,null,array('nodeType' => 'testplan'));

  if( !is_null($info) ) {
    $gui->tplan_name = $info['name'];
  } else {
    throw new Exception("Invalid Test Plan ID", 1);
  }  
 
  $gui->buildSet = $tplan_mgr->get_builds($gui->tplan_id);
  $gui->user_feedback = null;

  $cfg = getWebEditorCfg('build');
  $gui->editorType = $cfg['type'];
  
  // -----------------------------------------------------------------------------------
  // Feature Access Check
  $env = [
    'script' => basename(__FILE__),
    'tproject_id' => $args->tproject_id,
    'tplan_id' => $args->tplan_id
  ];
  $args->user->checkGUISecurityClearance($dbHandler,$env,['testplan_create_build'],'and');
  // -------------------------------------------------------------------------------------  

  return $gui;  
}
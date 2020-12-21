<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  planView.php
 *
 */
require_once('../../config.inc.php');
require_once("common.php");
require_once("planViewUtils.php");

testlinkInitPage($db);
$args = init_args($db);


list($gui,$tproject_mgr,$tplan_mgr) = initializeGui($db,$args);
if ($args->tproject_id) {  
  if (!is_null($gui->tplans) && count($gui->tplans) > 0) {
    $gui->getTestPlans = false;
    planViewGUIInit($db,$args,$gui,$tplan_mgr);
  }
}


$tplCfg = templateConfiguration();
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($tplCfg->template_dir . $tplCfg->default_template);


/**
 * init_args
 *
 */
function init_args(&$dbH) {
  $iParams = array("tproject_id" => array(tlInputParameter::INT_N),
                   "tplan_id" => array(tlInputParameter::INT_N),
                   "current_tproject_id" => array(tlInputParameter::INT_N)
                  );

  $args = new stdClass();
  $pParams = G_PARAMS($iParams,$args);

  if( $args->tproject_id == 0 ) {
    throw new Exception("Test Project ID = 0 - Abort Processing", 1);    
  }

  $tprojMgr = new testproject($dbH);
  $info = $tprojMgr->get_by_id($args->tproject_id);
  $args->tproject_name = $info['name'];
  $args->user = $_SESSION['currentUser'];


  // ----------------------------------------------------------------
  // Feature Access Check
  // This feature is affected only for right at Test Project Level
  $env = ['script' => basename(__FILE__),
          'tproject_id' => $args->tproject_id];
  $args->user->checkGUISecurityClearance($dbH,$env,
                    array('mgt_testplan_create'),'and');
  // ----------------------------------------------------------------

  return $args;
}

/**
 *
 */
function initializeGui(&$dbHandler,$argsObj) {
  $gui = new stdClass();
  $opt = array('skip' => array('tplanForInit' => true),
               'caller' => basename(__FILE__));
  $context = null;
  list($add2args,$gui,$tproject_mgr) = initUserEnv($dbHandler, $context, $opt); 

  $tplan_mgr = new testplan($dbHandler);

  $gui->doViewReload = false;
  if (property_exists($argsObj, 'doViewReload')) {
    $gui->doViewReload = $argsObj->doViewReload;
  }
  $gui->tproject_id = $argsObj->tproject_id;
  $gui->tplans = null;
  $gui->user_feedback = '';
  $gui->activeMenu['plans'] = 'active';


  $gui->main_descr = lang_get('testplan_title_tp_management') . 
                     " - " . 
                     lang_get('testproject') . ' ' . 
                     $argsObj->tproject_name;
  $cfg = getWebEditorCfg('testplan');
  $gui->editorType = $cfg['type'];
    

  $gui->createEnabled = $gui->grants->testplan_create == 'yes' &&
                        $gui->tproject_id > 0;

  $gui->drawPlatformQtyColumn = false;
  if ($gui->tproject_id) {
    $gui->tplans = $argsObj->user->getAccessibleTestPlans(
                     $dbHandler,
                     $argsObj->tproject_id,
                     null,
                     array('output' =>'mapfull', 'active' => null)); 
  }                     

  $ctx = new stdClass();
  $ctx->tproject_id = $gui->tproject_id;
  $ctx->tplan_id = $gui->tplan_id;
  $gui->actions = $tplan_mgr->getViewActions($ctx);
  return array($gui,$tproject_mgr,$tplan_mgr);
}
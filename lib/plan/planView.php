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
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$args = init_args($db);
list($gui,$tproject_mgr,$tplan_mgr) = initializeGui($db,$args);

if($args->tproject_id) {  
  if( !is_null($gui->tplans) && count($gui->tplans) > 0 ) {
    // do this test project has platform definitions ?
    $tplan_mgr->platform_mgr->setTestProjectID($args->tproject_id);
    $dummy = $tplan_mgr->platform_mgr->testProjectCount();
    $gui->drawPlatformQtyColumn = $dummy[$args->tproject_id]['platform_qty'] > 0;

    $tplanSet = array_keys($gui->tplans);
    $dummy = $tplan_mgr->count_testcases($tplanSet,null,array('output' => 'groupByTestPlan'));
    $buildQty = $tplan_mgr->get_builds($tplanSet,null,null,array('getCount' => true));
    $rightSet = array('testplan_user_role_assignment');

    foreach($tplanSet as $idk) {
      $gui->tplans[$idk]['tcase_qty'] = isset($dummy[$idk]['qty']) ? intval($dummy[$idk]['qty']) : 0;
      $gui->tplans[$idk]['build_qty'] = isset($buildQty[$idk]['build_qty']) ? intval($buildQty[$idk]['build_qty']) : 0;

      if( $gui->drawPlatformQtyColumn ) {
        $plat = $tplan_mgr->getPlatforms($idk);
        $gui->tplans[$idk]['platform_qty'] = is_null($plat) ? 0 : count($plat);
      }


      // Get rights for each test plan
      foreach($rightSet as $target) {
        // DEV NOTE - CRITIC
        // I've made a theorically good performance choice to 
        // assign to $roleObj a reference to different roleObj
        // UNFORTUNATELLY this choice was responsible to destroy 
        // the pointed object since second LOOP
        $roleObj = null;
        if($gui->tplans[$idk]['has_role'] > 0) {
          $roleObj = $args->user->tplanRoles[$gui->tplans[$idk]['has_role']];
        }  
        else if (!is_null($args->user->tprojectRoles) && 
                 isset($args->user->tprojectRoles[$args->tproject_id]) )
        {
          $roleObj = $args->user->tprojectRoles[$args->tproject_id];
        }  

        if(is_null($roleObj)) {
          $roleObj = $args->user->globalRole;
        }  
        $gui->tplans[$idk]['rights'][$target] = $roleObj->hasRight($target);  
      }  
    }    
  }
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


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

  $gui->tproject_id = $argsObj->tproject_id;
  $gui->tplans = null;
  $gui->user_feedback = '';
  $gui->activeMenu['plans'] = 'active';


  $gui->main_descr = lang_get('testplan_title_tp_management'). " - " . 
                     lang_get('testproject') . ' ' . $argsObj->tproject_name;
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


/**
 * checkRights
 *
 */
function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'mgt_testplan_create');
}
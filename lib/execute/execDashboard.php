<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource  execDashboard.php
 * 
 *
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once("attachments.inc.php");
require_once("specview.php");

$cfg=null;
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$smarty = new TLSmarty();
$tproject_mgr = new testproject($db);
$tree_mgr = new tree($db);
$attachmentRepository = tlAttachmentRepository::create($db);

list($args,$tplan_mgr) = init_args($db,$cfg);
$gui = initializeGui($db,$args,$cfg,$tplan_mgr);

$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function: 

  args:
  
  returns: 
*/
function init_args(&$dbHandler,$cfgObj) {

  $args = new stdClass();
  $_REQUEST = strings_stripSlashes($_REQUEST);
  $tplan_mgr = new testplan($dbHandler);


  // Settings we put on session to create some sort of persistent scope, 
  // because we have had issues when passing this info using GET mode (size limits)
  //
  // we get info about build_id, platform_id, etc ...
  getContextFromGlobalScope($args);
  $args->user = $_SESSION['currentUser'];
  $args->user_id = $args->user->dbID;
  $args->caller = isset($_REQUEST['caller']) ? $_REQUEST['caller'] : 'exec_feature';
  $args->reload_caller = false;
  
  $args->tplan_id = intval(isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testplanID']);
  $args->tproject_id = intval(isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : $_SESSION['testprojectID']);

  if($args->tproject_id <= 0) {
    $tree_mgr = new tree($dbHandler);
    $dm = $tree_mgr->get_node_hierarchy_info($args->tplan_id);
    $args->tproject_id = $dm['parent_id']; 
  }

  if(is_null($args->build_id) || ($args->build_id == 0) ) {
    // Go for the build
    // this info can be present in session, then we will try different ways
    // ATTENTION: 
    // give a look to tlTestCaseFilterControl.class.php method init_setting_build()
    //
    $key = $args->tplan_id . '_stored_setting_build';
    $args->build_id = isset($_SESSION[$key]) ? intval($_SESSION[$key]) : null;
    if( is_null($args->build_id) ) {
      $args->build_id = $tplan_mgr->get_max_build_id($args->tplan_id,1,1);
    }  
  }  

  if(is_null($args->platform_id) || ($args->platform_id <= 0) ) {
    // Go for the platform (if any exists)
    // this info can be present in session, then we will try different ways
    // ATTENTION: 
    // give a look to tlTestCaseFilterControl.class.php method init_setting_platform()
    //
    $itemSet = $tplan_mgr->getPlatforms($args->tplan_id);
    if(!is_null($itemSet)) {
      $key = $args->tplan_id . '_stored_setting_platform';
      $args->platform_id = isset($_SESSION[$key]) ? intval($_SESSION[$key]) : null;
      if( is_null($args->platform_id) || ($args->platform_id <= 0) ) {
        $args->platform_id = $itemSet[0]['id'];
      }  
    }  
  }  
  return array($args,$tplan_mgr);
}



/*
  function: initializeRights 
            create object with rights useful for this feature 
  
  args:
       dbHandler: reference to db object
       userObj: reference to current user object
       tproject_id:
       tplan_id
  
       Warning: this is right interface for this function, but
                has_rights() can works in a mode (that i consider a dirty one)
                using SESSION to achieve global coupling.
                 
  returns: 

*/
function initializeRights(&$dbHandler,&$userObj,$tproject_id,$tplan_id)
{
    $exec_cfg = config_get('exec_cfg');
    $grants = new stdClass();
    
    $grants->execute = $userObj->hasRight($dbHandler,"testplan_execute",$tproject_id,$tplan_id);
    $grants->execute = $grants->execute=="yes" ? 1 : 0;
    
    // IMPORTANT NOTICE - TICKET 5128
    // If is TRUE we will need also to analize, test case by test case
    // these settings:
    //           $tlCfg->exec_cfg->exec_mode->tester
    //          $tlCfg->exec_cfg->simple_tester_roles       
    //
    // Why ?
    // Because if a tester can execute ONLY test cases assigned to him, this also
    // has to mean that:
    // can delete executions ONLY of test cases assigned to him
    // can edit exec notes ONLY of test cases assigned to him
    // can manage uploads on executions, ONLY of test cases assigned to him
    //
    // These checks can not be done here
    //
    // TICKET 5310: Execution Config - convert options into rights
    $grants->delete_execution = $userObj->hasRight($dbHandler,"exec_delete",$tproject_id,$tplan_id);
  
    
    // Important:
    // Execution right must be present to consider this configuration option.
    // $grants->edit_exec_notes = $grants->execute && $exec_cfg->edit_notes;
    $grants->edit_exec_notes = $grants->execute && 
                               $userObj->hasRight($dbHandler,"exec_edit_notes",$tproject_id,$tplan_id);
    

    $grants->edit_testcase = $userObj->hasRight($dbHandler,"mgt_modify_tc",$tproject_id,$tplan_id);
    $grants->edit_testcase = $grants->edit_testcase=="yes" ? 1 : 0;
    return $grants;
}


/*
  function: initializeGui

  args :
  
  returns: 

*/
function initializeGui(&$dbHandler,&$argsObj,&$cfgObj,&$tplanMgr) {

  $buildMgr = new build_mgr($dbHandler);
  $platformMgr = new tlPlatform($dbHandler,$argsObj->tproject_id);
    
  $gui = new stdClass();
  $gui->form_token = $argsObj->form_token;
  $gui->remoteExecFeedback = $gui->user_feedback = '';
  $gui->tplan_id=$argsObj->tplan_id;
  $gui->tproject_id=$argsObj->tproject_id;
  $gui->build_id = $argsObj->build_id;
  $gui->platform_id = $argsObj->platform_id;
    
  $gui->attachmentInfos=null;
  $gui->refreshTree = 0;

  $cfgTestPlan = getWebEditorCfg('testplan');
  $gui->testPlanEditorType = $cfgTestPlan['type'];
  $cfgPlatform = getWebEditorCfg('platform');
  $gui->platformEditorType = $cfgPlatform['type'];
  $cfgBuild = getWebEditorCfg('build');
  $gui->buildEditorType = $cfgBuild['type'];
      
  // Just for the records:  
  // doing this here, we avoid to do on processTestSuite() and processTestCase(),
  // but absolutely this will not improve in ANY WAY perfomance, because we do not loop
  // over these two functions.   
  $tprojectMgr = new testproject($dbHandler);
  $gui->tcasePrefix = $tprojectMgr->getTestCasePrefix($argsObj->tproject_id);
  $build_info = $buildMgr->get_by_id($argsObj->build_id);
  $gui->build_notes=$build_info['notes'];
  $gui->build_is_open=($build_info['is_open'] == 1 ? 1 : 0);

  $dummy = $tplanMgr->get_builds_for_html_options($argsObj->tplan_id);
  $gui->build_name = isset($dummy[$argsObj->build_id]) ? $dummy[$argsObj->build_id] : '';
  $rs = $tplanMgr->get_by_id($argsObj->tplan_id);
  $gui->testplan_notes = $rs['notes'];
  $gui->testplan_name = $rs['name'];

  // Important note: 
  // custom fields for test plan can be edited ONLY on design, that's reason why we are using 
  // scope = 'design' instead of 'execution'
  $gui->testplan_cfields = $tplanMgr->html_table_of_custom_field_values($argsObj->tplan_id,'design',
                                                                        array('show_on_execution' => 1));
    

  $gui->build_cfields = $buildMgr->html_table_of_custom_field_values($argsObj->build_id,$argsObj->tproject_id,
                                                                     'design',array('show_on_execution' => 1));
    
  $dummy = $platformMgr->getLinkedToTestplan($argsObj->tplan_id);
  $gui->has_platforms = !is_null($dummy) ? 1 : 0;
    
  $gui->platform_info['id']=0;
  $gui->platform_info['name']='';
  if(!is_null($argsObj->platform_id) && $argsObj->platform_id > 0 ) { 
    $gui->platform_info = $platformMgr->getByID($argsObj->platform_id);
  }

  $gui->pageTitlePrefix = lang_get('execution_context') . ':';


  // JSON for REST API
  $gui->restArgs = new stdClass();
  $gui->restArgs->testPlanID = intval($argsObj->tplan_id);
  $gui->restArgs->buildID = intval($argsObj->build_id);
  $gui->restArgs->platformID = intval($argsObj->platform_id);
  
  $gui->RESTArgsJSON = json_encode($gui->restArgs);

  return $gui;
}


/**
 *  get info from ... 
 *
 */
function getContextFromGlobalScope(&$argsObj)
{
  $mode = 'execution_mode';
  $settings = array('build_id' => 'setting_build', 'platform_id' => 'setting_platform');
  $isNumeric = array('build_id' => 0, 'platform_id' => 0);

  $argsObj->form_token = isset($_REQUEST['form_token']) ? $_REQUEST['form_token'] : 0;
  $sf = isset($_SESSION['execution_mode']) && isset($_SESSION['execution_mode'][$argsObj->form_token]) ? 
        $_SESSION['execution_mode'][$argsObj->form_token] : null;

  if(is_null($sf))
  {
    foreach($settings as $key => $sfKey)
    {
      $argsObj->$key = null;
    }  
    return;
  } 

  foreach($settings as $key => $sfKey)
  {
    $argsObj->$key = isset($sf[$sfKey]) ? $sf[$sfKey] : null;
    if (is_null($argsObj->$key)) 
    {
      // let's this page be functional withouth a form token too 
      // (when called from testcases assigned to me)
      $argsObj->$key = isset($_REQUEST[$sfKey]) ? $_REQUEST[$sfKey] : null;
    }
    if(isset($isNumeric[$key]))
    {
      $argsObj->$key = intval($argsObj->$key);              
    }  
  }

}

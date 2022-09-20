<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Test Case and Test Steps operations
 *
 * @filesource  tcEdit.php
 * @package     TestLink
 * @author      TestLink community
 * @copyright   2007-2022, TestLink community 
 * @link        http://www.testlink.org/
 *
 *
 **/
require_once("../../config.inc.php");
require_once("common.php");
require_once("opt_transfer.php");
require_once("web_editor.php");

$cfg = getCfg();
$optTransferName = 'ot';

testlinkInitPage($db);
$tcase_mgr = new testcase($db);
$tproject_mgr = new testproject($db);
$tree_mgr = new tree($db);
$tsuite_mgr = new testsuite($db);


$args = init_args($cfg,$optTransferName,$tcase_mgr,$tproject_mgr);
require_once(require_web_editor($cfg->webEditorCfg['type']));

$tplCfg = templateConfiguration('tcEdit');

$commandMgr = new testcaseCommands($db,$args->user,$args->tproject_id);
$commandMgr->setTemplateCfg(templateConfiguration());

$testCaseEditorKeys = array('summary' => 'summary',
                            'preconditions' => 'preconditions');
$init_inputs = true;
$opt_cfg = initializeOptionTransferCfg($optTransferName,$args,$tproject_mgr);
$gui = initializeGui($db,$args,$cfg,$tcase_mgr,$tproject_mgr);

$smarty = new TLSmarty();

$name_ok = 1;
$doRender = false;
$pfn = $args->doAction;


$testCaseEditorKeys = null;
switch($args->doAction) {
  case "create":  
  case "edit":  
  case "doCreate":  
    $testCaseEditorKeys = [
      'summary' => 'summary',
      'preconditions' => 'preconditions'
    ];
  break;
    

  case "createStep":
  case "editStep":
  case "doCreateStep":
  case "doCreateStepAndExit":
  case "doCopyStep":
  case "doUpdateStep":
  case "doUpdateStepAndExit":
  case "doUpdateStepAndInsert":
  case "doDeleteStep":
  case "doReorderSteps":
  case "doInsertStep":
  case "doResequenceSteps":
  case "doStepOperationExit":
    $testCaseEditorKeys = [
      'steps' => 'steps', 
      'expected_results' => 'expected_results'
    ];
  break;

}

switch($args->doAction) {
  case "doUpdate":
  case "doAdd2testplan":
  case 'updateTPlanLinkToTCV':
    $op = $commandMgr->$pfn($args,$_REQUEST);
  break;

  case "create":  
  case "edit":  
  case "doCreate":  
    $op = $commandMgr->$pfn($args,$opt_cfg,array_keys($testCaseEditorKeys),$_REQUEST);
    $doRender = true;
  break;
    

  case "delete":  
  case "doDelete":  
  case "createStep":
  case "editStep":
  case "doCreateStep":
  case "doCreateStepAndExit":
  case "doCopyStep":
  case "doUpdateStep":
  case "doUpdateStepAndExit":
  case "doUpdateStepAndInsert":
  case "doDeleteStep":
  case "doReorderSteps":
  case "doInsertStep":
  case "doResequenceSteps":
  case "setImportance":
  case "setStatus":
  case "setExecutionType":
  case "setEstimatedExecDuration":
  case "removeKeyword":
  case "addKeyword":
  case "freeze":
  case "unfreeze":
  case "doStepOperationExit":
  case "removePlatform":
  case "addPlatform":
  case "addAlien":
  case "removeAlien":
    $op = $commandMgr->$pfn($args,$_REQUEST);
    $doRender = true;
  break;

  case "fileUpload":
    $args->uploadOp = fileUploadManagement($db,$args->tcversion_id,$args->fileTitle,$tcase_mgr->getAttachmentTableName());
    $commandMgr->show($args,$_REQUEST,array('status_ok' => true),false);
  break;

  case "deleteFile":
    $fileInfo = deleteAttachment($db,$args->file_id,false);
    if( $args->tcversion_id == 0 && null != $fileInfo ) {
      $args->tcversion_id = $fileInfo['fk_id'];
    }
    $commandMgr->show($args,$_REQUEST,array('status_ok' => true),false);
  break;


  case "doAddRelation":
  case "doDeleteRelation":
    $op = $commandMgr->$pfn($args,$_REQUEST);
    $doRender = true;
  break;
  
}


if ($doRender) {
  renderGui($args,$gui,$op,$tplCfg,$cfg,$testCaseEditorKeys);
  exit();
}

// Things that one day will be managed by command file
if($args->delete_tc_version) {
  $status_quo_map = $tcase_mgr->get_versions_status_quo($args->tcase_id);
  $exec_status_quo = $tcase_mgr->get_exec_status($args->tcase_id);
  $gui->delete_mode = 'single';
  $gui->delete_enabled = 1;

  $msg = '';
  $sq = null;
  if(!is_null($exec_status_quo)) {
    if(isset($exec_status_quo[$args->tcversion_id])) {
      $sq = array($args->tcversion_id => $exec_status_quo[$args->tcversion_id]);
    }
  }

  if(intval($status_quo_map[$args->tcversion_id]['executed'])) {
    $msg = lang_get('warning') . TITLE_SEP . lang_get('delete_linked_and_exec');
  }
  else if(intval($status_quo_map[$args->tcversion_id]['linked']))
  {
    $msg = lang_get('warning') . TITLE_SEP . lang_get('delete_linked');
  }

  $tcinfo = $tcase_mgr->get_by_id($args->tcase_id,$args->tcversion_id);

  $gui->main_descr = lang_get('title_del_tc') . TITLE_SEP_TYPE3 . lang_get('version') . " " . $tcinfo[0]['version'];
  $gui->testcase_name = $tcinfo[0]['name'];
  $gui->testcase_id = $args->tcase_id;
  $gui->tcversion_id = $args->tcversion_id;
  $gui->delete_message = $msg;
  $gui->exec_status_quo = $sq;
  $gui->refreshTree = 0;

  $smarty->assign('gui',$gui);
  $tplCfg = templateConfiguration('tcDelete');
  $smarty->display($tplCfg->template_dir . $tplCfg->default_template);
} else if($args->move_copy_tc) {
  // need to get the testproject for the test case
  $tproject_id = $tcase_mgr->get_testproject($args->tcase_id);
  $the_tc_node = $tree_mgr->get_node_hierarchy_info($args->tcase_id);
  $tc_parent_id = $the_tc_node['parent_id'];
  $the_xx = $tproject_mgr->gen_combo_test_suites($tproject_id);
  
  $the_xx[$the_tc_node['parent_id']] .= ' (' . lang_get('current') . ')';
  $tc_info = $tcase_mgr->get_by_id($args->tcase_id);
  
  $container_qty = count($the_xx);
  $gui->move_enabled = 1;
  if ($container_qty == 1) {
    // move operation is nonsense
    $gui->move_enabled = 0;
  }

  $gui->top_checked = 'checked=checked';
  $gui->bottom_checked = '';
  
  $gui->array_container = $the_xx;
  $gui->old_container = $the_tc_node['parent_id']; // original container
  $gui->testsuite_id = $the_tc_node['parent_id'];
  $gui->testcase_id = $args->tcase_id;
  $gui->name = $tc_info[0]['name'];
  $gui->testcase_name = $tcase_mgr->generateTimeStampName($gui->name);

  
  $smarty->assign('gui', $gui);
  $tplCfg = templateConfiguration('tcMove');
  $smarty->display($tplCfg->template_dir . $tplCfg->default_template);
} else if($args->do_move) {
  $result = $tree_mgr->change_parent($args->tcase_id,$args->new_container);
  $tree_mgr->change_child_order($args->new_container,
                                $args->tcase_id,
                                $args->target_position,
                                $cfg->exclude_node_types);

  $gui->refreshTree = $args->refreshTree;
  $tsuite_mgr->show($smarty,$gui,$tplCfg->template_dir,$args->old_container);
} else if($args->do_copy || $args->do_copy_ghost_zone) {
  $args->stepAsGhost = $args->do_copy_ghost_zone;
  $user_feedback='';
  $msg = '';
  $action_result = 'copied';
  $options = array('check_duplicate_name' => 
                      config_get('check_names_for_duplicates'),
                   'action_on_duplicate_name' => 
                      config_get('action_on_duplicate_name'),
                   'copy_also' => $args->copy, 
                   'stepAsGhost' => $args->do_copy_ghost_zone,
                   'use_this_name' => $args->name,
                   'copyOnlyLatest' => $args->copyOnlyLatestVersion);
  
  $result = $tcase_mgr->copy_to($args->tcase_id,$args->new_container,$args->user_id,$options);
  $msg = $result['msg'];
  if($result['status_ok']) {
    $tree_mgr->change_child_order($args->new_container
                                  ,$result['id']
                                  ,$args->target_position
                                  ,$cfg->exclude_node_types);
    
    $ts_sep = config_get('testsuite_sep');
    $tc_info = $tcase_mgr->get_by_id($args->tcase_id);
    $container_info = $tree_mgr->get_node_hierarchy_info($args->new_container);
    $container_path = $tree_mgr->get_path($args->new_container);
    $path = '';

    foreach($container_path as $key => $value)
    {
     $path .= $value['name'] . $ts_sep;
    }
    $path = trim($path,$ts_sep);
    $user_feedback = sprintf(lang_get('tc_copied'),$tc_info[0]['name'],$path);
  }

  $gui->refreshTree = $args->refreshTree;
  $gui->viewerArgs['action'] = $action_result;
  $gui->viewerArgs['refreshTree']=$args->refreshTree? 1 : 0;
  $gui->viewerArgs['msg_result'] = $msg;
  $gui->viewerArgs['user_feedback'] = $user_feedback;
  $gui->path_info = null;

  $identity = new stdClass();
  $identity->id = $args->tcase_id;
  $identity->tproject_id = $args->tproject_id;
  $identity->version_id = $args->tcversion_id;

  $tcase_mgr->show($smarty,$gui,$identity,$gui->grants);

}
else if($args->do_create_new_version) {
  createNewVersion($smarty,$args,$gui,$tcase_mgr,$args->tcversion_id);
}
else if($args->do_create_new_version_from_latest) { 
  $ltcv = $tcase_mgr->getLatestVersionID($args->tcase_id);
  createNewVersion($smarty,$args,$gui,$tcase_mgr,$ltcv);

}
else if($args->do_activate_this || $args->do_deactivate_this) {
  $commandMgr->setActiveAttr($args,$_REQUEST);
  exit();
}

// -----------------------------------------------------------------------

/*
  function:

  args:

  returns:

*/
function init_args(&$cfgObj,$otName,&$tcaseMgr,&$tprojMgr) 
{
  $_REQUEST = strings_stripSlashes($_REQUEST);

  list($args,$env) = initContext();
  $args->user_id = $args->userID;
  $tcaseMgr->setTestProject($args->tproject_id);
 
  // Compatibility
  $args->testproject_id = $args->tproject_id;

  $r_name = $otName . "_newRight";
  $args->assigned_keywords_list = isset($_REQUEST[$r_name]) ? $_REQUEST[$r_name] : "";

  $k2z = [
    'containerID',
    'file_id',
    'new_container',
    'old_container',
    'has_been_executed',
    'step_number',
    'step_id',
    'platform_id'
  ];

  foreach ($k2z as $zz) {
    $args->$zz = isset($_REQUEST[$zz]) ? intval($_REQUEST[$zz]) : 0;
  }

  $args->basehref = $_SESSION['basehref'];

  // Compatibility
  $args->container_id = $args->containerID;

  $e2n = [
    'step_set',
    'tcaseSteps'
  ];
  foreach ($e2n as $kiki) {
    $args->$kiki = isset($_REQUEST[$kiki]) ? $_REQUEST[$kiki] : null;
  }  

  // Normally Rich Web Editors
  $ek = [
    'summary',
    'preconditions',
    'steps',
    'expected_results'
  ];
  
  foreach ($ek as $kiki) {
    $args->$kiki = isset($_REQUEST[$kiki]) ? $_REQUEST[$kiki] : null;
  }  

  
  $args->tcase_id = isset($_REQUEST['testcase_id']) ? intval($_REQUEST['testcase_id']) : 0;
  if($args->tcase_id == 0) {
    $args->tcase_id = isset($_REQUEST['tcase_id']) ? intval($_REQUEST['tcase_id']) : 0;
  }  
  if($args->tcase_id == 0) {
    $args->tcase_id = intval(isset($_REQUEST['relation_source_tcase_id']) ? $_REQUEST['relation_source_tcase_id'] : 0);
  }
  $args->id = $args->tcase_id;
  
  $args->tcversion_id = isset($_REQUEST['tcversion_id']) ? intval($_REQUEST['tcversion_id']) : 0;
  if( $args->tcversion_id == 0 && $args->tcase_id > 0 ) {
    // get latest active version
    $nu = key($tcaseMgr->get_last_active_version($args->tcase_id));
  }

  $args->name = isset($_REQUEST['testcase_name']) ? $_REQUEST['testcase_name'] : null;
  $args->exec_type = isset($_REQUEST['exec_type']) ? $_REQUEST['exec_type'] : $cfgObj->exec_type['manual'];
  $args->importance = isset($_REQUEST['importance']) ? $_REQUEST['importance'] : $cfgObj->importance_default;
  $args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : '';  
  $args->status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 1; // sorry for the magic

  $dk = 'estimated_execution_duration';
  $args->$dk = trim(isset($_REQUEST[$dk]) ? $_REQUEST[$dk] : '');
  $args->estimatedExecDuration = $args->$dk;


  $key2loop = [
    'edit_tc' => 'edit', 
    'delete_tc' => 'delete',
    'do_delete' => 'doDelete',
    'create_tc' => 'create',
    'do_create' => 'doCreate'
  ];
  foreach($key2loop as $key => $action) {
    if( isset($_REQUEST[$key]) ) {
      $args->doAction = $action;
      break;
    }
  }

  $key2loop = [
    'move_copy_tc',
    'delete_tc_version',
    'do_move',
    'do_copy',
    'do_copy_ghost_zone',
    'do_delete_tc_version',
    'do_create_new_version',
    'do_create_new_version_from_latest'
  ];
  foreach($key2loop as $key) {
    $args->$key = isset($_REQUEST[$key]) ? 1 : 0;
  }

  $args->do_activate_this = isset($_REQUEST['activate_this_tcversion']) ? 1 : 0;
  $args->do_deactivate_this = isset($_REQUEST['deactivate_this_tcversion']) ? 1 : 0;
  $args->activeAttr = 0;
  if( $args->do_activate_this ) {
    $args->activeAttr = 1;
  }

  $args->target_position = isset($_REQUEST['target_position']) ? $_REQUEST['target_position'] : 'bottom';
    
  $key2loop = array("keyword_assignments","requirement_assignments");
  foreach($key2loop as $key) {
    $args->copy[$key] = isset($_REQUEST[$key]) ? true : false;    
  }    
  
  
  $args->show_mode = (isset($_REQUEST['show_mode']) && $_REQUEST['show_mode'] != '') ? $_REQUEST['show_mode'] : null;
  $args->refreshTree = isset($_SESSION['setting_refresh_tree_on_action']) ? intval($_SESSION['setting_refresh_tree_on_action']) : 0;
    
  $args->opt_requirements = null;
  $args->tprojOpt = $tprojMgr->getOptions($args->tproject_id);

  /*
  object(stdClass)#263 (4) {
  ["requirementsEnabled"]=>
  int(1)
  ["testPriorityEnabled"]=>
  int(1)
  ["automationEnabled"]=>
  int(1)
  ["inventoryEnabled"]=>
  int(0)
  }
  */
  $args->opt_requirements = $args->tprojOpt->requirementsEnabled;
  $args->requirementsEnabled = $args->tprojOpt->requirementsEnabled;

  $args->goback_url = isset($_REQUEST['goback_url']) ? $_REQUEST['goback_url'] : null;


  // Specialized webEditorConfiguration
  $action2check = array("editStep" => true,
                        "createStep" => true, 
                        "doCreateStep" => true,
                        "doUpdateStep" => true, 
                        "doInsertStep" => true, 
                        "doCopyStep" => true,
                        "doUpdateStepAndInsert" => true);
  if( isset($action2check[$args->doAction]) ) {
    $cfgObj->webEditorCfg = getWebEditorCfg('steps_design');  
  }   

  $args->stay_here = isset($_REQUEST['stay_here']) ? 1 : 0;


  $dummy = getConfigAndLabels('testCaseStatus','code');
  $args->tcStatusCfg['status_code'] = $dummy['cfg'];
  $args->tcStatusCfg['code_label'] = $dummy['lbl'];
  $args->tc_status = isset($_REQUEST['tc_status']) 
                     ? intval($_REQUEST['tc_status']) : 
                     $args->tcStatusCfg['status_code']['draft'];
  
  $args->fileTitle = isset($_REQUEST['fileTitle']) ? 
                     $_REQUEST['fileTitle'] : "";

  $args->relation_type = isset($_REQUEST['relation_type']) 
                         ? $_REQUEST['relation_type'] : null;
  $args->relation_id = intval(isset($_REQUEST['relation_id']) 
                       ? $_REQUEST['relation_id'] : 0);

  $args->relation_destination_tcase = 
         isset($_REQUEST['relation_destination_tcase']) ? 
         $_REQUEST['relation_destination_tcase'] : null;

  $args->relation_destination_tcase = 
    str_replace(' ','',$args->relation_destination_tcase);
  $getOpt = array('tproject_id' => null, 'output' => 'map');                         
  if( is_numeric($args->relation_destination_tcase) ) {
    $getOpt['tproject_id'] = $args->tproject_id;
  }  
  $args->dummy = $tcaseMgr->getInternalID($args->relation_destination_tcase,$getOpt);

  $args->destination_tcase_id = $args->dummy['id'];


  $args->keyword_id = isset($_GET['keyword_id']) ? intval($_GET['keyword_id']) : 0;

  $l2c = array('tckw_','tcplat_','tcalien_');
  foreach ($l2c as $lk) {
    $tko = $lk .'link_id';
    $args->$tko = isset($_GET[$tko]) ? intval($_GET[$tko]) : 0;
  }

  $args->tplan_id = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;
  $args->platform_id = isset($_REQUEST['platform_id']) ? intval($_REQUEST['platform_id']) : 0;
  

  $cbk = 'changeExecTypeOnSteps';
  $args->applyExecTypeChangeToAllSteps = isset($_REQUEST[$cbk]);

  $k2c = array('free_keywords','free_platforms','free_aliens');
  foreach ($k2c as $kv) {
    $args->$kv = isset($_REQUEST[$kv]) ? $_REQUEST[$kv] : null;
  }

  $args->copyOnlyLatestVersion = 
    isset($_REQUEST['copy_latest_version']) ? 1 : 0;

  $ki = 'alien_relation_type';
  $args->$ki = isset($_REQUEST[$ki]) ? intval($_REQUEST[$ki]) : TL_ALIEN_REL_TYPE_FIX;

  $tcaseMgr->setTestProject($args->tproject_id);

  return $args;
}


/*
  function: initializeOptionTransferCfg
  args :
  returns: 
*/
function initializeOptionTransferCfg($otName,&$argsObj,&$tprojMgr)
{
  $otCfg = new stdClass();
  $otCfg->js_ot_name = $otName;

  switch($argsObj->doAction) {
    case 'create':
    case 'edit':
    case 'doCreate':
      $otCfg = opt_transf_empty_cfg();
      $otCfg->global_lbl = '';
      $otCfg->from->lbl = lang_get('available_kword');
      $otCfg->to->lbl = lang_get('assigned_kword');
      $otCfg->from->map = 
        $tprojMgr->get_keywords_map($argsObj->tproject_id);
    break;
  }
  
  return $otCfg;
}

/*
  function: createWebEditors

      When using tinymce or none as web editor, we need to set rows and cols
      to appropriate values, to avoid an ugly ui.
      null => use default values defined on editor class file
      Rows and Cols values are useless for FCKeditor

  args :
  
  returns: object
  
*/
function createWebEditors($basehref,$editorCfg,$editorSet=null)
{
    $specGUICfg=config_get('spec_cfg');
    $layout=$specGUICfg->steps_results_layout;

    // Rows and Cols configuration
    $owe = new stdClass();

    $cols = array('steps' => array('horizontal' => 38, 'vertical' => 44),
                  'expected_results' => array('horizontal' => 38, 'vertical' => 44));

    $owe->cfg = array('summary' => array('rows'=> null,'cols' => null),
                      'preconditions' => array('rows'=> null,'cols' => null) ,
                      'steps' => array('rows'=> null,'cols' => $cols['steps'][$layout]) ,
                      'expected_results' => array('rows'=> null, 'cols' => $cols['expected_results'][$layout]));
    
    $owe->editor = array();
    $force_create = is_null($editorSet);
    foreach ($owe->cfg as $key => $value)
    {
     if( $force_create || isset($editorSet[$key]) )
     {
       $owe->editor[$key] = web_editor($key,$basehref,$editorCfg);
     }
     else
     {
       unset($owe->cfg[$key]);
     }
    }
    
    return $owe;
}

/*
  function: getCfg
  args :
  returns: object
*/
function getCfg()
{
  $cfg = new stdClass();

  $cfg->tcase_template = config_get('testcase_template');
  $cfg->spec = config_get('spec_cfg');
  $cfg->exec_type = config_get('execution_type');

  $cfg->importance_default = 
          config_get('testcase_importance_default');

  $cfg->treemenu_default_testcase_order = 
          config_get('treemenu_default_testcase_order');


  $cfg->exclude_node_types = array('testplan' => 1, 
                                   'requirement' => 1, 
                                   'requirement_spec' => 1);
  

  $cfg->webEditorCfg = getWebEditorCfg('design');
  $cfg->editorKeys = new stdClass();
  $cfg->editorKeys->testcase = array('summary' => true, 
                                     'preconditions' => true);    
  $cfg->editorKeys->step = array('steps' => true, 
                                 'expected_results' => true);    

  return $cfg;
}

/*
  function: getGrants
  args :
  returns: object
*/
function getGrants(&$dbHandler) {
  $grants = new stdClass();
  $grants->requirement_mgmt = has_rights($dbHandler,"mgt_modify_req"); 
  return $grants;
}


/**
 * 
 *
 */
function initializeGui(&$dbHandler,&$argsObj,$cfgObj,&$tcaseMgr,&$tprojMgr) {
  
  
  list($add2args,$guiObj) = initUserEnv($dbHandler,$argsObj);

  $guiObj->uploadOp = null;
  $guiObj->tplan_id = $argsObj->tplan_id;
  $guiObj->tproject_id = $argsObj->tproject_id;
  $guiObj->editorType = $cfgObj->webEditorCfg['type'];
  $guiObj->grants = getGrants($dbHandler);
  $guiObj->opt_requirements = $argsObj->opt_requirements; 
  $guiObj->action_on_duplicated_name = 'generate_new';
  $guiObj->show_mode = $argsObj->show_mode;
  $guiObj->has_been_executed = $argsObj->has_been_executed;
  $guiObj->attachments = null;
  $guiObj->parent_info = null;
  $guiObj->user_feedback = '';
  $guiObj->stay_here = $argsObj->stay_here;
  $guiObj->steps_results_layout = $cfgObj->spec->steps_results_layout;
  $guiObj->btn_reorder_testcases = lang_get('btn_reorder_testcases_externalid');
  $guiObj->import_limit = TL_REPOSITORY_MAXFILESIZE;
  $guiObj->msg = '';
  $guiObj->domainTCStatus = $argsObj->tcStatusCfg['code_label'];
  $guiObj->fileUploadURL = $_SESSION['basehref'] . $tcaseMgr->getFileUploadRelativeURL($argsObj);
  $guiObj->codeTrackerEnabled = $tprojMgr->isCodeTrackerEnabled($guiObj->tproject_id);


  // TODO remove???
  $guiObj->loadOnCancelURL = $_SESSION['basehref'] . "/lib/testcases/archiveData.php?edit=testcase&id=" . 
                             $argsObj->tcase_id . "&tproject_id=" . $argsObj->tproject_id .
                             "&show_mode={$argsObj->show_mode}";
  


  if($argsObj->containerID > 0) {
    $pnode_info = $tcaseMgr->tree_manager->get_node_hierarchy_info($argsObj->containerID);
    $node_descr = array_flip($tcaseMgr->tree_manager->get_available_node_types());
    $guiObj->parent_info['name'] = $pnode_info['name'];
    $guiObj->parent_info['description'] = lang_get($node_descr[$pnode_info['node_type_id']]);
  }
  
  $guiObj->direct_link = '';
  if (property_exists($argsObj,'id') && $argsObj->id > 0) {
    $guiObj->direct_link = $tcaseMgr->buildDirectWebLink($argsObj);
  }
  
  
  $grant2check = testcase::getStandardGrantsNames();
  $guiObj->grants = new stdClass();
  foreach($grant2check as $right) {
    $guiObj->$right = $guiObj->grants->$right = $argsObj->user->hasRight($dbHandler,$right,$argsObj->tproject_id);
  }

  return $guiObj;
}

/**
 * manage GUI rendering
 *
 */
function renderGui(&$argsObj,$guiObj,$opObj,$tplCfg,$cfgObj,$editorKeys)
{
  $smartyObj = new TLSmarty();
    
  // needed by webeditor loading logic present on inc_head.tpl
  $smartyObj->assign('editorType',$guiObj->editorType);  

  $renderType = 'none';

  //
  // key: operation requested (normally received from GUI on doAction)
  // value: operation value to set on doAction HTML INPUT
  // This is useful when you use same template (example xxEdit.tpl), 
  // for create and edit.
  // When template is used for create -> operation: doCreate.
  // When template is used for edit -> operation: doUpdate.
  //              
  // used to set value of: $guiObj->operation
  //
  $actionOperation = [
    'create' => 'doCreate', 
    'doCreate' => 'doCreate',
    'edit' => 'doUpdate',
    'delete' => 'doDelete', 
    'createStep' => 'doCreateStep', 
    'doCreateStep' => 'doCreateStep',
    'doCopyStep' => 'doUpdateStep',
    'editStep' => 'doUpdateStep', 
    'doUpdateStep' => 'doUpdateStep', 
    'doInsertStep' => 'doUpdateStep',
    'doUpdateStepAndInsert' => 'doUpdateStep'
  ];

  $nak = [
    'doDelete',
    'doDeleteStep',
    'doReorderSteps',
    'doResequenceSteps',
    'setImportance',
    'setStatus','setExecutionType', 
    'setEstimatedExecDuration',
    'doAddRelation',
    'doDeleteRelation',
    'freeze',
    'unfreeze',
    'removeKeyword',
    'addKeyword',
    'removePlatform',
    'addPlatform',
    'removeAlien',
    'addAlien'
  ];

  foreach($nak as $ak) {
    $actionOperation[$ak] = '';
  }

  $key2work = 'initWebEditorFromTemplate';
  $initWebEditorFromTemplate = property_exists($opObj,$key2work) ? $opObj->$key2work : false;                             
  $key2work = 'cleanUpWebEditor';
  $cleanUpWebEditor = property_exists($opObj,$key2work) ? $opObj->$key2work : false;                             

  $oWebEditor = createWebEditors($argsObj->basehref,$cfgObj->webEditorCfg,$editorKeys); 

  foreach ($oWebEditor->cfg as $key => $value) {
    $of = &$oWebEditor->editor[$key];
    $rows = $oWebEditor->cfg[$key]['rows'];
    $cols = $oWebEditor->cfg[$key]['cols'];
    
    switch($argsObj->doAction) {
      case "edit":
      case "delete":
      case "editStep":
        $initWebEditorFromTemplate = false;
        $of->Value = $argsObj->$key;
      break;

      case "doCreate":
        $initWebEditorFromTemplate = $opObj->actionOK;
        $of->Value = $argsObj->$key;
      break;
      
      case "doDelete":
      case "doCopyStep":
      case "doUpdateStep":
        $initWebEditorFromTemplate = false;
        $of->Value = $argsObj->$key;
      break;
       
      case "create":
      case "doCreateStep":
      case "doInsertStep":
      case "doUpdateStepAndInsert":
      default:  
        $initWebEditorFromTemplate = true;
      break;
    }
    $guiObj->operation = $actionOperation[$argsObj->doAction];
  
    if($initWebEditorFromTemplate) {
      $of->Value = getItemTemplateContents('testcase_template', $of->InstanceName, '');  
    } else if( $cleanUpWebEditor ) {
      $of->Value = '';
    }
    $smartyObj->assign($key, $of->CreateHTML($rows,$cols));
  }
      
  switch($argsObj->doAction) {
    case "doDelete":
      $guiObj->refreshTree = $argsObj->refreshTree;
    break;
  }

  switch($argsObj->doAction) {
    case "edit":
    case "create":
    case "delete":
    case "createStep":
    case "editStep":
    case "doCreate":
    case "doDelete":
    case "doCreateStep":
    case "doUpdateStep":
    case "doDeleteStep":
    case "doReorderSteps":
    case "doCopyStep":
    case "doInsertStep":
    case "doResequenceSteps":
    case "setImportance":
    case "setStatus":
    case "setExecutionType":
    case "setEstimatedExecDuration":
    case "doAddRelation":
    case "doDeleteRelation":
    case "doUpdateStepAndInsert":
    case "removeKeyword":  
    case "addKeyword":  
    case "freeze":        
    case "unfreeze":        
    case "removePlatform":  
    case "addPlatform":  
    case "removeAlien":  
    case "addAlien":  
      $renderType = 'template';
      
      // Document this !!!!
      $key2loop = get_object_vars($opObj);
      foreach($key2loop as $key => $value) {
       $guiObj->$key = $value;
      }
      $guiObj->operation = $actionOperation[$argsObj->doAction];
        
      $tplDir = (!isset($opObj->template_dir)  || is_null($opObj->template_dir)) ? $tplCfg->template_dir : $opObj->template_dir;
      $tpl = is_null($opObj->template) ? $tplCfg->default_template : $opObj->template;

      $pos = strpos($tpl, '.php');
      if($pos === false) {
        $tpl = $tplDir . $tpl;      
      } else {
        $renderType = 'redirect';  
      } 
    break;
  }

  switch($renderType) {
    case 'template':
      $smartyObj->assign('gui',$guiObj);
      $smartyObj->display($tpl);
    break;  

    case 'redirect':
      header("Location: {$tpl}");
      exit();
    break;

    default:
    break;
  }

}


/**
 *
 */
function createNewVersion(&$tplEng,&$argsObj,&$guiObj,&$tcaseMgr,$sourceTCVID) {
  $user_feedback = '';
  $msg = lang_get('error_tc_add');

  $op = $tcaseMgr->create_new_version($argsObj->tcase_id,
          $argsObj->user_id,$sourceTCVID);

  $candidate = $sourceTCVID;
  if ($op['msg'] == "ok") {
    $candidate = $op['id'];
    $user_feedback = sprintf(lang_get('tc_new_version'),$op['version']);
    $msg = 'ok';
    $tcCfg = config_get('testcase_cfg');
    $isOpen = !$tcCfg->freezeTCVersionOnNewTCVersion;
    $tcaseMgr->setIsOpen($argsObj->tcase_id,$sourceTCVID,$isOpen);
  } 
  $identity = new stdClass();
  $identity->id = $argsObj->tcase_id;
  $identity->tproject_id = $argsObj->tproject_id;
  $identity->version_id = !is_null($argsObj->show_mode) ? $candidate : testcase::ALL_VERSIONS;

  $guiObj->viewerArgs['action'] = "do_update";
  $guiObj->viewerArgs['refreshTree'] = DONT_REFRESH;
  $guiObj->viewerArgs['msg_result'] = $msg;
  $guiObj->viewerArgs['user_feedback'] = $user_feedback;
  $guiObj->path_info = null;
  
  // used to implement go back ??
  $guiObj->loadOnCancelURL = $_SESSION['basehref'] . '/lib/testcases/archiveData.php?edit=testcase&id=' . 
                             $argsObj->tcase_id . "&tproject_id=" . $argsObj->tproject_id . "&show_mode={$argsObj->show_mode}";


  $tcaseMgr->show($tplEng,$guiObj,$identity,$guiObj->grants,
                  array('getAttachments' => true));
  exit();
}

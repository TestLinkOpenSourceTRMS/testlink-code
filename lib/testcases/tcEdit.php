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
 * @copyright   2007-2017, TestLink community 
 * @link        http://www.testlink.org/
 *
 *
 **/
require_once("../../config.inc.php");
require_once("common.php");
require_once("opt_transfer.php");
require_once("web_editor.php");

$cfg = getCfg();
$optionTransferName = 'ot';

testlinkInitPage($db);
$tcase_mgr = new testcase($db);
$tproject_mgr = new testproject($db);
$tree_mgr = new tree($db);
$tsuite_mgr = new testsuite($db);


$args = init_args($cfg,$optionTransferName,$tcase_mgr);
require_once(require_web_editor($cfg->webEditorCfg['type']));

$templateCfg = templateConfiguration('tcEdit');

$commandMgr = new testcaseCommands($db,$args->user,$args->tproject_id);
$commandMgr->setTemplateCfg(templateConfiguration());

$testCaseEditorKeys = array('summary' => 'summary','preconditions' => 'preconditions');
$init_inputs = true;
$opt_cfg = initializeOptionTransferCfg($optionTransferName,$args,$tproject_mgr);
$gui = initializeGui($db,$args,$cfg,$tcase_mgr);

$smarty = new TLSmarty();

$active_status = 0;
$name_ok = 1;
$action_result = "deactivate_this_version";
if($args->do_activate_this)
{
  $active_status = 1;
  $action_result = "activate_this_version";
}

$doRender = false;
$pfn = $args->doAction;

$testCaseEditorKeys = null;
switch($args->doAction)
{
  case "create":  
  case "edit":  
  case "doCreate":  
    $testCaseEditorKeys = array('summary' => 'summary','preconditions' => 'preconditions');
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
    $testCaseEditorKeys = array('steps' => 'steps', 'expected_results' => 'expected_results');
  break;

}


switch($args->doAction)
{
  case "doUpdate":
  case "doAdd2testplan":
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
  case "freeze":
  case "unfreeze":
    $op = $commandMgr->$pfn($args,$_REQUEST);
    $doRender = true;
  break;

  case "fileUpload":
    fileUploadManagement($db,$args->tcase_id,$args->fileTitle,$tcase_mgr->getAttachmentTableName());
    $commandMgr->show($args,$_REQUEST,array('status_ok' => true),false);
  break;

  case "deleteFile":
    deleteAttachment($db,$args->file_id);
    $commandMgr->show($args,$_REQUEST,array('status_ok' => true),false);
  break;


  case "doAddRelation":
  case "doDeleteRelation":
    $op = $commandMgr->$pfn($args,$_REQUEST);
    $doRender = true;
  break;
  
}


if( $doRender )
{
  renderGui($args,$gui,$op,$templateCfg,$cfg,$testCaseEditorKeys);
  exit();
}

// Things that one day will be managed by command file
if($args->delete_tc_version)
{
  $status_quo_map = $tcase_mgr->get_versions_status_quo($args->tcase_id);
  $exec_status_quo = $tcase_mgr->get_exec_status($args->tcase_id);
  $gui->delete_mode = 'single';
  $gui->delete_enabled = 1;

  $msg = '';
  $sq = null;
  if(!is_null($exec_status_quo))
  {
    if(isset($exec_status_quo[$args->tcversion_id]))
    {
      $sq = array($args->tcversion_id => $exec_status_quo[$args->tcversion_id]);
    }
  }

  if(intval($status_quo_map[$args->tcversion_id]['executed']))
  {
    $msg = lang_get('warning') . TITLE_SEP . lang_get('delete_linked_and_exec');
  }
  else if(intval($status_quo_map[$args->tcversion_id]['linked']))
  {
    $msg = lang_get('warning') . TITLE_SEP . lang_get('delete_linked');
  }

  $tcinfo = $tcase_mgr->get_by_id($args->tcase_id,$args->tcversion_id);

  $gui->title = lang_get('title_del_tc') . 
                TITLE_SEP_TYPE3 . lang_get('version') . " " . $tcinfo[0]['version'];
  $gui->testcase_name = $tcinfo[0]['name'];
  $gui->testcase_id = $args->tcase_id;
  $gui->tcversion_id = $args->tcversion_id;
  $gui->delete_message = $msg;
  $gui->exec_status_quo = $sq;
  $gui->refreshTree = 0;

  $smarty->assign('gui',$gui);
  $templateCfg = templateConfiguration('tcDelete');
  $smarty->display($templateCfg->template_dir . $templateCfg->default_template);
}
else if($args->move_copy_tc)
{
  // need to get the testproject for the test case
  $tproject_id = $tcase_mgr->get_testproject($args->tcase_id);
  $the_tc_node = $tree_mgr->get_node_hierarchy_info($args->tcase_id);
  $tc_parent_id = $the_tc_node['parent_id'];
  $the_xx = $tproject_mgr->gen_combo_test_suites($tproject_id);
  
  $the_xx[$the_tc_node['parent_id']] .= ' (' . lang_get('current') . ')';
  $tc_info = $tcase_mgr->get_by_id($args->tcase_id);
  
  $container_qty = count($the_xx);
  $gui->move_enabled = 1;
  if($container_qty == 1)
  {
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
  $templateCfg = templateConfiguration('tcMove');
  $smarty->display($templateCfg->template_dir . $templateCfg->default_template);
}
else if($args->do_move)
{
  $result = $tree_mgr->change_parent($args->tcase_id,$args->new_container_id);
  $tree_mgr->change_child_order($args->new_container_id,$args->tcase_id,
                                $args->target_position,$cfg->exclude_node_types);

  $gui->refreshTree = $args->refreshTree;
  $tsuite_mgr->show($smarty,$gui,$templateCfg->template_dir,$args->old_container_id);
}
else if($args->do_copy || $args->do_copy_ghost_zone)
{
  $args->stepAsGhost = $args->do_copy_ghost_zone;
  $user_feedback='';
  $msg = '';
  $action_result = 'copied';
  $options = array('check_duplicate_name' => config_get('check_names_for_duplicates'),
                   'action_on_duplicate_name' => config_get('action_on_duplicate_name'),
                   'copy_also' => $args->copy, 'stepAsGhost' => $args->do_copy_ghost_zone,
                   'use_this_name' => $args->name);
  
  $result = $tcase_mgr->copy_to($args->tcase_id,$args->new_container_id,$args->user_id,$options);
  $msg = $result['msg'];
  if($result['status_ok'])
  {
    $tree_mgr->change_child_order($args->new_container_id,$result['id'],
                                  $args->target_position,$cfg->exclude_node_types);
    
    $ts_sep = config_get('testsuite_sep');
    $tc_info = $tcase_mgr->get_by_id($args->tcase_id);
    $container_info = $tree_mgr->get_node_hierarchy_info($args->new_container_id);
    $container_path = $tree_mgr->get_path($args->new_container_id);
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
else if($args->do_create_new_version)
{
  $user_feedback = '';
  $show_newTC_form = 0;
  $action_result = "do_update";
  $msg = lang_get('error_tc_add');
  $op = $tcase_mgr->create_new_version($args->tcase_id,$args->user_id,$args->tcversion_id);
  if ($op['msg'] == "ok")
  {
    $user_feedback = sprintf(lang_get('tc_new_version'),$op['version']);
    $msg = 'ok';
  
    // @since 1.9.15
    // Source version need to be set to FROZEN
    $tcase_mgr->setIsOpen($args->tcase_id,$args->tcversion_id,0);
  }

  $gui->viewerArgs['action'] = $action_result;
  $gui->viewerArgs['refreshTree'] = DONT_REFRESH;
  $gui->viewerArgs['msg_result'] = $msg;
  $gui->viewerArgs['user_feedback'] = $user_feedback;
  $gui->path_info = null;
  
  // used to implement go back ??
  $gui->loadOnCancelURL = $_SESSION['basehref'] . 
                          '/lib/testcases/archiveData.php?edit=testcase&id=' . $args->tcase_id .
                          "&show_mode={$args->show_mode}";
  
  $identity = new stdClass();
  $identity->id = $args->tcase_id;
  $identity->tproject_id = $args->tproject_id;
  $identity->version_id = !is_null($args->show_mode) ? $args->tcversion_id : testcase::ALL_VERSIONS;
 
  $tcase_mgr->show($smarty,$gui,$identity,$gui->grants);

}
else if($args->do_activate_this || $args->do_deactivate_this)
{
  $gui->loadOnCancelURL = $_SESSION['basehref'] . 
                          '/lib/testcases/archiveData.php?edit=testcase&id=' . $args->tcase_id .
                          "&show_mode={$args->show_mode}";
  
  $tcase_mgr->update_active_status($args->tcase_id, $args->tcversion_id, $active_status);
  $gui->viewerArgs['action'] = $action_result;
  $gui->viewerArgs['refreshTree']=DONT_REFRESH;
  $gui->path_info = null;
  
  $identity = new stdClass();
  $identity->id = $args->tcase_id;
  $identity->tproject_id = $args->tproject_id;
  $identity->version_id = testcase::ALL_VERSIONS;
 
  $tcase_mgr->show($smarty,$gui,$identity,$gui->grants);

}

// --------------------------------------------------------------------------


/*
  function:

  args:

  returns:

*/
function init_args(&$cfgObj,$otName,&$tcaseMgr)
{
  $tc_importance_default = config_get('testcase_importance_default');

  $args = new stdClass();
  $_REQUEST = strings_stripSlashes($_REQUEST);

  $rightlist_html_name = $otName . "_newRight";
  $args->assigned_keywords_list = isset($_REQUEST[$rightlist_html_name])? $_REQUEST[$rightlist_html_name] : "";
  $args->container_id = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : 0;
  
  $args->file_id = isset($_REQUEST['file_id']) ? intval($_REQUEST['file_id']) : 0;

  $args->tcase_id = isset($_REQUEST['testcase_id']) ? intval($_REQUEST['testcase_id']) : 0;
  if($args->tcase_id == 0)
  {
    $args->tcase_id = isset($_REQUEST['tcase_id']) ? intval($_REQUEST['tcase_id']) : 0;
  }  
  if($args->tcase_id == 0)
  {
    $args->tcase_id = intval(isset($_REQUEST['relation_source_tcase_id']) ? 
                             $_REQUEST['relation_source_tcase_id'] : 0);
  }
  
  $args->tcversion_id = isset($_REQUEST['tcversion_id']) ? intval($_REQUEST['tcversion_id']) : 0;
  $args->name = isset($_REQUEST['testcase_name']) ? $_REQUEST['testcase_name'] : null;

  // Normally Rich Web Editors  
  $args->summary = isset($_REQUEST['summary']) ? $_REQUEST['summary'] : null;
  $args->preconditions = isset($_REQUEST['preconditions']) ? $_REQUEST['preconditions'] : null;
  $args->steps = isset($_REQUEST['steps']) ? $_REQUEST['steps'] : null;
  $args->expected_results = isset($_REQUEST['expected_results']) ? $_REQUEST['expected_results'] : null;

  $args->new_container_id = isset($_REQUEST['new_container']) ? intval($_REQUEST['new_container']) : 0;
  $args->old_container_id = isset($_REQUEST['old_container']) ? intval($_REQUEST['old_container']) : 0;
  $args->has_been_executed = isset($_REQUEST['has_been_executed']) ? intval($_REQUEST['has_been_executed']) : 0;
  $args->exec_type = isset($_REQUEST['exec_type']) ? $_REQUEST['exec_type'] : TESTCASE_EXECUTION_TYPE_MANUAL;
  $args->importance = isset($_REQUEST['importance']) ? $_REQUEST['importance'] : $tc_importance_default;
  $args->status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 1; // sorry for the magic

  $args->estimatedExecDuration = isset($_REQUEST['estimated_execution_duration']) ? 
                                 $_REQUEST['estimated_execution_duration'] : null;

  
  $args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : '';

  $key2loop = array('edit_tc' => 'edit', 'delete_tc' => 'delete','do_delete' => 'doDelete',
                    'create_tc' => 'create','do_create' => 'doCreate');
  foreach($key2loop as $key => $action)
  {
    if( isset($_REQUEST[$key]) )
    {
      $args->doAction = $action;
      break;
    }
  }

   
  $key2loop = array('move_copy_tc','delete_tc_version','do_move','do_copy','do_copy_ghost_zone',
                    'do_create_new_version','do_delete_tc_version');
  foreach($key2loop as $key)
  {
    $args->$key = isset($_REQUEST[$key]) ? 1 : 0;
  }

  $args->do_activate_this = isset($_REQUEST['activate_this_tcversion']) ? 1 : 0;
  $args->do_deactivate_this = isset($_REQUEST['deactivate_this_tcversion']) ? 1 : 0;
  $args->target_position = isset($_REQUEST['target_position']) ? $_REQUEST['target_position'] : 'bottom';
    
  $key2loop=array("keyword_assignments","requirement_assignments");
  foreach($key2loop as $key)
  {
     $args->copy[$key]=isset($_REQUEST[$key])?true:false;    
  }    
  
  
  $args->show_mode = (isset($_REQUEST['show_mode']) && $_REQUEST['show_mode'] != '') ? $_REQUEST['show_mode'] : null;

  // Multiple Test Case Steps Feature
  $args->step_number = isset($_REQUEST['step_number']) ? intval($_REQUEST['step_number']) : 0;
  $args->step_id = isset($_REQUEST['step_id']) ? intval($_REQUEST['step_id']) : 0;
  $args->step_set = isset($_REQUEST['step_set']) ? $_REQUEST['step_set'] : null;
  $args->tcaseSteps = isset($_REQUEST['tcaseSteps']) ? $_REQUEST['tcaseSteps'] : null;
  
        
  // from session
  $args->testproject_id = $args->tproject_id = intval($_SESSION['testprojectID']);
  $args->user_id = intval($_SESSION['userID']);
  $args->refreshTree = isset($_SESSION['setting_refresh_tree_on_action']) ? intval($_SESSION['setting_refresh_tree_on_action']) : 0;
    
  $args->opt_requirements = null;
  if( isset($_SESSION['testprojectOptions']) )
  {
    $args->opt_requirements = $_SESSION['testprojectOptions']->requirementsEnabled;
    $args->requirementsEnabled = $_SESSION['testprojectOptions']->requirementsEnabled;
  } 

  $args->basehref = $_SESSION['basehref'];
  $args->goback_url = isset($_REQUEST['goback_url']) ? $_REQUEST['goback_url'] : null;


  // Specialized webEditorConfiguration
  $action2check = array("editStep" => true,"createStep" => true, "doCreateStep" => true,
                        "doUpdateStep" => true, "doInsertStep" => true, 
                        "doCopyStep" => true,"doUpdateStepAndInsert" => true);
  if( isset($action2check[$args->doAction]) )
  {
    $cfgObj->webEditorCfg = getWebEditorCfg('steps_design');  
  }   

  $args->stay_here = isset($_REQUEST['stay_here']) ? 1 : 0;


  $dummy = getConfigAndLabels('testCaseStatus','code');
  $args->tcStatusCfg['status_code'] = $dummy['cfg'];
  $args->tcStatusCfg['code_label'] = $dummy['lbl'];
  $args->tc_status = isset($_REQUEST['tc_status']) ? intval($_REQUEST['tc_status']) : 
                     $args->tcStatusCfg['status_code']['draft'];
  
  $dk = 'estimated_execution_duration';
  $args->$dk = trim(isset($_REQUEST[$dk]) ? $_REQUEST[$dk] : '');


  $args->fileTitle = isset($_REQUEST['fileTitle'])? $_REQUEST['fileTitle'] : "";



  $args->relation_type = isset($_REQUEST['relation_type']) ? $_REQUEST['relation_type'] : null;
  $args->relation_id = intval(isset($_REQUEST['relation_id']) ? $_REQUEST['relation_id'] : 0);

  $args->relation_destination_tcase = isset($_REQUEST['relation_destination_tcase']) ? 
                                      $_REQUEST['relation_destination_tcase'] : null;

  $args->relation_destination_tcase = str_replace(' ','',$args->relation_destination_tcase);
  $getOpt = array('tproject_id' => null, 'output' => 'map');                         
  if( is_numeric($args->relation_destination_tcase) )   
  {
    $getOpt['tproject_id'] = $args->tproject_id;
  }  
  $args->dummy = $tcaseMgr->getInternalID($args->relation_destination_tcase,$getOpt);

  $args->destination_tcase_id = $args->dummy['id'];


  $args->keyword_id = isset($_GET['keyword_id']) ? intval($_GET['keyword_id']) : 0;

  $args->tplan_id = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;
  $args->platform_id = isset($_REQUEST['platform_id']) ? intval($_REQUEST['platform_id']) : 0;
  
  // need to check if user has access rights to test project is project is private.
  $args->user = $_SESSION['currentUser'];

  $cbk = 'changeExecTypeOnSteps';
  $args->applyExecTypeChangeToAllSteps = isset($_REQUEST[$cbk]);

  return $args;
}


/*
  function: initializeOptionTransferCfg
  args :
  returns: 
*/
function initializeOptionTransferCfg($otName,&$argsObj,&$tprojectMgr)
{
  $otCfg = new stdClass();
  switch($argsObj->doAction)
  {
    case 'create':
    case 'edit':
    case 'doCreate':
      $otCfg = opt_transf_empty_cfg();
      $otCfg->global_lbl = '';
      $otCfg->from->lbl = lang_get('available_kword');
      $otCfg->from->map = $tprojectMgr->get_keywords_map($argsObj->testproject_id);
      $otCfg->to->lbl = lang_get('assigned_kword');
    break;
  }
  
  $otCfg->js_ot_name = $otName;
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
    $cfg=new stdClass();
    $cfg->treemenu_default_testcase_order = config_get('treemenu_default_testcase_order');
    $cfg->spec = config_get('spec_cfg');
    $cfg->exclude_node_types = array('testplan' => 1, 'requirement' => 1, 'requirement_spec' => 1);
    $cfg->tcase_template = config_get('testcase_template');
    $cfg->webEditorCfg=getWebEditorCfg('design');

    $cfg->editorKeys = new stdClass();
    $cfg->editorKeys->testcase = array('summary' => true, 'preconditions' => true);    
    $cfg->editorKeys->step = array('steps' => true, 'expected_results' => true);    

    return $cfg;
}

/*
  function: getGrants
  args :
  returns: object
*/
function getGrants(&$dbHandler)
{
  $grants=new stdClass();
  $grants->requirement_mgmt=has_rights($dbHandler,"mgt_modify_req"); 
  return $grants;
}


/**
 * 
 *
 */
function initializeGui(&$dbHandler,&$argsObj,$cfgObj,&$tcaseMgr)
{
  $guiObj = new stdClass();
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

  $guiObj->loadOnCancelURL = $_SESSION['basehref'] . 
                             "/lib/testcases/archiveData.php?edit=testcase&id=" . $argsObj->tcase_id .
                             "&show_mode={$argsObj->show_mode}";
  
  $guiObj->fileUploadURL = $_SESSION['basehref'] . $tcaseMgr->getFileUploadRelativeURL($argsObj);

   
  if($argsObj->container_id > 0)
  {
    $pnode_info = $tcaseMgr->tree_manager->get_node_hierarchy_info($argsObj->container_id);
    $node_descr = array_flip($tcaseMgr->tree_manager->get_available_node_types());
    $guiObj->parent_info['name'] = $pnode_info['name'];
    $guiObj->parent_info['description'] = lang_get($node_descr[$pnode_info['node_type_id']]);
  }
  
  $guiObj->direct_link = $tcaseMgr->buildDirectWebLink($_SESSION['basehref'],$argsObj->tcase_id,
                                                       $argsObj->testproject_id);

  $guiObj->domainTCStatus = $argsObj->tcStatusCfg['code_label'];
  

  $grant2check = array('mgt_modify_tc','mgt_view_req','testplan_planning','mgt_modify_product',
                       'testproject_edit_executed_testcases','testproject_delete_executed_testcases');
  $guiObj->grants = new stdClass();
  foreach($grant2check as $right)
  {
    $guiObj->$right = $guiObj->grants->$right = $argsObj->user->hasRight($dbHandler,$right,$argsObj->tproject_id);
  }


  return $guiObj;
}

/**
 * manage GUI rendering
 *
 */
function renderGui(&$argsObj,$guiObj,$opObj,$templateCfg,$cfgObj,$editorKeys)
{
    $smartyObj = new TLSmarty();
    
    // needed by webeditor loading logic present on inc_head.tpl
    $smartyObj->assign('editorType',$guiObj->editorType);  

    $renderType = 'none';

    //
    // key: operation requested (normally received from GUI on doAction)
    // value: operation value to set on doAction HTML INPUT
    // This is useful when you use same template (example xxEdit.tpl), for create and edit.
    // When template is used for create -> operation: doCreate.
    // When template is used for edit -> operation: doUpdate.
    //              
    // used to set value of: $guiObj->operation
    //
    $actionOperation = array('create' => 'doCreate', 'doCreate' => 'doCreate',
                             'edit' => 'doUpdate','delete' => 'doDelete', 'doDelete' => '',
                             'createStep' => 'doCreateStep', 'doCreateStep' => 'doCreateStep',
                             'doCopyStep' => 'doUpdateStep',
                             'editStep' => 'doUpdateStep', 
                             'doUpdateStep' => 'doUpdateStep', 
                             'doUpdateStepAndInsert' => 'doUpdateStep', 
                             'doDeleteStep' => '', 'doReorderSteps' => '','doResequenceSteps' => '',
                             'doInsertStep' => 'doUpdateStep',
                             'setImportance' => '','setStatus' => '',
                             'setExecutionType' => '', "setEstimatedExecDuration" => '',
                             'doAddRelation' => '', 'doDeleteRelation' => '',
                             'removeKeyword' => '', 'freeze' => '', 'unfreeze' => '');

  $key2work = 'initWebEditorFromTemplate';
  $initWebEditorFromTemplate = property_exists($opObj,$key2work) ? $opObj->$key2work : false;                             
  $key2work = 'cleanUpWebEditor';
  $cleanUpWebEditor = property_exists($opObj,$key2work) ? $opObj->$key2work : false;                             

  $oWebEditor = createWebEditors($argsObj->basehref,$cfgObj->webEditorCfg,$editorKeys); 

  foreach ($oWebEditor->cfg as $key => $value)
  {
    $of = &$oWebEditor->editor[$key];
    $rows = $oWebEditor->cfg[$key]['rows'];
    $cols = $oWebEditor->cfg[$key]['cols'];
    
    switch($argsObj->doAction)
    {
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
  
    if($initWebEditorFromTemplate)
    {
      $of->Value = getItemTemplateContents('testcase_template', $of->InstanceName, '');  
    }
    else if( $cleanUpWebEditor )
    {
      $of->Value = '';
    }
    $smartyObj->assign($key, $of->CreateHTML($rows,$cols));
  }
      
  switch($argsObj->doAction) 
  {
    case "doDelete":
      $guiObj->refreshTree = $argsObj->refreshTree;
    break;
  }

  switch($argsObj->doAction)
  {
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
        case "freeze":        
        case "unfreeze":        
            $renderType = 'template';
            
            // Document this !!!!
            $key2loop = get_object_vars($opObj);
            foreach($key2loop as $key => $value)
            {
             $guiObj->$key = $value;
            }
            $guiObj->operation = $actionOperation[$argsObj->doAction];
            
            $tplDir = (!isset($opObj->template_dir)  || is_null($opObj->template_dir)) ? $templateCfg->template_dir : $opObj->template_dir;
            $tpl = is_null($opObj->template) ? $templateCfg->default_template : $opObj->template;

            $pos = strpos($tpl, '.php');
            if($pos === false)
            {
              $tpl = $tplDir . $tpl;      
            }
            else
            {
              $renderType = 'redirect';  
            } 
        break;
    }

    switch($renderType)
    {
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
<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  platformsEdit.php
 * @package     TestLink
 * @copyright   2009-2022, TestLink community 
 * @link        http://www.testlink.org 
 * @link        http://mantis.testlink.org 
 * @link        https://github.com/TestLinkOpenSourceTRMS/testlink-code 
 * 
 *
 * allows users to manage platforms. 
 *
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("csv.inc.php");
require_once("xml.inc.php");

require_once("web_editor.php");
$editorCfg = getWebEditorCfg('build');
require_once(require_web_editor($editorCfg['type']));

// Security checks are done, if failed => exit()
list($args,$gui,$platform_mgr) = initEnv($db);

$tplCfg = templateConfiguration();
$smarty = new TLSmarty();
$tpl = $tplCfg->default_template;

$op = new stdClass();
$op->status = 0;

$of = web_editor('notes',$_SESSION['basehref'],$editorCfg);
$of->Value = getItemTemplateContents('platform_template', $of->InstanceName, $args->notes);

$method = $args->doAction;
switch ($args->doAction) {
  case "do_create":
  case "do_update":
  case "do_delete":
    if (!$gui->canManage) {
      break;
    }
      
  case "edit":
  case "create":
    $op = $method($args,$gui,$platform_mgr);
    $of->Value = $gui->notes;
  break;

  case "disableDesign":
  case "enableDesign":
  case "disableExec":
  case "enableExec":
  case "openForExec":
  case "closeForExec":      
    $platform_mgr->$method($args->platform_id);
    
    // optimistic
    $op = new stdClass();
    $op->status = 1;
    $op->user_feedback = '';
    $op->template = 'platformsView.tpl';
  break;
}

if ($op->status == 1) {
  $tpl = $op->template;
  $gui->user_feedback['message'] = $op->user_feedback;
} else {
  $gui->user_feedback['message'] = getErrorMessage($op->status, $args->name);
  $gui->user_feedback['type'] = 'ERROR';
}

// refresh
$guiX = $platform_mgr->initViewGui($args->currentUser,$args);    
$gui->platforms = $guiX->platforms;

$gui->notes = $of->CreateHTML();

$smarty->assign('gui',$gui);
$smarty->display($tplCfg->template_dir . $tpl);

/**
 * 
 *
 */
function initEnv(&$dbHandler) {
  testlinkInitPage($dbHandler);
  $argsObj = init_args($dbHandler);

  checkPageAccess($dbHandler,$argsObj);  // Will exit if check failed

  $platMgr = new tlPlatform($dbHandler, $argsObj->tproject_id);

  $guiObj = init_gui($dbHandler,$argsObj,$platMgr);

  return array($argsObj,$guiObj,$platMgr);
}



/**
 * 
 *
 */
function init_args( &$dbH ) 
{
  $_REQUEST = strings_stripSlashes($_REQUEST);

  $args = new stdClass();
  $iParams = [
    "doAction" => [tlInputParameter::STRING_N,0,50],
    "id" => [tlInputParameter::INT_N],
    "platform_id" => [tlInputParameter::INT_N],
    "name" => [tlInputParameter::STRING_N,0,100],
    "notes" => [tlInputParameter::STRING_N],
    "tproject_id" => [tlInputParameter::INT_N],
    "tplan_id" => [tlInputParameter::INT_N],
    "enable_on_execution" => [tlInputParameter::CB_BOOL],
    "enable_on_design" => [tlInputParameter::CB_BOOL],
    "is_open" => [tlInputParameter::CB_BOOL]
  ];
    
  R_PARAMS($iParams,$args);
  if (null == $args->platform_id || $args->platform_id <= 0) {
    $args->platform_id = $args->id;
  }

  $tables = tlDBObject::getDBTables(array('nodes_hierarchy','platforms'));
  
  list($context,$env) = initContext();
  $args->tplan_id = $context->tplan_id;    
  $args->tproject_id = $context->tproject_id;    

  if( 0 != $args->platform_id ) {
    $sql = "SELECT testproject_id FROM {$tables['platforms']}  
            WHERE id={$args->platform_id}";
    $info = $dbH->get_recordset($sql);

    $args->tproject_id = $info[0]['testproject_id'];    
  } 
    
  if( 0 == $args->tproject_id ) {
    throw new Exception("Unable to Get Test Project ID, Aborting", 1);
  }

  $args->testproject_name = '';
  $sql = "SELECT name FROM {$tables['nodes_hierarchy']}  
          WHERE id={$args->tproject_id}";
  $info = $dbH->get_recordset($sql);
  if( null != $info ) {
    $args->testproject_name = $info[0]['name'];
  }

  $args->currentUser = $_SESSION['currentUser'];
  
  // Checkboxes
  if (null == $args->enable_on_design) {
    $args->enable_on_design = 0;
  }

  if (null == $args->enable_on_execution) {
    $args->enable_on_execution = 0;
  }

  if (null == $args->is_open) {
    $args->is_open = 0;
  }

  return $args;
}

/*
  function: create
            initialize variables to launch user interface (smarty template)
            to get information to accomplish create task.

  args:
  
  returns: - 

*/
function create(&$args,&$gui) {
  $ret = new stdClass();
  $ret->template = 'platformsEdit.tpl';
  $ret->status = 1;
  $ret->user_feedback = '';
  $gui->submit_button_label = lang_get('btn_save');
  $gui->submit_button_action = 'do_create';
  $gui->action_descr = lang_get('create_platform');
  
  return $ret;
}


/*
  function: edit
            initialize variables to launch user interface (smarty template)
            to get information to accomplish edit task.

  args:
  
  returns: - 

*/
function edit(&$args,&$gui,&$platform_mgr) {
  $ret = new stdClass();
  $ret->template = 'platformsEdit.tpl';
  $ret->status = 1;
  $ret->user_feedback = '';

  $gui->action_descr = lang_get('edit_platform');
  $platform = $platform_mgr->getById($args->platform_id);
  
  if ($platform) {
    $args->enable_on_design = $platform['enable_on_design'];
    $args->enable_on_execution = $platform['enable_on_execution'];
    $args->is_open = $platform['is_open'];

    $args->name = $platform['name'];
    $args->notes = $platform['notes'];

    // ---------------------------------------------------------
    // Copy from args into $gui 
    $gui->enable_on_design = $args->enable_on_design;
    $gui->enable_on_execution = $args->enable_on_execution;
    $gui->is_open = $args->is_open;
  
    $gui->name = $args->name;
    $gui->notes = $args->notes;
    // ---------------------------------------------------------

    $gui->action_descr .= TITLE_SEP . $platform['name'];
  }
  
  $gui->submit_button_label = lang_get('btn_save');
  $gui->submit_button_action = 'do_update';
  $gui->main_descr = lang_get('platform_management');
  
  return $ret;
}

/**
 * function: do_create 
 *           do operations on db
 *
 */
function do_create(&$args,&$gui,&$platform_mgr) 
{
  $gui->main_descr = lang_get('platform_management');
  $gui->action_descr = lang_get('create_platform');
  $gui->submit_button_label = lang_get('btn_save');
  $gui->submit_button_action = 'do_create';

  $ret = new stdClass();
  $ret->template = 'platformsView.tpl';
  $plat = new stdClass();
  $plat->name = $args->name; 
  $k2c = [
    'notes' => null,
    'enable_on_design' => 0,
    'enable_on_execution' => 0,
    'is_open' => 0
  ];

  foreach ($k2c as $prop => $defa) {
    $plat->$prop = property_exists($args, $prop) ? $args->$prop : $defa;
  }
  $op = $platform_mgr->create($plat);

  $ret->status = $op['status']; 
  $ret->user_feedback = sprintf(lang_get('platform_created'), $args->name);
  
  return $ret;
}

/**
 *
 *
 */
function do_update(&$args,&$gui,&$platform_mgr) {
  $action_descr = lang_get('edit_platform');
  $platform = $platform_mgr->getPlatform($args->platform_id);
  if ($platform) {
    $action_descr .= TITLE_SEP . $platform['name'];
  }
    
  $gui->submit_button_label = lang_get('btn_save');
  $gui->submit_button_action = 'do_update';
  $gui->main_descr = lang_get('platform_management');
  $gui->action_descr = $action_descr;

  $ret = new stdClass();
  $ret->template = 'platformsView.tpl';
  $ret->status = $platform_mgr->update(
                    $args->platform_id,
                    $args->name,$args->notes,
                    $args->enable_on_design,
                    $args->enable_on_execution,
                    $args->is_open
                 );
  $ret->user_feedback = sprintf(lang_get('platform_updated'), $args->name);

  return $ret;
}

/*
  function: do_delete
            do operations on db

  args :
  
  returns: 

*/
function do_delete(&$args,&$gui,&$platform_mgr) {
  $gui->main_descr = lang_get('testproject') . TITLE_SEP . $args->testproject_name;

  $gui->submit_button_label = lang_get('btn_save');
  $gui->submit_button_action = 'do_update';
  $gui->action_descr = lang_get('edit_platform');

  $ret = new stdClass();
  $ret->template = 'platformsView.tpl';
  // This also removes all exec data on this platform
  $ret->status = $platform_mgr->delete($args->platform_id,true);
  $ret->user_feedback = sprintf(lang_get('platform_deleted'), $args->name);

  return $ret;
}

/**
 *
 */
function getErrorMessage($code,$platform_name) {
  switch($code) {
    case tlPlatform::E_NAMENOTALLOWED:
      $msg = lang_get('platforms_char_not_allowed'); 
      break;

    case tlPlatform::E_NAMELENGTH:
      $msg = lang_get('empty_platform_no');
      break;

    case tlPlatform::E_DBERROR:
    case ERROR: 
      $msg = lang_get('platform_update_failed');
      break;

    case tlPlatform::E_NAMEALREADYEXISTS:
      $msg = sprintf(lang_get('platform_name_already_exists'),$platform_name);
      break;

    default:
      $msg = 'ok';
  }
  return $msg;
}

/**
 *
 */
function init_gui(&$db,&$args,&$platMgr) {
  $gui = $platMgr->initViewGui($args->currentUser,$args);
  
  $gui->name = $args->name;
  $gui->notes = $args->notes;
  $gui->platform_id = $args->platform_id;
  $gui->tproject_id = $args->tproject_id;
  $gui->tplan_id = $args->tplan_id;

  $gui->enable_on_design = 0;
  $gui->enable_on_execution = 0;
  $gui->is_open = 0;

  return $gui;
}

/**
 *
 */
function checkPageAccess(&$db,&$argsObj) {
  $env['script'] = basename(__FILE__);
  $env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
  $env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
  $argsObj->currentUser->checkGUISecurityClearance($db,$env,array('platform_management'),'and');
}

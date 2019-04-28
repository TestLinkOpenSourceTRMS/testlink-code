<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource  reqSpecEdit.php
 * @author      Martin Havlat
 *
 * View existing and create a new req. specification.
 *
 * @internal revisions
 * @since 1.9.15
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
require_once("web_editor.php");
$editorCfg = getWebEditorCfg('requirement_spec');
require_once(require_web_editor($editorCfg['type']));
$req_cfg = config_get('req_cfg');

testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();
$commandMgr = new reqSpecCommands($db,$args->tproject_id);

$gui = initialize_gui($db,$args,$req_cfg,$commandMgr);

$auditContext = new stdClass();
$auditContext->tproject = $args->tproject_name;
$commandMgr->setAuditContext($auditContext);

$pFn = $args->doAction;
$op = null;
if(method_exists($commandMgr,$pFn))
{
  $op = $commandMgr->$pFn($args,$_REQUEST);
}

renderGui($args,$gui,$op,$templateCfg,$editorCfg);


/**
 * 
 *
 */
function init_args()
{
  $args = new stdClass();

  $iParams = array("countReq" => array(tlInputParameter::INT_N,99999),
                   "req_spec_id" => array(tlInputParameter::INT_N),
                   "req_spec_revision_id" => array(tlInputParameter::INT_N),
                   "parentID" => array(tlInputParameter::INT_N),
                   "doAction" => array(tlInputParameter::STRING_N,0,250),
                   "title" => array(tlInputParameter::STRING_N,0,100),
                   "scope" => array(tlInputParameter::STRING_N),
                   "doc_id" => array(tlInputParameter::STRING_N,1,32),
                   "nodes_order" => array(tlInputParameter::ARRAY_INT),
                   "containerID" => array(tlInputParameter::INT_N),
                   "itemSet" => array(tlInputParameter::ARRAY_INT),
                   "reqSpecType" => array(tlInputParameter::STRING_N,0,1),
                   "copy_testcase_assignment" => array(tlInputParameter::CB_BOOL),
                   "save_rev" => array(tlInputParameter::INT_N),
                   "do_save" => array(tlInputParameter::INT_N),
                   "log_message" => array(tlInputParameter::STRING_N),
                   "file_id" => array(tlInputParameter::INT_N),
                   "fileTitle" => array(tlInputParameter::STRING_N,0,100));

  $args = new stdClass();
  R_PARAMS($iParams,$args);

  // i guess due to required revison log it is necessary to strip slashes
  // after R_PARAMS call - at least this fixed the problem
  $_REQUEST=strings_stripSlashes($_REQUEST);
  
  $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
  $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : "";
  $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
  $args->basehref = $_SESSION['basehref'];
  
  $args->parentID = is_null($args->parentID) ? $args->tproject_id : $args->parentID;

  $args->refreshTree = isset($_SESSION['setting_refresh_tree_on_action'])
                       ? $_SESSION['setting_refresh_tree_on_action'] : 0;
  
  $args->countReq = is_null($args->countReq) ? 0 : intval($args->countReq);
  
  // Process buttons
  $args->op = null;
  $btnSet = array('toogleMon','startMon','stopMon');
  foreach( $btnSet as $btn )
  {
    if( isset($_REQUEST[$btn]) )
    {
      $args->op = $btn;
      break;
    }  
  }  
  
  return $args;
}


/**
 * renderGui
 *
 */
function renderGui(&$argsObj,$guiObj,$opObj,$templateCfg,$editorCfg)
{
  $smartyObj = new TLSmarty();
  $renderType = 'none';
  $tpl = $tpd = null;

  $actionOperation = array('create' => 'doCreate', 'edit' => 'doUpdate',
                           'doDelete' => '', 'doReorder' => '', 'reorder' => '',
                           'doCreate' => 'doCreate', 'doUpdate' => 'doUpdate',
                           'createChild' => 'doCreate', 'copy' => 'doCopy',
                           'doCopy' => 'doCopy',
                           'doFreeze' => 'doFreeze',
                           'copyRequirements' => 'doCopyRequirements',
                           'doCopyRequirements' => 'doCopyRequirements',
                           'doCreateRevision' => 'doCreateRevision',
                           'fileUpload' => '', 'deleteFile' => '',
                           'bulkReqMon' => 'doBulkReqMon',
                           'doBulkReqMon' => 'doBulkReqMon');
  // ------------------------------------------------------------------------------------------------
  // Web Editor Processing
  $owebEditor = web_editor('scope',$argsObj->basehref,$editorCfg) ;
  switch($argsObj->doAction)
  {
    case "edit":
    case "doCreate":
      $owebEditor->Value = $argsObj->scope;
    break;

    case "fileUpload":
    case "deleteFile":
    break;

    default:
      if($opObj->askForRevision || $opObj->askForLog || !$opObj->action_status_ok) 
      {
        $owebEditor->Value = $argsObj->scope;
      }
      else
      {
        $owebEditor->Value = getItemTemplateContents('req_spec_template',$owebEditor->InstanceName,$argsObj->scope);
      }                        
    break;
  }
  $guiObj->scope = $owebEditor->CreateHTML();
  $guiObj->editorType = $editorCfg['type'];  

  // Tree refresh Processing
  switch($argsObj->doAction)
  {
    case "doCreate":
    case "doUpdate": 
    case "doCopyRequirements":
    case "doCopy":
    case "doFreeze":
    case "doDelete":
    case "doBulkReqMon":
      $guiObj->refreshTree = $argsObj->refreshTree;
    break;
  }

  // GUI rendering Processing
  switch($argsObj->doAction)
  {
    case "edit":
    case "create":
    case "createChild":
    case "reorder":
    case "doDelete":
    case "doReorder":
    case "doCreate":
    case "doUpdate":
    case "copyRequirements":
    case "doCopyRequirements":
    case "copy":
    case "doCopy":
    case "doFreeze":
    case "doCreateRevision":
    case "fileUpload":
    case "deleteFile":
    case "bulkReqMon":
    case "doBulkReqMon":
      $renderType = 'template';
      $key2loop = get_object_vars($opObj);
            
      if($opObj->action_status_ok == false)
      {
        // Remember that scope normally is a WebRichEditor, and that
        // we have already processed WebRichEditor
        // Need to understand if remove of scope key can be done always
        // no matter action_status_ok
        unset($key2loop['scope']);
      }
      foreach($key2loop as $key => $value)
      {
        $guiObj->$key = $value;
      }
      
      $guiObj->operation = $actionOperation[$argsObj->doAction];
      $tpl = is_null($opObj->template) ? $templateCfg->default_template : $opObj->template;
      $tpd = isset($key2loop['template_dir']) ? $opObj->template_dir : $templateCfg->template_dir;

      $pos = strpos($tpl, '.php');
      if($pos === false)
      {
        $tpl = $tpd . $tpl;
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
      $smartyObj->assign('mgt_view_events',has_rights($db,"mgt_view_events"));
      $smartyObj->assign('gui',$guiObj);
      $smartyObj->display($tpl);
    break;  
 
    case 'redirect':
      header("Location: {$tpl}");
      exit();
    break;

    default:
      echo 'Can not process RENDERING!!!';
    break;
  }
}

/**
 * 
 *
 */
function initialize_gui(&$dbHandler, &$argsObj, &$req_cfg, &$commandMgr)
{
  $gui = $commandMgr->initGuiBean();
  $gui->parentID = $argsObj->parentID;
  $gui->user_feedback = null;
  $gui->main_descr = null;
  $gui->action_descr = null;
  $gui->refreshTree = 0;
  $gui->external_req_management = ($req_cfg->external_req_management == ENABLED) ? 1 : 0;
  $gui->grants = new stdClass();
  $gui->grants->req_mgmt = has_rights($dbHandler,"mgt_modify_req");

  return $gui;
}

/**
 * 
 *
 */
function checkRights(&$db,&$user)
{
  return ($user->hasRight($db,'mgt_view_req') && $user->hasRight($db,'mgt_modify_req'));
}
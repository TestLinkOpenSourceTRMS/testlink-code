<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  rolesEdit.php
 *
 * @package     TestLink
 * @copyright   2005-2018, TestLink community
 * @link        http://www.testlink.org
 *
 * 
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once("web_editor.php");

$editorCfg = getWebEditorCfg('role');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db,false,false,"checkRights");
init_global_rights_maps();
$templateCfg = templateConfiguration();
$args = init_args();
$gui = initialize_gui($args,$editorCfg['type']);
$lbl = initLabels();
$op = initialize_op();

$owebeditor = web_editor('notes',$args->basehref,$editorCfg) ;
$owebeditor->Value = getItemTemplateContents('role_template', $owebeditor->InstanceName, null);
$canManage = $args->user->hasRight($db,"role_management") ? true : false;

switch($args->doAction)
{
  case 'create':
    $gui->main_title = $lbl["action_{$args->doAction}_role"];
  break;

  case 'edit':
    $op->role = tlRole::getByID($db,$args->roleid);
    $gui->main_title = $lbl["action_{$args->doAction}_role"];
  break;

  case 'doCreate':
  case 'doUpdate':
  case 'duplicate':
    if($canManage)
    {
      $op = doOperation($db,$args,$args->doAction);
      $templateCfg->template = $op->template;
    }
  break;

  default:
  break;
}

$gui = complete_gui($db,$gui,$args,$op->role,$owebeditor);
$gui->userFeedback = $op->userFeedback;

// Kint::dump($gui);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
// $smarty->assign('highlight',$gui->highlight);
renderGui($smarty,$args,$templateCfg);

/**
 *
 */
function init_args()
{
  $_REQUEST = strings_stripSlashes($_REQUEST);

  $iParams = array("rolename" => array("POST",tlInputParameter::STRING_N,0,100),
                   "roleid" => array("REQUEST",tlInputParameter::INT_N),
                   "doAction" => array("REQUEST",tlInputParameter::STRING_N,0,100),
                   "notes" => array("POST",tlInputParameter::STRING_N),
                   "grant" => array("POST",tlInputParameter::ARRAY_STRING_N));

  $args = new stdClass();
  $pParams = I_PARAMS($iParams,$args);
  $args->basehref = $_SESSION['basehref'];
  $args->user = $_SESSION['currentUser'];

  return $args;
}

/**
 *
 */
function doOperation(&$dbHandler,$argsObj,$operation)
{
  $op = new stdClass();
  $op->role = new tlRole();
  $op->userFeedback = null;
  $op->template = 'rolesEdit.tpl';

  switch($operation)
  {

    case 'doCreate':
    case 'doUpdate':
      $rights = implode("','",array_keys($argsObj->grant));
      $op->role->rights = tlRight::getAll($dbHandler,"WHERE description IN ('{$rights}')");
      $op->role->name = $argsObj->rolename;
      $op->role->description = $argsObj->notes;
      $op->role->dbID = $argsObj->roleid;
    break;

    case 'duplicate':
      $op->role = tlRole::getByID($dbHandler,$argsObj->roleid);
      $op->role->dbID = null;
      $op->role->name = generateUniqueName($op->role->name);

    break;
  }


  $result = $op->role->writeToDB($dbHandler);
  if ($result >= tl::OK)
  {
    $auditCfg = null;
    switch($operation)
    {
      case 'doCreate':
      case 'duplicate':
        $auditCfg['msg'] = "audit_role_created";
        $auditCfg['activity'] = "CREATE";
      break;
  
      case 'doUpdate':
        $auditCfg['msg'] = "audit_role_saved";
        $auditCfg['activity'] = "SAVE";
      break;
    }
    
    logAuditEvent(TLS($auditCfg['msg'],$op->role->name),$auditCfg['activity'],$op->role->dbID,"roles");
    $op->template = null;
  }
  else
  {
      $op->userFeedback = getRoleErrorMessage($result);
  }

  return $op;
}


function renderGui(&$smartyObj,&$argsObj,$templateCfg)
{
    $doRender = false;
    switch($argsObj->doAction)
    {
      case "edit":
      case "create":
        $doRender = true;
        $tpl = $templateCfg->default_template;
      break;

      case "doCreate":
      case "doUpdate":
        if(!is_null($templateCfg->template))
        {
          $doRender = true;
          $tpl = $templateCfg->template;
        }
        else
        {
          header("Location: rolesView.php");
          exit();
        }
      break;

      case "duplicate":
        header("Location: rolesView.php");
        exit();
      break;   
    }

    if($doRender)
    {
      $smartyObj->display($templateCfg->template_dir . $tpl);
    }
}


/*
  function: getRightsCfg

  args : -

  returns: object
  
*/
function getRightsCfg()
{
    $cfg = new stdClass();
    $cfg->tplan_mgmt = config_get('rights_tp');
    $cfg->tcase_mgmt = config_get('rights_mgttc');
    $cfg->kword_mgmt = config_get('rights_kw');
    $cfg->tproject_mgmt = config_get('rights_product');
    $cfg->user_mgmt = config_get('rights_users');
    $cfg->req_mgmt = config_get('rights_req');
    $cfg->cfield_mgmt = config_get('rights_cf');
    $cfg->system_mgmt = config_get('rights_system');
    $cfg->platform_mgmt = config_get('rights_platforms');
    $cfg->issuetracker_mgmt = config_get('rights_issuetrackers');
    $cfg->codetracker_mgmt = config_get('rights_codetrackers');
    $cfg->execution = config_get('rights_executions');
    // $cfg->reqmgrsystem_mgmt = config_get('rights_reqmgrsystems');

    return $cfg;
}


function initialize_gui(&$argsObj,$editorType)
{
    $gui = new stdClass();
    $gui->checkboxStatus = null;
    $gui->userFeedback = null;
    $gui->affectedUsers = null;
    $gui->highlight = initialize_tabsmenu();
    $gui->editorType = $editorType;
    $gui->roleCanBeEdited = ($argsObj->roleid != TL_ROLES_ADMIN);

    return $gui;
}

/**
 *
 */
function initialize_op()
{
  $op = new stdClass();
  $op->role = new tlRole();
  $op->userFeedback = '';
    
  return $op;
}

/**
 *
 */
function complete_gui(&$dbHandler,&$guiObj,&$argsObj,&$roleObj,&$webEditorObj)
{
  $actionCfg['operation'] = array('create' => 'doCreate', 'edit' => 'doUpdate',
                                  'doCreate' => 'doCreate', 'doUpdate' => 'doUpdate',
                                  'duplicate' => 'duplicate');

  $actionCfg['highlight'] = array('create' => 'create_role', 'edit' => 'edit_role',
                                  'doCreate' => 'create_role', 
                                  'doUpdate' => 'edit_role',
                                  'duplicate' => 'create_role');

  $guiObj->highlight = new stdClass();
  $kp = $actionCfg['highlight'][$argsObj->doAction];
  $guiObj->highlight->$kp = 1;
  $guiObj->operation = $actionCfg['operation'][$argsObj->doAction];

  $guiObj->role = $roleObj;
  $guiObj->grants = getGrantsForUserMgmt($dbHandler,$_SESSION['currentUser']);
  $guiObj->grants->mgt_view_events = $argsObj->user->hasRight($db,"mgt_view_events");
  $guiObj->rightsCfg = getRightsCfg();
  
  $guiObj->disabledAttr = $guiObj->roleCanBeEdited ? ' ' : ' disabled="disabled" '; 

  // Create status for all checkboxes and set to unchecked
  foreach($guiObj->rightsCfg as $grantDetails)
  {
    foreach($grantDetails as $grantCode => $grantDescription)
    {
      $guiObj->checkboxStatus[$grantCode] = "" . $guiObj->disabledAttr;
    }
  }

  if($roleObj->dbID)
  {
    $webEditorObj->Value = $roleObj->description;

    // build checked attribute for checkboxes
    if(sizeof($roleObj->rights))
    {
      foreach($roleObj->rights as $key => $right)
      {
        $guiObj->checkboxStatus[$right->name] = ' checked="checked" ' . $guiObj->disabledAttr;
      }
    }
      //get all users which are affected by changing the role definition
    $guiObj->affectedUsers = $roleObj->getAllUsersWithRole($dbHandler);
  }

  $guiObj->notes = $webEditorObj->CreateHTML();
  return $guiObj;
}

function generateUniqueName($s)
{
  // sorry for the magic, but anyway user has to edit role to provide desired name
  // IMHO this quick & dirty solution is OK
  return substr($s . ' - Copy - ' . substr(sha1(rand()), 0, 50),0,100);
}


/**
 *
 */
function initLabels()
{
  $tg = array('action_create_role' => null,'action_edit_role' => null);
  $labels = init_labels($tg);
  return $labels;
}

function checkRights(&$db,&$user)
{
  return $user->hasRight($db,"role_management");
}

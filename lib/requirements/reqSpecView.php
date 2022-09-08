<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  reqSpecView.php
 * @author      Martin Havlat
 *
 * Screen to view existing requirements within a req. specification.
 *
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once('requirements.inc.php');
require_once('attachments.inc.php');
require_once("configCheck.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args($db);
$gui = initialize_gui($db,$args);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 *
 */
function init_args($dbH)
{
  $iParams = [
    "tproject_id" => array(tlInputParameter::INT_N),
    "tplan_id" => array(tlInputParameter::INT_N),
    "req_spec_id" => array(tlInputParameter::INT_N),
    "refreshTree" => array(tlInputParameter::INT_N),
    "uploadOPStatusCode" => array(tlInputParameter::STRING_N,0,30)
  ];

  $args = new stdClass();
  R_PARAMS($iParams,$args);
  $args->refreshTree = intval($args->refreshTree);
  
  if (0==$args->tproject_id) {
    throw new Exception("Test Project ID can not be 0", 1);
  }
  $args->tproject_name = testproject::getName($dbH,$args->tproject_id);
  

  return $args;
}

/**
 * 
 *
 */
function initialize_gui(&$dbHandler,&$argsObj)
{
  $req_spec_mgr = new requirement_spec_mgr($dbHandler);
  $tproject_mgr = new testproject($dbHandler);
  $commandMgr = new reqSpecCommands($dbHandler,$argsObj->tproject_id);
                            
  
  $gui = $commandMgr->initGuiBean();

  $gui->tproject_id = $argsObj->tproject_id;
  $gui->tproject_name = $argsObj->tproject_name;
  $gui->tplan_id = $argsObj->tplan_id;

  $gui->refreshTree = $argsObj->refreshTree;
  $gui->req_spec_cfg = config_get('req_spec_cfg');
  $gui->req_cfg = config_get('req_cfg');
  $gui->external_req_management = ($gui->req_cfg->external_req_management == ENABLED) ? 1 : 0;
  
  $gui->grants = new stdClass();
  $gui->grants->req_mgmt = has_rights($db,"mgt_modify_req");

  $gui->req_spec = $req_spec_mgr->get_by_id($argsObj->req_spec_id);
  $gui->revCount = $req_spec_mgr->getRevisionsCount($argsObj->req_spec_id);
  
  $gui->req_spec_id = intval($argsObj->req_spec_id);
  $gui->parentID = $argsObj->req_spec_id;

  $gui->req_spec_revision_id = $gui->req_spec['revision_id'];
  $gui->name = $gui->req_spec['title'];
  
  
  $gui->main_descr = lang_get('req_spec_short') . config_get('gui_title_separator_1') . 
                     "[{$gui->req_spec['doc_id']}] :: " .$gui->req_spec['title'];

  $gui->refresh_tree = 'no';
  
  $gui->cfields = $req_spec_mgr->html_table_of_custom_field_values($gui->req_spec_id,
                                                                   $gui->req_spec_revision_id,
                                                                   $argsObj->tproject_id);
                                   
  $gui->attachments = getAttachmentInfosFrom($req_spec_mgr,$argsObj->req_spec_id);
  $gui->requirements_count = $req_spec_mgr->get_requirements_count($argsObj->req_spec_id);
  
  $gui->reqSpecTypeDomain = init_labels($gui->req_spec_cfg->type_labels);

  $prefix = $tproject_mgr->getTestCasePrefix($argsObj->tproject_id);
  $gui->direct_link = $_SESSION['basehref'] . 'linkto.php?tprojectPrefix=' . urlencode($prefix) . 
                      '&item=reqspec&id=' . urlencode($gui->req_spec['doc_id']);


  $gui->fileUploadURL = $_SESSION['basehref'] . $req_spec_mgr->getFileUploadRelativeURL($gui->req_spec_id,$gui->tproject_id);
  $gui->delAttachmentURL = $_SESSION['basehref'] . $req_spec_mgr->getDeleteAttachmentRelativeURL($gui->req_spec_id,$gui->tproject_id);
  $gui->fileUploadMsg = '';
  $gui->import_limit = TL_REPOSITORY_MAXFILESIZE;
  
  $cfg = new stdClass();
  $cfg->reqSpecCfg = getWebEditorCfg('requirement_spec');
  $gui->reqSpecEditorType = $cfg->reqSpecCfg['type'];

  $gui->btn_import_req_spec = '';
  $gui->reqMgrSystemEnabled = 0;
  if( !is_null($reqMgrSystem = $commandMgr->getReqMgrSystem()) ) {
    $gui->btn_import_req_spec = sprintf(lang_get('importViaAPI'),$reqMgrSystem['reqmgrsystem_name']);
    $gui->reqMgrSystemEnabled = 1;
  }

  $gui->uploadOp = null;
  if (trim($argsObj->uploadOPStatusCode) != '') {
    $gui->uploadOp = new stdClass();
    $gui->uploadOp->statusOK = false;
    $gui->uploadOp->statusCode = $argsObj->uploadOPStatusCode;
    $gui->uploadOp->msg = lang_get($argsObj->uploadOPStatusCode);
  }
  
  return $gui;
}


function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'mgt_view_req');
}
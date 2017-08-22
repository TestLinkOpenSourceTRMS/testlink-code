<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource	reqView.php
 * @author Martin Havlat
 * 
 * Screen to view content of requirement.
 *
 * @internal revisions
 * @since 1.9.10
 *
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('attachments.inc.php');
require_once('requirements.inc.php');
require_once('users.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$tproject_mgr = new testproject($db);

$args = init_args();
$gui = initialize_gui($db,$args,$tproject_mgr);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . 'reqViewVersions.tpl');

/**
 *
 */
function init_args()
{
  $_REQUEST=strings_stripSlashes($_REQUEST);
  $iParams = array("req_id" => array(tlInputParameter::INT_N),
                   "requirement_id" => array(tlInputParameter::INT_N),
                   "req_version_id" => array(tlInputParameter::INT_N),
                   "showReqSpecTitle" => array(tlInputParameter::INT_N),
                   "refreshTree" => array(tlInputParameter::INT_N),
                   "relation_add_result_msg" => array(tlInputParameter::STRING_N),
                   "user_feedback" => array(tlInputParameter::STRING_N));

  // new dBug($_REQUEST);

  $args = new stdClass();
  R_PARAMS($iParams,$args);

  if($args->req_id <= 0)
  {
    $args->req_id = $args->requirement_id;
  }  

  $args->refreshTree = intval($args->refreshTree);
  $args->tproject_id = intval(isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0);
  $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
  $args->user = $_SESSION['currentUser'];
  $args->userID = $args->user->dbID;

  // new dBug($args);
  return $args;
}

/**
 * 
 *
 */
function initialize_gui(&$dbHandler,$argsObj,&$tproject_mgr)
{
  $req_mgr = new requirement_mgr($dbHandler);
  $commandMgr = new reqCommands($dbHandler);

  $gui = $commandMgr->initGuiBean();

  $gui->req_cfg = config_get('req_cfg');
  $gui->glueChar = config_get('testcase_cfg')->glue_character;
  $gui->pieceSep = config_get('gui_title_separator_1');

  $gui->refreshTree = $argsObj->refreshTree;
  $gui->tproject_name = $argsObj->tproject_name;
  $gui->req_id = $argsObj->req_id;
    
  /* if wanted, show only the given version */
  $gui->version_option = ($argsObj->req_version_id) ? $argsObj->req_version_id : requirement_mgr::ALL_VERSIONS;
  $gui->req_versions = $req_mgr->get_by_id($gui->req_id, $gui->version_option,
                                           1,array('renderImageInline' => true));
  
  $gui->reqHasBeenDeleted = false;
  if( is_null($gui->req_versions) )
  {
    // this means that requirement does not exist anymore.
    // We have to give just that info to user
    $gui->reqHasBeenDeleted = true;
    $gui->main_descr = lang_get('req_does_not_exist');
    unset($gui->show_match_count);
    return $gui; // >>>----> Bye!
  }

  $tproject_id = $req_mgr->getTestProjectID($argsObj->requirement_id);
  $target_id = $argsObj->tproject_id; 
  if( ($isAlien = ($tproject_id != $argsObj->tproject_id)) )
  {
    $target_id = $tproject_id;
  } 
  $gui->grants = new stdClass();
  $gui->grants->req_mgmt = $argsObj->user->hasRight($dbHandler,"mgt_modify_req",$target_id);
  $gui->grants->unfreeze_req = $argsObj->user->hasRight($dbHandler,"mgt_unfreeze_req",$target_id);
  
  $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($argsObj->tproject_id);
  
  $gui->req = current($gui->req_versions);
  $gui->reqMonitors = $req_mgr->getReqMonitors($gui->req_id);

  $gui->btn_monitor_mgmt = 'Start Monitoring';
  $gui->btn_monitor_action = 'startMonitoring';
  if(isset($gui->reqMonitors[$argsObj->userID]))
  {
    $gui->btn_monitor_mgmt = 'Stop Monitoring';
    $gui->btn_monitor_action = 'stopMonitoring';
  }  

  $gui->req_coverage = $req_mgr->get_coverage($gui->req_id);
  $gui->direct_link = $_SESSION['basehref'] . 'linkto.php?tprojectPrefix=' . 
                      urlencode($gui->tcasePrefix) . '&item=req&id=' . urlencode($gui->req['req_doc_id']);


  $gui->fileUploadURL = $_SESSION['basehref'] . $req_mgr->getFileUploadRelativeURL($gui->req_id,$argsObj);
  $gui->delAttachmentURL = $_SESSION['basehref'] . $req_mgr->getDeleteAttachmentRelativeURL($gui->req_id);
  $gui->fileUploadMsg = '';
  $gui->import_limit = TL_REPOSITORY_MAXFILESIZE;

  $cfg = new stdClass();
  $cfg->reqCfg = getWebEditorCfg('requirement');
  $gui->reqEditorType = $cfg->reqCfg['type'];
  
  $gui->log_target = null;
  $loop2do = count($gui->req_versions);
  for($rqx = 0; $rqx < $loop2do; $rqx++)
  {
    $gui->log_target[] = ($gui->req_versions[$rqx]['revision_id'] > 0) ?  $gui->req_versions[$rqx]['revision_id'] :  
                          $gui->req_versions[$rqx]['version_id'];
  }
  
  $gui->req_has_history = count($req_mgr->get_history($gui->req_id, array('output' => 'array'))) > 1; 
  
  
  
  // This seems weird but is done to adapt template than can display multiple
  // requirements. This logic has been borrowed from test case versions management
  $gui->current_version[0] = array($gui->req);
  $gui->cfields_current_version[0] = $req_mgr->html_table_of_custom_field_values($gui->req_id,$gui->req['version_id'],
                                                                                 $argsObj->tproject_id);

  // Now CF for other Versions
  $gui->other_versions[0] = null;
  $gui->cfields_other_versions[] = null;
  if( count($gui->req_versions) > 1 )
  {
    $gui->other_versions[0] = array_slice($gui->req_versions,1);
    $loop2do = count($gui->other_versions[0]);
    for($qdx=0; $qdx < $loop2do; $qdx++)
   {
     $target_version = $gui->other_versions[0][$qdx]['version_id'];
     $gui->cfields_other_versions[0][$qdx]= 
       $req_mgr->html_table_of_custom_field_values($gui->req_id,$target_version,$argsObj->tproject_id);
   }
  }
  
  $gui->show_title = false;
  $gui->main_descr = lang_get('req') . $gui->pieceSep .  $gui->req['title'];
  
  $gui->showReqSpecTitle = $argsObj->showReqSpecTitle;
  if($gui->showReqSpecTitle)
  {
      $gui->parent_descr = lang_get('req_spec_short') . $gui->pieceSep . $gui->req['req_spec_title'];
  }
  
   $gui->attachments[$gui->req_id] = getAttachmentInfosFrom($req_mgr,$gui->req_id);
  
  $gui->attachmentTableName = $req_mgr->getAttachmentTableName();
  $gui->reqStatus = init_labels($gui->req_cfg->status_labels);
  $gui->reqTypeDomain = init_labels($gui->req_cfg->type_labels);

  $gui->req_relations = FALSE;
  $gui->req_relation_select = FALSE;
  $gui->testproject_select = FALSE;
  $gui->req_add_result_msg = isset($argsObj->relation_add_result_msg) ? 
                     $argsObj->relation_add_result_msg : "";
  
  if ($gui->req_cfg->relations->enable) 
  {
    $gui->req_relations = $req_mgr->get_relations($gui->req_id);
    $gui->req_relations['rw'] = !$isAlien;
    $gui->req_relation_select = $req_mgr->init_relation_type_select();
    if ($gui->req_cfg->relations->interproject_linking) 
    {
      $gui->testproject_select = initTestprojectSelect($argsObj->userID, $argsObj->tproject_id,$tproject_mgr);
    }
  }



  $gui->user_feedback = $argsObj->user_feedback;
  return $gui;
}


/**
 * 
 *
 */
function checkRights(&$dbHandler,&$user)
{
  return $user->hasRight($dbHandler,'mgt_view_req');
}


/**
 * Initializes the select field for the testprojects.
 * 
 * @return array $htmlSelect array with info, needed to create testproject select box on template
 */
function initTestprojectSelect($userID, $tprojectID, &$tprojectMgr) 
{
  $opt = array('output' => 'map_name_with_inactive_mark', 'order_by' => config_get('gui')->tprojects_combo_order_by);  
  $testprojects = $tprojectMgr->get_accessible_for_user($userID,$opt);
  $htmlSelect = array('items' => $testprojects, 'selected' => $tprojectID);
  return $htmlSelect;
}

?>
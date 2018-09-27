<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource	reqView.php
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
$req_mgr = new requirement_mgr($db);

$args = init_args($req_mgr);
$gui = initialize_gui($db,$args,$tproject_mgr,$req_mgr);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . 'reqViewVersions.tpl');

/**
 *
 */
function init_args( &$reqMgr ) {
  $_REQUEST=strings_stripSlashes($_REQUEST);
  $iParams = array("req_id" => array(tlInputParameter::INT_N),
                   "requirement_id" => array(tlInputParameter::INT_N),
                   "req_version_id" => array(tlInputParameter::INT_N),
                   "showReqSpecTitle" => array(tlInputParameter::INT_N),
                   "refreshTree" => array(tlInputParameter::INT_N),
                   "relation_add_result_msg" => array(tlInputParameter::STRING_N),
                   "user_feedback" => array(tlInputParameter::STRING_N));

  $args = new stdClass();
  R_PARAMS($iParams,$args);

  if($args->req_id <= 0) {
    $args->req_id = $args->requirement_id;
  }  

  $args->reqVersionIDFromCaller = $args->req_version_id;
  $args->showAllVersions = false;

  if( $args->req_version_id == 0 ) {
    $args->showAllVersions = true;
    $lv = $reqMgr->get_last_version_info($args->req_id);
    $args->req_version_id = intval($lv['id']);
  }

  $args->refreshTree = intval($args->refreshTree);
  $args->tproject_id = intval(isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0);
  $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
  $args->user = $_SESSION['currentUser'];
  $args->userID = $args->user->dbID;

  return $args;
}

/**
 * 
 *
 */
function initialize_gui(&$dbHandler,$argsObj,&$tproject_mgr,&$req_mgr) {
  $commandMgr = new reqCommands($dbHandler);

  $gui = $commandMgr->initGuiBean( $argsObj );

  $opt = array('renderImageInline' => true);
  $gui->req_versions = 
    $req_mgr->get_by_id($gui->req_id, $gui->version_option,1,$opt);
  
  $gui->reqHasBeenDeleted = false;
  if( is_null($gui->req_versions) ) {
    // this means that requirement does not exist anymore.
    // We have to give just that info to user
    $gui->reqHasBeenDeleted = true;
    $gui->main_descr = lang_get('req_does_not_exist');
    unset($gui->show_match_count);
    return $gui; // >>>----> Bye!
  }

  // Everything OK, go ahead
  $tproject_id = $req_mgr->getTestProjectID($argsObj->requirement_id);
  $target_id = $argsObj->tproject_id; 
  if( ($isAlien = ($tproject_id != $argsObj->tproject_id)) ) {
    $target_id = $tproject_id;
  } 
  
  $gui->grants = getGrants($dbHandler,$argsObj->user,$target_id);
  $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($argsObj->tproject_id);

  
  $gui->reqMonitors = $req_mgr->getReqMonitors($gui->req_id);
  $gui->btn_monitor_mgmt = lang_get('btn_start_mon');
  $gui->btn_monitor_action = 'startMonitoring';
  if(isset($gui->reqMonitors[$argsObj->userID])) {
    $gui->btn_monitor_mgmt = lang_get('btn_stop_mon');
    $gui->btn_monitor_action = 'stopMonitoring';
  }  

  $gui->req = current($gui->req_versions);

  // 2018 $gui->req_coverage = $req_mgr->get_coverage($gui->req_id);  
  // This need to become an array.
  $loop2do = count($gui->req_versions);
  $gui->current_req_coverage = array();
  $gui->other_req_coverage = array();
  for($cvx = 0 ; $cvx < $loop2do; $cvx++) {
    $bebe = $gui->req_versions[$cvx]['version_id'];
    
    if( $cvx == 0 ) {
      $gui->current_req_coverage = $req_mgr->getActiveForReqVersion($bebe);
    } else {
      $gui->other_req_coverage[0][] = $req_mgr->getActiveForReqVersion($bebe);
    }
  }

  $gui->direct_link = $_SESSION['basehref'] . 'linkto.php?tprojectPrefix=' . 
                      urlencode($gui->tcasePrefix) . '&item=req&id=' . urlencode($gui->req['req_doc_id']);

  $gui->fileUploadURL = $gui->delAttachmentURL = $_SESSION['basehref'];
  $gui->fileUploadURL .= 
    $req_mgr->getFileUploadRelativeURL($gui->req_id, $gui->req_version_id);

  $gui->delAttachmentURL .= 
    $req_mgr->getDeleteAttachmentRelativeURL($gui->req_id, $gui->req_version_id);

  
  $gui->log_target = null;
  $loop2do = count($gui->req_versions);
  for($rqx = 0; $rqx < $loop2do; $rqx++) {
    $gui->log_target[] = ($gui->req_versions[$rqx]['revision_id'] > 0) ?  $gui->req_versions[$rqx]['revision_id'] :  
                          $gui->req_versions[$rqx]['version_id'];
  }
  
  $gui->req_has_history = count($req_mgr->get_history($gui->req_id, array('output' => 'array'))) > 1; 
  
  
  // This seems weird but is done to adapt template than can 
  // display multiple requirements. 
  // This logic has been borrowed from test case versions management
  $gui->current_version[0] = array($gui->req);
  $gui->cfields_current_version[0] = 
    $req_mgr->html_table_of_custom_field_values($gui->req_id,$gui->req['version_id'],
                                                $argsObj->tproject_id);

  // Now CF for other Versions
  $gui->other_versions[0] = null;
  $gui->cfields_other_versions[] = null;
  if( count($gui->req_versions) > 1 ) {
    $gui->other_versions[0] = array_slice($gui->req_versions,1);
    $loop2do = count($gui->other_versions[0]);
    for($qdx=0; $qdx < $loop2do; $qdx++) {
     $target_version = $gui->other_versions[0][$qdx]['version_id'];
     $gui->cfields_other_versions[0][$qdx]= 
       $req_mgr->html_table_of_custom_field_values($gui->req_id,$target_version,$argsObj->tproject_id);
    }
  }
  
  $gui->show_title = false;
  $gui->main_descr = lang_get('req') . $gui->pieceSep .  $gui->req['title'];
  
  $gui->showReqSpecTitle = $argsObj->showReqSpecTitle;
  if($gui->showReqSpecTitle) {
    $gui->parent_descr = lang_get('req_spec_short') . $gui->pieceSep . $gui->req['req_spec_title'];
  }
  
  
  if( $gui->showAllVersions ) {
    $versionSet = array();
    $loop2do = count($gui->req_versions);    
    for( $ggx=0; $ggx < $loop2do; $ggx++ ) {
      $versionSet[] = intval($gui->req_versions[$ggx]['version_id']);
    }
  } else {
    $versionSet = array($gui->req_version_id);    
  }

  foreach ($versionSet as $kiwi) {
    $gui->attachments[$kiwi] = getAttachmentInfosFrom($req_mgr,$kiwi);
  }

  $gui->reqStatus = init_labels($gui->req_cfg->status_labels);
  $gui->reqTypeDomain = init_labels($gui->req_cfg->type_labels);

  $gui->req_relations = FALSE;
  $gui->req_relation_select = FALSE;
  $gui->testproject_select = FALSE;
  $gui->req_add_result_msg = isset($argsObj->relation_add_result_msg) ? 
                     $argsObj->relation_add_result_msg : "";
  
  if ($gui->req_cfg->relations->enable) {
    $gui->req_relations = $req_mgr->get_relations($gui->req_id);
    $gui->req_relations['rw'] = !$isAlien;
    $gui->req_relation_select = $req_mgr->init_relation_type_select();
    if ($gui->req_cfg->relations->interproject_linking) {
      $gui->testproject_select = initTestprojectSelect($argsObj->userID, $argsObj->tproject_id,$tproject_mgr);
    }
  }

  return $gui;
}


/**
 * 
 *
 */
function getGrants( &$dbH, &$userObj, $tproject_id ) {

  $grants = new stdClass();
  $gk = array('req_mgmt' => "mgt_modify_req", 'monitor_req' => "monitor_requirement",
              'req_tcase_link_management' => 'req_tcase_link_management',
              'unfreeze_req' => 'mgt_unfreeze_req');

  foreach($gk as $p => $g) {
    $grants->$p = $userObj->hasRight($dbH,$g,$tproject_id);
  }
  return $grants;
}


/**
 * 
 *
 */
function checkRights(&$dbHandler,&$user) {
  return $user->hasRight($dbHandler,'mgt_view_req');
}


/**
 * Initializes the select field for the testprojects.
 * 
 * @return array $htmlSelect array with info, needed to create testproject select box on template
 */
function initTestprojectSelect($userID, $tprojectID, &$tprojectMgr)  {
  $opt = array('output' => 'map_name_with_inactive_mark', 'order_by' => config_get('gui')->tprojects_combo_order_by);  
  $testprojects = $tprojectMgr->get_accessible_for_user($userID,$opt);
  $htmlSelect = array('items' => $testprojects, 'selected' => $tprojectID);
  return $htmlSelect;
}
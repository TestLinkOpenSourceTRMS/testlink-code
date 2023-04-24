<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Manages test plans
 *
 * @package   TestLink
 * @copyright 2007-2023, TestLink community 
 * @version   planEdit.php
 * @link      http://www.testlink.org/
 *
 *
 **/

require_once('../../config.inc.php');
require_once("common.php");
require_once("planViewUtils.php");
require_once("web_editor.php");
$editorCfg = getWebEditorCfg('testplan');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db);

$templateCfg = templateConfiguration();
$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$smarty = new TLSmarty();
$do_display = false;
$template = null;

$args = init_args($db,$tplan_mgr);
$gui = initializeGui($db,$args,$editorCfg,$tproject_mgr);

$of = web_editor('notes',$_SESSION['basehref'],$editorCfg);
$of->Value = getItemTemplateContents('testplan_template', 
                 $of->InstanceName, $args->notes);

// Checks on testplan name, and testplan name<=>testplan id
if ($args->do_action == "do_create" || $args->do_action == "do_update") {
  $gui->testplan_name = $args->testplan_name;
  $name_exists = $tproject_mgr->check_tplan_name_existence(
                   $args->tproject_id,$args->testplan_name);
  $name_id_rel_ok = (isset($gui->tplans[$args->itemID]) && 
                     $gui->tplans[$args->itemID]['name'] == $args->testplan_name);
}

$uploadOp = null;
switch ($args->do_action) {
  case 'fileUpload':
    $uploadOp = fileUploadManagement($db,$args->itemID,
                  $args->fileTitle,
                  $tplan_mgr->getAttachmentTableName());
  break;

  case 'deleteFile':
    deleteAttachment($db,$args->file_id);
  break;

  case 'edit':
  break;

  case 'do_delete':
    $tplanInfo = $tplan_mgr->get_by_id($args->itemID);
    if ($tplanInfo) {
      $tplan_mgr->delete($args->itemID);
      logAuditEvent(TLS("audit_testplan_deleted",$args->tproject_name,
                    $tplanInfo['name']),
                    "DELETE",$args->itemID,"testplan");
    }
  break;

  case 'do_update':
    $of->Value = $args->notes;
    $gui->testplan_name = $args->testplan_name;
    $gui->is_active = ($args->active == 'on') ? 1 :0 ;
    $gui->is_public = ($args->is_public == 'on') ? 1 :0 ;

    $template = 'planEdit.tpl';
    $status_ok = false;
    
    if (!$name_exists || $name_id_rel_ok) {
      if(!$tplan_mgr->update($args->itemID,$args->testplan_name,$args->notes,
                             $args->active,$args->is_public)) {
        $gui->user_feedback = lang_get('update_tp_failed1'). 
                              $gui->testplan_name . 
                              lang_get('update_tp_failed2').": " . $db->error_msg() . "<br />";
      } else {
        logAuditEvent(TLS("audit_testplan_saved",$args->tproject_name,$args->testplan_name),"SAVE",
                          $args->itemID,"testplans");
        $cf_map = $tplan_mgr->get_linked_cfields_at_design($args->itemID);
        $tplan_mgr->cfield_mgr->design_values_to_db($_REQUEST,$args->itemID,$cf_map);

        $status_ok = true;
        $template = null;

        if (!$args->is_public) {
          $tprojectEffectiveRole = $args->user->getEffectiveRole($db,$args->tproject_id,null);

          // does user have an SPECIFIC role on TestPlan ?
          // if answer is yes => do nothing
          if(!tlUser::hasRoleOnTestPlan($db,$args->user_id,$args->itemID)) {  
            $tplan_mgr->addUserRole($args->user_id,$args->itemID,$tprojectEffectiveRole->dbID);
          }  
        }
      }
    } else {
      $gui->user_feedback = lang_get("warning_duplicate_tplan_name");
    }
    
    if(!$status_ok) {
      $gui->itemID = $args->itemID;
      $gui->tproject_name = $args->tproject_name;
      $gui->notes = $of->CreateHTML();
    }
  break;

  case 'do_create':
    $template = 'planEdit.tpl';
    $status_ok = false;

    $of->Value = $args->notes;
    $gui->testplan_name = $args->testplan_name;
    $gui->is_active = ($args->active == 'on') ? 1 :0 ;
    $gui->is_public = ($args->is_public == 'on') ? 1 :0 ;
    
    if (!$name_exists) {
      $new_tplan_id = $tplan_mgr->create($args->testplan_name,
                                         $args->notes,
                                         $args->tproject_id,
                                         $args->active,
                                         $args->is_public);
      if ($new_tplan_id == 0) {
        $gui->user_feedback = $db->error_msg();
      } else {
        logAuditEvent(TLS("audit_testplan_created",$args->tproject_name,$args->testplan_name),
                      "CREATED",$new_tplan_id,"testplans");
        $cf_map = $tplan_mgr->get_linked_cfields_at_design($new_tplan_id,$args->tproject_id);
        $tplan_mgr->cfield_mgr->design_values_to_db($_REQUEST,$new_tplan_id,$cf_map);

        $status_ok = true;
        $template = null;
        $gui->user_feedback ='';

        // Operations Order is CRITIC  
        if ($args->copy) {
          $options = array('items2copy' => $args->copy_options,
                           'copy_assigned_to' => $args->copy_assigned_to,
                           'tcversion_type' => $args->tcversion_type);
          $tplan_mgr->copy_as($args->source_tplanid, 
                              $new_tplan_id,$args->testplan_name,
                              $args->tproject_id,$args->user_id,$options);
        }

        if (!$args->is_public) {
          // does user have an SPECIFIC role on TestPlan ?
          // if answer is yes => do nothing
          if(!tlUser::hasRoleOnTestPlan($db,$args->user_id,$new_tplan_id)) {  
            $effectiveRole = $args->user->getEffectiveRole($db,$args->tproject_id,null);
            $tplan_mgr->addUserRole($args->user_id,$new_tplan_id,$effectiveRole->dbID);
          }  
        }
        // End critic block
      }
    } else {
      $gui->user_feedback = lang_get("warning_duplicate_tplan_name");
    }
    
    if(!$status_ok) {
      $gui->tproject_name = $args->tproject_name;
      $gui->notes = $of->CreateHTML();
    }
  break;

  case 'setActive':
  case 'setInactive':
    $dynMethod = $args->do_action;
    $tplan_mgr->$dynMethod($args->itemID);
  break;
  
  case 'setActiveBulk':
  case 'setInactiveBulk':
    if (count($args->tplan2use) > 0) {
      $dynMethod = str_replace('Bulk','',$args->do_action);
      $tplan_mgr->$dynMethod($args->tplan2use);
    }
  break;
  


}


// This is CRITIC to update the left side menu
$argsUX = $args;
$opt = array('caller' => 'planEdit');
list($add2args,$ux) = initUserEnv($db,$argsUX,$opt);
$gui->uri = $ux->uri;

$createNotesHTML = false;
switch ($args->do_action) {
  case "do_create":
  case "do_delete":
  case "do_update":
  case "list":
  case 'setActive':
  case 'setInactive':
  case 'setActiveBulk':
  case 'setInactiveBulk':
    $do_display = true;
    $template = is_null($template) ? 'planView.tpl' : $template;

    $gui->tplans = $args->user->getAccessibleTestPlans(
      $db,$args->tproject_id,null,
      array('output' =>'mapfull','active' => null));

    $gui->drawPlatformQtyColumn = false;

    // 20200308 - DO REFACTOR PLEASE
    if( !is_null($gui->tplans) ) {
      // do this test project has platform definitions ?
      $tplan_mgr->platform_mgr->setTestProjectID($args->tproject_id);
      $dummy = $tplan_mgr->platform_mgr->testProjectCount();
      $gui->drawPlatformQtyColumn = $dummy[$args->tproject_id]['platform_qty'] > 0;
  
      $tplanSet = array_keys($gui->tplans);
      $dummy = $tplan_mgr->count_testcases($tplanSet,null,array('output' => 'groupByTestPlan'));
      $buildQty = $tplan_mgr->get_builds($tplanSet,null,null,array('getCount' => true));

      $rightSet = array('testplan_user_role_assignment');
      foreach ($tplanSet as $idk) {
        $gui->tplans[$idk]['tcase_qty'] = isset($dummy[$idk]['qty']) ? intval($dummy[$idk]['qty']) : 0;
        $gui->tplans[$idk]['build_qty'] = isset($buildQty[$idk]['build_qty']) ? intval($buildQty[$idk]['build_qty']) : 0;
        if ($gui->drawPlatformQtyColumn) {
          $plat = $tplan_mgr->getPlatforms($idk);
          $gui->tplans[$idk]['platform_qty'] = is_null($plat) ? 0 : count($plat);
        }
  
        // Get rights for each test plan
        foreach ($rightSet as $target) {
          // DEV NOTE - CRITIC
          // I've made a theorically good performance choice to 
          // assign to $roleObj a reference to different roleObj
          // UNFORTUNATELLY this choice was responsible to destroy point object
          // since second LOOP
          $roleObj = null;
          if ($gui->tplans[$idk]['has_role'] > 0) {
            $the_role = $gui->tplans[$idk]['has_role'];
            if( isset($args->user->tplanRoles[$the_role]) ) { 
              $roleObj = $args->user->tplanRoles[$the_role];
            } else {
              // Need To review this comment
              // session cache has not still updated => get from DB ?
              $roleObj = $args->user->getEffectiveRole($db,$args->tproject_id,$idk);
            }  
          }  
          else if (!is_null($args->user->tprojectRoles) && 
                   isset($args->user->tprojectRoles[$args->tproject_id])){
            $roleObj = $args->user->tprojectRoles[$args->tproject_id];
          }  

          if (is_null($roleObj)) {
            $roleObj = $args->user->globalRole;
          }  
          $gui->tplans[$idk]['rights'][$target] = $roleObj->hasRight($target);  
        }  
      }   
    }
    break;

   case "edit":
   case "create":
   case 'fileUpload':
   case 'deleteFile':
     $do_display = true;
     $template = is_null($template) ? 'planEdit.tpl' : $template;
     $createNotesHTML = true;
   break;
}

if ($do_display) {
  $gui = initializeGui($db,$args,$editorCfg,$tproject_mgr);

  $gui->uploadOp = $uploadOp;
  if ($gui->doViewReload = ($template == 'planView.tpl')) {
    $gui->getTestPlans = true;
    planViewGUIInit($db,$args,$gui,$tplan_mgr);
  }

  switch ($args->do_action) {
   case "edit":
   case 'fileUpload':
   case 'deleteFile':
     getItemData($tplan_mgr,$gui,$of,$args->itemID);
   break;
  }
   
  if ($createNotesHTML) {
    $gui->notes = $of->CreateHTML();
  }

  $smarty->assign('gui',$gui);
  $smarty->display($templateCfg->template_dir . $template);
}


/*
 * INITialize page ARGuments, using the $_REQUEST and $_SESSION
 * super-global hashes.
 * Important: changes in HTML input elements on the Smarty template
 *            must be reflected here.
 *
 *
 * @return    object with html values tranformed and other
 *                   generated variables.
 *
 */
function init_args(&$dbH,&$tplanMgr)
{
  $_REQUEST = strings_stripSlashes($_REQUEST);
  list($args,$env) = initContext();

  $nullable_keys = array('testplan_name',
                         'notes','rights',
                         'active','do_action');

  foreach ($nullable_keys as $value) {
    $args->$value = isset($_REQUEST[$value]) ? 
                    trim($_REQUEST[$value]) : null;
  }

  $checkboxes_keys = array('is_public' => 0,'active' => 0);
  foreach ($checkboxes_keys as $key => $value) {
    $args->$key = isset($_REQUEST[$key]) ? 1 : 0;
  }

  $intval_keys = array('copy_from_tplan_id' => 0,'tplan_id' => 0);
  foreach ($intval_keys as $key => $value) {
    $args->$key = isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : $value;
  }
  $args->source_tplanid = $args->copy_from_tplan_id;
  $args->copy = ($args->copy_from_tplan_id > 0) ? TRUE : FALSE;

  $args->copy_options=array();
  $boolean_keys = array('copy_tcases' => 0,
                        'copy_priorities' => 0,
                        'copy_milestones' => 0, 
                        'copy_user_roles' => 0, 
                        'copy_builds' => 0, 
                        'copy_platforms_links' => 0,
                        'copy_attachments' => 0);

  foreach ($boolean_keys as $key => $value) {
    $args->copy_options[$key] = isset($_REQUEST[$key]) ? 1 : 0;
  }

  $args->copy_assigned_to = isset($_REQUEST['copy_assigned_to']) ? 1 : 0;
  $args->tcversion_type = isset($_REQUEST['tcversion_type']) ? $_REQUEST['tcversion_type'] : null;

  // all has to be refactored this way  
  $iParams = array("tproject_id" => array(tlInputParameter::INT_N),
                   "tplan_id" => array(tlInputParameter::INT_N),
                   "itemID" => array(tlInputParameter::INT_N),
                   "file_id" => array(tlInputParameter::INT_N),
                   "fileTitle" => array(tlInputParameter::STRING_N,0,100));
  R_PARAMS($iParams,$args);


  // For certain actions this is the plan we are working on
  switch($args->do_action) {
    case "do_delete":
    case "do_update":
    case "list":
    case 'setActive':
    case 'setInactive':
    default:
      $checkItemID = true;
    break;

    case "edit":
    case "create":
    case "do_create":
    case 'fileUpload':
    case 'deleteFile':
    case 'setActiveBulk':
    case 'setInactiveBulk':
      $checkItemID = false;    
    break;
  }

  if ($checkItemID && $args->itemID <= 0) {
    throw new Exception("BAD Test Plan ID (in itemID)", 1);
  }

  if ($args->tproject_id <= 0) {
    $info = $tplanMgr->get_by_id($args->itemID);
    $args->tproject_id = $info['testproject_id'];
  }   
  $args->tproject_name = testproject::getName($dbH,$args->tproject_id);

  $args->user_id = intval($_SESSION['userID']);
  $args->user = $_SESSION['currentUser'];

  $args->tplan2use = [];
  if (isset($_REQUEST["tplan2use"])) {
    $args->tplan2use = array_keys($_REQUEST["tplan2use"]);
  }

  // ----------------------------------------------------------------
  // Feature Access Check
  // This feature is affected only for right at Test Project Level
  $env = ['script' => basename(__FILE__),
          'tproject_id' => $args->tproject_id];
  $args->user->checkGUISecurityClearance($dbH,$env,
                    array('mgt_testplan_create'),'and');
  // ----------------------------------------------------------------

  return $args;
}

/**
 * initializeGui
 *
 */
function initializeGui(&$dbHandler,&$argsObj,&$editorCfg,
                       &$tprojectMgr)
{
  $tplan_mgr = new testplan($dbHandler);
  $opt = array('caller' => basename(__FILE__) . '::' . __FUNCTION__);
  list($add2args,$guiObj) = initUserEnv($dbHandler,$argsObj,$opt); 

  $guiObj->uploadOp  = null;
  $guiObj->activeMenu['plans'] = 'active';
  $guiObj->tproject_id = intval($argsObj->tproject_id); 
  $guiObj->itemID = intval($argsObj->itemID);

  $ctx = new stdClass();
  $ctx->tproject_id = $guiObj->tproject_id;
  $ctx->tplan_id = $guiObj->tplan_id;
  $guiObj->actions = $tplan_mgr->getViewActions($ctx);

  $guiObj->editorType = $editorCfg['type'];
  $guiObj->tplans = $argsObj->user->getAccessibleTestPlans(
                      $dbHandler,
                      $argsObj->tproject_id,
                      null,
                      array('output' =>'mapfull','active' => null));
  $guiObj->tproject_name = $argsObj->tproject_name;
  $guiObj->main_descr = lang_get('testplan_title_tp_management'). " - " .
                       lang_get('testproject') . ' ' . $argsObj->tproject_name;

  
  // $guiObj->attachments[$guiObj->tplan_id] = getAttachmentInfosFrom($tplan_mgr,$guiObj->tplan_id);
  $guiObj->attachments = 
    getAttachmentInfosFrom($tplan_mgr,$guiObj->tplan_id);
  $guiObj->attachmentTableName = $tplan_mgr->getAttachmentTableName();
  

  $guiObj->fileUploadURL = $_SESSION['basehref'] . $tplan_mgr->getFileUploadRelativeURL($guiObj->tplan_id,$guiObj->tproject_id);
  $guiObj->delAttachmentURL = $_SESSION['basehref'] . $tplan_mgr->getDeleteAttachmentRelativeURL($guiObj->tplan_id,$guiObj->tproject_id);

  $guiObj->fileUploadMsg = '';
  $guiObj->import_limit = TL_REPOSITORY_MAXFILESIZE;

  $guiObj->user_feedback = '';               

  $guiObj->userGrants = new stdClass();
  $guiObj->userGrants->testplan_create = 
    $argsObj->user->hasRight($dbHandler,"mgt_testplan_create",$argsObj->tproject_id);
  $guiObj->userGrants->mgt_view_events = 
    $argsObj->user->hasRight($dbHandler,"mgt_view_events");

  if ($argsObj->do_action != 'edit') {
    $guiObj->testplan_name = null;
    $guiObj->is_active = 0;
    $guiObj->is_public = 0;
    $guiObj->cfields = '';
    $guiObj->notes = '';    
  } else {

    $guiObj->cfields =  
      $tplan_mgr->customFieldInputsForUX(
         $argsObj->itemID,$argsObj->tproject_id,
         'design','',$_REQUEST);


      /*$tplan_mgr->html_table_of_custom_field_inputs(
         $argsObj->itemID,$argsObj->tproject_id,
         'design','',$_REQUEST);*/


  }
 


  return $guiObj;
}

/**
 *
 */
function getItemData(&$itemMgr,&$guiObj,&$ofObj,$itemID,
                     $updateAttachments=false)
{
  $dummy = $itemMgr->get_by_id($itemID);
  if (sizeof($dummy)) {

    $guiObj->testplan_name = $dummy['name'];
    $guiObj->is_active = $dummy['active'];
    $guiObj->is_public = $dummy['is_public'];
    $guiObj->api_key = $dummy['api_key'];
    $guiObj->tplan_id = $itemID;
    $guiObj->notes = $dummy['notes'];

    $ofObj->Value = $dummy['notes'];

    if($updateAttachments) {  
      $guiObj->attachments = 
        getAttachmentInfosFrom($itemMgr,$guiObj->tplan_id);
    }
  }
}
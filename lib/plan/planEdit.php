<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Manages test plans
 *
 * @package   TestLink
 * @copyright 2007-2014, TestLink community 
 * @version   planEdit.php
 * @link      http://www.testlink.org/
 *
 *
 * @internal revisions
 * @since 1.9.13
 **/

require_once('../../config.inc.php');
require_once("common.php");
require_once("web_editor.php");
$editorCfg = getWebEditorCfg('testplan');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$smarty = new TLSmarty();
$do_display=false;
$template = null;
$args = init_args($_REQUEST);
if (!$args->tproject_id)
{
  $smarty->assign('title', lang_get('fatal_page_title'));
  $smarty->assign('content', lang_get('error_no_testprojects_present'));
  $smarty->display('workAreaSimple.tpl');
  exit();
}

$gui = initializeGui($db,$args,$editorCfg,$tproject_mgr);
$of = web_editor('notes',$_SESSION['basehref'],$editorCfg);
$of->Value = getItemTemplateContents('testplan_template', $of->InstanceName, $args->notes);


// Checks on testplan name, and testplan name<=>testplan id
if($args->do_action == "do_create" || $args->do_action == "do_update")
{
  $gui->testplan_name = $args->testplan_name;
  $name_exists = $tproject_mgr->check_tplan_name_existence($args->tproject_id,$args->testplan_name);
  $name_id_rel_ok = (isset($gui->tplans[$args->tplan_id]) && 
                     $gui->tplans[$args->tplan_id]['name'] == $args->testplan_name);
}

// interface changes to be able to do not loose CF values if some problem arise on User Interface
$gui->cfields = $tplan_mgr->html_table_of_custom_field_inputs($args->tplan_id,$args->tproject_id,'design','',$_REQUEST);

switch($args->do_action)
{
  case 'fileUpload':
    fileUploadManagement($db,$args->tplan_id,$args->fileTitle,$tplan_mgr->getAttachmentTableName());
    getItemData($tplan_mgr,$gui,$of,$args->tplan_id,true);
  break;

  case 'deleteFile':
    deleteAttachment($db,$args->file_id);
    getItemData($tplan_mgr,$gui,$of,$args->tplan_id,true);
  break;

  case 'edit':
    getItemData($tplan_mgr,$gui,$of,$args->tplan_id);
  break;

  case 'do_delete':
    $tplanInfo = $tplan_mgr->get_by_id($args->tplan_id);
    if ($tplanInfo)
    {
      $tplan_mgr->delete($args->tplan_id);
      logAuditEvent(TLS("audit_testplan_deleted",$args->tproject_name,$tplanInfo['name']),
                    "DELETE",$args->tplan_id,"testplan");
    }

    //unset the session test plan if it is deleted
    if (isset($_SESSION['testplanID']) && ($_SESSION['testplanID'] = $args->tplan_id))
    {
      $_SESSION['testplanID'] = 0;
      $_SESSION['testplanName'] = null;
    }
  break;

  case 'do_update':
    $of->Value = $args->notes;
    $gui->testplan_name = $args->testplan_name;
    $gui->is_active = ($args->active == 'on') ? 1 :0 ;
    $gui->is_public = ($args->is_public == 'on') ? 1 :0 ;

    $template = 'planEdit.tpl';
    $status_ok = false;
    
    if(!$name_exists || $name_id_rel_ok)
    {
      if(!$tplan_mgr->update($args->tplan_id,$args->testplan_name,$args->notes,
                             $args->active,$args->is_public))
      {
        $gui->user_feedback = lang_get('update_tp_failed1'). $gui->testplan_name . 
                              lang_get('update_tp_failed2').": " . $db->error_msg() . "<br />";
      }
      else
      {
        logAuditEvent(TLS("audit_testplan_saved",$args->tproject_name,$args->testplan_name),"SAVE",
                          $args->tplan_id,"testplans");
        $cf_map = $tplan_mgr->get_linked_cfields_at_design($args->tplan_id);
        $tplan_mgr->cfield_mgr->design_values_to_db($_REQUEST,$args->tplan_id,$cf_map);

        if(isset($_SESSION['testplanID']) && ($args->tplan_id == $_SESSION['testplanID']))
        {
          $_SESSION['testplanName'] = $args->testplan_name;
        }
        $status_ok = true;
        $template = null;

        if(!$args->is_public)
        {
          $tprojectEffectiveRole = $args->user->getEffectiveRole($db,$args->tproject_id,null);

          // does user have an SPECIFIC role on TestPlan ?
          // if answer is yes => do nothing
          if(!tlUser::hasRoleOnTestPlan($db,$args->user_id,$args->tplan_id))
          {  
            $tplan_mgr->addUserRole($args->user_id,$args->tplan_id,$tprojectEffectiveRole->dbID);
          }  
        }
      }
    }
    else
    {
      $gui->user_feedback = lang_get("warning_duplicate_tplan_name");
    }
    
    if(!$status_ok)
    {
      $gui->tplan_id=$args->tplan_id;
      $gui->tproject_name=$args->tproject_name;
      $gui->notes=$of->CreateHTML();
    }
  break;

  case 'do_create':
    $template = 'planEdit.tpl';
    $status_ok = false;

    $of->Value = $args->notes;
    $gui->testplan_name = $args->testplan_name;
    $gui->is_active = ($args->active == 'on') ? 1 :0 ;
    $gui->is_public = ($args->is_public == 'on') ? 1 :0 ;
    
    if(!$name_exists)
    {
      $new_tplan_id = $tplan_mgr->create($args->testplan_name,$args->notes,
                                         $args->tproject_id,$args->active,$args->is_public);
      if ($new_tplan_id == 0)
      {
        $gui->user_feedback = $db->error_msg();
      }
      else
      {
        logAuditEvent(TLS("audit_testplan_created",$args->tproject_name,$args->testplan_name),
                      "CREATED",$new_tplan_id,"testplans");
        $cf_map = $tplan_mgr->get_linked_cfields_at_design($new_tplan_id,$args->tproject_id);
        $tplan_mgr->cfield_mgr->design_values_to_db($_REQUEST,$new_tplan_id,$cf_map);

        $status_ok = true;
        $template = null;
        $gui->user_feedback ='';

        // Operations Order is CRITIC  
        if($args->copy)
        {
          $options = array('items2copy' => $args->copy_options,'copy_assigned_to' => $args->copy_assigned_to,
                           'tcversion_type' => $args->tcversion_type);
          $tplan_mgr->copy_as($args->source_tplanid, $new_tplan_id,$args->testplan_name,
                              $args->tproject_id,$args->user_id,$options);
        }

        if(!$args->is_public)
        {
          // does user have an SPECIFIC role on TestPlan ?
          // if answer is yes => do nothing
          if(!tlUser::hasRoleOnTestPlan($db,$args->user_id,$new_tplan_id))
          {  
            $effectiveRole = $args->user->getEffectiveRole($db,$args->tproject_id,null);
            $tplan_mgr->addUserRole($args->user_id,$new_tplan_id,$effectiveRole->dbID);
          }  
        }
        // End critic block

      }
    }
    else
    {
      $gui->user_feedback = lang_get("warning_duplicate_tplan_name");
    }
    
    if(!$status_ok)
    {
      // $gui->tplan_id=$new_tplan_id;
      $gui->tproject_name=$args->tproject_name;
      $gui->notes=$of->CreateHTML();
    }
  break;

  case 'setActive':
    $tplan_mgr->setActive($args->tplan_id);
  break;

  case 'setInactive':
    $tplan_mgr->setInactive($args->tplan_id);
  break;

}

switch($args->do_action)
{
  case "do_create":
  case "do_delete":
  case "do_update":
  case "list":
  case 'setActive':
  case 'setInactive':
    $do_display=true;
    $template = is_null($template) ? 'planView.tpl' : $template;
    $gui->tplans = $args->user->getAccessibleTestPlans($db,$args->tproject_id,null,
                                                           array('output' =>'mapfull','active' => null));
    $gui->drawPlatformQtyColumn = false;
    if( !is_null($gui->tplans) )
    {
      // do this test project has platform definitions ?
      $tplan_mgr->platform_mgr->setTestProjectID($args->tproject_id);
      $dummy = $tplan_mgr->platform_mgr->testProjectCount();
      $gui->drawPlatformQtyColumn = $dummy[$args->tproject_id]['platform_qty'] > 0;
  
      $tplanSet = array_keys($gui->tplans);
      $dummy = $tplan_mgr->count_testcases($tplanSet,null,array('output' => 'groupByTestPlan'));
      $buildQty = $tplan_mgr->get_builds($tplanSet,null,null,array('getCount' => true));

      $rightSet = array('testplan_user_role_assignment');
      foreach($tplanSet as $idk)
      {
        $gui->tplans[$idk]['tcase_qty'] = isset($dummy[$idk]['qty']) ? intval($dummy[$idk]['qty']) : 0;
        $gui->tplans[$idk]['build_qty'] = isset($buildQty[$idk]['build_qty']) ? intval($buildQty[$idk]['build_qty']) : 0;
        if( $gui->drawPlatformQtyColumn )
        {
          $plat = $tplan_mgr->getPlatforms($idk);
          $gui->tplans[$idk]['platform_qty'] = is_null($plat) ? 0 : count($plat);
        }
  
        // Get rights for each test plan
        foreach($rightSet as $target)
        {
          // DEV NOTE - CRITIC
          // I've made a theorically good performance choice to 
          // assign to $roleObj a reference to different roleObj
          // UNFORTUNATELLY this choice was responsible to destroy point object
          // since second LOOP
          $roleObj = null;
          if($gui->tplans[$idk]['has_role'] > 0)
          {
            if( isset($args->user->tplanRoles[ $gui->tplans[$idk]['has_role'] ]) )
            { 
              $roleObj = $args->user->tplanRoles[ $gui->tplans[$idk]['has_role'] ];
            }
            else
            {
              // Need To review this comment
              // session cache has not still updated => get from DB ?
              $roleObj = $args->user->getEffectiveRole($db,$args->tproject_id,$idk);
            }  
          }  
          else if (!is_null($args->user->tprojectRoles) && 
                   isset($args->user->tprojectRoles[$args->tproject_id]) )
          {
            $roleObj = $args->user->tprojectRoles[$args->tproject_id];
          }  

          if(is_null($roleObj))
          {
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
     $do_display=true;
     $template = is_null($template) ? 'planEdit.tpl' : $template;
     $gui->notes=$of->CreateHTML();
   break;
}

if($do_display)
{
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
 * @parameter hash request_hash the $_REQUEST
 * @return    object with html values tranformed and other
 *                   generated variables.
 *
 */
function init_args($request_hash)
{
  $session_hash = $_SESSION;
  $args = new stdClass();
  $request_hash = strings_stripSlashes($request_hash);

  $nullable_keys = array('testplan_name','notes','rights','active','do_action');
  foreach($nullable_keys as $value)
  {
    $args->$value = isset($request_hash[$value]) ? trim($request_hash[$value]) : null;
  }

  $checkboxes_keys = array('is_public' => 0,'active' => 0);
  foreach($checkboxes_keys as $key => $value)
  {
    $args->$key = isset($request_hash[$key]) ? 1 : 0;
  }

  $intval_keys = array('copy_from_tplan_id' => 0,'tplan_id' => 0);
  foreach($intval_keys as $key => $value)
  {
    $args->$key = isset($request_hash[$key]) ? intval($request_hash[$key]) : $value;
  }
  $args->source_tplanid = $args->copy_from_tplan_id;
  $args->copy = ($args->copy_from_tplan_id > 0) ? TRUE : FALSE;

  $args->copy_options=array();
  $boolean_keys = array('copy_tcases' => 0,'copy_priorities' => 0,
                        'copy_milestones' => 0, 'copy_user_roles' => 0, 
                        'copy_builds' => 0, 'copy_platforms_links' => 0,
                        'copy_attachments' => 0);

  foreach($boolean_keys as $key => $value)
  {
    $args->copy_options[$key]=isset($request_hash[$key]) ? 1 : 0;
  }

  $args->copy_assigned_to = isset($request_hash['copy_assigned_to']) ? 1 : 0;
  $args->tcversion_type = isset($request_hash['tcversion_type']) ? $request_hash['tcversion_type'] : null;
  $args->tproject_id = intval($session_hash['testprojectID']);
  $args->tproject_name = $session_hash['testprojectName'];
  $args->user_id = intval($session_hash['userID']);
  $args->user = $session_hash['currentUser'];


  // all has to be refactored this way  
  $iParams = array("file_id" => array(tlInputParameter::INT_N),
                   "fileTitle" => array(tlInputParameter::STRING_N,0,100));
  R_PARAMS($iParams,$args);

  return $args;
}

/**
 * checkRights
 *
 */
function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'mgt_testplan_create');
}

/**
 * initializeGui
 *
 */
function initializeGui(&$dbHandler,&$argsObj,&$editorCfg,&$tprojectMgr)
{
    $tplan_mgr = new testplan($dbHandler);
    
    $guiObj = new stdClass();
    $guiObj->tproject_id = $argsObj->tproject_id; 
    $guiObj->editorType = $editorCfg['type'];
    $guiObj->tplans = $argsObj->user->getAccessibleTestPlans($dbHandler,$argsObj->tproject_id,
                                                             null,array('output' =>'mapfull','active' => null));
    $guiObj->tproject_name = $argsObj->tproject_name;
    $guiObj->main_descr = lang_get('testplan_title_tp_management'). " - " .
                         lang_get('testproject') . ' ' . $argsObj->tproject_name;
    $guiObj->testplan_name = null;
    $guiObj->tplan_id = intval($argsObj->tplan_id);
    $guiObj->is_active = 0;
    $guiObj->is_public = 0;
    $guiObj->cfields = '';
    $guiObj->user_feedback = '';               
    
    $guiObj->grants = new stdClass();  
    $guiObj->grants->testplan_create = $argsObj->user->hasRight($dbHandler,"mgt_testplan_create",$argsObj->tproject_id);
    $guiObj->grants->mgt_view_events = $argsObj->user->hasRight($dbHandler,"mgt_view_events");
    $guiObj->notes = '';
    
    $guiObj->attachments[$guiObj->tplan_id] = getAttachmentInfosFrom($tplan_mgr,$guiObj->tplan_id);
    $guiObj->attachmentTableName = $tplan_mgr->getAttachmentTableName();
    

    $guiObj->fileUploadURL = $_SESSION['basehref'] . $tplan_mgr->getFileUploadRelativeURL($guiObj->tplan_id);
    $guiObj->delAttachmentURL = $_SESSION['basehref'] . $tplan_mgr->getDeleteAttachmentRelativeURL($guiObj->tplan_id);

    $guiObj->fileUploadMsg = '';
    $guiObj->import_limit = TL_REPOSITORY_MAXFILESIZE;
    
    return $guiObj;
}

/**
 *
 */
function getItemData(&$itemMgr,&$guiObj,&$ofObj,$itemID,$updateAttachments=false)
{
  $dummy = $itemMgr->get_by_id($itemID);
  if (sizeof($dummy))
  {
    $ofObj->Value = $dummy['notes'];
    $guiObj->testplan_name = $dummy['name'];
    $guiObj->is_active = $dummy['active'];
    $guiObj->is_public = $dummy['is_public'];
    $guiObj->api_key = $dummy['api_key'];
    $guiObj->tplan_id = $itemID;

    if($updateAttachments)
    {  
      $guiObj->attachments[$guiObj->tplan_id] = getAttachmentInfosFrom($itemMgr,$guiObj->tplan_id);
    }
  }
}
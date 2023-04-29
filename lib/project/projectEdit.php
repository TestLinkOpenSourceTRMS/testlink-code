<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * test project management
 *
 * @package     TestLink
 * @copyright   2007-2020, TestLink community 
 * @filesource  projectEdit.php
 * @uses        projectCommon.php
 * @link        http://www.testlink.org
 *
 */

require_once('../../config.inc.php');
require_once('common.php');
require_once("projectCommon.php");


testlinkInitPage($db,true,false,"checkUserRights");


// OK, user has the rights to proceed
$templateCfg = templateConfiguration();
$template = null;

$tproject_mgr = new testproject($db);
list($args,$gui,$ui) = initScriptEnv($tproject_mgr);


$status_ok = 1;

switch($args->doAction) {
  case 'create':
    $template = $templateCfg->default_template;
    $ui = create($args,$tproject_mgr);

    // Needed to implement the CREATE AS COPY
    $gui->testprojects = $ui->testprojects;
    $gui->pageTitle = $ui->pageTitle;
  break;

  case 'edit':
    $template = $templateCfg->default_template;
    $ui = edit($args,$tproject_mgr);
    $gui->pageTitle = $ui->pageTitle;
  break;

  case 'doCreate':
    $op = doCreate($args,$tproject_mgr);
    $template= $op->status_ok ?  null : $templateCfg->default_template;
    $ui = $op->ui;
    $status_ok = $op->status_ok;
    $gui->user_feedback = $op->msg;

    // 20201022
    /* We are creating the FIRST Test Project
       special processing is needed */
    if ($args->projectCount == 0) {
      $gui->tproject_id = $op->id;      
    }
    $gui->reloadType = $op->reloadType;
  break;

  case 'doUpdate':
    $op = doUpdate($args,$tproject_mgr);
    $template= $op->status_ok ?  null : $templateCfg->default_template;
    $ui = $op->ui;
    $status_ok = $op->status_ok;
    $gui->user_feedback = $op->msg;
    $gui->reloadType = $op->reloadType;
  break;

  case 'doDelete':
    $op = doDelete($args,$tproject_mgr);
    $status_ok = $op->status_ok;
    $gui->user_feedback = $op->msg;

    // 20201022
    if ($status_ok) {
      $args->projectCount--;
    }
    if ($args->projectCount <= 0) {
      $gui->tproject_id = 0;            
    }
    $gui->reloadType = $op->reloadType;
  break;

  case 'setActive':
  case 'setInactive':
  case 'enableRequirements':
  case 'disableRequirements':
    $m2c = $args->doAction;
    $tproject_mgr->$m2c($args->itemID);
    $template= null;
    $ui = new stdClass();
    $status_ok = 1;
    $gui->user_feedback = '';
    $gui->reloadType = 'reloadNavBar';
  break;

}

$ui->main_descr = lang_get('title_testproject_management');
$smarty = new TLSmarty();

$feedback_type = '';  
if(!$status_ok) {
  $feedback_type = 'error';  
  $args->doAction = "ErrorOnAction";
}

switch($args->doAction) {
  case "doCreate":
  case "doDelete":
  case "doUpdate":
  case "setActive":
  case "setInactive":
  case 'enableRequirements':
  case 'disableRequirements':
    if( ($addIssueTracker = $addCodeTracker = $addReqMgrSystem = is_null($template)) ) {
      $template = 'projectView.tpl';
      // needed after addition of search function on test project view
      $gui->name = '';  
      $gui->feedback = '';  
    }  

    //??? 20201219 $gui->doAction = $reloadType;
    $opt = array('output' => 'array_of_map', 
                 'order_by' => " ORDER BY nodes_hierarchy.name ",
                 'add_issuetracker' => $addIssueTracker, 
                 'add_codetracker' => $addCodeTracker,
                 'add_reqmgrsystem' => $addReqMgrSystem);
    $gui->tprojects = (array)$tproject_mgr->get_accessible_for_user($args->userID,$opt);
      
    $gui->pageTitle = lang_get('title_testproject_management');
 
    $gui->doViewReload = ($template == 'projectView.tpl');

    $gui->itemQty = count($gui->tprojects);
    if($gui->itemQty > 0) {
      initIntegrations($gui->tprojects,$gui->itemQty,$smarty);
    }  
  break;


  case "ErrorOnAction":
  default:
    // CRITIC do not move
    $gui = $args;
    if( $args->doAction != "edit" && $args->doAction != "ErrorOnAction") {
      $gui->of->Value = getItemTemplateContents('project_template', $gui->of->InstanceName, $args->notes);
    } else {
      $gui->of->Value = $args->notes;
    }
    $gui->doViewReload = ($template == 'projectView.tpl');
    $gui->notes = $gui->of->CreateHTML();

      
    foreach($ui as $prop => $value) {
      $smarty->assign($prop,$value);
      $gui->$prop = $value;
    }


  break;
}

if (is_array($gui->user_feedback)) {
  $gui->user_feedback = implode("\n",$gui->user_feedback);
}
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $template);





/**
 *
 */
function initScriptEnv(&$tprojMgr)
{
  $ui = new stdClass();
  $ui->doActionValue = '';
  $ui->buttonValue = '';
  $ui->caption = '';
  $ui->main_descr = lang_get('title_testproject_management');



  $args = init_args($tprojMgr);
  $gui = initializeGui($tprojMgr->db,$args,$tprojMgr);
  $args->of = $gui->of;

  return [$args,$gui,$ui];  
}


/**
 * INITialize page ARGuments, using the $_REQUEST and $_SESSION
 * super-global hashes.
 * Important: changes in HTML input elements on the Smarty template
 *            must be reflected here.
 *
 * @param array $_REQUEST the $_REQUEST
 * @param hash session_hash the $_SESSION
 * @return singleton object with html values tranformed and other
 *                   generated variables.
 * @internal
 */
function init_args($tprojectMgr) 
{
  $_REQUEST = strings_stripSlashes($_REQUEST);

  list($args,$env) = initContext();

  $nullable_keys = array('tprojectName','color',
                         'notes','doAction','tcasePrefix','api_key');
  foreach ($nullable_keys as $value) {
    $args->$value = isset($_REQUEST[$value]) ? trim($_REQUEST[$value]) : null;
  }

  $intval_keys = array('itemID' => 0, 
                       'tproject_id' => 0,
                       'tplan_id' => 0,
                       'copy_from_tproject_id' => 0
                      );
  foreach ($intval_keys as $key => $value) {
    $args->$key = isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : $value;
  }

  // get input from the project edit/create page
  $checkbox_keys = array('is_public' => 0,'active' => 0,
                         'optPriority' => 0,'optAutomation' => 0,
                         'optReq' => 0,'optInventory' => 0,
                         'issue_tracker_enabled' => 0,
                         'code_tracker_enabled' => 0,
                         'reqmgr_integration_enabled' => 0);
  foreach ($checkbox_keys as $key => $value) {
    $args->$key = isset($_REQUEST[$key]) ? 1 : $value;
  }

  $args->issue_tracker_id = isset($_REQUEST['issue_tracker_id']) ? intval($_REQUEST['issue_tracker_id']) : 0;
  $args->code_tracker_id = isset($_REQUEST['code_tracker_id']) ? intval($_REQUEST['code_tracker_id']) : 0;
  $args->reqmgrsystem_id = isset($_REQUEST['reqmgrsystem_id']) ? intval($_REQUEST['reqmgrsystem_id']) : 0;

  // This way we are safe
  if($args->issue_tracker_id == 0) {
    $args->issue_tracker_enabled = 0;  
  }  

  if($args->code_tracker_id == 0) {
    $args->code_tracker_enabled = 0;  
  }  

  if($args->doAction != 'doUpdate' 
     && $args->doAction != 'doCreate') {
    if ($args->itemID > 0) {
      $the_data = $tprojectMgr->get_by_id($args->itemID);
      $args->notes = $the_data['notes'];

      $args->issue_tracker_enabled = intval($the_data['issue_tracker_enabled']);  
      $args->issue_tracker_id = 0;
      $itMgr = new tlIssueTracker($tprojectMgr->db);
      $issueT = $itMgr->getLinkedTo($args->itemID);
      if( !is_null($issueT)  ) {
        $args->issue_tracker_id = $issueT['issuetracker_id'];
      }

      $args->code_tracker_enabled = 
        intval($the_data['code_tracker_enabled']);  
      $args->code_tracker_id = 0;
      $ctMgr = new tlCodeTracker($tprojectMgr->db);
      $codeT = $ctMgr->getLinkedTo($args->itemID);
      if( !is_null($codeT)  ) {
        $args->code_tracker_id = $codeT['codetracker_id'];
      }

      $args->reqmgr_integration_enabled = intval($the_data['reqmgr_integration_enabled']);  
      $args->reqmgrsystem_id = 0;
      $mgr = new tlReqMgrSystem($tprojectMgr->db);
      $et = $mgr->getLinkedTo($args->itemID);
      if( !is_null($et)  ) {
        $args->reqmgrsystem_id = $et['reqmgrsystem_id'];
      }

      if ($args->doAction == 'doDelete') {
        $args->tprojectName = $the_data['name'];
      }

    } else {
      $args->notes = '';
    }
  }
  
  // sanitize output via black list
  if($args->notes != '') {
    // The Black List - Jon Bokenkamp
    $bl = array('<script>','</script>');
    foreach($bl as $tg) {
      $cl[] = htmlentities($tg);
    }  
    $args->notes = str_replace($bl,$cl,$args->notes);
  }  

  $args->user = isset($_SESSION['currentUser']) ? $_SESSION['currentUser'] : null;
  $args->userID = intval(isset($_SESSION['userID']) ? intval($_SESSION['userID']) : 0);
  $args->testprojects = null;
  $args->projectOptions = prepareOptions($args);

  $args->projectCount = $tprojectMgr->getItemCount();

  return $args;
}

/**
 * Collect a test project options (input from form) to a singleton
 * 
 * @param array $argsObj the page input
 * @return singleton data to be stored
 */
function prepareOptions($argsObj) 
{
  $opts = new stdClass();
  $opts->requirementsEnabled = $argsObj->optReq;
  $opts->testPriorityEnabled = $argsObj->optPriority;
  $opts->automationEnabled = $argsObj->optAutomation;
  $opts->inventoryEnabled = $argsObj->optInventory;

  return $opts;
}

/**
 * 
 * ATTENTION: logEvent() is done on testproject->create()
 *
 */
function doCreate($argsObj,&$tprojectMgr) 
{
  $key2get=array('status_ok','msg');
  
  $op = new stdClass();
  $op->ui = new stdClass();
  
  $op->status_ok = 0;
  $op->template = null;
  $op->msg = '';
  $op->id = 0;
  $op->reloadType = 'none';
  
  $check_op = crossChecks($argsObj,$tprojectMgr);
  foreach($key2get as $key) {
    $op->$key=$check_op[$key];
  }

  if($op->status_ok) {
    try {
      $shazam = false;    
      $item = $argsObj;
      $item->name = $argsObj->tprojectName;
      $item->prefix = $argsObj->tcasePrefix;
      $item->options = prepareOptions($argsObj);
      $new_id = $tprojectMgr->create($item, array('doChecks' => true));            
    } catch (Exception $e) {
      $new_id = -1;
      $op->status_ok = false;       
      $op->msg = $e->getMessage();
      $shazam = true;    
    }
                                   
    if ($new_id <= 0) {
      if(!$shazam) { 
        $op->msg = lang_get('refer_to_log');
      } 
    } else {
      $op->template = 'projectView.tpl';
      $op->id = $new_id;
      
      if($argsObj->issue_tracker_enabled) {
        $tprojectMgr->enableIssueTracker($new_id);
      } else {
        $tprojectMgr->disableIssueTracker($new_id);
      }
      
      
      $itMgr = new tlIssueTracker($tprojectMgr->db);
      if($argsObj->issue_tracker_id > 0) { 
        $itMgr->link($argsObj->issue_tracker_id,$new_id);
      }

      if($argsObj->code_tracker_enabled) {
        $tprojectMgr->enableCodeTracker($new_id);
      } else {
        $tprojectMgr->disableCodeTracker($new_id);
      }
      
      
      $ctMgr = new tlCodeTracker($tprojectMgr->db);
      if($argsObj->code_tracker_id > 0) { 
        $ctMgr->link($argsObj->code_tracker_id,$new_id);
      }
  
      if( !$argsObj->is_public) {
        // Need to add specific role on test project in order to not make
        // it invisible for me!!!
        $tprojectMgr->addUserRole($argsObj->userID,$new_id,$argsObj->user->globalRole->dbID);
      }  
    }
  }

  if( $op->status_ok ) {
    $op->reloadType = 'reloadNavBar';      
    if($argsObj->copy_from_tproject_id > 0) {
      $options = array('copy_requirements' => $argsObj->optReq);
      $tprojectMgr->copy_as($argsObj->copy_from_tproject_id,$new_id,
                            $argsObj->userID,trim($argsObj->tprojectName),$options);
    }
  } else {
    $op->ui->doActionValue = 'doCreate';
    $op->ui->buttonValue = lang_get('btn_create');
    $op->ui->caption = lang_get('caption_new_tproject');
  }

  return $op;
}

/**
 * function: doUpdate
 *
 */
function doUpdate($argsObj,&$tprojectMgr) 
{
  $key2get = array('status_ok','msg');

  $op = new stdClass();
  $op->ui = new stdClass();
  $op->status_ok = 0;
  $op->msg = '';
  $op->template = null;
  $op->reloadType = 'none';

  $oldObjData = $tprojectMgr->get_by_id($argsObj->itemID);
  $op->oldName = $oldObjData['name'];

  $check_op = crossChecks($argsObj,$tprojectMgr);
  foreach($key2get as $key) {
    $op->$key = $check_op[$key];
  }

  if($op->status_ok) {
    $options = prepareOptions($argsObj);
    if( $tprojectMgr->update($argsObj->itemID,trim($argsObj->tprojectName),
      $argsObj->color, $argsObj->notes, $options, $argsObj->active,
      $argsObj->tcasePrefix, $argsObj->is_public) ) {
      $op->msg = '';
      $tprojectMgr->activate($argsObj->itemID,$argsObj->active);
      
      $tprojectMgr->setIssueTrackerEnabled($argsObj->itemID,$argsObj->issue_tracker_enabled);
      $itMgr = new tlIssueTracker($tprojectMgr->db);
      if( ($doLink = $argsObj->issue_tracker_id > 0)  ) {
        $itMgr->link($argsObj->issue_tracker_id,$argsObj->itemID);
      } else {
        $issueT = $itMgr->getLinkedTo($argsObj->itemID);
        if( !is_null($issueT) ) {
          $itMgr->unlink($issueT['issuetracker_id'],$issueT['testproject_id']);
        }  
      } 

      $tprojectMgr->setCodeTrackerEnabled($argsObj->itemID,$argsObj->code_tracker_enabled);
      $ctMgr = new tlCodeTracker($tprojectMgr->db);
      if( ($doLink = $argsObj->code_tracker_id > 0)  ) {
        $ctMgr->link($argsObj->code_tracker_id,$argsObj->itemID);
      } else {
        $codeT = $ctMgr->getLinkedTo($argsObj->itemID);
        if( !is_null($codeT) ) {
          $ctMgr->unlink($codeT['codetracker_id'],$codeT['testproject_id']);
        }  
      } 
      
      /*
      $tprojectMgr->setReqMgrIntegrationEnabled($argsObj->itemID,$argsObj->reqmgr_integration_enabled);
      $mgr = new tlReqMgrSystem($tprojectMgr->db);
      if( ($doLink = $argsObj->reqmgrsystem_id > 0)  ) {
        $mgr->link($argsObj->reqmgrsystem_id,$argsObj->itemID);
      } else {
        $et = $mgr->getLinkedTo($argsObj->itemID);
        if( !is_null($et) ) {
          $mgr->unlink($et['reqmgrsystem_id'],$et['testproject_id']);
        }  
      } 
      */

      if( !$argsObj->is_public) {
        // does user have an SPECIFIC role on Test Project ?
        // if answer is yes => do nothing
        if(!tlUser::hasRoleOnTestProject($tprojectMgr->db,$argsObj->userID,$argsObj->itemID)) {  
            $tprojectMgr->addUserRole($argsObj->userID,$argsObj->itemID,$argsObj->user->globalRole->dbID);
        }  
      }  
         
      $event = new stdClass();
      $event->message = TLS("audit_testproject_saved",$argsObj->tprojectName);
      $event->logLevel = "AUDIT";
      $event->source = "GUI";
      $event->objectID = $argsObj->itemID;
      $event->objectType = "testprojects";
      $event->code = "UPDATE";
      logEvent($event);
    } else {
      $op->status_ok=0;
    }  
  }
  
  if($op->status_ok) {
    if($argsObj->tproject_id == $argsObj->itemID) {
      $op->reloadType = 'reloadNavBar';
    }  
  } else {
    $op->ui->doActionValue = 'doUpdate';
    $op->ui->buttonValue = lang_get('btn_save');
    $op->ui->caption = sprintf(lang_get('caption_edit_tproject'),$op->oldName);
  }
  $op->ui->pageTitle = lang_get('title_testproject_management');
  return $op;
}


/**
 *
 * function: edit
 */
function edit(&$argsObj,&$tprojectMgr)
{
  $tprojectInfo = $tprojectMgr->get_by_id($argsObj->itemID);

  $argsObj->tprojectName = $tprojectInfo['name'];
  $argsObj->projectOptions = $tprojectInfo['opt'];
  $argsObj->tcasePrefix = $tprojectInfo['prefix'];

  $k2l = array('color','notes', 'active','is_public',
               'issue_tracker_enabled',
               'code_tracker_enabled',
               'reqmgr_integration_enabled',
               'api_key');  
  foreach($k2l as $key) {
    $argsObj->$key = $tprojectInfo[$key];
  }
  
  $ui = new stdClass();
  $ui->main_descr = lang_get('title_testproject_management');
  $ui->doActionValue = 'doUpdate';
  $ui->buttonValue = lang_get('btn_save');
  $ui->caption = 
    sprintf(lang_get('caption_edit_tproject'),$argsObj->tprojectName);
  $ui->pageTitle = $ui->main_descr . $ui->caption;

  return $ui;
}

/*
  function: crossChecks
            do checks that are common to create and update operations
            - name is valid ?
            - name already exists ?
            - prefix already exits ?
  args:

  returns: -


*/
function crossChecks($argsObj,&$tprojectMgr) 
{
  $op = new stdClass();
  $updateAdditionalSQLFilter = null ;
  $op = $tprojectMgr->checkName($argsObj->tprojectName);

  $check_op = array();
  $check_op['msg'] = array();
  $check_op['status_ok'] = $op['status_ok'];

  if($argsObj->doAction == 'doUpdate') {
    $updateAdditionalSQLFilter = " testprojects.id <> {$argsObj->itemID}";
  }

  if($check_op['status_ok']) {
    if($tprojectMgr->get_by_name($argsObj->tprojectName,$updateAdditionalSQLFilter)) {
      $check_op['msg'][] = sprintf(lang_get('error_product_name_duplicate'),$argsObj->tprojectName);
      $check_op['status_ok'] = 0;
    }
            
    // Check prefix no matter what has happen with previous check
    $rs = $tprojectMgr->get_by_prefix($argsObj->tcasePrefix,$updateAdditionalSQLFilter);
    if(!is_null($rs)) {
      $check_op['msg'][] = sprintf(lang_get('error_tcase_prefix_exists'),$argsObj->tcasePrefix);
      $check_op['status_ok'] = 0;
    }
  } else {
    $check_op['msg'][] = $op['msg'];
  }
  
  //  var_dump($check_op);
  return $check_op;
}

/*
  function: create

  args :

  returns:

*/
function create(&$argsObj,&$tprojectMgr)
{
  $gui = new stdClass();

  // Set defaults here
  $argsObj->active = 1;
  $argsObj->is_public = 1;
  $argsObj->optPriority = 1;
  $argsObj->optAutomation = 1;

  $gui->active = $argsObj->active;
  $gui->is_public = $argsObj->is_public;
  $gui->projectOptions = $argsObj->projectOptions = prepareOptions($argsObj);
  $gui->doActionValue = 'doCreate';
  $gui->buttonValue = lang_get('btn_create');
  $gui->caption = lang_get('caption_new_tproject');

  $gui->testprojects = $tprojectMgr->get_all(null,array('access_key' => 'id'));

  $gui->pageTitle = lang_get('caption_new_tproject');

  return $gui;
}


/*
  function: doDelete

  args :

  returns:

*/
function doDelete($argsObj,&$tprojectMgr) 
{
  $tprojectMgr->setAuditLogOn();
  $ope_status = $tprojectMgr->delete($argsObj->itemID);

  $op = new stdClass();
  $op->status_ok = $ope_status['status_ok'];
  $op->reloadType = 'none';

  if ($ope_status['status_ok']) {
    $op->reloadType = 'reloadNavBar';
    $op->msg = sprintf(lang_get('test_project_deleted'),$argsObj->tprojectName);
  } else {
    $op->msg = lang_get('info_product_not_deleted_check_log') . ' ' . $ope_status['msg'];
  }

  return $op;
}



/*
 *
 * @internal revisions
 *
 */
function initializeGui(&$dbHandler,$argsObj,&$tprojMgr) 
{
  $guiObj = $argsObj;
  $guiObj->reloadType = 'none';


  $guiObj->editorCfg = getWebEditorCfg('testproject');
  $guiObj->editorType = $guiObj->editorCfg['type'];    

  require_once(require_web_editor($guiObj->editorType));
  $guiObj->of = web_editor('notes',$_SESSION['basehref'],$guiObj->editorCfg) ;

  list($add2args,$add2guiObj) = initUserEnv($dbHandler,$argsObj);
  foreach($add2guiObj as $prop => $value) {
    $guiObj->$prop = $value;
  }

  $guiObj->gui_cfg = config_get('gui');

  $guiObj->user_feedback = '';
  $guiObj->activeMenu['projects'] = 'active';

  $guiObj->canManage = $argsObj->user->hasRight($dbHandler,"mgt_modify_product");
  $guiObj->mgt_view_events = $argsObj->user->hasRight($dbHandler,"mgt_view_events");
  $guiObj->found = 'yes';

  $ent2loop = array('tlIssueTracker' => 'issueTrackers', 
                    'tlCodeTracker' => 'codeTrackers',
                    'tlReqMgrSystem' => 'reqMgrSystems');
  
  foreach($ent2loop as $cl => $pr) {
    $mgr = new $cl($dbHandler);
    $guiObj->$pr = $mgr->getAll();
    unset($mgr);
  }

  $guiObj->itemID = $argsObj->itemID;
  $guiObj->tproject_id = $argsObj->tproject_id;
  $guiObj->tplan_id = $argsObj->tplan_id;


  $guiObj->actions = $tprojMgr->getViewActions($argsObj);


  return $guiObj;
}


/**
 *
 */
function checkUserRights(&$db,&$user) {
  csrfguard_start(); //?? 20190812
  return $user->hasRight($db,'mgt_modify_product');
}

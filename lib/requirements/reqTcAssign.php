<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource  reqTcAssign.php
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();
$gui = initializeGui($db,$args);

$bulkCounter = 0;
$bulkDone = false;
$bulkMsg = null;
$pfn = null;

switch($args->doAction) {
    case 'assign':
      $pfn = "assign_to_tcase";
    break;  

    case 'unassign':
      $pfn = "delReqVersionTCVersionLinkByID";
    break;  

    case 'bulkassign':
      // need to check if we have test cases to work on
      $tcase_set = getTargetTestCases($db,$args);
      $bulkCounter = 0;
      $bulkDone = true;
      $args->edit = 'testsuite';
      if( !is_null($tcase_set) && count($tcase_set) > 0 ) {
        $bulkCounter = doBulkAssignment($db,$args,$tcase_set);
      }
    break;  

    case 'switchspec':
      $args->edit = 'testsuite';
    break;  
}

if(!is_null($pfn)) {
  $gui = doSingleTestCaseOperation($db,$args,$gui,$pfn);
}

switch($args->edit) {
  case 'testproject':
    show_instructions('assignReqs');
    exit();
  break;
    
  case 'testsuite':
    $gui = processTestSuite($db,$args,$gui);
    $templateCfg->default_template = 'reqTcBulkAssignment.tpl';
    if($bulkDone) {
      $gui->user_feedback = sprintf(lang_get('bulk_assigment_done'),$bulkCounter); 
    }    
  break;
      
  case 'testcase':
    $gui = processTestCase($db,$args,$gui);
  break;
  
  default:
    tlog("Wrong GET/POST arguments.", 'ERROR');
    exit();
  break;
}

$tpl = $templateCfg->template_dir . $templateCfg->default_template;
$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($tpl);


/**
 *
 */
function init_args() {
  $iParams = array("id" => array(tlInputParameter::INT_N),
                   "req_id" => array(tlInputParameter::ARRAY_INT),
                   "link_id" => array(tlInputParameter::ARRAY_INT),

                   "req" => array(tlInputParameter::INT_N),
                   "showCloseButton" => array(tlInputParameter::STRING_N,0,1),
                   "doAction" => array(tlInputParameter::STRING_N,0,100),
                   "edit" => array(tlInputParameter::STRING_N,0,100),
                   "unassign" => array(tlInputParameter::STRING_N,0,1),
                   "assign" => array(tlInputParameter::STRING_N,0,1),
                   "form_token" => array(tlInputParameter::INT_N),
                   "callback" => array(tlInputParameter::STRING_N,0,1),
                   "idSRS" => array(tlInputParameter::INT_N));  
    
  $args = new stdClass();
  R_PARAMS($iParams,$args);

  // take care of proper escaping when magic_quotes_gpc is enabled
  $_REQUEST = strings_stripSlashes($_REQUEST);
  
  $args->idReqSpec = null;
  $args->idReq = $args->req;
  $args->reqIdSet = $args->req_id;
  $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;

  $args->tcaseSet = null;
  if(isset($_SESSION['edit_mode'][$args->form_token]['testcases_to_show'])) {
    $args->tcaseSet = $_SESSION['edit_mode'][$args->form_token]['testcases_to_show'];
  }  

  if(is_null($args->doAction)) {
    $args->doAction = ($args->unassign != "") ? "unassign" : null;
  }

  if(is_null($args->doAction)){
    $args->doAction = ($args->assign != "") ? "assign" : null;
  }

  if ($args->idSRS) {
    $args->idReqSpec = $args->idSRS;
    $_SESSION['currentSrsId'] = $args->idReqSpec;
  }
  else if(isset($_SESSION['currentSrsId']) && intval($_SESSION['currentSrsId']) > 0) {
    $args->idReqSpec = intval($_SESSION['currentSrsId']);
  }

  
  $args->user = $_SESSION['currentUser'];
  return $args;
}

/**
 * 
 *
 */
function processTestSuite(&$dbHandler,&$argsObj,&$guiObj) {
  $tproject_mgr = new testproject($dbHandler);

  $guiObj->bulkassign_warning_msg = '';
  $guiObj->tsuite_id = $argsObj->id;
    
  $tsuite_info = $tproject_mgr->tree_manager->get_node_hierarchy_info($guiObj->tsuite_id);
  $guiObj->pageTitle = lang_get('test_suite') . config_get('gui_title_separator_1') . $tsuite_info['name'];
     
  $guiObj->req_specs = $tproject_mgr->genComboReqSpec($argsObj->tproject_id,'dotted',"&nbsp;");
  $guiObj->selectedReqSpec = $argsObj->idReqSpec;

  $dummy = $guiObj->req_specs[$argsObj->idReqSpec];
  $guiObj->selectedReqSpecName = trim($dummy,'&nbsp;');

  $guiObj->tcase_number = 0;
  $guiObj->has_req_spec = false;

  if(!is_null($guiObj->req_specs) && count($guiObj->req_specs)) {  
    $guiObj->has_req_spec = true;
       
    if(is_null($argsObj->idReqSpec)) {
      $guiObj->selectedReqSpec = key($guiObj->req_specs);
    }

    $req_spec_mgr = new requirement_spec_mgr($dbHandler);
       
    $getOpt = array('output' => 'array');   
    $guiObj->requirements = 
      $req_spec_mgr->getAllLatestRQVOnReqSpec($guiObj->selectedReqSpec,$getOpt);

    $guiObj->reqCountOnReqSpec = count($guiObj->requirements);

    $guiObj->reqCountFeedback = 
      sprintf(lang_get('req_on_req_spec'),$guiObj->reqCountOnReqSpec,
              $guiObj->selectedReqSpecName);


    $tcase_set = getTargetTestCases($dbHandler,$argsObj);
    $guiObj->tcase_number = count($tcase_set);            
    if( $guiObj->tcase_number > 0 ) {
      $guiObj->bulkassign_warning_msg = 
        sprintf(lang_get('bulk_req_assign_msg'),$guiObj->tcase_number,$tsuite_info['name']);
    } else {
      $guiObj->bulkassign_warning_msg = 
        lang_get('bulk_req_assign_no_test_cases');
    } 
  }
  return $guiObj;
}

/**
 * 
 *
 */
function doBulkAssignment(&$dbHandler,&$argsObj,$targetTestCaseSet = null)
{
  $req_mgr = new requirement_mgr($dbHandler);
  $assignmentCounter = 0;
  $requirements = array_keys($argsObj->reqIdSet);
  if(!is_null($requirements) && count($requirements) > 0)
  {
    $tcase_set = $targetTestCaseSet;
    if( is_null($tcase_set) )
    {
      $tsuite_mgr = new testsuite($dbHandler);
      $tcase_set = $tsuite_mgr->get_testcases_deep($argsObj->id,'only_id');
    }

    if( !is_null($tcase_set) && count($tcase_set) )
    {
      // $assignmentCounter = $req_mgr->bulk_assignment($requirements,$tcase_set,$argsObj->user->dbID);

      $assignmentCounter = 
        $req_mgr->bulkAssignLatestREQVTCV($requirements,$tcase_set,$argsObj->user->dbID);

    }

  } 
  return $assignmentCounter;
}

/**
 *
 */
function doSingleTestCaseOperation(&$dbHandler,&$argsObj,&$guiObj,$pfn) {
  $msg = '';

  switch($pfn) {
    case 'assign_to_tcase':
      $items = array_keys($argsObj->reqIdSet);
    break;

    case 'delReqVersionTCVersionLinkByID':
      $items = array_keys($argsObj->link_id);
    break;
  }

  if( count($items) == 0 ) {
    $guiObj->user_feedback = lang_get('req_msg_noselect');
    return $guiObj;
  }
  
  $req_mgr = new requirement_mgr($dbHandler);


  switch($pfn) {
    case 'assign_to_tcase':
      foreach ($items as $idOneReq) {
        $res = $req_mgr->$pfn($idOneReq,$argsObj->id,$argsObj->user->dbID);
        if (!$res) {
          $msg .= $idOneReq . ', ';
        } 
      }
      if (!empty($msg)) {
        $guiObj->user_feedback = lang_get('req_msg_notupdated_coverage') . $msg;
      } 
    break;

    case 'delReqVersionTCVersionLinkByID':
      foreach ($items as $idLink) {
        $res = $req_mgr->$pfn($idLink);
        if (!$res) {
          $msg .= $idOneReq . ', ';
        } 
      }
      if (!empty($msg)) {
        $guiObj->user_feedback = lang_get('req_msg_notupdated_coverage') . $msg;
      } 
    break;
  }

  return $guiObj;
} 


/**
 *
 *
 */
function array_diff_byId($arrAll, $arrPart) {

  if (is_null($arrAll) || !count($arrAll)) {
    return null;
  }

  if (is_null($arrPart) || !count($arrPart)) {
    return $arrAll;
  }

  $arrTempAll = array();
  foreach ($arrAll as $penny) {
    $highLander[$penny['id']] = $penny;
  }

  foreach ($arrPart as $penny) {
    if(isset($highLander[$penny['id']])) {
      unset($highLander[$penny['id']]);
    }  
  }

  return array_values($highLander);
}


/**
 * processTestCase
 *
 */
function processTestCase(&$dbHandler,&$argsObj,&$guiObj) {
  $tproject_mgr = new testproject($dbHandler);
  $guiObj->arrReqSpec = $tproject_mgr->genComboReqSpec($argsObj->tproject_id,'dotted',"&nbsp;");
  $SRS_qty = count($guiObj->arrReqSpec);
  
  if($SRS_qty > 0) {
    $tc_mgr = new testcase($dbHandler);
    $arrTc = $tc_mgr->get_by_id($argsObj->id,testcase::LATEST_VERSION);
    if($arrTc) {
      $guiObj->tcTitle = $arrTc[0]['name'];
      $guiObj->tcVersion = $arrTc[0]['version'];

      // get test case version execution status
      $tcversion_id = $arrTc[0]['id'];
      $statusQuo = $tc_mgr->get_versions_status_quo($argsObj->id,$tcversion_id);

      $statusQuo = current($statusQuo);
      $guiObj->tcaseHasBeenExecuted = (intval($statusQuo['executed']) > 0);

      // get first ReqSpec if not defined
      if(is_null($argsObj->idReqSpec)) {
        reset($guiObj->arrReqSpec);
        $argsObj->idReqSpec = key($guiObj->arrReqSpec);
      }

      if($argsObj->idReqSpec) {
        $req_spec_mgr = new requirement_spec_mgr($dbHandler);
        $fx = array('link_status' => array(LINK_TC_REQ_OPEN,LINK_TC_REQ_CLOSED_BY_EXEC));
        $theAssigned = $req_spec_mgr->getReqsOnSpecForLatestTCV($argsObj->idReqSpec,$argsObj->id,null,$fx);

        $guiObj->assignedReq = $theAssigned;
        $guiObj->allReq = $req_spec_mgr->get_requirements($argsObj->idReqSpec);

        $guiObj->unassignedReq = array_diff_byId($guiObj->allReq, $guiObj->assignedReq);
      }
    }
  } 
  return $guiObj;
}

/**
 *
 */
function initializeGui(&$dbH,$argsObj) { 
  $guiObj = new stdClass();
  $guiObj->user_feedback = '';
  $guiObj->tcTitle = $guiObj->assignedReq = null;
  $guiObj->unassignedReq = $guiObj->arrReqSpec = null;
  
  $guiObj->showCloseButton = $argsObj->showCloseButton;
  $guiObj->selectedReqSpec = $argsObj->idReqSpec;
  $guiObj->form_token = $argsObj->form_token;

  $guiObj->tcase_id = $argsObj->id;
  $guiObj->callback = $argsObj->callback;
  
  $reqCfg = getWebEditorCfg('requirement');
  $guiObj->reqEditorType = $reqCfg['type'];
  $reqCfg = getWebEditorCfg('requirement_spec');
  $guiObj->reqSpecEditorType = $reqCfg['type'];

  $guiObj->req_tcase_link_management = 
    $argsObj->user->hasRight($dbH,'req_tcase_link_management');

  return $guiObj;
}

/**
 *
 */
function getTargetTestCases(&$dbHandler,&$argsObj) {
  $mgr = new testsuite($dbHandler);
  $items = $mgr->get_testcases_deep($argsObj->id,'only_id');
  
  if(!is_null($argsObj->tcaseSet)) {  
    $rr = array_intersect($items,$argsObj->tcaseSet);
    $items = $rr;
  }

  return $items;
}


function checkRights(&$db,&$user) {
  return ($user->hasRight($db,'req_tcase_link_management'));
}
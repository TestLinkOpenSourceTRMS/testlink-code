<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource  reqTcAssign.php
 * @author Martin Havlat
 *
 * @internal revisions
 * @since 1.9.14
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
testlinkInitPage($db,false,false,"checkRights");


$templateCfg = templateConfiguration();
$args = init_args();
$gui = initializeGui($args);


$bulkCounter = 0;
$bulkDone = false;
$bulkMsg = null;
$pfn = null;

switch($args->doAction)
{
    case 'assign':
      $pfn = "assign_to_tcase";
    break;  

    case 'unassign':
      $pfn = "unassign_from_tcase";
    break;  

    case 'bulkassign':
      // need to check if we have test cases to work on
      $tcase_set = getTargetTestCases($db,$args);
      $bulkCounter = 0;
      $bulkDone = true;
      $args->edit = 'testsuite';
      if( !is_null($tcase_set) && count($tcase_set) > 0 )
      {
        $bulkCounter = doBulkAssignment($db,$args,$tcase_set);
      }
    break;  

    case 'switchspec':
      $args->edit = 'testsuite';
    break;  
}

if(!is_null($pfn))
{
  $gui = doSingleTestCaseOperation($db,$args,$gui,$pfn);
}

switch($args->edit)
{
  case 'testproject':
    show_instructions('assignReqs');
    exit();
  break;
    
  case 'testsuite':
    $gui = processTestSuite($db,$args,$gui);
    $templateCfg->default_template = 'reqTcBulkAssignment.tpl';
    if($bulkDone)
    {
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


$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req")); 
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 *
 */
function init_args()
{
  $iParams = array("id" => array(tlInputParameter::INT_N),
                   "req_id" => array(tlInputParameter::ARRAY_INT),
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
  $_REQUEST=strings_stripSlashes($_REQUEST);
  
  $args->idReqSpec = null;
  $args->idReq = $args->req;
  $args->reqIdSet = $args->req_id;
  $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;

  $args->tcaseSet = null;
  if(isset($_SESSION['edit_mode'][$args->form_token]['testcases_to_show']))
  {
    $args->tcaseSet = $_SESSION['edit_mode'][$args->form_token]['testcases_to_show'];
  }  

  if(is_null($args->doAction))
  {
    $args->doAction = ($args->unassign != "") ? "unassign" : null;
  }
  if(is_null($args->doAction))
  {
    $args->doAction = ($args->assign != "") ? "assign" : null;
  }

  if ($args->idSRS)
  {
    $args->idReqSpec = $args->idSRS;
    $_SESSION['currentSrsId'] = $args->idReqSpec;
  }
  else if(isset($_SESSION['currentSrsId']) && intval($_SESSION['currentSrsId']) > 0)
  {
    $args->idReqSpec = intval($_SESSION['currentSrsId']);
  }

  
  $args->user = $_SESSION['currentUser'];
  return $args;
}

/**
 * 
 *
 */
function processTestSuite(&$dbHandler,&$argsObj,&$guiObj)
{
  $tproject_mgr = new testproject($dbHandler);

  $guiObj->bulkassign_warning_msg = '';
  $guiObj->tsuite_id = $argsObj->id;
    
  $tsuite_info = $tproject_mgr->tree_manager->get_node_hierarchy_info($guiObj->tsuite_id);
  $guiObj->pageTitle = lang_get('test_suite') . config_get('gui_title_separator_1') . $tsuite_info['name'];
     
  $guiObj->req_specs = $tproject_mgr->genComboReqSpec($argsObj->tproject_id,'dotted',"&nbsp;");
  $guiObj->selectedReqSpec = $argsObj->idReqSpec;
  $guiObj->tcase_number = 0;
  $guiObj->has_req_spec = false;
  $guiObj->tsuite_id = $argsObj->id;

  if(!is_null($guiObj->req_specs) && count($guiObj->req_specs))
  {  
    $guiObj->has_req_spec = true;
       
    if(is_null($argsObj->idReqSpec))
    {
      $guiObj->selectedReqSpec = key($guiObj->req_specs);
    }
       
    $req_spec_mgr = new requirement_spec_mgr($dbHandler);
    $guiObj->requirements =$req_spec_mgr->get_requirements($guiObj->selectedReqSpec);
        
    // $tsuite_mgr = new testsuite($dbHandler);
    // $tcase_set = $tsuite_mgr->get_testcases_deep($argsObj->id,'only_id');
    $tcase_set = getTargetTestCases($dbHandler,$argsObj);
    $guiObj->tcase_number = count($tcase_set); 
           
    if( $guiObj->tcase_number > 0 )
    {
      $guiObj->bulkassign_warning_msg = sprintf(lang_get('bulk_req_assign_msg'),$guiObj->tcase_number);
    }
    else
    {
      $guiObj->bulkassign_warning_msg = lang_get('bulk_req_assign_no_test_cases');
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
      $assignmentCounter = $req_mgr->bulk_assignment($requirements,$tcase_set,$argsObj->user->dbID);
    }

  } 
  return $assignmentCounter;
}

/**
 *
 */
function doSingleTestCaseOperation(&$dbHandler,&$argsObj,&$guiObj,$pfn)
{
  $msg = '';
  $req_ids = array_keys($argsObj->reqIdSet);
  if (count($req_ids))
  {
    $req_mgr = new requirement_mgr($dbHandler);
    foreach ($req_ids as $idOneReq)
    {
      $result = $req_mgr->$pfn($idOneReq,$argsObj->id,$argsObj->user->dbID);
      if (!$result)
      {
        $msg .= $idOneReq . ', ';
      } 
    }
    if (!empty($msg))
    {
      $guiObj->user_feedback = lang_get('req_msg_notupdated_coverage') . $msg;
    } 
  }
  else
  {
    $guiObj->user_feedback = lang_get('req_msg_noselect');
  }
  return $guiObj;
} 


function array_diff_byId($arrAll, $arrPart)
{
  // solve empty arrays
  if (!count($arrAll) || is_null($arrAll))
  {
    return null;
  }
  if (!count($arrPart) || is_null($arrPart))
  {
    return $arrAll;
  }

  $arrTempAll = array();
  foreach ($arrAll as $penny) 
  {
    $highLander[$penny['id']] = $penny;
  }

  foreach ($arrPart as $penny) 
  {
    if(isset($highLander[$penny['id']]))
    {
      unset($highLander[$penny['id']]);
    }  
  }
  return array_values($highLander);
}


/**
 * processTestCase
 *
 */
function processTestCase(&$dbHandler,&$argsObj,&$guiObj)
{
  $tproject_mgr = new testproject($dbHandler);
  $guiObj->arrReqSpec = $tproject_mgr->genComboReqSpec($argsObj->tproject_id,'dotted',"&nbsp;");
  $SRS_qty = count($guiObj->arrReqSpec);
  
  if($SRS_qty > 0)
  {
    $tc_mgr = new testcase($dbHandler);
    $arrTc = $tc_mgr->get_by_id($argsObj->id);
    if($arrTc)
    {
      $guiObj->tcTitle = $arrTc[0]['name'];
      
      // get first ReqSpec if not defined
      if(is_null($argsObj->idReqSpec))
      {
          reset($guiObj->arrReqSpec);
          $argsObj->idReqSpec = key($guiObj->arrReqSpec);
      }

      if($argsObj->idReqSpec)
      {
        $req_spec_mgr = new requirement_spec_mgr($dbHandler);
        $guiObj->arrAssignedReq = $req_spec_mgr->get_requirements($argsObj->idReqSpec, 'assigned', $argsObj->id);

        if(!is_null($guiObj->arrAssignedReq))
        {
          $xc = $req_spec_mgr->getAssignedCoverage($argsObj->idReqSpec);
          $l2d = count($guiObj->arrAssignedReq);

          for($xdx=0; $xdx < $l2d; $xdx++)
          {
            $guiObj->arrAssignedReq[$xdx]['coverageAuthor'] = $xc[$guiObj->arrAssignedReq[$xdx]['id']]['login'];
            $guiObj->arrAssignedReq[$xdx]['coverageTS'] = $xc[$guiObj->arrAssignedReq[$xdx]['id']]['creation_ts'];
          }  
        }  
        $guiObj->arrAllReq = $req_spec_mgr->get_requirements($argsObj->idReqSpec);
        $guiObj->arrUnassignedReq = array_diff_byId($guiObj->arrAllReq, $guiObj->arrAssignedReq);
      }
    }
  } 
  return $guiObj;
}

/**
 *
 */
function initializeGui($argsObj)
{ 
  $guiObj = new stdClass();
  $guiObj->user_feedback = '';
  $guiObj->tcTitle = $guiObj->arrAssignedReq = null;
  $guiObj->arrUnassignedReq = $guiObj->arrReqSpec = null;
  
  $guiObj->showCloseButton = $argsObj->showCloseButton;
  $guiObj->selectedReqSpec = $argsObj->idReqSpec;
  $guiObj->form_token = $argsObj->form_token;

  $guiObj->tcase_id = $argsObj->id;
  $guiObj->callback = $argsObj->callback;
  
  $cfg->reqCfg = getWebEditorCfg('requirement');
  $guiObj->reqEditorType = $cfg->reqCfg['type'];
  $cfg->reqCfg = getWebEditorCfg('requirement_spec');
  $guiObj->reqSpecEditorType = $cfg->reqCfg['type'];

  return $guiObj;
}

/**
 *
 */
function getTargetTestCases(&$dbHandler,&$argsObj)
{
  $mgr = new testsuite($dbHandler);
  $items = $mgr->get_testcases_deep($argsObj->id,'only_id');
  
  if(!is_null($argsObj->tcaseSet))
  {  
    $rr = array_intersect($items,$argsObj->tcaseSet);
    $items = $rr;
  }

  return $items;
}



function checkRights(&$db,&$user)
{
  return ($user->hasRight($db,'mgt_view_req') && $user->hasRight($db,'mgt_modify_req'));
}
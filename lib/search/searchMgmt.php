<?php
/** 
 *  TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 *  @filesource   search.php
 *  @author       Francisco Mancardi
 * 
 *  @internal revision
 *  @since 1.9.16
 */

require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$smarty = new TLSmarty();
$smarty->tlTemplateCfg = $templateCfg = templateConfiguration();

$cfg = array('testcase' => config_get('testcase_cfg'),'testcase_reorder_by' => config_get('testcase_reorder_by'),
             'spec' => config_get('spec_cfg'));

list($args,$gui,$grants) = initializeEnv($db);

processSearch($db,$smarty,$args,$gui,$grants,$cfg);

/**
 * 
 *
 */
function init_args(&$dbHandler)
{
  $_REQUEST=strings_stripSlashes($_REQUEST);

  $iParams = array("edit" => array(tlInputParameter::STRING_N,0,50),
                   "target" => array(tlInputParameter::STRING_N,0,200),
                   "caller" => array(tlInputParameter::STRING_N,0,10));               

  $args = new stdClass();
  R_PARAMS($iParams,$args);

  $tprojectMgr = new testproject($dbHandler);
  
  $cfg = config_get('testcase_cfg');
  $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
  $args->feature = $args->edit;
  $args->tcaseTestProject = null;
  $args->viewerArgs = null;

  $args->automationEnabled = 0;
  $args->requirementsEnabled = 0;
  $args->testPriorityEnabled = 0;
  $args->tcasePrefix = trim($args->tcasePrefix);
  $args->form_token = isset($_REQUEST['form_token']) ? $_REQUEST['form_token'] : 0;



  // For more information about the data accessed in session here, see the comment
  // in the file header of lib/functions/tlTestCaseFilterControl.class.php.
  $args->refreshTree = getSettingFromFormNameSpace('edit_mode','setting_refresh_tree_on_action');

    
  switch($args->feature)
  {
    case 'testsuite':
      $args->viewerArgs = null;
      $_SESSION['setting_refresh_tree_on_action'] = ($args->refreshTree) ? 1 : 0;
    break;
     
    case 'testcase':
      $args->viewerArgs = array('action' => '', 'msg_result' => '', 'user_feedback' => '',
                                'disable_edit' => 0, 'refreshTree' => 0,
                                'add_relation_feedback_msg' => $args->add_relation_feedback_msg);
            
      $args->id = is_null($args->id) ? 0 : $args->id;
      $args->tcase_id = $args->id;

      if( is_null($args->tcaseTestProject) && $args->id > 0 )
      {
        $args->tcaseTestProject = $tprojectMgr->getByChildID($args->id);
      }
    break;
  }

  if(is_null($args->tcaseTestProject))
  {  
    $args->tcaseTestProject = $tprojectMgr->get_by_id($args->tproject_id);
  }
  $args->requirementsEnabled = $args->tcaseTestProject['opt']->requirementsEnabled;
  $args->automationEnabled = $args->tcaseTestProject['opt']->automationEnabled;
  $args->testPriorityEnabled = $args->tcaseTestProject['opt']->testPriorityEnabled;

  return $args;
}



/**
 * 
 *
 */
function initializeEnv($dbHandler)
{
  $args = init_args($dbHandler);
  $gui = new stdClass();

  $grant2check = array('mgt_modify_tc','mgt_view_req','testplan_planning',
                       'mgt_modify_product',
                       'mgt_modify_req','testcase_freeze', 
                       'testproject_edit_executed_testcases','testproject_delete_executed_testcases');
  $grants = new stdClass();
  foreach($grant2check as $right)
  {
      $grants->$right = $_SESSION['currentUser']->hasRight($dbHandler,$right,$args->tproject_id);
      $gui->$right = $grants->$right;
  }
  
  $gui->target = $args->target;
  $gui->form_token = $args->form_token;
  $gui->tproject_id = $args->tproject_id;
  $gui->page_title = lang_get('container_title_' . $args->feature);
  $gui->requirementsEnabled = $args->requirementsEnabled; 
  $gui->automationEnabled = $args->automationEnabled; 
  $gui->testPriorityEnabled = $args->testPriorityEnabled;
  $gui->show_mode = $args->show_mode;
  $lblkey = config_get('testcase_reorder_by') == 'NAME' ? '_alpha' : '_externalid';
  $gui->btn_reorder_testcases = lang_get('btn_reorder_testcases' . $lblkey);

  // has sense only when we work on test case
  $dummy = testcase::getLayout();
  $gui->tableColspan = $dummy->tableToDisplayTestCaseSteps->colspan;

  $gui->platforms = null;
  $gui->loadOnCancelURL = '';
  $gui->attachments = null;
  $gui->direct_link = null;
  $gui->steps_results_layout = config_get('spec_cfg')->steps_results_layout;
  $gui->bodyOnUnload = "storeWindowSize('TCEditPopup')";
  $gui->viewerArgs = $args->viewerArgs;
  $gui->caller = $args->caller;


  return array($args,$gui,$grants);
}


/**
 *
 *
 */
function systemWideTestCaseSearch(&$dbHandler,&$argsObj,$glue)
{
  // Attention: 
  // this algorithm has potential flaw (IMHO) because we can find the glue character
  // in situation where it's role is not this.
  // Anyway i will work on this in the future (if I've time)
  //
  if (strpos($argsObj->targetTestCase,$glue) === false)
  {
    // We suppose user was lazy enough to do not provide prefix,
    // then we will try to help him/her
    $argsObj->targetTestCase = $argsObj->tcasePrefix . $argsObj->targetTestCase;
  }

  if( !is_null($argsObj->targetTestCase) )
  {
    // parse to get JUST prefix, find the last glue char.
    // This useful because from navBar, user can request search of test cases that belongs
    // to test project DIFFERENT to test project setted in environment
    if( ($gluePos = strrpos($argsObj->targetTestCase, $glue)) !== false)
    {
      $tcasePrefix = substr($argsObj->targetTestCase, 0, $gluePos);
    }

    $tprojectMgr = new testproject($dbHandler);
    $argsObj->tcaseTestProject = $tprojectMgr->get_by_prefix($tcasePrefix);

    $tcaseMgr = new testcase($dbHandler);
    $argsObj->tcase_id = $tcaseMgr->getInternalID($argsObj->targetTestCase);
    $dummy = $tcaseMgr->get_basic_info($argsObj->tcase_id,array('number' => $argsObj->tcaseVersionNumber));
    if(!is_null($dummy))
    {
      $argsObj->tcversion_id = $dummy[0]['tcversion_id'];
    }
  }
}

/**
 *
 */
function getSettingFromFormNameSpace($mode,$setting)
{
  $form_token = isset($_REQUEST['form_token']) ? $_REQUEST['form_token'] : 0;
  $sd = isset($_SESSION[$mode]) && isset($_SESSION[$mode][$form_token]) ? $_SESSION[$mode][$form_token] : null;
  
  $rtSetting = isset($sd[$setting]) ? $sd[$setting] : 0;
  return $rtSetting;
}

/**
 *
 *
 */ 
function processSearch(&$dbHandler,$tplEngine,$args,&$gui,$grants,$cfg)
{
  $get_path_info = false;
  $item_mgr = new testcase($dbHandler);


  // has sense only when we work on test case
  $dummy = testcase::getLayout();
  $gui->tableColspan = $dummy->tableToDisplayTestCaseSteps->colspan;

  $gui->viewerArgs['refresh_tree'] = 'no';
  $gui->path_info = null;
  $gui->platforms = null;
  $gui->loadOnCancelURL = '';
  $gui->attachments = null;
  $gui->direct_link = null;
  $gui->steps_results_layout = $cfg['spec']->steps_results_layout;
  $gui->bodyOnUnload = "storeWindowSize('TCEditPopup')";

  $templateCfg = templateConfiguration();

  // need to initialize search fields
  $xbm = getSearchSkeleton();
  $xbm->warning_msg = lang_get('no_records_found');
  $xbm->pageTitle = lang_get('search_title');
  $xbm->tableSet = null;
  $xbm->doSearch = false;
  $xbm->tproject_id = $args->tproject_id;
  $xbm->target = $args->target;

  $tproject_mgr = new testproject($dbHandler);
  $xbm->keywords = $tproject_mgr->getKeywords($args->tproject_id);
  $xbm->filter_by['keyword'] = !is_null($xbm->keywords);

  $reqCfg = config_get('req_cfg');
  $xbm->rtypes = array_flip(init_labels($reqCfg->type_labels));
  foreach ($xbm->rtypes as $key => $value) 
  {
    $xbm->rtypes[$key] = 'RQ' . $value;  
  }
  $xbm->rtypes = array_flip($xbm->rtypes);
  $reqSpecCfg = config_get('req_spec_cfg');
  $rsTypes = init_labels($reqSpecCfg->type_labels);
  $xbm->rtypes = $rsTypes+$xbm->rtypes;

  $xbm->reqStatusDomain = init_labels($reqCfg->status_labels);

  if( strlen(trim($args->target)) > 0)
  {
    $xbm->rs_scope = $xbm->rs_title = 1;
    $xbm->tc_summary = $xbm->tc_title = 1;
    $xbm->tc_steps = $xbm->tc_expected_results = $xbm->tc_id = 1;
    $xbm->tc_preconditions = $xbm->ts_summary = $xbm->ts_title = 1;
    $xbm->rq_scope = $xbm->rq_title = $xbm->rq_doc_id = 1;

    $xbm->or_checked = ' checked="checked" ';
    $xbm->and_checked = '';
  }

  $tplEngine->assign('gui',$xbm);

  // var_dump($templateCfg->template_dir);die();

  $tplEngine->display($templateCfg->template_dir . 'searchResults.tpl');
}

/**
 *
 */
function getSearchSkeleton($userInput=null)
{
  $sk = new stdClass();

  $sk->searchText = null;

  return $sk;
}


<?php
/** 
 *  TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 *  @filesource   searchMgmt.php
 *  @author       Francisco Mancardi
 * 
 *  @internal revision
 *  @since 1.9.16
 */

require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);
processSearch($db);

/**
 * 
 *
 */
function init_args(&$dbHandler)
{
  $_REQUEST=strings_stripSlashes($_REQUEST);

  $iParams = array("target" => array(tlInputParameter::STRING_N,0,200),
                   "caller" => array(tlInputParameter::STRING_N,0,20));               

  $args = new stdClass();
  R_PARAMS($iParams,$args);

  $tprojectMgr = new testproject($dbHandler);
  $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
  $args->form_token = isset($_REQUEST['form_token']) ? $_REQUEST['form_token'] : 0;

  if(is_null($args->tcaseTestProject))
  {  
    $args->tcaseTestProject = $tprojectMgr->get_by_id($args->tproject_id);
  }
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
  $gui->caller = trim($args->caller);
  return array($args,$gui,$grants);
}


/**
 *
 *
 */ 
function processSearch(&$dbHandler)
{
  //$cfg = array('testcase' => config_get('testcase_cfg'),
  //             'testcase_reorder_by' => config_get('testcase_reorder_by'),
  //             'spec' => config_get('spec_cfg'));

  // list($args,$gui,$grants) = initializeEnv($dbHandler);

  $tplEngine = new TLSmarty();
  $tplEngine->tlTemplateCfg = $templateCfg = templateConfiguration();

  $cmdMgr = new searchCommands($dbHandler);
  $cmdMgr->initEnv();

  $args = $cmdMgr->getArgs();


  // need to initialize search fields
  $xbm = $cmdMgr->getGui();
  $xbm->warning_msg = lang_get('no_records_found');
  $xbm->pageTitle = lang_get('search_title');

  /*
  $xbm->doSearch = false;
  $xbm->tproject_id = $args->tproject_id;
  $xbm->target = $args->target;
  $xbm->keyword_id = 0;
  $xbm->custom_field_id = 0;
  $xbm->custom_field_value = null;
  $xbm->creation_date_from = $xbm->creation_date_to = null;
  $xbm->modification_date_from = $xbm->modification_date_to = null;
  $xbm->created_by = $xbm->edited_by = null;
  $xbm->tcasePrefix = null;
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
  $xbm->rType = 0;

  $xbm->reqStatusDomain = init_labels($reqCfg->status_labels);
  $xbm->reqStatus = 0;

  $xbm->or_checked = ' checked="checked" ';
  $xbm->and_checked = '';

  */

  /*
  */

  $xbm->forceSearch = (strlen(trim($args->target)) > 0);
  $xbm->caller = basename(__FILE__);

  $xbm->tc_summary = $xbm->tc_title = 1;
  $xbm->tc_steps = $xbm->tc_expected_results = $xbm->tc_id = 1;
  $xbm->tc_preconditions = $xbm->ts_summary = $xbm->ts_title = 1;

  $xbm->reqEnabled = $cmdMgr->isReqFeatureEnabled($args->tproject_id);
  if($xbm->reqEnabled)
  {
    $xbm->rs_scope = $xbm->rs_title = 1;
    $xbm->rq_scope = $xbm->rq_title = $xbm->rq_doc_id = 1;
  }  


  /*
  $xbm->filter_by['custom_fields'] = !is_null($args->cf);
  $xbm->cf = $args->cf;
  $xbm->filter_by['keyword'] = !is_null($args->keywords);
  $xbm->keywords = $args->keywords; 
  $xbm->tcWKFStatus = 0;
  $xbm->tcWKFStatusDomain = searchCommands::getTestCaseWKFStatusDomain();
  */

  $tplEngine->assign('gui',$xbm);
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


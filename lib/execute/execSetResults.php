<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource  execSetResults.php
 *
 * IMPORTANT DEVELOPMENT NOTICE - about $args->testcases_to_show 
 *
 * Normally this script is called from the tree.
 * Filters and other conditions (example display test cases just assigned to me,etc)
 * can be applied, creating a set of test cases that can be used.
 * Due to size restrictions on POST variables this info is transfered via $_SESSION.
 * 
 * But because we have choosen to add access to this script from other features
 * we have forgot to populate this info.
 * This is the reason for several issues.
 * The approach will be to understand who is the caller and apply different logics
 * instead of recreate the logic to populate $_SESSION 
 * (I think this approach will be simpler).
 * 
 *
 * Note about step info
 * is present in gui->map_last_exec
 *
 *
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once("attachments.inc.php");
require_once("specview.php");
require_once("web_editor.php");
require_once('event_api.php');

$cfg = getCfg();
require_once(require_web_editor($cfg->editorCfg['type']));

if( $cfg->exec_cfg->enable_test_automation ) {
  require_once('remote_exec.php');
}

// CRITIC:
// If call to testlinkInitPage() is done AFTER require_once for BTS
// log to event viewer fails, but log to file works ok
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tcversion_id = null;
$submitResult = null;
list($args,$its,$cts) = init_args($db,$cfg);

$smarty = new TLSmarty();
$smarty->assign('tsuite_info',null);

$tree_mgr = new tree($db);
$tplan_mgr = new testplan($db);
$tcase_mgr = new testcase($db);
$exec_cfield_mgr = new exec_cfield_mgr($db,$args->tproject_id);
$attachmentRepository = tlAttachmentRepository::create($db);
$req_mgr = new requirement_mgr($db);

$gui = initializeGui($db,$args,$cfg,$tplan_mgr,$tcase_mgr,$its,$cts);

$_SESSION['history_on'] = $gui->history_on;
$attachmentInfos = null;

$do_show_instructions = ($args->level == "" || $args->level == 'testproject') ? 1 : 0;
if ($do_show_instructions) {
  show_instructions('executeTest');
  exit();
}

// Testplan executions and result archiving. 
// Checks whether execute cases button was clicked
if($args->doExec == 1 && !is_null($args->tc_versions) && count($args->tc_versions)) {
  $gui->remoteExecFeedback = launchRemoteExec($db,$args,$gui->tcasePrefix,$tplan_mgr,$tcase_mgr);
}  

list($linked_tcversions,$itemSet) = 
  getLinkedItems($args,$gui->history_on,$cfg,$tcase_mgr,$tplan_mgr);

$tcase_id = 0;
$userid_array = null;
if(!is_null($linked_tcversions)) {
  $items_to_exec = array();
  $_SESSION['s_lastAttachmentInfos'] = null;
  if($args->level == 'testcase') {
    // passed by reference to be updated inside function
    // $gui, $args
    $tcase = null;
    list($tcase_id,$tcversion_id) = 
      processTestCase($tcase,$gui,$args,$cfg,$linked_tcversions,
                      $tree_mgr,$tcase_mgr,$attachmentRepository);
  } else {
    processTestSuite($db,$gui,$args,$itemSet,$tree_mgr,$tcase_mgr,$attachmentRepository);
    $tcase_id = $itemSet->tcase_id;
    $tcversion_id = $itemSet->tcversion_id;
  }

  // Send Event for Drawing UI from plugins
  $ctx = array('tplan_id' => $args->tplan_id,
               'build_id' => $args->build_id,
               'tcase_id' => $tcase_id,
               'tcversion_id' => $tcversion_id);
  
  $gui->plugins = array();
  $gui->plugins['EVENT_TESTRUN_DISPLAY'] = 
    event_signal('EVENT_TESTRUN_DISPLAY', $ctx);
  
  // check if value is an array before calling implode to avoid warnings in event log
  $gui->tcversionSet = is_array($tcversion_id) ? implode(',',$tcversion_id) : $tcversion_id;

  // will create a record even if the testcase version has not been executed (GET_NO_EXEC)
  //
  // Can be DONE JUST ONCE AFTER write results to DB
  // --------------------------------------------------------------------------------------------
  // Results to DB
  // 
  // 20130917 - this implementation regarding save_results is confusing.
  // why ?
  // because in some situations args->save_results is a number (0) an in other is an array
  // with just one element with key => test case version ID executed.
  //
  if ($args->doSave || $args->doNavigate) {
    // this has to be done to do not break logic present on write_execution()
    $args->save_results = $args->save_and_next ? $args->save_and_next : 
                          ($args->save_results ? $args->save_results : $args->save_and_exit);

     
    if( $args->save_results || $args->do_bulk_save) {  
      // Need to get Latest execution ID before writing
      $lexid = 0;
      if($args->copyIssues && $args->level == 'testcase') {
        $lexid = $tcase_mgr->getSystemWideLastestExecutionID($args->version_id);
      }  

      $_REQUEST['save_results'] = $args->save_results;
      list($execSet,$gui->addIssueOp) = write_execution($db,$args,$_REQUEST,$its);
      
      if($args->assignTask) {
        $fid = $tplan_mgr->getFeatureID($args->tplan_id,$args->platform_id,$args->version_id);
        $taskMgr = new assignment_mgr($db);
        $taskDomain = $taskMgr->get_available_types();
        $taskStatusDomain = $taskMgr->get_available_status();

        $fmap[$fid]['user_id'] = $fmap[$fid]['assigner_id'] = $args->user_id;
        $fmap[$fid]['build_id'] = $args->build_id;
        $fmap[$fid]['type'] = $taskDomain['testcase_execution']['id'];
        $fmap[$fid]['status'] = $taskStatusDomain['open']['id'];
        $taskMgr->assign($fmap);
      }  

      if($lexid > 0 && $args->copyIssues && $args->level == 'testcase') {
        copyIssues($db,$lexid,$execSet[$args->version_id]);
      }

      // Propagate events
      $ctx = array('id' => $execSet[$tcversion_id],
                   'tplan_id' => $args->tplan_id,
                   'build_id' => $args->build_id,
                   'tcase_id' => $tcase_id,
                   'status'   => $args->statusSingle[$args->version_id],
                   'directLink' => $args->direct_link);
      event_signal('EVENT_EXECUTE_TEST', $ctx);
  	  $tc_info = $tcase_mgr->getExternalID($tcase_id);
  	  $tp_info = $tplan_mgr->get_by_id($args->tplan_id);
  	  $build_info = $tplan_mgr->get_build_by_id($args->tplan_id,$args->build_id);

  	  logAuditEvent(TLS("audit_exec_saved",$tc_info[0],$build_info['name'],$tp_info['name']),"CREATE",$execSet[$tcversion_id],"execution");
      }

    // Need to re-read to update test case status
    if ($args->save_and_next || $args->doMoveNext || $args->doMovePrevious) {  
      $nextInChain = -1;
      if( $cfg->exec_cfg->exec_mode->save_and_move == 'unlimited' ) {
        if( $args->caller ==  'tcAssignedToMe') {
          $optz = array('order_by' => 'ORDER BY TPTCV.node_order');
          $filters['build_id'] = $args->build_id;

          $xx = $tcase_mgr->get_assigned_to_user(
                  $args->user_id, $args->tproject_id,
                  array($args->tplan_id), $optz, $filters);
          $xx = current($xx);

          // key test case id
          // inside an idx array
          $args->testcases_to_show = array_keys($xx);
        }

        $chainLen = count($args->testcases_to_show);
        foreach($args->testcases_to_show as $ix => $val) {
          if( $val == $args->tc_id) {
            $nextInChain = $ix+1;
            if($nextInChain == $chainLen) {
              $nextInChain = 0;  
            }  
            break;
          }  
        }
      }  
        
      // IMPORTANT DEVELOPMENT NOTICE
      // Normally this script is called from the tree.
      // Filters and other conditions (example display test cases just assigned to me,etc)
      // can be applied, creating a set of test cases that can be used.
      // Due to size restrictions on POST variables this info is transfered via $_SESSION.
      //
      // But because we have choosen to add access to this script from other features
      // we have forgot to populate this info.
      // This is the reason for several issues.
      // The approach will be to understand who is the caller and apply different logics
      // instead of recreate the logic to populate $_SESSION (I think this approach
      // will be simpler).
      $doSingleStep = is_null($args->testcases_to_show);
      $args->testcases_to_show = (array)$args->testcases_to_show;
        
      $opt4sibling = array('move' => $args->moveTowards);
      switch ($args->caller) {
        case 'tcAssignedToMe':
          $doSingleStep = true;
          $opt4sibling['assigned_to'] = array('user_id' => $args->user_id, 'build_id' => $args->build_id);
        break;
          
        default:
        break;  
      }
  
      switch($cfg->exec_cfg->exec_mode->save_and_move) {
        case 'unlimited':
          // get position on chain
          $opx = array('tcase_id' => 
                       $args->testcases_to_show[$nextInChain]);
          $nextItem = $tplan_mgr->get_linked_tcvid($args->tplan_id,$args->platform_id,$opx);
          $nextItem = current($nextItem);
        break;

        case 'limited':
          $nextItem = $tplan_mgr->getTestCaseNextSibling($args->tplan_id,$tcversion_id,$args->platform_id,$opt4sibling);
          if(!$doSingleStep)
          { 
            while (!is_null($nextItem) && !in_array($nextItem['tcase_id'], $args->testcases_to_show)) 
            {
              $nextItem = $tplan_mgr->getTestCaseNextSibling($args->tplan_id,$nextItem['tcversion_id'],
                                                             $args->platform_id,$opt4sibling);
            }
          }
        break;
      }  // cfg

      if( !is_null($nextItem) )
      {
        $tcase_id = $nextItem['tcase_id'];
        $tcversion_id = $nextItem['tcversion_id'];
        
        // Save and Next - Issues with display CF for test plan design - always EMPTY  
        // need info about this test case => need to update linked_tcversions info
        $identity = array('id' => $nextItem['tcase_id'], 'version_id' => $nextItem['tcversion_id']);
        list($lt,$xdm) = getLinkedItems($args,$gui->history_on,$cfg,$tcase_mgr,$tplan_mgr,$identity);
        processTestCase($nextItem,$gui,$args,$cfg,$lt,$tree_mgr,$tcase_mgr,$attachmentRepository);
      }
    }
    else if($args->save_and_exit)
    {
      $args->reload_caller = true;
    }  
  }
  
  if(!$args->reload_caller)
  {  
    if ($args->doDelete)
    {
      $dummy = delete_execution($db,$args->exec_to_delete);
	  if ($dummy){
	    $tc_info = $tcase_mgr->getExternalID($tcase_id);
	    $tp_info = $tplan_mgr->get_by_id($args->tplan_id);
	    $build_info = $tplan_mgr->get_build_by_id($args->tplan_id,$args->build_id);
		logAuditEvent(TLS("audit_exec_deleted",$tc_info[0],$build_info['name'],$tp_info['name']),"DELETE",$args->exec_to_delete,"execution");
	  }
    }

    // Important Notice: 
    // $tcase_id and $tcversions_id, can be ARRAYS 
    // when user enable bulk execution
    if( is_array($tcase_id)) {
      $tcase_id = array_intersect($tcase_id, $args->testcases_to_show);
    }  

    $gui->map_last_exec = 
      getLatestExec($db,$tcase_id,$tcversion_id,$gui,$args,$tcase_mgr);

    $gui->map_last_exec_any_build = null;
    $gui->other_execs=null;
    $testerid = null;
      
    if($args->level == 'testcase') {
      // @TODO 20090815 - franciscom check what to do with platform
      if( $cfg->exec_cfg->show_last_exec_any_build ) {
        $options=array('getNoExecutions' => 1, 'groupByBuild' => 0);
        $gui->map_last_exec_any_build = $tcase_mgr->get_last_execution($tcase_id,$tcversion_id,$args->tplan_id,testcase::ANY_BUILD,
          $args->platform_id,$options);

        // Get UserID and Updater ID for current Version
        $tc_current = $gui->map_last_exec_any_build;
        foreach ($tc_current as $key => $value) {
          $testerid = $value['tester_id'];
          $userid_array[$testerid] = $testerid;
        }      
      }
        
      $gui->req_details = $req_mgr->getActiveForTCVersion($tcversion_id);

      // 2018 - to be refactored
      // $gui->relations = $tcase_mgr->getRelations($tcase_id);
      $idCard = array('tcase_id' => $tcase_id, 'tcversion_id' => $tcversion_id);

      $gui->relations = $tcase_mgr->getRelations($idCard);
      $gui->kw = $tcase_mgr->getKeywordsByIdCard($idCard,array('output' => 'kwfull'));

      if(!is_null($cts)) {
        $gui->scripts[$tcversion_id]=$tcase_mgr->get_scripts_for_testcase($cts, $tcversion_id);
      }

      $gui->other_execs = getOtherExecutions($db,$tcase_id,$tcversion_id,$gui,$args,$cfg,$tcase_mgr);
        
      // Get attachment,bugs, etc
      if(!is_null($gui->other_execs))
      {
        //Get the Tester ID for all previous executions
        foreach ($gui->other_execs as $key => $execution)
        {      
          foreach ($execution as $singleExecution)
          {            
            $testerid = $singleExecution['tester_id'];
            $userid_array[$testerid] = $testerid;
          }      
        }
        $other_info = exec_additional_info($db,$attachmentRepository,$tcase_mgr,$gui->other_execs,
                                           $args->tplan_id,$args->tproject_id, 
                                           $args->issue_tracker_enabled,$its);
                             
        $gui->attachments=$other_info['attachment'];
        $gui->bugs=$other_info['bugs'];
        $gui->other_exec_cfields=$other_info['cfexec_values'];
         
        // this piece of code is useful to avoid error on smarty template due to undefined value   
        if( is_array($tcversion_id) && (count($gui->other_execs) != count($gui->map_last_exec)) )
        {
          foreach($tcversion_id as $version_id)
          {
            if( !isset($gui->other_execs[$version_id]) )
            {
              $gui->other_execs[$version_id]=null;  
            }  
          }
        }
        
      } // if(!is_null($gui->other_execs))
    }   
  }
} // if(!is_null($linked_tcversions))


if($args->reload_caller)
{
  windowCloseAndOpenerReload();
  exit();
} 
else
{
  // Removing duplicate and NULL id's
  unset($userid_array['']);
  $userSet = null;
  if ($userid_array)
  {
    foreach($userid_array as $value)
    {    
      $userSet[] = $value;
    }
  }
  smarty_assign_tsuite_info($smarty,$_REQUEST,$db,$tree_mgr,$tcase_id,$args->tproject_id,$cfg);

  // Bulk is possible when test suite is selected (and is allowed in config)
  if( $gui->can_use_bulk_op = ($args->level == 'testsuite') )
  {
    $xx = null;
    if( property_exists($gui, 'execution_time_cfields') )
    {
      $xx = current((array)$gui->execution_time_cfields);
    }  

    $gui->execution_time_cfields = null;
    
    if( !is_null($xx) )
    {
      $gui->execution_time_cfields[0] = $xx;
    }  
  }  
  initWebEditors($gui,$cfg,$_SESSION['basehref']);

  // To silence smarty errors
  //  future must be initialized in a right way
  $smarty->assign('test_automation_enabled',0);
  $smarty->assign('gui',$gui);
  $smarty->assign('cfg',$cfg);
  $smarty->assign('users',tlUser::getByIDs($db,$userSet));

  $smarty->display($templateCfg->template_dir . $templateCfg->default_template);
} 

/*
  function: 

  args:
  
  returns: 
  
  @internal revisions
*/
function init_args(&$dbHandler,$cfgObj)
{
  $args = new stdClass();
  $_REQUEST = strings_stripSlashes($_REQUEST);

  // Settings and Filters that we put on session to create some 
  // sort of persistent scope, because we have had issues when passing this info
  // using GET mode (size limits)
  //
  // we get info about build_id, platform_id, etc ...
  getSettingsAndFilters($args);
  manageCookies($args,$cfgObj);

  // need to comunicate with left frame, will do via $_SESSION and form_token 
  if( ($args->treeFormToken = isset($_REQUEST['form_token']) ? $_REQUEST['form_token'] : 0) > 0 )
  {
    // do not understand why this do not works OK
    // $_SESSION[$args->treeFormToken]['loadExecDashboard'] = false;
    $_SESSION['loadExecDashboard'][$args->treeFormToken] = false;
  }  


  if(is_null($args->refreshTree))
  {
    $args->refreshTree = isset($_REQUEST['refresh_tree']) ? intval($_REQUEST['refresh_tree']) : 0;  
  }  

  $args->basehref = $_SESSION['basehref'];
  $args->assignTask = isset($_REQUEST['assignTask']) ? 1: 0;
  $args->createIssue = isset($_REQUEST['createIssue']) ? 1: 0;
  $args->copyIssues = isset($_REQUEST['copyIssues']) ? 1: 0;


  $args->tc_id = null;
  $args->tsuite_id = null;
  $args->user = $_SESSION['currentUser'];
  $args->user_id = intval($args->user->dbID);
  $args->id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

  $args->caller = isset($_REQUEST['caller']) ? $_REQUEST['caller'] : 'exec_feature';
  $args->reload_caller = false;

  $args->doExec = isset($_REQUEST['execute_cases']) ? 1 : 0;
  $args->doDelete = isset($_REQUEST['do_delete']) ? $_REQUEST['do_delete'] : 0;
  
  // $args->doMoveNext = isset($_REQUEST['move2next']) ? $_REQUEST['move2next'] : 0;
  $args->doMoveNext = isset($_REQUEST['move2next']) ? 1 : 0;
  
  $args->doMovePrevious = isset($_REQUEST['move2previous']) ? $_REQUEST['move2previous'] : 0;
  $args->moveTowards = $args->doMoveNext ? 'forward' : ($args->doMovePrevious ? 'backward' : null);

  // can be a list, will arrive via form POST
  $args->tc_versions = isset($_REQUEST['tc_version']) ? $_REQUEST['tc_version'] : null;  

  $key2loop = array('level' => '','status' => null, 'statusSingle' => null, 
                    'do_bulk_save' => 0,'save_results' => 0,'save_and_next' => 0, 
                    'save_and_exit' => 0);
  foreach($key2loop as $key => $value)
  {
    $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
  }

 $args->doSave = $args->save_results || $args->save_and_next || 
                 $args->save_and_exit || $args->do_bulk_save;
 
 $args->doNavigate =  $args->doMoveNext || $args->doMovePrevious;


  // See details on: "When nullify filter_status - 20080504" in this file
  if( $args->level == 'testcase' || is_null($args->filter_status) || 
      (!is_array($args->filter_status) && trim($args->filter_status)=='')
    ) {
    $args->filter_status = null;  
  }
  else {
    // 20130306 - franciscom
    // This (without the strlen() check) generated issue 5541: When "Result" filter is used ...
    // at least when result DIFFERENT that NOT RUN is used on filter
    //
    // 20120616 - franciscom
    // some strange thing to investigate, seems that unserialize is invoked
    // under the hood when getting data from $_REQUEST, then this piece
    // of code not only will be useless BUT WRONG, because will try
    // to unserialize something that IS NOT SERIALIZED!!!!

    // After TICKET 6651, may be need to limit size of $args->filter_status
    if(is_string($args->filter_status) && strlen($args->filter_status) > 1) {
      $args->filter_status = json_decode($args->filter_status);
    }
  }
  

  switch($args->level) {
    case 'testcase':
      $args->tc_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null;
      if( !is_null($args->tc_versions) ) {
        $args->tc_id = current($args->tc_versions);
        $args->id = $args->tc_id;
        $args->version_id = key($args->tc_versions);
      } 
      $args->tsuite_id = null;
    break;
      
    case 'testsuite':
      $args->tsuite_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null;
      $args->tc_id = null;
    break;
  }
    
  
  $args->tsuitesInBranch = null; 
  if( !is_null($args->tsuite_id) ) {
    // will get all test suites in this branch, in order to limit amount of data returned 
    // by functions/method that collect linked tcversions
    // THIS COLLECT ONLY FIRST LEVEL UNDER test suite, do not do deep search
    // Need to understand is still needed
    $tsuite_mgr = new testsuite($dbHandler);
    $xx = $tsuite_mgr->get_children($args->tsuite_id,array('details' => 'id'));
    $ldx = count($xx);
    $xx[$ldx] = $args->tsuite_id;
    $args->tsuitesInBranch = $xx;
    unset($tsuite_mgr);
  }


  // TICKET 5630: Test Results by direct link ...
  $args->tplan_id = intval(isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testplanID']);
  $args->tproject_id = intval(isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : $_SESSION['testprojectID']);

  if($args->tproject_id <= 0) {
    $tree_mgr = new tree($dbHandler);
    $dm = $tree_mgr->get_node_hierarchy_info($args->tplan_id);
    $args->tproject_id = $dm['parent_id']; 
  }


  $args->addLinkToTL = isset($_REQUEST['addLinkToTL']) ? TRUE : FALSE;

  // Do this only on single execution mode
  // get issue tracker config and object to manage TestLink - BTS integration 
  $args->itsCfg = null;
  $its = null;

  $tproject_mgr = new testproject($dbHandler);
  $info = $tproject_mgr->get_by_id($args->tproject_id);
  unset($tproject_mgr);  
  $bug_summary['minLengh'] = 1; 
  $bug_summary['maxLengh'] = 1; 

  if( ($args->issue_tracker_enabled = $info['issue_tracker_enabled']) ) {
    $it_mgr = new tlIssueTracker($dbHandler);
    $args->itsCfg = $it_mgr->getLinkedTo($args->tproject_id);
    $its = $it_mgr->getInterfaceObject($args->tproject_id);
    
    if(!is_null($args->itsCfg) && !is_null($its)) {
      $bug_summary['maxLengh'] = $its->getBugSummaryMaxLength(); 
    }  
    unset($it_mgr);
  }

  initArgsIssueOnTestCase($args,$bug_summary);
  
  initArgsIssueOnSteps($args,$bug_summary);

  // get code tracker config and object to manage TestLink - CTS integration
  $args->ctsCfg = null;
  $cts = null;

  if( ($args->codeTrackerEnabled = intval($info['code_tracker_enabled'])) ) {
    $ct_mgr = new tlCodeTracker($dbHandler);
    $args->ctsCfg = $ct_mgr->getLinkedTo($args->tproject_id);
    $cts = $ct_mgr->getInterfaceObject($args->tproject_id);

    unset($ct_mgr);
  }

  return array($args,$its,$cts);
}

/**
 *
 *
 */
function initArgsIssueOnTestCase(&$argsObj,$bugSummaryProp) {

  $inputCfg = array("bug_notes" => array("POST",tlInputParameter::STRING_N),
                    "issueType" => array("POST",tlInputParameter::INT_N),
                    "issuePriority" => array("POST",tlInputParameter::INT_N),
                    "artifactComponent" => array("POST",tlInputParameter::ARRAY_INT),
                    "artifactVersion" => array("POST",tlInputParameter::ARRAY_INT));

  $inputCfg["bug_summary"] = array("POST",tlInputParameter::STRING_N);

  // hmm this MAGIC needs to be commented 
  if(!$argsObj->do_bulk_save) {
    $inputCfg["bug_summary"][2] = $bugSummaryProp['minLengh'];
    $inputCfg["bug_summary"][3] = $bugSummaryProp['maxLengh']; 
  } 

  I_PARAMS($inputCfg,$argsObj);

}

/**
 *
 *
 */
function initArgsIssueOnSteps(&$argsObj,$bugSummaryProp) {
  $arrayOfInt = array("POST",tlInputParameter::ARRAY_INT);

  $cfg = array("issueBodyForStep" => array("POST",tlInputParameter::ARRAY_STRING_N),
               "issueTypeForStep" => $arrayOfInt,
               "issuePriorityForStep" => $arrayOfInt);

  $cfg["issueSummaryForStep"] = array("POST",tlInputParameter::ARRAY_STRING_N);

  // hmm this MAGIC needs to be commented 
  if(!$argsObj->do_bulk_save) {
    $cfg["issueSummaryForStep"][2] = $bugSummaryProp['minLengh'];
    $cfg["issueSummaryForStep"][3] = $bugSummaryProp['maxLengh']; 
  } 

  I_PARAMS($cfg,$argsObj);

  // Special
  // Array of Check Boxes:
  // 'issueForStep','addLinkToTLForStep'
  $sk = array('issueForStep','addLinkToTLForStep', 
              'artifactComponentForStep','artifactVersionForStep');
  
  foreach($sk as $kt) {
    $argsObj->$kt = null;
    if(isset($_REQUEST[$kt])) {
      $argsObj->$kt = $_REQUEST[$kt];
    }  
  }

}

/*
  function: 

  args :
  
  returns: 

*/
function manage_history_on($hash_REQUEST,$hash_SESSION,
                           $exec_cfg,$btn_on_name,$btn_off_name,$hidden_on_name)
{


  if( isset($hash_REQUEST[$btn_on_name]) )
  {
    $history_on = true;
  }
  elseif(isset($_REQUEST[$btn_off_name]))
  {
    $history_on = false;
  }
  elseif (isset($_REQUEST[$hidden_on_name]))
  {
    $history_on = $_REQUEST[$hidden_on_name];
  }
  elseif (isset($_SESSION[$hidden_on_name]))
  {
    $history_on = $_SESSION[$hidden_on_name];
  }
  else
  {
    $history_on = $exec_cfg->history_on;
  }

  return $history_on ? true : false;
}

/*
  function: get_ts_name_details

  args :
  
  returns: map with key=TCID
           values= assoc_array([tsuite_id => 5341
                               [details] => my detailas ts1
                               [tcid] => 5343
                               [tsuite_name] => ts1)
*/
function get_ts_name_details(&$db,$tcase_id)
{
  $tables = array();
    $tables['testsuites'] = DB_TABLE_PREFIX . 'testsuites';
    $tables['nodes_hierarchy'] = DB_TABLE_PREFIX . 'nodes_hierarchy';

    
  $rs = '';
  $do_query = true;
  $sql = "SELECT TS.id AS tsuite_id, TS.details, 
               NHA.id AS tc_id, NHB.name AS tsuite_name 
        FROM {$tables['testsuites']} TS, {$tables['nodes_hierarchy']} NHA, 
             {$tables['nodes_hierarchy']} NHB
        WHERE TS.id=NHA.parent_id
        AND   NHB.id=NHA.parent_id ";
  if( is_array($tcase_id) && count($tcase_id) > 0)
  {
    $in_list = implode(",",$tcase_id);
    $sql .= "AND NHA.id IN (" . $in_list . ")";
  }
  else if(!is_null($tcase_id))
  {
    $sql .= "AND NHA.id={$tcase_id}";
  }
  else
  {
    $do_query = false;
  }
  if($do_query)
  {
    $rs = $db->fetchRowsIntoMap($sql,'tc_id');
  }
  return $rs;
}

/*
  function: 

  args :
  
  returns: 

*/
function smarty_assign_tsuite_info(&$smarty,&$request_hash, &$db,&$tree_mgr,$tcase_id,$tproject_id,$cfgObj)
{
  if( ($safeTCaseID = intval($tcase_id)) <= 0)
  {
    // hmm, no good
    return;
  }  

  $fpath = $tree_mgr->get_full_path_verbose($tcase_id, array('output_format' => 'id_name'));
  $tsuite_info = get_ts_name_details($db,$tcase_id);

  foreach($fpath as $key => $value)
  {
      unset($value['name'][0]);  // Remove test plan name
      unset($value['node_id'][0]);  // Remove test plan name
      $str='';
      foreach($value['name'] as $jdx => $elem)
      {
        $str .= "<a href=\"javascript:openTestSuiteWindow(" . $value['node_id'][$jdx] . ")\"> ";
        // BUGID 4324 - Julian - Encoding did not work properly
        $str .= htmlspecialchars($elem,ENT_QUOTES) . '</a>/';
      }
      $tsuite_info[$key]['tsuite_name']=$str;  
  }
  $smarty->assign('tsuite_info',$tsuite_info);
  
  // ------------------------------------------------------------------------------
  if(!is_null($tsuite_info))
  {
    $ckObj = new stdClass();
    $ckCfg = config_get('cookie');
    $cookieKey = $ckCfg->prefix . 'TL_execSetResults_tsdetails_view_status';

    $exec_cfg = config_get('exec_cfg');
    $a_tsvw=array();
    $a_ts=array();
    $a_tsval=array();
      
    $tsuite_mgr = New testsuite($db);
     
    foreach($tsuite_info as $key => $elem)
    {
      $main_k = 'tsdetails_view_status_' . $key;
      $a_tsvw[] = $main_k;
      $a_ts[] = 'tsdetails_' . $key;
      $expand_collapse = 0;
      if( !isset($request_hash[$main_k]) )
      {
        // First time we are entered here => we can need to understand how to proceed
        switch($exec_cfg->expand_collapse->testsuite_details)
        {
          case LAST_USER_CHOICE:
          if (isset($_COOKIE[$cookieKey]) ) 
          {
            $expand_collapse = $_COOKIE[$cookieKey];
          }
          break;  
          
          default:
            $expand_collapse = $exec_cfg->expand_collapse->testsuite_details;
          break;
        } 
      }
      $a_tsval[] = isset($request_hash[$main_k]) ? $request_hash[$main_k] : $expand_collapse;
      $tsuite_id = $elem['tsuite_id'];
      $tc_id = $elem['tc_id'];
      if(!isset($cached_cf[$tsuite_id]))
      {
        $cached_cf[$tsuite_id] = $tsuite_mgr->html_table_of_custom_field_values($tsuite_id,'design',null,$tproject_id);
      }
      $ts_cf_smarty[$tc_id] = $cached_cf[$tsuite_id];
    }

    if( count($a_tsval) > 0 ) {
      $ckObj->value = $a_tsval[0];
      tlSetCookie($ckObj);
    }
      
    $smarty->assign('tsd_div_id_list',implode(",",$a_ts));
    $smarty->assign('tsd_hidden_id_list',implode(",",$a_tsvw));
    $smarty->assign('tsd_val_for_hidden_list',implode(",",$a_tsval));
      
    $smarty->assign('ts_cf_smarty',$ts_cf_smarty);
  }

}  
// --------------------------------------------------------------------------------


/*
  function: 

  args :
  
  returns: 

  @internal revisions:
*/
function exec_additional_info(&$db, $attachmentRepository, &$tcase_mgr, $other_execs, 
                              $tplan_id, $tproject_id, $bugInterfaceOn, $bugInterface)
{
  $attachmentInfos = null;
  $bugs = null;
  $cfexec_values = null;

  
  foreach($other_execs as $tcversion_id => $execInfo)
  {
    $num_elem = sizeof($execInfo);   
    for($idx = 0;$idx < $num_elem;$idx++)
    {
      $exec_id = $execInfo[$idx]['execution_id'];
      $aInfo = getAttachmentInfos($attachmentRepository,$exec_id,'executions',true,1);
      if ($aInfo)
      {
        $attachmentInfos[$exec_id] = $aInfo;
      }
      
      if($bugInterfaceOn)
      {
        $the_bugs = get_bugs_for_exec($db,$bugInterface,$exec_id);
        if(count($the_bugs) > 0)
        {
          $bugs[$exec_id] = $the_bugs;
        }  
      }


      // Custom fields
      $cfexec_values[$exec_id] = $tcase_mgr->html_table_of_custom_field_values($tcversion_id,'execution',null,
                                                                               $exec_id,$tplan_id,$tproject_id);
    }
  }
  
  $info = array( 'attachment' => $attachmentInfos,
                 'bugs' => $bugs,
                 'cfexec_values' => $cfexec_values);      
               
  return $info;
} //function end


/*
  function: 

  args : context hash with following keys
       target => array('tc_versions' => array, 'version_id' =>, 'feature_id' => array) 
       context => array with keys 
                     tproject_id
                     tplan_id
                     platform_id
                     build_id
                     user_id
  
  
  returns: 

*/
function do_remote_execution(&$dbHandler,$context)
{
  $debugMsg = "File:" . __FILE__ . " Function: " . __FUNCTION__;
  
  $tables = array();
  $tables['executions'] = DB_TABLE_PREFIX . 'executions';

  $resultsCfg = config_get('results');
  $tc_status = $resultsCfg['status_code'];
  $tree_mgr = new tree($dbHandler);
  $cfield_mgr = new cfield_mgr($dbHandler);
  
  $ret = null;
  $executionResults = array();

  $myResult = array();
  $sql = " /* $debugMsg */ INSERT INTO {$tables['executions']} " . 
         " (testplan_id,platform_id,build_id,tester_id,execution_type," .
         "  tcversion_id,execution_ts,status,notes) " .
         " VALUES ({$context['context']['tplan_id']}, " . 
         "      {$context['context']['platform_id']}, " .
         "      {$context['context']['build_id']}," .
         " {$context['context']['user_id']}," . TESTCASE_EXECUTION_TYPE_AUTO . ",";

  // have we got multiple test cases to execute ?
  $target = &$context['target'];
  foreach($target['tc_versions'] as $version_id => $tcase_id)
  {
    $ret[$version_id] = array("verboseID" => null,
                              "status" => null,"notes" => null,"system" => null,
                              "scheduled" => null, "timestamp" => null);

    $tcaseInfo = $tree_mgr->get_node_hierarchy_info($tcase_id);
    $tcaseInfo['version_id'] = $version_id;
    
    // For each test case version we can have a different server config
    $serverCfg = $cfield_mgr->getXMLRPCServerParams($version_id,$target['feature_id'][$version_id]);
    $execResult[$version_id] = executeTestCase($tcaseInfo,$serverCfg,$context['context']); // RPC call

    
    $tryWrite = false;
    switch($execResult[$version_id]['system']['status'])
    {
      case 'configProblems':
        $tryWrite = false;
      break;
      
      case 'connectionFailure':
        $tryWrite = false;
      break;
        
      case 'ok';
        $tryWrite = true;
      break;  
    }
    
    if( $tryWrite )
    {
      $trun = &$execResult[$version_id]['execution'];
      $ret[$version_id]["status"] = strtolower($trun['result']);
      $ret[$version_id]["statusVerbose"] = $trun['resultVerbose'];
      $ret[$version_id]["notes"] = trim($trun['notes']);
      if( $trun['scheduled'] == 'now' )
      {
        $notes = $dbHandler->prepare_string($ret[$version_id]["notes"]);

        if( $ret[$version_id]["status"] != $tc_status['passed'] && 
          $ret[$version_id]["status"] != $tc_status['failed'] && 
            $ret[$version_id]["status"] != $tc_status['blocked'])
        {
            $ret[$version_id]["status"] = $tc_status['blocked'];
        }
        
        //
        $sql2exec = $sql . $version_id . "," . $dbHandler->db_now() . 
              ", '{$ret[$version_id]["status"]}', '{$notes}' )"; 
        $dbHandler->exec_query($sql2exec);
      }
      else
      {
        $ret[$version_id]["scheduled"] = $trun['scheduled'];
        $ret[$version_id]["timestamp"]= $trun['timestampISO'];
      }
    }
    else
    {
      $ret[$version_id]["system"] = $execResult[$version_id]['system'];
    }
  }
  
  return $ret;
}


/*
  function: initializeExecMode 

  args:
  
  returns: 

*/
function initializeExecMode(&$db,$exec_cfg,$userObj,$tproject_id,$tplan_id)
{

    $simple_tester_roles=array_flip($exec_cfg->simple_tester_roles);
    $effective_role = $userObj->getEffectiveRole($db,$tproject_id,$tplan_id);
    
  // Role is considered tester if:
  // role == TL_ROLES_TESTER OR Role has Test Plan execute but not Test Plan planning
  //
  //
  $can_execute = $effective_role->hasRight('testplan_execute');
  $can_manage = $effective_role->hasRight('testplan_planning');
    
    $use_exec_cfg = isset($simple_tester_roles[$effective_role->dbID]) || ($can_execute && !$can_manage);
    
    return  $use_exec_cfg ? $exec_cfg->exec_mode->tester : 'all';
} // function end


/*
  function: setTesterAssignment 

  args:
  
  returns: 
  
*/
function setTesterAssignment(&$db,$exec_info,&$tcase_mgr,$tplan_id,$platform_id, $build_id)
{     
  foreach($exec_info as $version_id => $value)
  {
    $exec_info[$version_id]['assigned_user'] = null;
    $exec_info[$version_id]['assigned_user_id'] = null;
    
    // map of map: main key version_id, secondary key: platform_id
    $p3 = $tcase_mgr->get_version_exec_assignment($version_id,$tplan_id, $build_id);
    if(!is_null($p3))
    { 
      foreach($p3[$version_id][$platform_id] as $uu)
      {
        $assignedTesterId = intval($uu['user_id']);
        if($assignedTesterId)
        {
          $user = tlUser::getByID($db,$assignedTesterId);
          if ($user)
          {
            $exec_info[$version_id]['assigned_user'][]= $user->getDisplayName();  

          }
          $exec_info[$version_id]['assigned_user_id'][] = $assignedTesterId;
        } 
      }
    }  
    $exec_info[$version_id]['assigned_user'] = implode(',',(array)$exec_info[$version_id]['assigned_user']);
    $exec_info[$version_id]['assigned_user_id'] = implode(',',(array)$exec_info[$version_id]['assigned_user_id']);
  }
  return $exec_info;
} //function end

/*
  function: 
           Reorder executions to mantaing correct visualization order.

  args:
  
  returns: 

*/
function reorderExecutions(&$tcversion_id,&$exec_info)
{
    $dummy = array();
    foreach($tcversion_id as $idx => $tcv_id)
    {
      if(isset($exec_info[$tcv_id]))
      { 
        $dummy[$idx] = $exec_info[$tcv_id];    
      } 
    }
    return $dummy;    
}

/*
  function: setCanExecute 

  args:
  
  returns: 

*/
function setCanExecute($exec_info,$execution_mode,$can_execute,$tester_id)
{     
  foreach($exec_info as $key => $tc_exec) 
  {
    $execution_enabled = 0;  

    if($can_execute == 1 && $tc_exec['active'] == 1)
    {
      $is_free = $tc_exec['assigned_user_id'] == '' ? 1 : 0;
      
      $testerSet = array_flip(explode(',',$tc_exec['assigned_user_id']));
      $assigned_to_me = isset($testerSet[$tester_id]) ? 1 : 0;
      
      switch($execution_mode)
      {
        case 'assigned_to_me':
          $execution_enabled = $assigned_to_me;
          break;

        case 'assigned_to_me_or_free':
          $execution_enabled = $assigned_to_me || $is_free;
          break;

        case 'all':
          $execution_enabled = 1;
          break;

        default:
          $execution_enabled = 0;  
          break;
      } // switch
    }
    $exec_info[$key]['can_be_executed']=$execution_enabled;
  }
  return $exec_info;
}


/*
  function: createExecNotesWebEditor
            creates map of html needed to display web editors
            for execution notes.
            
  args: tcversions: array where each element has information
                    about testcase version that can be executed.
                    
        basehref: URL            
        editorCfg:
  
  returns: map
           key: testcase id
           value: html to display web editor.

  rev : 20080104 - creation  
*/
function createExecNotesWebEditor(&$tcversions,$basehref,$editorCfg)
{
  
    if(is_null($tcversions) || count($tcversions) == 0 )
    {
        return null;  // nothing todo >>>------> bye!  
    }
     
    // Important Notice:
    //
    // When using tinymce or none as web editor, we need to set rows and cols
    // to appropriate values, to avoid an ugly ui.
    // null => use default values defined on editor class file
    //
    // Rows and Cols values are useless for FCKeditor.
    //
    $itemTemplateValue = getItemTemplateContents('execution_template', 'notes', null);
    foreach($tcversions as $key => $tcv)
    {
      $tcversion_id=$tcv['id'];
      $tcase_id=$tcv['testcase_id'];

      $of=web_editor("notes[{$tcversion_id}]",$basehref,$editorCfg) ;
      $of->Value = $itemTemplateValue;
       
      // Magic numbers that can be determined by trial and error
      $cols = intval(isset($editorCfg['cols']) ? $editorCfg['cols'] : 60);
      $rows = intval(isset($editorCfg['rows']) ? $editorCfg['rows'] : 10); 
      $editors[$tcase_id]=$of->CreateHTML($rows,$cols);         
      unset($of);
    }
    return $editors;
}



/*
  function: getCfg 

  args:
  
  returns: 

*/
function getCfg() {
  $cfg = new stdClass();
  $cfg->exec_cfg = config_get('exec_cfg');
  $cfg->gui_cfg = config_get('gui');
    
  $results = config_get('results');
  $cfg->tc_status = $results['status_code'];
  $cfg->execStatusToExclude = $results['execStatusToExclude'];
  
  $cfg->testcase_cfg = config_get('testcase_cfg'); 
  $cfg->editorCfg = getWebEditorCfg('execution');
  
  $cfg->cookie = config_get('cookie');  
  return $cfg;
}


/*
  function: initializeRights 
            create object with rights useful for this feature 
  
  args:
       dbHandler: reference to db object
       userObj: reference to current user object
       tproject_id:
       tplan_id
  
       Warning: this is right interface for this function, but
                has_rights() can works in a mode (that i consider a dirty one)
                using SESSION to achieve global coupling.
                 
  returns: 

*/
function initializeRights(&$dbHandler,&$userObj,$tproject_id,$tplan_id) {
    $exec_cfg = config_get('exec_cfg');
    $grants = new stdClass();
    
    $grants->execute = $userObj->hasRight($dbHandler,"testplan_execute",$tproject_id,$tplan_id,true);
    $grants->execute = $grants->execute=="yes" ? 1 : 0;
    
    // IMPORTANT NOTICE - TICKET 5128
    // If is TRUE we will need also to analize, test case by test case
    // these settings:
    //           $tlCfg->exec_cfg->exec_mode->tester
    //          $tlCfg->exec_cfg->simple_tester_roles       
    //
    // Why ?
    // Because if a tester can execute ONLY test cases assigned to him, this also
    // has to mean that:
    // can delete executions ONLY of test cases assigned to him
    // can edit exec notes ONLY of test cases assigned to him
    // can manage uploads on executions, ONLY of test cases assigned to him
    //
    // These checks can not be done here
    //
    // TICKET 5310: Execution Config - convert options into rights
    $grants->delete_execution = $userObj->hasRight($dbHandler,"exec_delete",$tproject_id,$tplan_id,true);
  
    
    // Important:
    // Execution right must be present to consider this configuration option.
    // $grants->edit_exec_notes = $grants->execute && $exec_cfg->edit_notes;
    $grants->edit_exec_notes = $grants->execute && 
                               $userObj->hasRight($dbHandler,"exec_edit_notes",$tproject_id,$tplan_id);
    

    $grants->edit_testcase = $userObj->hasRight($dbHandler,"mgt_modify_tc",$tproject_id,$tplan_id);
    $grants->edit_testcase = $grants->edit_testcase=="yes" ? 1 : 0;
    return $grants;
}


/*
  function: initializeGui

  args :
  
  returns: 

*/
function initializeGui(&$dbHandler,&$argsObj,&$cfgObj,&$tplanMgr,&$tcaseMgr,&$issueTracker,&$codeTracker)
{
  $buildMgr = new build_mgr($dbHandler);
  $platformMgr = new tlPlatform($dbHandler,$argsObj->tproject_id);
    
  $gui = new stdClass();
  $gui->tcversionSet = null;
  $gui->plugins = null;
  $gui->addLinkToTLChecked = $cfgObj->exec_cfg->exec_mode->addLinkToTLChecked;

  $k2i = array('import','attachments','exec','edit_exec');
  $gui->features = array();
  foreach($k2i as $olh) {
    $gui->features[$olh] = false;
  }  

  if( $argsObj->user->hasRight($dbHandler,'testplan_execute',
                      $argsObj->tproject_id,$argsObj->tplan_id,true) ) {
    foreach($k2i as $olh) {
      $gui->features[$olh] = true;
    }  
  }  

  $gui->showExternalAccessString = true;
  $gui->showImgInlineString = false;
  
  $gui->issueSummaryForStep = null;
  $gui->addIssueOp = null;
  $gui->allowStepAttachments = true;
  $gui->tlCanCreateIssue = !is_null($issueTracker) && method_exists($issueTracker,'addIssue');
  $gui->remoteExecFeedback = $gui->user_feedback = '';
  $gui->tplan_id=$argsObj->tplan_id;
  $gui->tproject_id=$argsObj->tproject_id;
  $gui->build_id = $argsObj->build_id;
  $gui->platform_id = $argsObj->platform_id;
  $gui->loadExecDashboard = false;
  $gui->treeFormToken = $argsObj->treeFormToken;
  $gui->import_limit = TL_REPOSITORY_MAXFILESIZE;


  $gui->execStatusIcons = getResultsIcons();
  $gui->execStatusIconsNext = getResultsIconsNext();

  list($gui->execStatusValues,$gui->execStepStatusValues) =
   initExecValuesMenus($cfgObj->tc_status,$cfgObj->execStatusToExclude);

  $gui->can_use_bulk_op=0;
  $gui->exec_notes_editors=null;
  $gui->bulk_exec_notes_editor=null;
  $gui->req_details=null;
  $gui->attachmentInfos=null;
  $gui->bugs=null;
  $gui->scripts=null;
  $gui->other_exec_cfields=null;
  $gui->ownerDisplayName = null;
    
  $gui->editorType=$cfgObj->editorCfg['type'];
  $cfgTestPlan = getWebEditorCfg('testplan');
  $gui->testPlanEditorType = $cfgTestPlan['type'];
  $cfgPlatform = getWebEditorCfg('platform');
  $gui->platformEditorType = $cfgPlatform['type'];
  $cfgBuild = getWebEditorCfg('build');
  $gui->buildEditorType = $cfgBuild['type'];
  $cfgDesign = getWebEditorCfg('design');
  $gui->testDesignEditorType = $cfgDesign['type'];
  $cfgStepsDesign = getWebEditorCfg('design');
  $gui->stepDesignEditorType = $cfgStepsDesign['type'];
  
  $gui->filter_assigned_to=$argsObj->filter_assigned_to;
  $gui->tester_id=$argsObj->user_id;
  $gui->include_unassigned=$argsObj->include_unassigned;
  $gui->tpn_view_status=$argsObj->tpn_view_status;
  $gui->bn_view_status=$argsObj->bn_view_status;
  $gui->bc_view_status=$argsObj->bc_view_status;
  $gui->platform_notes_view_status=$argsObj->platform_notes_view_status;

  $gui->refreshTree = $argsObj->refreshTree;
  if (!$argsObj->statusSingle || current($argsObj->statusSingle) == $cfgObj->tc_status['not_run'])  {
    $gui->refreshTree = 0;
  }

  $gui->map_last_exec_any_build=null;
  $gui->map_last_exec=null;

  // 20081122 - franciscom
  // Just for the records:  
  // doing this here, we avoid to do on processTestSuite() and processTestCase(),
  // but absolutely this will not improve in ANY WAY perfomance, because we do not loop
  // over these two functions.   
  $tprojectMgr = new testproject($dbHandler);
  $gui->tcasePrefix = $tprojectMgr->getTestCasePrefix($argsObj->tproject_id);
  
  $build_info = $buildMgr->get_by_id($argsObj->build_id);

  $gui->build_name = $build_info['name'];  
  $gui->build_notes=$build_info['notes'];
  $gui->build_is_open=($build_info['is_open'] == 1 ? 1 : 0);
  $gui->execution_types=$tcaseMgr->get_execution_types();

  if($argsObj->filter_assigned_to)
  {
    $userSet = tlUser::getByIds($dbHandler,array_values($argsObj->filter_assigned_to));
    if ($userSet)
    {
      foreach($userSet as $key => $userObj) 
      {
        $gui->ownerDisplayName[$key] = $userObj->getDisplayName();
      }    
    }    
  }
  // ------------------------------------------------------------------

  $dummy = $tplanMgr->get_builds_for_html_options($argsObj->tplan_id);
  $gui->build_name = isset($dummy[$argsObj->build_id]) ? $dummy[$argsObj->build_id] : '';
  $gui->build_div_title = lang_get('build') . ' ' . $gui->build_name;

  $gui->exec_mode = initializeExecMode($dbHandler,$cfgObj->exec_cfg,
                                       $argsObj->user,$argsObj->tproject_id,$argsObj->tplan_id);
 
  $gui->grants = initializeRights($dbHandler,$argsObj->user,$argsObj->tproject_id,$argsObj->tplan_id);

  $rs = $tplanMgr->get_by_id($argsObj->tplan_id);

  $gui->testplan_name = $rs['name'];
  $gui->testproject_name = $rs['tproject_name'];
  $gui->testplan_notes = $rs['notes'];
  $gui->testplan_div_title = lang_get('test_plan') . ' ' . $gui->testplan_name;

  $argsObj->tplan_apikey = $rs['api_key'];


  // Important note: 
  // custom fields for test plan can be edited ONLY on design, that's reason why we are using 
  // scope = 'design' instead of 'execution'
  $gui->testplan_cfields = $tplanMgr->html_table_of_custom_field_values($argsObj->tplan_id,'design',
                                                                        array('show_on_execution' => 1));
    

  $gui->build_cfields = $buildMgr->html_table_of_custom_field_values($argsObj->build_id,$argsObj->tproject_id,
                                                                     'design',array('show_on_execution' => 1));
    

  
  $gui->history_on = manage_history_on($_REQUEST,$_SESSION,$cfgObj->exec_cfg,
                                       'btn_history_on','btn_history_off','history_on');
  $gui->history_status_btn_name = $gui->history_on ? 'btn_history_off' : 'btn_history_on';



  $dummy = $platformMgr->getLinkedToTestplan($argsObj->tplan_id);
  $gui->has_platforms = !is_null($dummy) ? 1 : 0;
    
  $gui->platform_info['id'] = 0;
  $gui->platform_info['name'] = '';
  if(!is_null($argsObj->platform_id) && $argsObj->platform_id > 0 )
  { 
    $gui->platform_info = $platformMgr->getByID($argsObj->platform_id);
  }
  $gui->platform_div_title = lang_get('platform') . ' ' . $gui->platform_info['name'];
    

  $gui->issueTrackerIntegrationOn = $gui->tlCanCreateIssue = $gui->tlCanAddIssueNote = false;
  
  $gui->node_id = $argsObj->id;
  $gui->draw_save_and_exit = ($argsObj->caller == 'tcAssignedToMe');

  
  $gui->issueTrackerCfg = new stdClass(); 
  $gui->issueTrackerCfg->bugSummaryMaxLength = 100;  // MAGIC I'm sorry
  $gui->issueTrackerCfg->editIssueAttr = false;
  

  
  if(!is_null($issueTracker))
  {    
    if( $issueTracker->isConnected() )
    {
      $itsCfg = $issueTracker->getCfg();

      $gui->issueTrackerCfg->bugSummaryMaxLength = $issueTracker->getBugSummaryMaxLength();
      $gui->issueTrackerCfg->editIssueAttr = intval($itsCfg->userinteraction);

      $gui->issueTrackerIntegrationOn = true;
      $gui->accessToIssueTracker = lang_get('link_bts_create_bug') . 
                                   "({$argsObj->itsCfg['issuetracker_name']})"; 

      $gui->createIssueURL = $issueTracker->getEnterBugURL();
      $gui->tlCanCreateIssue = method_exists($issueTracker,'addIssue') && 
                               $issueTracker->canCreateViaAPI();

      $gui->tlCanAddIssueNote = method_exists($issueTracker,'addNote');
    }  
    else
    {
      $gui->user_feedback = lang_get('issue_tracker_integration_problems');
    }
  }
  
  // get matadata
  $gui->issueTrackerMetaData = null;
  if($gui->issueTrackerCfg->editIssueAttr == 1)
  {
    $gui->issueTrackerMetaData = !is_null($issueTracker) ? 
                                 getIssueTrackerMetaData($issueTracker) : null; 
    
  
    $k2c = array('issueType','issuePriority','artifactVersion',
                 'artifactComponent');
    foreach($k2c as $kj)
    {
      $gui->$kj = $argsObj->$kj;  

      $kx = $kj . 'ForStep';
      $gui->$kx = $argsObj->$kx;  
    }  
  }  
 
  $gui->bug_summary = '';


  $gui->executionContext = array();
  $gui->executionContext['tproject_name'] = $gui->testproject_name;
  $gui->executionContext['tplan_name'] = $gui->testplan_name;
  $gui->executionContext['platform_name'] = $gui->platform_info['name'];
  $gui->executionContext['build_name'] = $gui->build_name;


  return $gui;
}


/*
  function: processTestCase

  args :
  
  returns: 

*/
function processTestCase($tcase,&$guiObj,&$argsObj,&$cfgObj,$tcv,&$treeMgr,&$tcaseMgr,&$docRepository)
{     
  
  // IMPORTANT due  to platform feature
  // every element on linked_tcversions will be an array.
  $cf_filters = array('show_on_execution' => 1); 
  $locationFilters = $tcaseMgr->buildCFLocationMap();
  
  $guiObj->design_time_cfields = array();
  $guiObj->testplan_design_time_cfields = array();
  
  $tcase_id = isset($tcase['tcase_id']) ? $tcase['tcase_id'] : $argsObj->id;

  // Development Notice:
  // accessing a FIXED index like in:
  //
  // $items_to_exec[$tcase_id] = $linked_tcversions[$tcase_id][0]['tcversion_id'];    
  // $link_id = $linked_tcversions[$tcase_id][0]['feature_id'];
  //
  // Because we want to access FIRTS element is better to use current.
  //
  $target = current(current($tcv));
  $items_to_exec[$tcase_id] = $target['tcversion_id'];    
  $link_id = $target['feature_id'];
  $tcversion_id = isset($tcase['tcversion_id']) ? $tcase['tcversion_id'] : $items_to_exec[$tcase_id];
    
  $guiObj->tcAttachments[$tcase_id] = getAttachmentInfos($docRepository,$tcase_id,'nodes_hierarchy',1);


  foreach($locationFilters as $locationKey => $filterValue) {
    $finalFilters=$cf_filters+$filterValue;
    $guiObj->design_time_cfields[$tcase_id][$locationKey] = 
      $tcaseMgr->html_table_of_custom_field_values($tcase_id,'design',$finalFilters,null,null,
                                                   $argsObj->tproject_id,null,$tcversion_id);
      
    $guiObj->testplan_design_time_cfields[$tcase_id] = 
      $tcaseMgr->html_table_of_custom_field_values($tcversion_id,'testplan_design',$cf_filters,
                                                   null,null,$argsObj->tproject_id,null,$link_id);
  }

  if($guiObj->grants->execute) {
    $guiObj->execution_time_cfields[$tcase_id] = 
      $tcaseMgr->html_table_of_custom_field_inputs($tcase_id,null,'execution',"_{$tcase_id}",null,
                                                   null,$argsObj->tproject_id);
  }

  $tc_info = $treeMgr->get_node_hierarchy_info($tcase_id);
  $guiObj->tSuiteAttachments[$tc_info['parent_id']] = 
    getAttachmentInfos($docRepository,$tc_info['parent_id'],
                       'nodes_hierarchy',true,1);
  // Direct Link
  $lk = current($tcv);
  $guiObj->direct_link = trim($_SESSION['basehref'],'/') . 
                        "/ltx.php?item=exec&feature_id=" . $lk[0]['feature_id'] .
                        "&build_id=" . $argsObj->build_id;

  $argsObj->direct_link = $guiObj->direct_link;


  // Information for Issue Management
  // Common Info
  $signature = new stdClass();
  $signature->tcname = $tc_info['name'];
  $signature->tcpathname = $tcaseMgr->getPathName($tcase_id);
  $signature->tcversion_id = $tcversion_id;

  list($guiObj->bug_summary,$guiObj->issueSummaryForStep) = 
    genIssueSummary($tcaseMgr,$signature,$guiObj->executionContext);

  return array($tcase_id,$tcversion_id);
}




/*
  function: getLatestExec
            Important Notice: 
            $tcase_id and $tcversions_id, can be ARRAYS when user enable bulk execution

  args :
  
  returns: 

*/
function getLatestExec(&$dbHandler,$tcase_id,$tcversion_id,$guiObj,$argsObj,&$tcaseMgr)
{ 
  $options = array('getNoExecutions' => 1, 'groupByBuild' => 0, 'getStepsExecInfo' => 1);

  $last_exec = 
    $tcaseMgr->get_last_execution($tcase_id,$tcversion_id,$argsObj->tplan_id,
      $argsObj->build_id,$argsObj->platform_id,$options);
    
  if( !is_null($last_exec) ) {
    $last_exec = setTesterAssignment($dbHandler,$last_exec,$tcaseMgr,
                                     $argsObj->tplan_id,$argsObj->platform_id, 
                                     $argsObj->build_id);

    // Warning: setCanExecute() must be called AFTER setTesterAssignment()  
    $can_execute = $guiObj->grants->execute && ($guiObj->build_is_open);
    $last_exec = setCanExecute($last_exec,$guiObj->exec_mode,$can_execute,$argsObj->user_id);
  }
    
  // Reorder executions to mantaing correct visualization order.
  if( is_array($tcversion_id) ) {
    $last_exec = reorderExecutions($tcversion_id,$last_exec);
  }

  return $last_exec;
}



/*
  function: getOtherExecutions

  args :
  
  returns: 

  rev: 
*/
function getOtherExecutions(&$dbHandler,$tcase_id,$tcversion_id,$guiObj,$argsObj,&$cfgObj,&$tcaseMgr)
{      
    $other_execs = null;
    if($guiObj->history_on)
    {
      // CRITIC see for key names - testcases.class.php -> getExecutionSet() 
      $execContext = array('testplan_id' => $argsObj->tplan_id, 'platform_id' => $argsObj->platform_id, 
                           'build_id' => $argsObj->build_id);

      if($cfgObj->exec_cfg->show_history_all_builds )
      {
        $execContext['build_id'] = null;
      }  
      if($cfgObj->exec_cfg->show_history_all_platforms )
      {
        $execContext['platform_id'] = null;
      }  
      
      $options = array('exec_id_order' => $cfgObj->exec_cfg->history_order);
      $other_execs = $tcaseMgr->getExecutionSet($tcase_id,$tcversion_id,$execContext,$options);
    }    
    else
    {
      // Warning!!!:
      // we can't use the data we have got with previous call to get_last_execution()
      // because if user have asked to save results last execution data may be has changed
      $aux_map = $tcaseMgr->get_last_execution($tcase_id,$tcversion_id,$argsObj->tplan_id,
                                               $argsObj->build_id,$argsObj->platform_id);
      if(!is_null($aux_map))
      {
        $other_execs = array();
        foreach($aux_map as $key => $value )
        {
          $other_execs[$key] = array($value);
        }
      }
    }
    return $other_execs;
}


/*
  function: processTestSuite

  args :
  
  returns: 

*/
function processTestSuite(&$dbHandler,&$guiObj,&$argsObj,$testSet,&$treeMgr,&$tcaseMgr,&$docRepository)
{
  $locationFilters = $tcaseMgr->buildCFLocationMap();
  $cf_filters = array('show_on_execution' => 1);
  $tsuite_mgr=new testsuite($dbHandler); 
  $tsuite_data = $tsuite_mgr->get_by_id($argsObj->id);
 
  // Get the path for every test case, grouping test cases that have same parent.
  $testCaseQty = count($testSet->tcase_id);
  if( $testCaseQty > 0 )
  {
    $dummy = $tcaseMgr->cfield_mgr->getLocations();
    $verboseLocationCode = array_flip($dummy['testcase']);
    $filters=null;
    foreach($verboseLocationCode as $key => $value)
    {
      $filters[$key]['location']=$value;
    }       

    $dummy_id = current($testSet->tcase_id);
    $index = $testCaseQty == 1 ? $dummy_id : 0;  // 0 => BULK
    $suffix = '_' . $index;
    $execution_time_cfields = 
        $tcaseMgr->html_table_of_custom_field_inputs($dummy_id,$argsObj->tproject_id,'execution',$suffix,
                                                      null,null,$argsObj->tproject_id);
    
    $guiObj->execution_time_cfields[$index] = $execution_time_cfields;
    $gdx=0;
    foreach($testSet->tcase_id as $testcase_id)
    {
      $path_f = $treeMgr->get_path($testcase_id,null,'full');
      foreach($path_f as $key => $path_elem)
      {
        if( $path_elem['parent_id'] == $argsObj->id )
        {
          // Can be added because is present in the branch the user wants to view
          // ID of branch starting node is in $argsObj->id
          $guiObj->tcAttachments[$testcase_id] = getAttachmentInfos($docRepository,$testcase_id,'nodes_hierarchy',true,1);
                  
          foreach($locationFilters as $locationKey => $filterValue)
          {
            $finalFilters = $cf_filters+$filterValue;
            $guiObj->design_time_cfields[$testcase_id][$locationKey] = 
                     $tcaseMgr->html_table_of_custom_field_values($testcase_id,'design',$finalFilters,null,null,
                                                                  $argsObj->tproject_id,null,$testSet->tcversion_id[$gdx]);

            $guiObj->testplan_design_time_cfields[$testcase_id] = 
                     $tcaseMgr->html_table_of_custom_field_values($testcase_id,'testplan_design',$cf_filters,
                                                                         null,null,$argsObj->tproject_id);
                                                                                          
          }                       
          if($guiObj->grants->execute)
          {
            $guiObj->execution_time_cfields[$testcase_id] = 
                    $tcaseMgr->html_table_of_custom_field_inputs($testcase_id, null,'execution',   
                                                                 "_".$testcase_id,null,null,
                                                                 $argsObj->tproject_id);
          }
        } // if( $path_elem['parent_id'] == $argsObj->id )
              
        // We do this because do not know if some test case not yet analised will be direct
        // child of this test suite, then we get this info in advance.
        // In situations where only last test suite on branch have test cases, we are colleting
        // info we will never use.
        if($path_elem['node_table'] == 'testsuites' && !isset($guiObj->tSuiteAttachments[$path_elem['id']]))
        {
          $guiObj->tSuiteAttachments[$path_elem['id']] = 
                     getAttachmentInfos($docRepository,$path_elem['id'],'nodes_hierarchy',true,1);
        }
                 
      } //foreach($path_f as $key => $path_elem) 
      $gdx++;
    }  
  }
    // return array($testSet->tcase_id,$testSet->tcversion_id);  
}


/**
 *
 */
function buildExecContext(&$argsObj,$tcasePrefix,&$tplanMgr,&$tcaseMgr)
{
  $debugMsg = "File:" . __FILE__ . "Function:" . __FUNCTION__;

  $ret = array();
  $ret['target'] = array('tc_versions' => null,'version_id' => null,'feature_id' => null, 'basic_info' => null);
  $ret['context'] = array('tproject_id' => null,'tplan_id' => null, 'platform_id' => null,
                          'build_id' => null,'user_id' => null);
  
  
  foreach($ret as $area => &$value)
  {  
    foreach($value as $key => $dummy)
    {  
      if( property_exists($argsObj,$key) )
      {  
        $value[$key] = $argsObj->$key;      
      }
    }  
  }

  // Now get another important information feature_id on testplan_tcversions
  // needed to get remote execution server config if this config has been
  // done with Custom Fields at Test Plan Design Time
  foreach($ret['target']['tc_versions'] as $tcv_id => $tc_id)
  {  
    $ret['target']['feature_id'][$tcv_id] = $tplanMgr->getFeatureID($ret['context']['tplan_id'],
                                      $ret['context']['platform_id'],
                                      $tcv_id);
                        
    $dummy = $tcaseMgr->get_basic_info($tc_id,array('id' => $tcv_id));
    $dummy[0]['tcasePrefix'] = $tcasePrefix;
    $ret['target']['basic_info'][$tcv_id] = $dummy[0];
                                      
  }
  return $ret;
}



function launchRemoteExec(&$dbHandler,&$argsObj,$tcasePrefix,&$tplanMgr,&$tcaseMgr)
{
    // IMPORTANT NOTICE
    // Remote execution will NOT use ANY of data typed by user,
    // - notes
    // - custom fields
    //
    // IMPORTANT NOTICE
    // need to understand what to do with feedback provided
    // by do_remote_execution().
    // Right now no matter how things go, no feedback is given to user.
    // May be this need to be improved in future.
    //
    // Only drawback i see is when remote exec is done on a test suite
    // and amount of feedback can be high, then do not see what can be effect
    // on GUI
    $execContext = buildExecContext($argsObj,$tcasePrefix,$tplanMgr,$tcaseMgr);
    $feedback = do_remote_execution($dbHandler,$execContext);
    $feedback = current($feedback);
    return $feedback;
}



/**
 * @use testplan->filterByOnDesignCustomFields
 *
 */
function getLinkedItems($argsObj,$historyOn,$cfgObj,$tcaseMgr,$tplanMgr,$identity=null)
{          
  
  $ltcv = null;
  $idCard = null;
  $itemSet = null;

  if( !is_null($identity) )
  {
    $idCard = $identity;  
  }
  else if(!is_null($argsObj->tc_id) && !is_array($argsObj->tc_id) )
  {
    $idCard = array('id' => $argsObj->tc_id, 'version_id' => $argsObj->version_id);
  }
  
  if( !is_null($idCard) )
  {
    // CRITIC see for key names - testcases.class.php -> getExecutionSet() 
    $execContext = array('testplan_id' => $argsObj->tplan_id,'platform_id' => $argsObj->platform_id,
                         'build_id' => $argsObj->build_id);    

    $ltcv = null;
    if($historyOn)
    {
      $execContext['testplan_id'] = $argsObj->tplan_id;
      $ltcv = $tcaseMgr->getExecutionSet($idCard['id'],null,$execContext);
    }

    // lazy implementation:
    // getExecutionSet() returns data ONLY for Statuses that are written ON DB,
    // then if full history for test case is NOT RUN, we are doomed!!
    if(!$historyOn || is_null($ltcv))
    {
      $opt = null;
      $ltcv = $tcaseMgr->getLatestExecSingleContext($idCard,$execContext,$opt);
    }
  }
  else
  {
    // -----------------------------------------------------------
    // When nullify filter_status - 20080504 - DO NOT REMOVE -
    // 
    // May be in the following situation we do not HAVE to apply filter status:
    // 1. User have filter for Not Run on Tree
    // 2. Clicks on TC XXX
    // 3. Executes TC
    // 4. DO NOT UPDATE TREE.
    //    we do not update automatically to avoid:
    //    a) performance problems
    //    b) delays on operations due to tree redraw
    //    c) loose tree status due to lack of feature of tree engine
    //
    // 5. Clicks again on TC XXX
    // If we use filter, we will get No Data Available.
    //
    // When working on show_testsuite_contents mode (OLD MODE) when we show
    // all testcases inside a testsuite that verifies a filter criteria WE NEED TO APPLY FILTER
    //
    // We do not have this problem when this page is called after user have executed,
    // probably because filter_status is not send back.
    //
    // I will add logic to nullify filter_status on init_args()
    // 
    
    $options = array('only_executed' => true, 'output' => $historyOn ? 'mapOfArray' : 'mapOfMap',
                     'include_unassigned' => $argsObj->include_unassigned,
                     'group_by_build' => 'add_build',
                     'last_execution' => !$historyOn);
    
    if(is_null($argsObj->filter_status) || in_array($cfgObj->tc_status['not_run'],(array)$argsObj->filter_status))
    {
        $options['only_executed'] = false;
    }

    // args->tsuites_id: is only used when user click on a test suite.
    //                   probably is used only when bulk execution is enabled.
    //
    // if args->tc_id is not null, theorically all other filters are useless.
    // why ?
    // Because will normally call this script, from the execution tree and if we can click
    // on a tree node, this means it has passed all filters.
    //
    //
    // $args->platform_id: needed to get execution status info
    // $args->build_id: needed to get execution status info
    //
    $basic_filters = array('tcase_id' => $argsObj->tc_id, 'platform_id' => $argsObj->platform_id,'build_id' => $argsObj->build_id);
    
    // This filters are useful when bulk execution is enabled, 
    // and user do click on a test suite on execution tree.

    // seems to be useless => 'cf_hash' => $argsObj->filter_cfields,
    // need to review $tplanMgr->getLinkedForExecTree
    //
    
    /*
    $nt = $tplanMgr->tree_manager->get_available_node_types();
    $setOfTestSuites = $tplanMgr->tree_manager->get_subtree_list($argsObj->tsuite_id,$nt['testsuite']);

    if( !is_null($setOfTestSuites) && $setOfTestSuites != '')
    {
      $setOfTestSuites = explode(',',$argsObj->tsuite_id .',' . $setOfTestSuites); 
      
    }  
    else
    {
      $setOfTestSuites = (array)$argsObj->tsuite_id; 
    }  
    */

    // $setOfTestSuites = (array)$argsObj->tsuite_id; 
    $bulk_filters = array('keyword_id' => $argsObj->keyword_id,'assigned_to' => $argsObj->filter_assigned_to, 
                          'exec_status' => $argsObj->filter_status,
                          'tsuites_id' => $argsObj->tsuite_id,
                          'assigned_on_build' => $argsObj->build_id,
                          'exec_type' => $argsObj->execution_type,
                          'urgencyImportance' => $argsObj->priority);

    // CRITIC / IMPORTANT 
    // With BULK Operation enabled, we prefer to display Test cases tha are ONLY DIRECT CHILDREN
    // of test suite id => we do not do deep walk.
    // Think is a good choice, to avoid retrieving lot of info.
    // May be we need to add a config parameter (or better an option at GUI level)
    // in order to allow use how he / she wants to work.
    //
    $filters = array_merge($basic_filters,$bulk_filters);

    if( !is_null($sql2do = $tplanMgr->getLinkedForExecTree($argsObj->tplan_id,$filters,$options)) )
    {
      if( is_array($sql2do) )
      {        
        if( isset($filters['keyword_filter_type']) && ($filters['keyword_filter_type'] == 'And') )
        { 
          $kmethod = "fetchRowsIntoMapAddRC";
          $unionClause = " UNION ALL ";
        }
        else
        {
          $kmethod = "fetchRowsIntoMap";
          $unionClause = ' UNION ';
        }
        $sql2run = $sql2do['exec'] . $unionClause . $sql2do['not_run'];
      }
      else
      {
        $sql2run = $sql2do;
      }
      
      // Development Notice: 
      // CUMULATIVE is used only to create same type of datastructe that existed
      // before this refactoring
      //
      // $tex = $tcaseMgr->db->$kmethod($sql2run,'tcase_id',database::CUMULATIVE);
      $sql2run .= ' ORDER BY exec_order ';

      $ltcv = $tex = $tcaseMgr->db->$kmethod($sql2run,'tcase_id');
      if(!is_null($tex))
      {
        // We need to create:
        // one set for Custom fields that apply to DESIGN
        // one set for Custom fields that apply to TESTPLAN DESIGN

        if(!is_null($argsObj->filter_cfields))
        {
          $tk = array_keys($argsObj->filter_cfields);
          $cf = null;  
          // foreach( array('design','testplan_design') as $l4)
          foreach( array('design') as $l4)
          {
            $cf[$l4] = $tplanMgr->cfield_mgr->getByIDAndEnableOn($tk,array($l4 => true));
          }  
          if(isset($cf['design']) && !is_null($cf['design']))
          {
            foreach($cf['design'] as $yy => $xc)
            {
              $az[$yy] = $argsObj->filter_cfields[$yy];
            }  
            $tex = $tplanMgr->filterByOnDesignCustomFields($tex,$az);  
          }  
        }  

        foreach($tex as $xkey => $xvalue)
        {
          $itemSet->tcase_id[]=$xkey;
          $itemSet->tcversion_id[]=$xvalue['tcversion_id'];
        }  
      }
    }
  }
  return array($ltcv,$itemSet);         
}


/**
 *
 *
 */
function initWebEditors(&$guiObj,$cfgObj,$baseHREF)
{
  if( $guiObj->can_use_bulk_op )
  {
      $of = web_editor("bulk_exec_notes",$baseHREF,$cfgObj->editorCfg);
      $of->Value = getItemTemplateContents('execution_template', $of->InstanceName, null);
      
      // Magic numbers that can be determined by trial and error
      $cols = intval(isset($editorCfg['cols']) ? $cfgObj->editorCfg['cols'] : 60);
      $rows = intval(isset($editorCfg['rows']) ? $cfgObj->editorCfg['rows'] : 10);       
      $guiObj->bulk_exec_notes_editor = $of->CreateHTML($rows,$cols);         
      unset($of);    
  }
  else
  {
      $guiObj->exec_notes_editors=createExecNotesWebEditor($guiObj->map_last_exec,$baseHREF,$cfgObj->editorCfg);
  }
}




/**
 *  get info from ... 
 *
 */
function getSettingsAndFilters(&$argsObj)
{
  $mode = 'execution_mode';
  $form_token = isset($_REQUEST['form_token']) ? $_REQUEST['form_token'] : 0;
  $sf = isset($_SESSION[$mode]) && isset($_SESSION[$mode][$form_token]) ? $_SESSION[$mode][$form_token] : null;

  $argsObj->testcases_to_show = isset($sf['testcases_to_show']) ? $sf['testcases_to_show'] : null;

  // just for better readability
  $filters = array('filter_status' => 'filter_result_result','filter_assigned_to' => 'filter_assigned_user',
                   'execution_type' => 'filter_execution_type', 'priority' => 'filter_priority',
                   'filter_cfields' => 'filter_custom_fields');
  $settings = array('build_id' => 'setting_build', 'platform_id' => 'setting_platform');

  $key2null = array_merge($filters,$settings);
  $isNumeric = array('build_id' => 0, 'platform_id' => -1);

  foreach($key2null as $key => $sfKey)
  {
    $argsObj->$key = isset($sf[$sfKey]) ? $sf[$sfKey] : null;
    if (is_null($argsObj->$key)) 
    {
      // let's this page be functional withouth a form token too 
      // (when called from testcases assigned to me)
      $argsObj->$key = isset($_REQUEST[$sfKey]) ? $_REQUEST[$sfKey] : null;
    }
    
    if(isset($isNumeric[$key]))
    {
      $argsObj->$key = intval($argsObj->$key);              
    }  
  }


  // keywords filter
  $argsObj->keyword_id = 0;
  if (isset($sf['filter_keywords'])) 
  {
    $argsObj->keyword_id = $sf['filter_keywords'];
    if (is_array($argsObj->keyword_id) && count($argsObj->keyword_id) == 1) 
    {
      $argsObj->keyword_id = $argsObj->keyword_id[0];
    }
  }
  
  $argsObj->keywordsFilterType = null;
  if (isset($sf['filter_keywords_filter_type'])) 
  {
    $argsObj->keywordsFilterType = $sf['filter_keywords_filter_type'];
  }

  $argsObj->refreshTree = isset($sf['setting_refresh_tree_on_action']) ? 
                                $sf['setting_refresh_tree_on_action'] : null;
                                  
  // Checkbox
  $tgk = 'filter_assigned_user_include_unassigned';
  $argsObj->include_unassigned = isset($sf[$tgk]) && ($sf[$tgk] != 0 ? 1 : 0);
}



/**
 *  get info from cookies and also set values on cookies
 *
 */
function manageCookies(&$argsObj,$cfgObj)
{
  $cookieExecPrefix = 'TL_execSetResults_';
      
  // IMPORTANT: logic for test suite notes CAN NOT BE IMPLEMENTED HERE
  //            see smarty_assign_tsuite_info() in this file.  
  $key4cookies = array('tpn_view_status' => 'testplan_notes','bn_view_status' => 'build_description',
                       'platform_notes_view_status' => 'platform_description');
    
  $key2loop = array('id' => 0, 'exec_to_delete' => 0, 'version_id' => 0, 'tpn_view_status' => 0, 
                    'bn_view_status' => 0, 'bc_view_status' => 1,'platform_notes_view_status' => 0);

  foreach($key4cookies as $key => $cfgKey)
  {
    $cookieKey = $cookieExecPrefix . $key;
    if( !isset($_REQUEST[$key]) )
    {
      // First time we are entered here => we can need to understand how to proceed
        switch($cfgObj->exec_cfg->expand_collapse->$cfgKey )
        {
          case LAST_USER_CHOICE:
          if (isset($_COOKIE[$cookieKey]) ) 
          {
            $key2loop[$key] = $_COOKIE[$cookieKey];
          }
          break;  

          default:
            $key2loop[$key] = $cfgObj->exec_cfg->expand_collapse->$cfgKey;
          break;
        } 
    }
  }
             
  $ckObj = new stdClass();                      
  foreach($key2loop as $key => $value)
  {
    $argsObj->$key = isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : $value;
    if( isset($key4cookies[$key]) )
    {
      $ckObj->name = $cfgObj->cookie->prefix . $cookieExecPrefix . $key;
      $ckObj->value = $argsObj->$key;
      tlSetCookie($ckObj);
    }
  }
}  

/**
 *
 */
function getResultsIcons()
{
  $resultsCfg = config_get('results');
  // loop over status for user interface, because these are the statuses
  // user can assign while executing test cases
  foreach($resultsCfg['status_icons_for_exec_ui'] as $verbose_status => $ele)
  {
    if( $verbose_status != 'not_run' )
    {  
      $code = $resultsCfg['status_code'][$verbose_status];
      $items[$code] = $ele;
      $items[$code]['title'] = lang_get($items[$code]['title']);
    } 
  }
  return $items;
}

/**
 *
 */
function getResultsIconsNext()
{
  $resultsCfg = config_get('results');
  // loop over status for user interface, because these are the statuses
  // user can assign while executing test cases
  foreach($resultsCfg['status_icons_for_exec_next_ui'] as $verbose_status => $ele)
  {
    if( $verbose_status != 'not_run' )
    {  
      $code = $resultsCfg['status_code'][$verbose_status];
      $items[$code] = $ele;
      $items[$code]['title'] = lang_get($items[$code]['title']);
    } 
  }
  return $items;
}

/**
 *
 */
function genIssueSummary(&$tcaseMgr,$signature,$context) {

  $cfg = config_get('exec_cfg');

  // Work on labels
  $text = array();
  $text['tcase'] = helperLabels($cfg->issues->tcase_level->subject);
  $text['tcstep'] = helperLabels($cfg->issues->tcstep_level->subject);

  // Work on values
  $ecx = &$context;
  $searchFor = array('%%TCNAME%%', '%%PROJECTNAME%%', 
                     '%%PLANNAME%%','%%BUILDNAME%%','%%PLATFNAME%%',
                     '%%TCPATHNAME%%','%%EXECTSISO%%');

  $replaceWith = array($signature->tcname,$ecx['tproject_name'],
                       $ecx['tplan_name'],$ecx['build_name'],
                       $ecx['platform_name'],$signature->tcpathname,
                       date('Y-m-dTH:i',time()));

  $nu = array();
  $nu['tcase'] = str_replace($searchFor, $replaceWith, $text['tcase']);
  $nu['tcstep'] = null;

  $opt = array('fields2get' => 'step_number,id');
  $steps = $tcaseMgr->get_steps($signature->tcversion_id,0,$opt);
  if(null != $steps) {
    $tstx = str_replace($searchFor, $replaceWith, $text['tcstep']);
    foreach($steps as $elem) {
      $nu['tcstep'][$elem['id']] = 
        str_replace('%%STEPNUMBER%%',$elem['step_number'],$tstx); 
    }     
  }

  return array($nu['tcase'],$nu['tcstep']);
}



/**
 *
 */
function helperLabels($haystack) {
  $searchFor = array('$$issue_on_step', 
                     '$$issue_subject_tcname','$$issue_subject_tcpathname', 
                     '$$issue_subject_projectname',
                     '$$issue_subject_planname','$$issue_subject_buildname',
                     '$$issue_subject_platfname','$$issue_subject_execon');

  $replaceWith = array();
  foreach ( $searchFor as $lblKey ) { 
    $jk = str_replace('$$','',$lblKey);
    $replaceWith[] = lang_get($jk);
  }
  $hy = str_replace($searchFor, $replaceWith, $haystack);
  return $hy; 
}  

/**
 *
 */
function initExecValuesMenus($tcStatusCfg, $execStatusToExclude) {

  $execStatusTestCase = $execStatusTestCaseStep = createResultsMenu();

  $execStatusTestCase[$tcStatusCfg['not_run']] = '';
  $execStatusTestCaseStep[$tcStatusCfg['not_run']] = '';

  foreach($execStatusToExclude['testcase'] as $code) {  
    if( isset($execStatusTestCase[$code]) ) {
      unset($execStatusTestCase[$code]);
    }
  }

  foreach($execStatusToExclude['step'] as $code) {  
    if( isset($execStatusTestCaseStep[$code]) ) {
      unset($execStatusTestCaseStep[$code]);
    }
  }

  return array($execStatusTestCase,$execStatusTestCaseStep);
}

<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	execSetResults.php
 *
 * @internal revisions:
 *
 *  20110622 - asimon - TICKET 4600: Blocked execution of testcases
 *  20110323 - Julian - BUGID 4324 - Encoding of Test Suite did not work properly
 *  20110322 - eloff - BUGID 3643
 *  20110308 - franciscom - remote execution
 *  20110123 - franciscom - BUGID 3338
 *  20110105 - asimon - BUGID 3878: "Save and move to next" does not respect filter settings
 *  20110104 - aismon - BUGID 3643: apply filters earlier in script instead of loading unnecessary data
 *  20100927 - asimon - avoid warning in event log
 *	20100926 - franciscom - BUGID 3421: Test Case Execution feature - Add Export All test Case in TEST SUITE button
 *							added $gui->tcversionSet
 *	
 *  20100922 - asimon - let this page be functional withouth a form token too, changed init_args()
 *	20100821 - franciscom - BUGID 3431 - Custom Field values at Test Case VERSION Level
 *	20100821 - franciscom - code layout refactoring
 *  20100812 - asimon - BUGID 3672
 *  20100709 - asimon - BUGID 3590, BUGID 3574: build_id set to 0 as default instead of null
 *  20100628 - asimon - removal of constants from filter control class
 *  20100625 - asimon - added parameters $bugInterfaceOn, $bugInterface to exec_additional_info()
 *                      to avoid warnings in event log,
 *                      fixed a little bug in platform id initializing in init_args()
 *                      (now number 0 instead of value null)
 *  20100624 - asimon - CVS merge (experimental branch to HEAD)
 *  20100624 - asimon - refactoring for new filters
 *	20100527 - franciscom - BUGID 3479: Bulk Execution - Custom Fields Bulk Assignment
 *  20100527 - Julian - platform description is now shown/hidden according to setting on config
 *	20100520 - franciscom - BUGID 3478  Testcase ID not updated when using save and move next
 *  20100428 - asimon - BUGID 3301 and related, added logic to refresh tree after tc execution
 *  20100313 - franciscom - BUGID 3276
 *  20100204 - asimon - BUGID 2455 & 3026, little changes for filtering
 *  20100121 - franciscom - missing platform feature refactoring
 *	20091205 - franciscom - BUGID 0002469: CFG-Parameters to show notes/details on test-execution
 *  20091111 - franciscom - BUGID 2938 - Feature: Save and Go to next test case in test suite.
 *  20090922 - franciscom - added contribution idea, when using bulk operation
 *                          display last execution status.
 *
 *	20090913 - franciscom - fixed bug on filter_status initialization
 *                          fixed bug on bulk execution due to bad option
 *                          on get_linked_tcversions() call.
 *                         
 *	20090815 - franciscom - platform feature	
 *  20090808 - franciscom - gen_spec_view call refactoring
 *  20090526 - franciscom - now custom fields for testplan_design are managed
 *
 *  20090419 - franciscom - BUGID 2364 - added management of refreshTree
 *                          initializeRights() refactored
 *  20090409 - amkhullar - updated code not written properly.
 *  20090330 - franciscom - fixed bug on test plan custom field get.
 *  20090325 - amkhullar - BUGID 2267
 *  20090210 - amkhullar - BUGID 2068
 *  20080827 - franciscom - BUGID 1692
 *  20080811 - franciscom - BUGID 1650 (REQ)
 *  
 *  20080104 - franciscom - REQ 1232 - web editor on execution notes
 *                          added createExecNotesWebEditor()
 *
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once("attachments.inc.php");
require_once("specview.php");
require_once("web_editor.php");

$cfg=getCfg();
require_once(require_web_editor($cfg->editorCfg['type']));

// BUGID 3338
if( $cfg->exec_cfg->enable_test_automation )
{
  require_once('remote_exec.php');
}

// BUGID 3276
// CRITIC:
// If call to testlinkInitPage() is done AFTER require_once for BTS
// log to event viewer fails, but log to file works ok
testlinkInitPage($db);
if($cfg->bts_type != 'NO')
{
  require_once(TL_ABS_PATH. 'lib' . DIRECTORY_SEPARATOR . 'bugtracking' . 
               DIRECTORY_SEPARATOR . 'int_bugtracking.php');
}


$templateCfg = templateConfiguration();

$tcversion_id = null;
$submitResult = null;
$args = init_args($cfg);

$smarty = new TLSmarty();
$tree_mgr = new tree($db);
$tplan_mgr = new testplan($db);
$tcase_mgr = new testcase($db);
$exec_cfield_mgr = new exec_cfield_mgr($db,$args->tproject_id);
$attachmentRepository = tlAttachmentRepository::create($db);
$req_mgr = new requirement_mgr($db);

$gui = initializeGui($db,$args,$cfg,$tplan_mgr,$tcase_mgr);
$_SESSION['history_on'] = $gui->history_on;
$attachmentInfos = null;

$do_show_instructions = ($args->level == "" || $args->level == 'testproject') ? 1 : 0;
if ($do_show_instructions)
{
    show_instructions('executeTest');
    exit();
}

// ---------------------------------------------------------
// Testplan executions and result archiving. Checks whether execute cases button was clicked
//
if($args->doExec == 1)
{

	if(!is_null($args->tc_versions) && count($args->tc_versions))
	{
		// 20110129 - franciscom
		// IMPORTANT NOTICE
		// Remote execution will NOT use ANY of data typed by user,
		// - notes
		// - custom fields
		//
		$execContext = buildExecContext($args,$gui->tcasePrefix,$tplan_mgr,$tcase_mgr);
		$gui->remoteExecFeedback = do_remote_execution($db,$execContext);
		$gui->remoteExecFeedback = current($gui->remoteExecFeedback);
		
		// IMPORTANT NOTICE
		// need to understand what to do with feedback provided
		// by do_remote_execution().
		// Right now no matter how things go, no feedback is given to user.
		// May be this need to be improved in future.
		//
		// Only drawback i see is when remote exec is done on a test suite
		// and amount of feedback can be high, then do not see what can be effect
		// on GUI
		
		
	}
}	


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
// 20080224 - franciscom - BUGID 1056
// 20070306 - franciscom - BUGID 705
// 20070914 - jbarchibald - added $cf_selected parameter
//

// 20081221 - franciscom                              
// BUGID 3406
$options = array('only_executed' => true, 'output' => 'mapOfArray',
				 'include_unassigned' => $args->include_unassigned,
                 'user_assignments_per_build' => $args->build_id);


if(is_null($args->filter_status) || in_array($cfg->tc_status['not_run'],$args->filter_status))
{
    $options['only_executed'] = false;
}

// Added platform_id filter
// BUGID 3643 - don't apply filters further down below, do the filtering already here
//$filters = array('tcase_id' => $args->tc_id,  'keyword_id' => $args->keyword_id,
$filters = array('tcase_id' => $args->testcases_to_show,  'keyword_id' => $args->keyword_id,
                 'assigned_to' => $args->filter_assigned_to, 'exec_status' => $args->filter_status,
                 'build_id' => $args->build_id, 'cf_hash' => $args->cf_selected,
                 'platform_id' => $args->platform_id);

$linked_tcversions = $tplan_mgr->get_linked_tcversions($args->tplan_id,$filters,$options);
$tcase_id = 0;
$userid_array = null;

if(!is_null($linked_tcversions))
{
	$items_to_exec = array();
	$_SESSION['s_lastAttachmentInfos'] = null;
    if($args->level == 'testcase')
    {
    	// Warning!!! - $gui is passed by reference to be updated inside function
    	$tcase = null;
        list($tcase_id,$tcversion_id) = processTestCase($tcase,$gui,$args,$cfg,$linked_tcversions,
                                                        $tree_mgr,$tcase_mgr,$attachmentRepository);
    }
    else
    {
        list($tcase_id,$tcversion_id) = processTestSuite($db,$gui,$args,$linked_tcversions,
                                                         $tree_mgr,$tcase_mgr,$attachmentRepository);
    }

	// 20100927 - asimon - check if value is an array before calling implode
	// to avoid warnings in event log
   	$gui->tcversionSet = is_array($tcversion_id) ? implode(',',$tcversion_id) : $tcversion_id;

    // will create a record even if the testcase version has not been executed (GET_NO_EXEC)
    //
    // Can be DONE JUST ONCE AFTER write results to DB
    // $gui->map_last_exec = getLastExecution($db,$tcase_id,$tcversion_id,$gui,$args,$tcase_mgr);
    
    // --------------------------------------------------------------------------------------------
    // Results to DB
    if ($args->save_results || $args->do_bulk_save || $args->save_and_next)
    {
		
    	// this has to be done to do not break logic present on write_execution()
    	$args->save_results = $args->save_and_next ? $args->save_and_next : $args->save_results;
    	$_REQUEST['save_results'] = $args->save_results;
    	
    	// 20110129 - franciscom - seems $gui->map_last_exec is USELESS on write_execution()
    	// $submitResult = write_execution($db,$args,$_REQUEST,$gui->map_last_exec);
        write_execution($db,$args,$_REQUEST);
        
        // Need to re-read to update test case status
        if ($args->save_and_next) 
        {
			$nextItem = $tplan_mgr->getTestCaseNextSibling($args->tplan_id,$tcversion_id,$args->platform_id);
			
			// BUGID 3878
			while (!is_null($nextItem) && !in_array($nextItem['tcase_id'], $args->testcases_to_show)) {
				$nextItem = $tplan_mgr->getTestCaseNextSibling($args->tplan_id,$nextItem['tcversion_id'],$args->platform_id);
			}
			
			if( !is_null($nextItem) )
			{
				$tcase_id = $nextItem['tcase_id'];
				$tcversion_id = $nextItem['tcversion_id'];
				// BUGID 3478
         		processTestCase($nextItem,$gui,$args,$cfg,$linked_tcversions,$tree_mgr,$tcase_mgr,$attachmentRepository);
			}
			
        }
    }
    $gui->map_last_exec = getLastExecution($db,$tcase_id,$tcversion_id,$gui,$args,$tcase_mgr);
    
    if ($args->doDelete)
    {
    	delete_execution($db,$args->exec_to_delete);
    }
    // --------------------------------------------------------------------------------------------
    
    $gui->map_last_exec_any_build = null;
    $gui->other_execs=null;
    $testerid = null;
    
    
    if($args->level == 'testcase')
    {
    	// @TODO 20090815 - franciscom check what to do with platform
    	if( $cfg->exec_cfg->show_last_exec_any_build )
    	{
    		// 20090716 - franciscom - get_last_execution() interface changes
			$options=array('getNoExecutions' => 1, 'groupByBuild' => 0);
    	    $gui->map_last_exec_any_build = $tcase_mgr->get_last_execution($tcase_id,$tcversion_id,$args->tplan_id,
    	                                                                   testcase::ANY_BUILD,
    	                                                                   $args->platform_id,$options);
    	    
    	    //Get UserID and Updater ID for current Version
    	    $tc_current = $gui->map_last_exec_any_build;
    	    foreach ($tc_current as $key => $value)
    	    {
				$testerid = $value['tester_id'];
			    $userid_array[$testerid] = $testerid;
    	    }	    
    	}
    	
    	$gui->req_details = $req_mgr->get_all_for_tcase($tcase_id); //Bug 2068
    	$gui->other_execs=getOtherExecutions($db,$tcase_id,$tcversion_id,$gui,$args,$cfg,$tcase_mgr);
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
    	
		  // asimon - added $g_bugInterfaceOn, $g_bugInterface
    	  $other_info=exec_additional_info($db,$attachmentRepository,$tcase_mgr,$gui->other_execs,
    	  $args->tplan_id,$args->tproject_id, $g_bugInterfaceOn, $g_bugInterface);
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

} // if(!is_null($linked_tcversions))


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
smarty_assign_tsuite_info($smarty,$_REQUEST,$db,$tree_mgr,$tcase_id,$args->tproject_id);

// BUGID 3643 - don't apply filters here
// BUGID 2455, BUGID 3026
// BUGID 3516
// remove testcases which shall not be displayed because they were filtered out
//if (!is_null($args->testcases_to_show) && $args->level == 'testsuite') {
//	foreach($gui->map_last_exec as $key => $tc) {
//		if (!in_array($tc['testcase_id'], $args->testcases_to_show)) {
//			unset($gui->map_last_exec[$key]); // tc shall not be displayed
//		}
//	}
//	// fix indexes for smarty
//	$gui->map_last_exec = array_values($gui->map_last_exec);
//}

// Bulk is possible when test suite is selected (and is allowed in config)
$gui->can_use_bulk_op = ($args->level == 'testsuite');

if( $gui->can_use_bulk_op )
{
    $gui->execStatusValues=createResultsMenu();
    if( isset($gui->execStatusValues[$cfg->tc_status['all']]) )
    {
        unset($gui->execStatusValues[$cfg->tc_status['all']]);
    }

    $of=web_editor("bulk_exec_notes",$_SESSION['basehref'],$cfg->editorCfg);
   	$of->Value = getItemTemplateContents('execution_template', $of->InstanceName, null);
    
    // Magic numbers that can be determined by trial and error
    $gui->bulk_exec_notes_editor=$of->CreateHTML(10,60);         
    unset($of);    
}
else
{
    $gui->exec_notes_editors=createExecNotesWebEditor($gui->map_last_exec,$_SESSION['basehref'],$cfg->editorCfg);
}

// To silence smarty errors
//  future must be initialized in a right way
$smarty->assign('test_automation_enabled',0);
$smarty->assign('cfg',$cfg);
$smarty->assign('users',tlUser::getByIDs($db,$userSet,'id'));
$smarty->assign('gui',$gui);
$smarty->assign('g_bugInterface', $g_bugInterface);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/*
  function: 

  args:
  
  returns: 
  
  rev:
    20100922 - asimon - let this page be functional withouth a form token too
	20100625 - asimon - fixed a little bug in platform id initializing when no platform is used
	                    (now number 0 instead of value null)
	20090913 - franciscom - fixed bug on filter_status initialization
*/
function init_args($cfgObj)
{
	$args = new stdClass();
	$_REQUEST = strings_stripSlashes($_REQUEST);
	
    // BUGID 3516
	$mode = 'execution_mode';
	$form_token = isset($_REQUEST['form_token']) ? $_REQUEST['form_token'] : 0;
	$session_data = isset($_SESSION[$mode]) && isset($_SESSION[$mode][$form_token]) ? $_SESSION[$mode][$form_token] : null;
	
	$args->doExec = isset($_REQUEST['execute_cases']) ? 1 : 0;
	$args->doDelete = isset($_REQUEST['do_delete']) ? $_REQUEST['do_delete'] : 0;
	$args->cf_selected = isset($_REQUEST['cfields']) ? unserialize($_REQUEST['cfields']) : null;
	
	// can be a list, will arrive via form POST
	$args->tc_versions = isset($_REQUEST['tc_version']) ? $_REQUEST['tc_version'] : null;  
  
	// BUGID 3516,3590, 3574, 3672
	$key2null = array('filter_status' => 'filter_result_result','filter_assigned_to' => 'filter_assigned_user', 
					  'build_id' => 'setting_build', 'platform_id' => 'setting_platform');
	
	foreach($key2null as $key => $sessionKey)
	{
		$args->$key = isset($session_data[$sessionKey]) ? $session_data[$sessionKey] : null;
		// let this page be functional withouth a form token too (when called from testcases assigned to me)
		if (is_null($args->$key)) {
			$args->$key = isset($_REQUEST[$sessionKey]) ? $_REQUEST[$sessionKey] : null;
		}
	}

	if (is_null($args->build_id)) {
		$args->build_id = (isset($_REQUEST['build_id']) && is_numeric($_REQUEST['build_id'])) ? $_REQUEST['build_id'] : 0;
	}
	
	if (is_null($args->platform_id)) {
		$args->platform_id = (isset($_REQUEST['platform_id']) && is_numeric($_REQUEST['platform_id'])) ? $_REQUEST['platform_id'] : 0;
	}

	$key2loop = array('level' => '','status' => null, 'do_bulk_save' => 0, 'save_results' => 0, 'save_and_next' => 0);
	foreach($key2loop as $key => $value)
	{
		$args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
	}

    // See details on: "When nullify filter_status - 20080504" in this file
    if(is_null($args->filter_status) || trim($args->filter_status) || $args->level == 'testcase')
    {
        $args->filter_status = null;  
    }
    else
    {
        $args->filter_status = unserialize($args->filter_status);
    }

 	$args->id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    $cookiePrefix = 'TL_execSetResults_';
      
    // IMPORTANT: logic for test suite notes CAN NOT BE IMPLEMENTED HERE
    //            see smarty_assign_tsuite_info() in this file.  
    $key4cookies = array('tpn_view_status' => 'testplan_notes','bn_view_status' => 'build_description',
                         'platform_notes_view_status' => 'platform_description');
    
    // BUGID 3516, 3590, 3574
	$key2loop = array('id' => 0, 'exec_to_delete' => 0, 'version_id' => 0, 'tpn_view_status' => 0, 
					  'bn_view_status' => 0, 'bc_view_status' => 1,'platform_notes_view_status' => 0);
	
	foreach($key4cookies as $key => $cfgKey)
	{
		$cookieKey = $cookiePrefix . $key;
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
    				            
	foreach($key2loop as $key => $value)
	{
 		$args->$key = isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : $value;
        if( isset($key4cookies[$key]) )
		{
			setcookie($cookiePrefix . $key,$args->$key,TL_COOKIE_KEEPTIME, '/');
		}
	}

	
    switch($args->level)
    {
        case 'testcase':
        $args->tc_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null;
        // some problems with $_GET that has impact on logic 'Save and Go to next test case';
        if( !is_null($args->tc_versions) )
        {
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


	// BUGID 3516
	$args->keyword_id = 0;
	if (isset($session_data['filter_keywords'])) {
		$args->keyword_id = $session_data['filter_keywords'];
		if (is_array($args->keyword_id) && count($args->keyword_id) == 1) {
			$args->keyword_id = $args->keyword_id[0];
		}
	}
	
	$args->keywordsFilterType = null;
	if (isset($session_data['filter_keywords_filter_type'])) {
		$args->keywordsFilterType = $session_data['filter_keywords_filter_type'];
	}
    
    // Checkbox
    $args->include_unassigned = isset($session_data['filter_assigned_user_include_unassigned']) 
                                && $session_data['filter_assigned_user_include_unassigned'] != 0 ? 1 : 0;
	
	
    // 20090419 - franciscom - BUGID
    // BUGID 3301 and related - asimon - changed refresh tree logic 
    // to adapt behavior of other forms (like tc edit)
    // additionally modified to only refresh on saving of test results, not on every click
    $args->refreshTree = isset($session_data['setting_refresh_tree_on_action'])
                         ? $session_data['setting_refresh_tree_on_action'] : 0;
	
	$args->tproject_id = isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : $_SESSION['testprojectID'];
	
	// BUGID 2267
	$args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testplanID'];
	$args->user = $_SESSION['currentUser'];
    $args->user_id = $args->user->dbID;

    // BUGID 3516
   	// BUGID 2455,BUGID 3026
   	$args->testcases_to_show = null;
	if (isset($session_data['testcases_to_show'])) {
		$args->testcases_to_show = $session_data['testcases_to_show'];
	}
	return $args;
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
    return $history_on;
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
function smarty_assign_tsuite_info(&$smarty,&$request_hash, &$db,&$tree_mgr,$tcase_id,$tproject_id)
{
  $fpath=$tree_mgr->get_full_path_verbose($tcase_id, array('output_format' => 'id_name'));
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
  
  // --------------------------------------------------------------------------------
	if(!is_null($tsuite_info))
  	{
        $cookieKey = 'TL_execSetResults_tsdetails_view_status';
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
    	if( count($a_tsval) > 0 )
    	{
			setcookie($cookieKey,$a_tsval[0],TL_COOKIE_KEEPTIME, '/');
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
  20100625 - asimon - added parameters $bugInterfaceOn, $bugInterface 
                      to get rid of warning in event log

*/
function exec_additional_info(&$db, $attachmentRepository, &$tcase_mgr, $other_execs, 
                              $tplan_id, $tproject_id, $bugInterfaceOn, $bugInterface)
{
//  $bugInterfaceOn = config_get('bugInterfaceOn');
//  $bugInterface = config_get('bugInterface');
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
  			$attachmentInfos[$exec_id] = $aInfo;
  		
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
	$sql = 	" /* $debugMsg */ INSERT INTO {$tables['executions']} " . 
			" (testplan_id,platform_id,build_id,tester_id,execution_type," .
			"  tcversion_id,execution_ts,status,notes) " .
			" VALUES ({$context['context']['tplan_id']}, " . 
			"		  {$context['context']['platform_id']}, " .
			"		  {$context['context']['build_id']}," .
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
    
	// SCHLUNDUS: hmm, for user defined roles, this wont work correctly
	// 20080104 - franciscom - Please explain why do you think will not work ok ?
	//                         do you prefer to check for exec right ?
	//
	// SCHLUNDUS: jep, exactly. If a user defines it own roles than a check for the tester
	// role will not do the desired effect of putting the logged in user in tester-view-mode
	// instead we must check for presence (and or absence) the right(s) which mades a user a tester 
	//
	// 20080310 - franciscom - 
	// Role is considered tester if:
	// role == TL_ROLES_TESTER OR Role has Test Plan execute but not Test Plan planning
	//
	//
	$can_execute = $effective_role->hasRight('testplan_execute');
	$can_manage = $effective_role->hasRight('testplan_planning');
    
	// 20081217 - franciscom
	// $use_exec_cfg = $effective_role->dbID == TL_ROLES_TESTER || ($can_execute && !$can_manage);
    $use_exec_cfg = isset($simple_tester_roles[$effective_role->dbID]) || ($can_execute && !$can_manage);
    
    return  $use_exec_cfg ? $exec_cfg->exec_mode->tester : 'all';
} // function end


/*
  function: setTesterAssignment 

  args:
  
  returns: 
  
  rev: 20110622 - asimon - TICKET 4600: Blocked execution of testcases
       20100121 - franciscom - platform refactoring

*/
function setTesterAssignment(&$db,$exec_info,&$tcase_mgr,$tplan_id,$platform_id, $build_id)
{     
	foreach($exec_info as $version_id => $value)
	{
		$exec_info[$version_id]['assigned_user'] = '';
		$exec_info[$version_id]['assigned_user_id'] = 0;
		
		// map of map: main key version_id, secondary key: platform_id
		// TICKET 4600: Blocked execution of testcases
		$p3 = $tcase_mgr->get_version_exec_assignment($version_id,$tplan_id, $build_id);
		$assignedTesterId = intval($p3[$version_id][$platform_id]['user_id']);
		
		if($assignedTesterId)
		{
			$user = tlUser::getByID($db,$assignedTesterId);
			if ($user)
			{
				$exec_info[$version_id]['assigned_user']= $user->getDisplayName();  
			}
			$exec_info[$version_id]['assigned_user_id'] = $assignedTesterId;
		}  
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
    foreach($tcversion_id as $key => $value)
    {
       $dummy[$key] = $exec_info[$value];    
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
			$assigned_to_me = $tc_exec['assigned_user_id'] == $tester_id ? 1 : 0;
			$is_free = $tc_exec['assigned_user_id'] == '' ? 1 : 0;

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
} //function end


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
        $editors[$tcase_id]=$of->CreateHTML(10,60);         
        unset($of);
    }
    return $editors;
}



/*
  function: getCfg 

  args:
  
  returns: 

*/
function getCfg()
{
    $cfg = new stdClass();
    $cfg->exec_cfg = config_get('exec_cfg');
    $cfg->gui_cfg = config_get('gui');
    $cfg->bts_type = config_get('interface_bugs');
    
    $results = config_get('results');
    $cfg->tc_status = $results['status_code'];
    $cfg->testcase_cfg = config_get('testcase_cfg'); 
    $cfg->editorCfg = getWebEditorCfg('execution');
    
    return $cfg;
}


/*
  function: initializeRights 
            create object with rights useful for this feature 
  
  args:
       dbHandler: reference to db object
       $userObj: reference to current user object
       tproject_id:
       tplan_id
  
       Warning: this is right interface for this function, but
                has_rights() can works in a mode (that i consider a dirty one)
                using SESSION to achieve global coupling.
                 
  returns: 

*/
function initializeRights(&$dbHandler,&$userObj,$tproject_id,$tplan_id)
{
    $exec_cfg = config_get('exec_cfg');
    $grants = new stdClass();
    
    $grants->execute = $userObj->hasRight($dbHandler,"testplan_execute",$tproject_id,$tplan_id);
    $grants->execute = $grants->execute=="yes" ? 1 : 0;
    
    // may be in the future this can be converted to a role right
    $grants->delete_execution=$exec_cfg->can_delete_execution;
    
    
    // may be in the future this can be converted to a role right
    // Important:
    // Execution right must be present to consider this configuration option.
    $grants->edit_exec_notes = $grants->execute && $exec_cfg->edit_notes;
    
    // 20090419 - franciscom - BUGID 
    $grants->edit_testcase = $userObj->hasRight($dbHandler,"mgt_modify_tc",$tproject_id,$tplan_id);
    $grants->edit_testcase = $grants->edit_testcase=="yes" ? 1 : 0;
    return $grants;
}


/*
  function: initializeGui

  args :
  
  returns: 

  rev: 20080429 - franciscom
*/
function initializeGui(&$dbHandler,&$argsObj,&$cfgObj,&$tplanMgr,&$tcaseMgr)
{
    $buildMgr = new build_mgr($dbHandler);
    $platformMgr = new tlPlatform($dbHandler,$argsObj->tproject_id);
    
    $gui = new stdClass();
    $gui->remoteExecFeedback = '';
    $gui->tplan_id=$argsObj->tplan_id;
    $gui->tproject_id=$argsObj->tproject_id;
    $gui->build_id = $argsObj->build_id;
    $gui->platform_id = $argsObj->platform_id;
    
    $gui->execStatusValues=null;
    $gui->can_use_bulk_op=0;
    $gui->exec_notes_editors=null;
    $gui->bulk_exec_notes_editor=null;
    $gui->req_details=null;
    $gui->attachmentInfos=null;
    $gui->bugs=null;
    $gui->other_exec_cfields=null;
    $gui->ownerDisplayName = null;
    
    $gui->editorType=$cfgObj->editorCfg['type'];
    $gui->filter_assigned_to=$argsObj->filter_assigned_to;
    $gui->tester_id=$argsObj->user_id;
    $gui->include_unassigned=$argsObj->include_unassigned;
    $gui->tpn_view_status=$argsObj->tpn_view_status;
    $gui->bn_view_status=$argsObj->bn_view_status;
    $gui->bc_view_status=$argsObj->bc_view_status;
    $gui->platform_notes_view_status=$argsObj->platform_notes_view_status;

    $gui->refreshTree = $argsObj->refreshTree;
    if (!$argsObj->status || $argsObj->status == $cfgObj->tc_status['not_run']) {
    	$gui->refreshTree = 0;
    }
    
    $gui->map_last_exec_any_build=null;
    $gui->map_last_exec=null;

    	
    // 20081122 - franciscom
    // Just for the record:	
    // doing this here, we avoid to do on processTestSuite() and processTestCase(),
    // but absolutely this will not improve in ANY WAY perfomance, because we do not loop
    // over these two functions. 	
    $tprojectMgr = new testproject($dbHandler);
    $gui->tcasePrefix = $tprojectMgr->getTestCasePrefix($argsObj->tproject_id);
    $build_info = $buildMgr->get_by_id($argsObj->build_id);
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

    $the_builds = $tplanMgr->get_builds_for_html_options($argsObj->tplan_id);
    $gui->build_name = isset($the_builds[$argsObj->build_id]) ? $the_builds[$argsObj->build_id] : '';


    // 20090419 - franciscom
    $gui->grants = initializeRights($dbHandler,$argsObj->user,$argsObj->tproject_id,$argsObj->tplan_id);
    $gui->exec_mode = initializeExecMode($dbHandler,$cfgObj->exec_cfg,
                                         $argsObj->user,$argsObj->tproject_id,$argsObj->tplan_id);



    $rs = $tplanMgr->get_by_id($argsObj->tplan_id);
    $gui->testplan_notes = $rs['notes'];

    // Important note: 
    // custom fields for test plan can be edited ONLY on design, that's reason why we are using 
    // scope = 'design' instead of 'execution'
    $gui->testplan_cfields = $tplanMgr->html_table_of_custom_field_values($argsObj->tplan_id,'design',
                                                                          array('show_on_execution' => 1));
    
    $gui->history_on = manage_history_on($_REQUEST,$_SESSION,$cfgObj->exec_cfg,
                                         'btn_history_on','btn_history_off','history_on');
    $gui->history_status_btn_name = $gui->history_on ? 'btn_history_off' : 'btn_history_on';

    $dummy = $platformMgr->getLinkedToTestplan($argsObj->tplan_id);
    $gui->has_platforms = !is_null($dummy) ? 1 : 0;
    
    $gui->platform_info['id']=0;
    $gui->platform_info['name']='';
    if(!is_null($argsObj->platform_id) && $argsObj->platform_id > 0 )
    { 
    	$gui->platform_info = $platformMgr->getByID($argsObj->platform_id);
    }
    
    
    $gui->node_id = $argsObj->id;
    return $gui;
}


/*
  function: processTestCase

  args :
  
  returns: 

  rev: 
    20090913 - franciscom - changes due to platform feature
  	20090718 - franciscom - cfield location management
  	20080811 - franciscom - BUGID 1650 (REQ)
  
*/
function processTestCase($tcase,&$guiObj,&$argsObj,&$cfgObj,$linked_tcversions,
                         &$treeMgr,&$tcaseMgr,&$docRepository)
{     

  	// IMPORTANT due  to platform feature
  	// every element on linked_tcversions will be an array.
    $cf_filters=array('show_on_execution' => 1); // BUGID 1650 (REQ)
    $locationFilters=$tcaseMgr->buildCFLocationMap();
    $guiObj->design_time_cfields='';
  	$guiObj->testplan_design_time_cfields='';
  	
  	$tcase_id = isset($tcase['tcase_id']) ? $tcase['tcase_id'] : $argsObj->id;

  	$items_to_exec[$tcase_id] = $linked_tcversions[$tcase_id][0]['tcversion_id'];    
  	// $tcversion_id = $linked_tcversions[$tcase_id][0]['tcversion_id'];
  	$tcversion_id = isset($tcase['tcversion_id']) ? $tcase['tcversion_id'] : $items_to_exec[$tcase_id];
  	
  	$link_id = $linked_tcversions[$tcase_id][0]['feature_id'];
  	$guiObj->tcAttachments[$tcase_id] = getAttachmentInfos($docRepository,$tcase_id,'nodes_hierarchy',1);

	foreach($locationFilters as $locationKey => $filterValue)
	{

		// BUGID 3431 - Custom Field values at Test Case VERSION Level
		$finalFilters=$cf_filters+$filterValue;
    	$guiObj->design_time_cfields[$tcase_id][$locationKey] = 
  		         $tcaseMgr->html_table_of_custom_field_values($tcase_id,'design',$finalFilters,null,null,
  		         											  $argsObj->tproject_id,null,$tcversion_id);
    	
    	// 20090718 - franciscom - TO BE refactored
    	$guiObj->testplan_design_time_cfields[$tcase_id] = 
  		         $tcaseMgr->html_table_of_custom_field_values($tcversion_id,'testplan_design',$cf_filters,
  		                                                      null,null,$argsObj->tproject_id,null,$link_id);

    }

    // BUGID 856: Guest user can execute test case
  	if($guiObj->grants->execute)
  	{
  	   $guiObj->execution_time_cfields[$tcase_id] = 
  	            $tcaseMgr->html_table_of_custom_field_inputs($tcase_id,null,'execution',"_{$tcase_id}",null,
  	                                                         null,$argsObj->tproject_id);
  	}
    $tc_info=$treeMgr->get_node_hierarchy_info($tcase_id);
	$guiObj->tSuiteAttachments[$tc_info['parent_id']] = getAttachmentInfos($docRepository,$tc_info['parent_id'],
		                                                                   'nodes_hierarchy',true,1);


    return array($tcase_id,$tcversion_id);
}




/*
  function: getLastExecution

  args :
  
  returns: 

  rev: 20110622 - asimon - TICKET 4600: Blocked execution of testcases
*/
function getLastExecution(&$dbHandler,$tcase_id,$tcversion_id,$guiObj,$argsObj,&$tcaseMgr)
{      
	// 20090716 - franciscom - get_last_execution() interface changes
	$options=array('getNoExecutions' => 1, 'groupByBuild' => 0);
    $last_exec = $tcaseMgr->get_last_execution($tcase_id,$tcversion_id,$argsObj->tplan_id,
                                               $argsObj->build_id,$argsObj->platform_id,$options);
    
    if( !is_null($last_exec) )
    {
	    // TICKET 4600: Blocked execution of testcases
        $last_exec=setTesterAssignment($dbHandler,$last_exec,$tcaseMgr,
                                       $argsObj->tplan_id,$argsObj->platform_id, $argsObj->build_id);
        
        // Warning: setCanExecute() must be called AFTER setTesterAssignment()  
        $can_execute=$guiObj->grants->execute && ($guiObj->build_is_open);
        $last_exec=setCanExecute($last_exec,$guiObj->exec_mode,
                                 $can_execute,$argsObj->user_id);
    }
    
    // Reorder executions to mantaing correct visualization order.
    if( is_array($tcversion_id) )
    {
      $last_exec=reorderExecutions($tcversion_id,$last_exec);
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
		$filters['build_id'] = $argsObj->build_id;
      	$filters['platform_id'] = $argsObj->platform_id;
      	
      	if($cfgObj->exec_cfg->show_history_all_builds )
      	{
      	  $filters['build_id'] = ANY_BUILD;
      	}  
      	if($cfgObj->exec_cfg->show_history_all_platforms )
      	{
      	  $filters['platform_id'] = null;
      	}  
      	$options = array('exec_id_order' => $cfgObj->exec_cfg->history_order);
      	$other_execs = $tcaseMgr->get_executions($tcase_id,$tcversion_id,$argsObj->tplan_id,
      	                                         $filters['build_id'],$filters['platform_id'],$options);
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

  rev: 20080811 - franciscom - BUGID 1650 (REQ)

*/
function processTestSuite(&$dbHandler,&$guiObj,&$argsObj,$linked_tcversions,
                          &$treeMgr,&$tcaseMgr,&$docRepository)
{
    $locationFilters=$tcaseMgr->buildCFLocationMap();
    $testSet = new stdClass();
    $cf_filters=array('show_on_execution' => 1); // BUGID 1650 (REQ)    
    
    $tsuite_mgr=new testsuite($dbHandler); 
    $tsuite_data = $tsuite_mgr->get_by_id($argsObj->id);
    $opt = array('write_button_only_if_linked' => 1, 'prune_unlinked_tcversions' => 1);

    // @TODO - 20090815 - franciscom
    // why here we do not have filtered by tester ?
    // same for platform_id
    $filters = array('keywords' => $argsObj->keyword_id);
    $out = gen_spec_view($dbHandler,'testplan',$argsObj->tplan_id,$argsObj->id,$tsuite_data['name'],
                         $linked_tcversions,null,$filters,$opt);
       
    $testSet->tcase_id = array();
    $testSet->tcversion_id = array();
    foreach($out['spec_view'] as $key => $value)
    {
     if( count($value['testcases']) > 0 )
     {
       foreach($value['testcases'] as $xkey => $xvalue)
       {
         $testSet->tcase_id[]=$xkey;
         $testSet->tcversion_id[]=$xvalue['linked_version_id'];
       }  
     }
    }
    // ---------------------------------------------------------------------------------
   
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
            		$guiObj->tcAttachments[$testcase_id] = getAttachmentInfos($docRepository,$testcase_id,
            		                                                            'nodes_hierarchy',true,1);
                	
	            	foreach($locationFilters as $locationKey => $filterValue)
	            	{
                        $finalFilters=$cf_filters+$filterValue;
                        
                        // 
						// BUGID 3431 - Custom Field values at Test Case VERSION Level
            			// $guiObj->design_time_cfields[$testcase_id][$locationKey] = 
            			// 	$tcaseMgr->html_table_of_custom_field_values($testcase_id,'design',$finalFilters);
            			$guiObj->design_time_cfields[$testcase_id][$locationKey] = 
            				$tcaseMgr->html_table_of_custom_field_values($testcase_id,'design',$finalFilters,null,null,
  		         											             $argsObj->tproject_id,null,$testSet->tcversion_id[$gdx]);

                		$guiObj->testplan_design_time_cfields[$testcase_id] = 
  	            		        $tcaseMgr->html_table_of_custom_field_values($testcase_id,'testplan_design',$cf_filters,
  	            		                                                     null,null,$argsObj->tproject_id);
            			                                                                        
            		}	                     

                	// BUGID 856: Guest user can execute test case
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

    return array($testSet->tcase_id,$testSet->tcversion_id);  
}





function buildExecContext(&$argsObj,$tcasePrefix,&$tplanMgr,&$tcaseMgr)
{
	$debugMsg = "File:" . __FILE__ . "Function:" . __FUNCTION__;

	$ret = array();
	$ret['target'] = array(	'tc_versions' => null,'version_id' => null, 
							'feature_id' => null, 'basic_info' => null);
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


?>

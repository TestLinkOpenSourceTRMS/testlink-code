<?php
/** 
 * 	TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 * 	@filesource	archiveData.php
 * 	@author 	Martin Havlat
 * 
 * 	Allows you to show test suites, test cases.
 * 	Normally launched from tree navigator.
 *	Also called when search option on Navigation Bar is used
 *
 *	@internal revisions
 *  @since 2.0
 *  20121004 - asimon - TICKET 5265: test case search displays only the first version of all test cases
 *  20120909 - franciscom - attachment management refactoring
 *
 */
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);
$smarty = new TLSmarty();
$smarty->tlTemplateCfg = $templateCfg = templateConfiguration();

list($args,$gui,$grants) = initializeEnv($db);

$identity = new stdClass();
$identity->id = $args->id;
$identity->tproject_id = $args->tproject_id;

switch($args->feature)
{
	case 'testproject':
	case 'testsuite':
		$gui->id = $args->id;
    $gui->grants = $grants;
		$item_mgr = new $args->feature($db);
		if($args->feature == 'testproject')
		{
			$item_mgr->show($smarty,$gui,$identity);
		}
		else
		{
		  $dummy = $item_mgr->getNode($identity->id);
		  $identity->parent_id = $dummy['parent_id'];
			$item_mgr->show($smarty,$gui,$identity,array('show_mode' => $gui->show_mode));
    }
	break;
		
	case 'testcase':
	  $gui->path_info = null;
		$get_path_info = false;
		$item_mgr = new testcase($db);
		$gui->viewerArgs['refresh_tree'] = 'no';
    	
   		// has been called from a test case search
  	if(!is_null($args->targetTestCase) && strcmp($args->targetTestCase,$args->tcasePrefix) != 0)
	  {
		  $gui->viewerArgs['show_title'] = 'no';
		  $gui->viewerArgs['display_testproject'] = 1;
		  $gui->viewerArgs['display_parent_testsuite'] = 1;
		  $args->id = $item_mgr->getInternalID($args->targetTestCase);
          
		  if( !($get_path_info = ($args->id > 0)) )
		  {
			  $gui->warning_msg = $args->id == 0 ? lang_get('testcase_does_not_exists') : lang_get('prefix_does_not_exists');
 		  }
      // TICKET 5265: test case search displays only the first version of all test cases
      $args->tcversion_id = testcase::ALL_VERSIONS;
	  }

	  if( $args->id > 0 )
	  {
		  if( $get_path_info || $args->show_path )
		  {
		    $gui->path_info = $item_mgr->tree_manager->get_full_path_verbose($args->id);
		  }
		
	    $platform_mgr = new tlPlatform($db,$args->tproject_id);
	    $gui->platforms = $platform_mgr->getAllAsMap();
      $gui->attachments[$args->id] = $item_mgr->getAttachmentInfos($args->id);
	    $gui->direct_link = $item_mgr->buildDirectWebLink($_SESSION['basehref'],$args->id);
	  }
    $gui->id = $args->id;
		$identity->version_id = $args->tcversion_id;
	  $item_mgr->show($smarty,$gui,$identity,$grants);
	break;

  default:
		tLog('Argument "edit" has invalid value: ' . $args->feature , 'ERROR');
		trigger_error($_SESSION['currentUser']->login.'> Argument "edit" has invalid value.', E_USER_ERROR);
	break;
		
}



/**
 * 
 *
 */
function initializeEnv($dbHandler)
{
  $args = init_args($dbHandler);

  $gui = new stdClass();

  $grant2check = array('mgt_modify_tc','mgt_view_req','testplan_planning','mgt_modify_product');
  $grants = new stdClass();
  foreach($grant2check as $right)
  {
      $grants->$right = $_SESSION['currentUser']->hasRight($dbHandler,$right,$args->tproject_id);
      $gui->$right = $grants->$right;
  }
  
  $gui->tproject_id = $args->tproject_id;
  $gui->page_title = lang_get('container_title_' . $args->feature);
  $gui->opt_requirements = $args->opt_requirements; 
  $gui->requirementsEnabled = $args->requirementsEnabled; 
  $gui->automationEnabled = $args->automationEnabled; 
  $gui->testPriorityEnabled = $args->testPriorityEnabled;
  $gui->show_mode = $args->show_mode;
	$lblkey = config_get('testcase_reorder_by') == 'NAME' ? '_alpha' : '_externalid';
	$gui->btn_reorder_testcases = lang_get('btn_reorder_testcases' . $lblkey);


  // has sense only when we work on test case
	$gui->platforms = null;
  $gui->tableColspan = 5;
	$gui->loadOnCancelURL = '';
	$gui->attachments = null;
	$gui->direct_link = null;
	$gui->steps_results_layout = config_get('spec_cfg')->steps_results_layout;
	$gui->bodyOnUnload = "storeWindowSize('TCEditPopup')";
	$gui->viewerArgs = array('action' => '', 'msg_result' => '','user_feedback' => '', 'disable_edit' => 0,
	                         'refreshTree' => 0);

  return array($args,$gui,$grants);
}

/**
 * 
 *
 */
function init_args(&$dbHandler)
{
	$_REQUEST=strings_stripSlashes($_REQUEST);
  

	$iParams = array("edit" => array(tlInputParameter::STRING_N,0,50),
			             "id" => array(tlInputParameter::INT_N),
			             "show_path" => array(tlInputParameter::INT_N),
    			         "show_mode" => array(tlInputParameter::STRING_N,0,50),
    			         "tcase_id" => array(tlInputParameter::INT_N),
    			         "tcversion_id" => array(tlInputParameter::INT_N),
    			         "tproject_id" => array(tlInputParameter::INT_N),
    			         "targetTestCase" => array(tlInputParameter::STRING_N,0,24),
    			         "tcasePrefix" => array(tlInputParameter::STRING_N,0,16),
    			         "tcaseExternalID" => array(tlInputParameter::STRING_N,0,16),
    			         "tcaseVersionNumber" => array(tlInputParameter::INT_N));

	$args = new stdClass();
  R_PARAMS($iParams,$args);

	// For more information about the data accessed in session here, see the comment
	// in the file header of lib/functions/tlTestCaseFilterControl.class.php.
	$form_token = isset($_REQUEST['form_token']) ? $_REQUEST['form_token'] : 0;
	
	$mode = 'edit_mode';
  $cfg = config_get('testcase_cfg');
	
	$session_data = isset($_SESSION[$mode]) && isset($_SESSION[$mode][$form_token])
	                ? $_SESSION[$mode][$form_token] : null;
	
	$args->refreshTree = isset($session_data['setting_refresh_tree_on_action']) ?
                         $session_data['setting_refresh_tree_on_action'] : 0;
	
  $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
  $args->feature = $args->edit;
    
  $args->opt_requirements = null;
	$args->automationEnabled = 0;
	$args->requirementsEnabled = 0;
	$args->testPriorityEnabled = 0;
	$args->tcasePrefix = trim($args->tcasePrefix);

  $tprojectMgr = new testproject($dbHandler);
	if($args->tproject_id <= 0 &&  strlen($args->tcaseExternalID) > 0 )
	{
		// parse to get JUST prefix
		$gluePos = strrpos($args->tcaseExternalID, $cfg->glue_character); // Find the last glue char
		$status_ok = ($gluePos !== false);
		if($status_ok)
		{
			$tcasePrefix = substr($args->tcaseExternalID, 0, $gluePos);
		}
		$dummy = $tprojectMgr->get_by_prefix($tcasePrefix);	

		$tcaseMgr = new testcase($dbHandler);
		$args->tcase_id = $tcaseMgr->getInternalID($args->tcaseExternalID);
		$tcinfo = $tcaseMgr->get_basic_info($args->tcase_id,array('number' => $args->tcaseVersionNumber));
		if(!is_null($tcinfo))
		{
			$args->tcversion_id = $tcinfo[0]['tcversion_id'];
		}
		unset($tcaseMgr); 
	}
	else
	{
		$dummy = $tprojectMgr->get_by_id($args->tproject_id);
	}	
	
	if(!is_null($dummy))
	{
		$args->tproject_id = $dummy['id'];
	}
	$args->opt_requirements = $dummy['opt']->requirementsEnabled;
	$args->requirementsEnabled = $dummy['opt']->requirementsEnabled;
	$args->automationEnabled = $dummy['opt']->automationEnabled;
	$args->testPriorityEnabled = $dummy['opt']->testPriorityEnabled;

    
  if (!$args->tcversion_id)
  {
   		 $args->tcversion_id = testcase::ALL_VERSIONS;
  }
  
  // used to manage goback  
  if(intval($args->tcase_id) > 0)
  {
    	$args->feature = 'testcase';
    	$args->id = intval($args->tcase_id);
  }
	$args->id = is_null($args->id) ? 0 : $args->id;
    
  switch($args->feature)
  {
		case 'testsuite':
        	$_SESSION['setting_refresh_tree_on_action'] = ($args->refreshTree) ? 1 : 0;
    break;
     
  	break;
  }
  
  if (strpos($args->targetTestCase,$cfg->glue_character) === false)
  {
    	$args->targetTestCase = $args->tcasePrefix . $args->targetTestCase;
 	}
  return $args;
}
?>
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
 *	@internal revision
 *  20111105 - franciscom - TICKET 4796: Test Case reuse - Quick & Dirty Approach
 */

require_once('../../config.inc.php');
require_once('common.php');
require_once('testsuite.class.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$viewerArgs = null;

$tproject_mgr = new testproject($db);

$userObj = $_SESSION['currentUser'];
$args = init_args($viewerArgs,$tproject_mgr);

$grants = new stdClass();
$grants->mgt_modify_tc = $userObj->hasRight($db,'mgt_modify_tc',$args->tproject_id);
$grants->mgt_view_req = $userObj->hasRight($db,"mgt_view_req",$args->tproject_id);
$grants->testplan_planning = $userObj->hasRight($db,"testplan_planning",$args->tproject_id);

$smarty = new TLSmarty();
$gui = new stdClass();
$gui->modify_tc_rights = $grants->mgt_modify_tc;  // TICKET 
$gui->tproject_id = $args->tproject_id;
$gui->page_title = lang_get('container_title_' . $args->feature);
$gui->opt_requirements = $args->opt_requirements; 
$gui->requirementsEnabled = $args->requirementsEnabled; 
$gui->automationEnabled = $args->automationEnabled; 
$gui->testPriorityEnabled = $args->testPriorityEnabled;

switch($args->feature)
{
	case 'testproject':
	case 'testsuite':
		$item_mgr = new $args->feature($db);
		$gui->attachments = getAttachmentInfosFrom($item_mgr,$args->id);
		$gui->id = $args->id;
		
		$lblkey = config_get('testcase_reorder_by') == 'NAME' ? '_alpha' : '_externalid';
		$gui->btn_reorder_testcases = lang_get('btn_reorder_testcases' . $lblkey);

		if($args->feature == 'testproject')
		{
			$item_mgr->show($smarty,$gui,$templateCfg->template_dir,$args->id);
		}
		else
		{
			$item_mgr->show($smarty,$args->tproject_id,$gui,$templateCfg->template_dir,
							$args->id,array('show_mode' => $args->show_mode));
        }
		break;
		
	case 'testcase':
		$path_info = null;
		$get_path_info = false;
		$item_mgr = new testcase($db);
		$viewerArgs['refresh_tree'] = 'no';

	    $gui->platforms = null;
        $gui->tableColspan = 5;
		$gui->loadOnCancelURL = '';
		$gui->attachments = null;
		$gui->direct_link = null;
		$gui->steps_results_layout = config_get('spec_cfg')->steps_results_layout;
		$gui->bodyOnUnload = "storeWindowSize('TCEditPopup')";
    	
   		// has been called from a test case search
		if(!is_null($args->targetTestCase) && strcmp($args->targetTestCase,$args->tcasePrefix) != 0)
		{
			$viewerArgs['show_title'] = 'no';
			$viewerArgs['display_testproject'] = 1;
			$viewerArgs['display_parent_testsuite'] = 1;
			$args->id = $item_mgr->getInternalID($args->targetTestCase);
            
			if( !($get_path_info = ($args->id > 0)) )
			{
				$gui->warning_msg = $args->id == 0 ? lang_get('testcase_does_not_exists') : lang_get('prefix_does_not_exists');
 			} 
			
		}

		if( $args->id > 0 )
		{
			if( $get_path_info || $args->show_path )
			{
			    $path_info = $item_mgr->tree_manager->get_full_path_verbose($args->id);
			}
			
		  	$platform_mgr = new tlPlatform($db,$args->tproject_id);
	    	$gui->platforms = $platform_mgr->getAllAsMap();
      		$gui->attachments[$args->id] = getAttachmentInfosFrom($item_mgr,$args->id);
			$gui->direct_link = $item_mgr->buildDirectWebLink($_SESSION['basehref'],$args->id);
		}
	    $gui->id = $args->id;
	    
	    
		$item_mgr->show($smarty,$args->tproject_id,$grants,$gui,$templateCfg->template_dir,
						$args->id,$args->tcversion_id,$viewerArgs,$path_info,$args->show_mode);
		break;

	default:
		tLog('Argument "edit" has invalid value: ' . $args->feature , 'ERROR');
		trigger_error($_SESSION['currentUser']->login.'> Argument "edit" has invalid value.', E_USER_ERROR);
}

/**
 * 
 *
 */
function init_args(&$viewerCfg,&$tprojectMgr)
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
		$tcaseMgr = new testcase($tprojectMgr->db);
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
    
   	switch($args->feature)
    {
		case 'testsuite':
        	$_SESSION['setting_refresh_tree_on_action'] = ($args->refreshTree) ? 1 : 0;
        	break;
     
        case 'testcase':
			$args->id = is_null($args->id) ? 0 : $args->id;
			$spec_cfg = config_get('spec_cfg');
			$viewerCfg = array('action' => '', 'msg_result' => '','user_feedback' => '');
			$viewerCfg['disable_edit'] = 0;

			// need to understand if using this logic is ok
			// Why I'm ignoring $args->setting_refresh_tree_on_action ?
			// Seems here I have to set refresh always to NO!!!
			//
			// $viewerCfg['refresh_tree'] = $spec_cfg->automatic_tree_refresh ? "yes" : "no";
			// if(isset($_SESSION['setting_refresh_tree_on_action']))
			// {
			// 	$viewerCfg['refresh_tree'] = $_SESSION['setting_refresh_tree_on_action'];
            // }
            $viewerCfg['refreshTree'] = 0;
			break;
    }
    if (strpos($args->targetTestCase,$cfg->glue_character) === false)
    {
    	$args->targetTestCase = $args->tcasePrefix . $args->targetTestCase;
 	}
    return $args;
}
?>
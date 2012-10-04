<?php
/** 
 * 	TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 * 	@version 	$Id: archiveData.php,v 1.78.2.3 2011/01/10 15:38:59 asimon83 Exp $
 * 	@author 	Martin Havlat
 * 
 * 	Allows you to show test suites, test cases.
 * 	Normally launched from tree navigator.
 *	Also called when search option on Navigation Bar is used
 *
 *	@internal revision
 *	@since 1.9.5
 *  20121004 - asimon - TICKET 5265: test case search displays only the first version of all test cases
 *  20120927 - franciscom - TICKET 5250: User rights "Test Case view" not work
 */

require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$viewerArgs = null;
$cfg = array('testcase' => config_get('testcase_cfg'),                  
			 'testcase_reorder_by' => config_get('testcase_reorder_by'),
			 'spec' => config_get('spec_cfg'));                         
$smarty = new TLSmarty();

$args = init_args($db,$viewerArgs,$cfg);
$gui = new stdClass();
$gui->page_title = lang_get('container_title_' . $args->feature);
$gui->user_feedback = '';


// User right at test project level has to be done
// Because this script can be called requesting an item that CAN BELONG
// to a test project DIFFERENT that value present on SESSION,
// we need to use requested item to get its right Test Project
// We will start with Test Cases ONLY
switch($args->feature)
{
	case 'testproject':
	case 'testsuite':
		$item_mgr = new $args->feature($db);
		$gui->id = $args->id;
		
		$lblkey = $cfg['testcase_reorder_by'] == 'NAME' ? '_alpha' : '_externalid';
		$gui->btn_reorder_testcases = lang_get('btn_reorder_testcases' . $lblkey);

		if($args->feature == 'testproject')
		{
		  $gui->id = $args->id = $args->tproject_id;
			$item_mgr->show($smarty,$gui,$templateCfg->template_dir,$args->id);
		}
		else
		{
		  $gui->attachments = getAttachmentInfosFrom($item_mgr,$args->id);
			$item_mgr->show($smarty,$gui,$templateCfg->template_dir,$args->id,
							array('show_mode' => $args->show_mode));
        
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
		$gui->steps_results_layout = $cfg['spec']->steps_results_layout;
		$gui->bodyOnUnload = "storeWindowSize('TCEditPopup')";
    	
   		// we use $args->targetTestCase, to understand if this script has been called from a test case search
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
            // TICKET 5265: test case search displays only the first version of all test cases
            $args->tcversion_id = testcase::ALL_VERSIONS;
		}

		if( $args->id > 0 )
		{
			// Get Test Project in order to check user rights
			if( !is_null($args->tcaseTestProject) )
			{
			  $check = null;
				$grant2check = array('mgt_view_tc','mgt_modify_tc');
				foreach($grant2check as $grant)
				{
						$grantlbl['desc_' . $grant] = null;
				    $check = $_SESSION['currentUser']->hasRight($db,$grant,$args->tcaseTestProject['id']);
            if( !is_null($check) )
            {
              break;
            }
				}

				if( is_null($check) )
				{
					$grantLabels = init_labels($grantlbl);
					$logtext = lang_get('access_denied_feedback');
					foreach($grantLabels as $lbl)
					{
						$logtext .= "'" . $lbl . "',";
					}
					$logtext = trim($logtext,",");
					
					$dummy = 
					$smarty->assign('title', lang_get('access_denied'));
					$smarty->assign('content', $logtext);
					$smarty->assign('link_to_op', null);
					$smarty->display('workAreaSimple.tpl'); 
					exit();
				}
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
		$item_mgr->show($smarty,$gui,$templateCfg->template_dir,$args->id,$args->tcversion_id,
		                $viewerArgs,$path_info,$args->show_mode);
		break;

	default:
		tLog('Argument "edit" has invalid value: ' . $args->feature , 'ERROR');
		trigger_error($_SESSION['currentUser']->login.'> Argument "edit" has invalid value.', E_USER_ERROR);
}

/**
 * 
 *
 */
function init_args(&$dbHandler,&$viewerCfg,$cfgObj)
{
	$iParams = array("edit" => array(tlInputParameter::STRING_N,0,50),
			         "id" => array(tlInputParameter::INT_N),
			         "tcase_id" => array(tlInputParameter::INT_N),
			         "tcversion_id" => array(tlInputParameter::INT_N),
			         "targetTestCase" => array(tlInputParameter::STRING_N,0,24),
			         "show_path" => array(tlInputParameter::INT_N),
			         "show_mode" => array(tlInputParameter::STRING_N,0,50),
			         "tcasePrefix" => array(tlInputParameter::STRING_N,0,16),
					 "tcaseExternalID" => array(tlInputParameter::STRING_N,0,16),
					 "tcaseVersionNumber" => array(tlInputParameter::INT_N));			         

	$args = new stdClass();
    R_PARAMS($iParams,$args);
	$_REQUEST=strings_stripSlashes($_REQUEST);

	// For more information about the data accessed in session here, see the comment
	// in the file header of lib/functions/tlTestCaseFilterControl.class.php.
	$form_token = isset($_REQUEST['form_token']) ? $_REQUEST['form_token'] : 0;
	
	$mode = 'edit_mode';
	
	$session_data = isset($_SESSION[$mode]) && isset($_SESSION[$mode][$form_token])
	                ? $_SESSION[$mode][$form_token] : null;
	
	$args->refreshTree = isset($session_data['setting_refresh_tree_on_action']) ?
                         $session_data['setting_refresh_tree_on_action'] : 0;
	
    $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
    $args->feature = $args->edit;
	$args->tcaseTestProject = null;

	$tprojectMgr = new testproject($dbHandler);
	$tcaseMgr = new testcase($dbHandler);


    // For lazy users that do not provide test case external id as PREFIX-NN, but just NN
    // we try to help adding $args->tcasePrefix, that we can get on call
    // need to understand in what situation call provides this argument
	$tcasePrefix = $args->tcasePrefix;
    if (strpos($args->targetTestCase,$cfgObj['testcase']->glue_character) === false)
    {
    	$args->targetTestCase = $args->tcasePrefix . $args->targetTestCase;
 	}


	// TICKET 4842: Test Case search - add check of user rights when trying to access test case 
	$verboseTCID = strlen($args->tcaseExternalID) > 0 ? $args->tcaseExternalID : null;
	if( is_null($verboseTCID) )
	{
		$verboseTCID = strlen($args->targetTestCase) > 0  ? $args->targetTestCase : null;
	}
	
	if( !is_null($verboseTCID) )
	{
		// parse to get JUST prefix
		$gluePos = strrpos($verboseTCID, $cfgObj['testcase']->glue_character); // Find the last glue char
		$status_ok = ($gluePos !== false);
		if($status_ok)
		{
			$tcasePrefix = substr($verboseTCID, 0, $gluePos);
		}
		
		$args->tcaseTestProject = $tprojectMgr->get_by_prefix($tcasePrefix);
				
		$args->tcase_id = $tcaseMgr->getInternalID($verboseTCID);
		$tcinfo = $tcaseMgr->get_basic_info($args->tcase_id,array('number' => $args->tcaseVersionNumber));
		if(!is_null($tcinfo))
		{
			$args->tcversion_id = $tcinfo[0]['tcversion_id'];
		}
	}
    else
    {
	   	if (!$args->tcversion_id)
	   	{
	   		 $args->tcversion_id = testcase::ALL_VERSIONS;
	    }
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
			$viewerCfg = array('action' => '', 'msg_result' => '','user_feedback' => '');
			$viewerCfg['disable_edit'] = 0;
            $viewerCfg['refreshTree'] = 0;
            
            if( is_null($args->tcaseTestProject) )
            {
            	$id = $tcaseMgr->get_testproject($args->id);
            	$args->tcaseTestProject = $tprojectMgr->get_by_id($id);
            }
			break;
    }
    
   	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;


	unset($tcaseMgr);
	unset($tprojectMgr);
    return $args;
}
?>
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
 *  20101008 - asimon - BUGID 3311
 *  20100916 - amitkhullar - BUGID 3639
 *  20100628 - asimon - BUGID 3406: removed old logic from BUGID 3049,
 *                      functionality will be changed because of user assigments per build
 *  20100628 - asimon - removal of constants from filter control class
 *  20160625 - asimon - refactoring for new filter features and BUGID 3516
 *  20100624 - asimon - CVS merge (experimental branch to HEAD)
 *	20100621 - eloff - BUGID 3241 - Implement vertical layout
 *	20100502 - franciscom - BUGID 3405: Navigation Bar - Test Case Search - Crash when search a nonexistent testcase	
 *  20100315 - franciscom - fixed refesh tree logic	
 *  20100223 - asimon - BUGID 3049
 */

require_once('../../config.inc.php');
require_once('common.php');
require_once('testsuite.class.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$viewerArgs = null;
$cfg = array('testcase' => config_get('testcase_cfg'),                  
			 'testcase_reorder_by' => config_get('testcase_reorder_by'),
			 'spec' => config_get('spec_cfg'));                         

$args = init_args($viewerArgs,$cfg);
$smarty = new TLSmarty();
$gui = new stdClass();
$gui->page_title = lang_get('container_title_' . $args->feature);


switch($args->feature)
{
	case 'testproject':
	case 'testsuite':
		$item_mgr = new $args->feature($db);
		$gui->attachments = getAttachmentInfosFrom($item_mgr,$args->id);
		$gui->id = $args->id;
		
		$lblkey = $cfg['testcase_reorder_by'] == 'NAME' ? '_alpha' : '_externalid';
		$gui->btn_reorder_testcases = lang_get('btn_reorder_testcases' . $lblkey);

		if($args->feature == 'testproject')
		{
			$item_mgr->show($smarty,$gui,$templateCfg->template_dir,$args->id);
		}
		else
		{
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
		// 20101008 - asimon - BUGID 3311
		$gui->bodyOnUnload = 'storeWindowSize(\'TCEditPopup\')';
    	
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
function init_args(&$viewerCfg,$cfgObj)
{
	$iParams = array("edit" => array(tlInputParameter::STRING_N,0,50),
			         "id" => array(tlInputParameter::INT_N),
			         "tcase_id" => array(tlInputParameter::INT_N),
			         "tcversion_id" => array(tlInputParameter::INT_N),
			         "targetTestCase" => array(tlInputParameter::STRING_N,0,24),
			         "show_path" => array(tlInputParameter::INT_N),
			         "show_mode" => array(tlInputParameter::STRING_N,0,50),
			         "tcasePrefix" => array(tlInputParameter::STRING_N,0,16));
	 				 //"setting_refresh_tree_on_action" => array(tlInputParameter::STRING_N,0,1));

	$args = new stdClass();
    R_PARAMS($iParams,$args);
	
	// BUGID 4066 - take care of proper escaping when magic_quotes_gpc is enabled
	$_REQUEST=strings_stripSlashes($_REQUEST);

	// BUGID 3516
	// For more information about the data accessed in session here, see the comment
	// in the file header of lib/functions/tlTestCaseFilterControl.class.php.
	$form_token = isset($_REQUEST['form_token']) ? $_REQUEST['form_token'] : 0;
	
	$mode = 'edit_mode';
	
	$session_data = isset($_SESSION[$mode]) && isset($_SESSION[$mode][$form_token])
	                ? $_SESSION[$mode][$form_token] : null;
	
	$args->refreshTree = isset($session_data['setting_refresh_tree_on_action']) ?
                         $session_data['setting_refresh_tree_on_action'] : 0;
	
    $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
    //@TODO schlundus, rename Parameter from edit to feature
    $args->feature = $args->edit;

    
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
			$viewerCfg = array('action' => '', 'msg_result' => '','user_feedback' => '');
			$viewerCfg['disable_edit'] = 0;
            $viewerCfg['refreshTree'] = 0;
			break;
    }
    if (strpos($args->targetTestCase,$cfgObj['testcase']->glue_character) === false)
    {
    	$args->targetTestCase = $args->tcasePrefix . $args->targetTestCase;
 	}
   	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    return $args;
}
?>
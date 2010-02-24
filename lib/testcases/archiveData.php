<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @version $Id: archiveData.php,v 1.64 2010/02/24 08:31:13 asimon83 Exp $
 * @author Martin Havlat
 *
 * Allows you to show test suites, test cases.
 * Normally launched from tree navigator.
 *
 * rev :
 *  20100223 - asimon - BUGID 3049
 *  20100124 - franciscom - pass platform info to testcase.show()
 *	20100103 - franciscom - changes on calls to show()
 *	20090329 - franciscom - added management of new call parameter tcversion_id
 *	20090326 - franciscom - solved bug related to forced READ ONLY when called as 
 *	           result of search on Navigator bar.
 *	20090228 - franciscom - this page is called when search option on Navigation Bar is used.
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('testsuite.class.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$viewerArgs = null;
$args = init_args($viewerArgs);
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
		// $smarty->assign('id',$args->id);
		// $smarty->assign('attachmentInfos',$attachments);
		
		if($args->feature == 'testproject')
		{
			$item_mgr->show($smarty,$gui,$templateCfg->template_dir,$args->id);
		}
		else
		{
			$item_mgr->show($smarty,$gui,$templateCfg->template_dir,$args->id,array('show_mode' => $args->show_mode));
        }
        
		break;

	// BUGID 3049
	case 'testplan': 
		$tplan_mgr = new testplan($db);
		$tproject_mgr = new testproject($db);
		$gui->id = $args->id;
		$gui->tplan_id = $args->id;
		
		$options = array('output' => 'array');
		$linked_tcversions=$tplan_mgr->get_linked_tcversions($gui->tplan_id,null,$options);
		
		foreach ($linked_tcversions as $tc_id => $tc) {
			if (!isset($tc['user_id']) || !is_numeric($tc['user_id'])) {
				unset($linked_tcversions[$tc_id]);
			}
		}
		
		if (count($linked_tcversions) != 0) {
			// yes, we have testcases to unassign, draw the button
			$gui->draw_tc_unassign_button = true;
		} else {
			$gui->draw_tc_unassign_button = false;
		}

		$tplan = $tplan_mgr->get_by_id($args->id);
		$gui->tplan_name = $tplan['name'];
		$gui->container_data['name'] = $tplan['name'];
		$gui->tplan_description = $tplan['notes'];
		$tproject = $tproject_mgr->get_by_id($tplan['testproject_id']);
		$gui->tproject_name = $tproject['name'];
		$gui->tproject_description = $tproject['notes'];
				
		$gui->level = 'testplan';
		$gui->mainTitle = lang_get('remove_assigned_testcases');
		$gui->page_title = lang_get('testplan');
		$gui->refreshTree = false;
		$gui->unassign_all_tcs_warning_msg = 
				sprintf(lang_get('unassign_all_tcs_warning_msg'), "{$gui->tplan_name}");
		
		$smarty->assign('gui', $gui);
		$smarty->display($templateCfg->template_dir . 'containerView.tpl');
		
		break;
		
	case 'testcase':
		$path_info = null;
		$get_path_info = false;
		$item_mgr = new testcase($db);
    	
   		// has been called from a test case search
		if(!is_null($args->targetTestCase) && strcmp($args->targetTestCase,$args->tcasePrefix) != 0)
		{
			$viewerArgs['show_title'] = 'no';
			$viewerArgs['display_testproject'] = 1;
			$viewerArgs['display_parent_testsuite'] = 1;

			// need to get internal Id from External ID
			$args->id = $item_mgr->getInternalID($args->targetTestCase);

            if($args->id > 0)
            {
                $get_path_info = true;
                $path_info = $item_mgr->tree_manager->get_full_path_verbose($args->id);
            }
		}
		
		if($get_path_info || $args->show_path)
		{
		    $path_info = $item_mgr->tree_manager->get_full_path_verbose($args->id);
		}
			
	    $platform_mgr = new tlPlatform($db,$args->tproject_id);
	    $gui->platforms = $platform_mgr->getAllAsMap();
        $gui->tableColspan = 5;
		$gui->loadOnCancelURL = '';
		$gui->attachments[$args->id] = ($args->id > 0) ? getAttachmentInfosFrom($item_mgr,$args->id): null;
		$gui->direct_link = $item_mgr->buildDirectWebLink($_SESSION['basehref'],$args->id);
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
function init_args(&$viewerCfg)
{
	$iParams = array("edit" => array(tlInputParameter::STRING_N,0,50),
			         "id" => array(tlInputParameter::INT_N),
			         "tcversion_id" => array(tlInputParameter::INT_N),
			         "targetTestCase" => array(tlInputParameter::STRING_N,0,24),
			         "show_path" => array(tlInputParameter::INT_N),
			         "show_mode" => array(tlInputParameter::STRING_N,0,50),
			         "tcasePrefix" => array(tlInputParameter::STRING_N,0,16),
	 				 "tcspec_refresh_on_action" => array(tlInputParameter::STRING_N,0,1));

	$args = new stdClass();
    R_PARAMS($iParams,$args);
	
    $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
    //@TODO schlundus, rename Parameter from edit to feature
    $args->feature = $args->edit;

    
   	if (!$args->tcversion_id)
   	{
   		 $args->tcversion_id = testcase::ALL_VERSIONS;
    }
    
   	switch($args->feature)
    {
		case 'testsuite':
        	$_SESSION['tcspec_refresh_on_action'] = ($args->tcspec_refresh_on_action == 'y') ? "yes" : "no";
        	break;
     
        case 'testcase':
			$args->id = is_null($args->id) ? 0 : $args->id;
			$spec_cfg = config_get('spec_cfg');
			$viewerCfg = array('action' => '', 'msg_result' => '','user_feedback' => '');
			$viewerCfg['refresh_tree'] = $spec_cfg->automatic_tree_refresh ? "yes" : "no";
			$viewerCfg['disable_edit'] = 0;

			if(isset($_SESSION['tcspec_refresh_on_action']))
			{
				$viewerCfg['refresh_tree'] = $_SESSION['tcspec_refresh_on_action'];
            }
			break;
    }
    $cfg = config_get('testcase_cfg');
    if (strpos($args->targetTestCase,$cfg->glue_character) === false)
    {
    	$args->targetTestCase = $args->tcasePrefix . $args->targetTestCase;
 	}
   	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    return $args;
}
?>
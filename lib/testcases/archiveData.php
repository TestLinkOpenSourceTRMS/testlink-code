<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @version $Id: archiveData.php,v 1.46 2009/04/20 19:39:33 schlundus Exp $
 * @author Martin Havlat
 *
 * Allows you to show test suites, test cases.
 * Normally launched from tree navigator.
 *
 * rev :
 *      20090329 - franciscom - added management of new call parameter tcversion_id
 *      20090326 - franciscom - solved bug related to forced READ ONLY when called as 
 *                 result of search on Navigator bar.
 *      20090228 - franciscom - this page is called when search option on Navigation Bar is used.
 *      20080425 - franciscom - refactoring
 *      20080120 - franciscom - show() method for test cases - interface changes
 *      20070930 - franciscom - REQ - BUGID 1078
 *
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('testsuite.class.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$viewerArgs = null;
$args = init_args($viewerArgs);

$path_info = null;
$smarty = new TLSmarty();
$smarty->assign('page_title',lang_get('container_title_' . $args->feature));


switch($args->feature)
{
	case 'testproject':
	case 'testsuite':
		$item_mgr = new $args->feature($db);
		$attachments = getAttachmentInfosFrom($item_mgr,$args->id);
		$smarty->assign('id',$args->id);
		$smarty->assign('attachmentInfos',$attachments);
		$item_mgr->show($smarty,$templateCfg->template_dir,$args->id);
		break;

	case 'testcase':
		$get_path_info=false;
		$item_mgr = new testcase($db);
    	$args->id = is_null($args->id) ? 0 : $args->id;
    
   		// has been called from a test case search
		if( !is_null($args->targetTestCase) && strcmp($args->targetTestCase,$args->tcasePrefix) != 0)
		{
			$viewerArgs['show_title'] = 'no';
			$viewerArgs['display_testproject'] = 1;
			$viewerArgs['display_parent_testsuite'] = 1;

			// need to get internal Id from External ID
			$cfg = config_get('testcase_cfg');
			$args->id=$item_mgr->getInternalID($args->targetTestCase,$cfg->glue_character);
      
            if( $args->id > 0)
            {
                $get_path_info = true;
                $path_info = $item_mgr->tree_manager->get_full_path_verbose($args->id);
            }
		}
		
		if( $get_path_info || $args->show_path)
		{
		    $path_info = $item_mgr->tree_manager->get_full_path_verbose($args->id);
		}
			
		$attachments[$args->id] = $args->id > 0 ? getAttachmentInfosFrom($item_mgr,$args->id): null ;

        $smarty->assign('id',$args->id);
		$smarty->assign('attachments',$attachments);
		$item_mgr->show($smarty,$templateCfg->template_dir,$args->id,$args->tcversion_id,
		                $viewerArgs,$path_info);

//		                testcase::ALL_VERSIONS,$viewerArgs,$path_info);
		break;

	default:
		tLog('$_GET["edit"] has invalid value: ' . $args->feature , 'ERROR');
		trigger_error($_SESSION['currentUser']->login.'> $_GET["edit"] has invalid value.', E_USER_ERROR);
}


/*
  function: init_args

  args:

  returns:

*/
function init_args(&$viewerCfg)
{
	  $args = new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);

    $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
    $args->feature = isset($_REQUEST['edit']) ? $_REQUEST['edit'] : null;
    $args->id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null;
    $args->targetTestCase = isset($_REQUEST['targetTestCase']) ? $_REQUEST['targetTestCase'] : null;
    // $args->allow_edit = isset($_REQUEST['allow_edit']) ? intval($_REQUEST['allow_edit']) : 1;
    $args->tcasePrefix = isset($_REQUEST['tcasePrefix']) ? trim($_REQUEST['tcasePrefix']) : null;
    $args->show_path = isset($_REQUEST['show_path']) ? $_REQUEST['show_path'] : 0;
    $args->tcversion_id = isset($_REQUEST['tcversion_id']) ? intval($_REQUEST['tcversion_id']) : testcase::ALL_VERSIONS;

    switch($args->feature)
    {
        case 'testsuite':
            $_SESSION['tcspec_refresh_on_action'] = isset($_REQUEST['tcspec_refresh_on_action'])? "yes":"no";
        break;

        case 'testcase':
		        $spec_cfg = config_get('spec_cfg');
		        $viewerCfg = array('action' => '', 'msg_result' => '','user_feedback' => '');
		        $viewerCfg['refresh_tree'] = $spec_cfg->automatic_tree_refresh?"yes":"no";
		        // $viewerCfg['disable_edit'] = !$args->allow_edit;
            $viewerCfg['disable_edit'] = 0;

  	        if(isset($_SESSION['tcspec_refresh_on_action']))
  	        {
		            $viewerCfg['refresh_tree']=$_SESSION['tcspec_refresh_on_action'];
	          }
        break;
    }

    return $args;
}


?>


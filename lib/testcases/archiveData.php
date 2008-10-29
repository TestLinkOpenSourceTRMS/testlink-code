<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @version $Id: archiveData.php,v 1.39 2008/10/29 12:26:56 havlat Exp $
 * @author Martin Havlat
 *
 * Allows you to show test suites, test cases.
 * Normally launched from tree navigator.
 *
 * rev :
 *      20080425 - franciscom - refactoring
 *      20080120 - franciscom - show() method for test cases - interface changes
 *      20070930 - franciscom - REQ - BUGID 1078
 *
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('testsuite.class.php');
testlinkInitPage($db);

$template_dir = 'testcases/';
$viewerArgs = null;
$args = init_args($viewerArgs);

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
		$item_mgr->show($smarty,$template_dir,$args->id);
		break;

	case 'testcase':
		$item_mgr = new testcase($db);

    	// has been called from a test case search
		if(!is_null($args->targetTestCase))
		{
			$viewerArgs['display_testproject'] = 1;
			$viewerArgs['display_parent_testsuite'] = 1;

			// need to get internal Id from External ID
			$cfg = config_get('testcase_cfg');
			$args->id=$item_mgr->getInternalID($args->targetTestCase,$cfg->glue_character);
		}

    	// need to be managed in a different way that for testproject and testsuites
		$attachments[$args->id] = getAttachmentInfosFrom($item_mgr,$args->id);;
		$smarty->assign('id',$args->id);
		$smarty->assign('attachments',$attachments);
		$item_mgr->show($smarty,$template_dir,$args->id,testcase::ALL_VERSIONS,$viewerArgs);
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
    $args->allow_edit = isset($_REQUEST['allow_edit']) ? intval($_REQUEST['allow_edit']) : 1;

    switch($args->feature)
    {
        case 'testsuite':
            $_SESSION['tcspec_refresh_on_action'] = isset($_REQUEST['tcspec_refresh_on_action'])? "yes":"no";
        break;

        case 'testcase':
		        $spec_cfg = config_get('spec_cfg');
		        $viewerCfg = array('action' => '', 'msg_result' => '','user_feedback' => '');
		        $viewerCfg['refresh_tree'] = $spec_cfg->automatic_tree_refresh?"yes":"no";
		        $viewerCfg['disable_edit'] = !$args->allow_edit;

  	        if(isset($_SESSION['tcspec_refresh_on_action']))
  	        {
		            $viewerCfg['refresh_tree']=$_SESSION['tcspec_refresh_on_action'];
	          }
        break;
    }

    return $args;
}


?>


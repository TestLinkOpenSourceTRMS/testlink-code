<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @version $Id: archiveData.php,v 1.28 2007/12/03 20:42:27 schlundus Exp $
 * @author Martin Havlat
 *  
 * Allows you to show test suites, test cases.
 * Normally launched from tree navigator.
 *
 * rev :
 *      20070930 - franciscom - REQ - BUGID 1078
 * 
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once("../functions/attachments.inc.php");
testlinkInitPage($db);

$template_dir='testcases/';

$user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
$feature = isset($_GET['edit']) ? $_GET['edit'] : null;
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$allow_edit = isset($_GET['allow_edit']) ? intval($_GET['allow_edit']) : 1;

// load data and show template
$smarty = new TLSmarty();
$smarty->assign('page_title',lang_get('container_title_' . $feature));
switch($feature)
{
	case 'testproject':
		$item_mgr = new testproject($db);
		$smarty->assign('id',$id);
		$attachments = getAttachmentInfosFrom($item_mgr,$id);
		$smarty->assign('attachmentInfos',$attachments);
		
    	$item_mgr->show($smarty,$template_dir,$id);
		break;

	case 'testsuite':
		$smarty->assign('id',$id);
		$item_mgr = new testsuite($db);
		$attachments = getAttachmentInfosFrom($item_mgr,$id);
		$smarty->assign('attachmentInfos',$attachments);
		
	    $_SESSION['tcspec_refresh_on_action'] = isset($_REQUEST['tcspec_refresh_on_action'])? "yes":"no";
		$item_mgr->show($smarty,$template_dir,$id);
		break;

	case 'testcase':
		$smarty->assign('id',$id);
		$item_mgr = new testcase($db);
		$attachments = getAttachmentInfosFrom($item_mgr,$id);
		$attachmentsTpl[$id] = $attachments;
		
		$smarty->assign('attachments',$attachmentsTpl);
				
		$no_msg = '';
		$no_action = '';
		$no_user_feedback = '';

		$spec_cfg = config_get('spec_cfg');
		$do_refresh_yes_no=$spec_cfg->automatic_tree_refresh?"yes":"no";
		if(isset($_SESSION['tcspec_refresh_on_action']))
			$do_refresh_yes_no=$_SESSION['tcspec_refresh_on_action'];
    	
	    // 20070930 - franciscom - REQ - BUGID 1078
    	// added two arguments on call.
		$item_mgr->show($smarty,$template_dir,$id,$user_id,TC_ALL_VERSIONS,
		                $no_action,$no_msg,$do_refresh_yes_no,$no_user_feedback,!$allow_edit);
		break;

	default:
		tLog('$_GET["edit"] has invalid value: ' . $feature , 'ERROR');
		trigger_error($_SESSION['user'].'> $_GET["edit"] has invalid value.', E_USER_ERROR);
}
?>

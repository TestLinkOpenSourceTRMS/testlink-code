<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @version $Id: archiveData.php,v 1.24 2007/02/20 18:48:50 franciscom Exp $
 * @author Martin Havlat
 *  
 * This page allows you to show data (test cases, categories, and
 * components. This is refered by tree.
 * 
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once("../functions/attachments.inc.php");
testlinkInitPage($db);


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
		$attachments = getAttachmentInfos($db,$id,'nodes_hierarchy');
		$smarty->assign('attachmentInfos',$attachments);
		$smarty->assign('id',$id);
		$item_mgr = new testproject($db);
    $item_mgr->show($smarty,$id);
		break;

	case 'testsuite':

    // 20070220 - franciscom - a question for myself. Do I really need to do this ???
    //
    $_SESSION['tcspec_refresh_on_action']=isset($_REQUEST['tcspec_refresh_on_action'])? "yes":"no";

		$attachments = getAttachmentInfos($db,$id,'nodes_hierarchy');
		$smarty->assign('attachmentInfos',$attachments);
		$smarty->assign('id',$id);
		$item_mgr = new testsuite($db);
		$item_mgr->show($smarty,$id);
		break;

	case 'testcase':
		$attachments[$id] = getAttachmentInfos($db,$id,'nodes_hierarchy');
		$smarty->assign('attachments',$attachments);
		$smarty->assign('id',$id);
		$item_mgr = new testcase($db);
		
		// 20070220 - franciscom - automatic tree refresh logic
		$no_msg='';
		$no_action='';
		$spec_cfg=config_get('spec_cfg');
		$do_refresh_yes_no=$spec_cfg->automatic_tree_refresh?"yes":"no";
		if( isset($_SESSION['tcspec_refresh_on_action']) )
    {
        $do_refresh_yes_no=$_SESSION['tcspec_refresh_on_action'];
    }
		$item_mgr->show($smarty,$id,$user_id,TC_ALL_VERSIONS,$no_action,$no_msg,$do_refresh_yes_no);
		break;

	default:
		tLog('$_GET["edit"] has invalid value: ' . $feature , 'ERROR');
		trigger_error($_SESSION['user'].'> $_GET["edit"] has invalid value.', E_USER_ERROR);
}
?>

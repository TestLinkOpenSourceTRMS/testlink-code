<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: bug_add.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2007/12/02 17:11:48 $ by $Author: franciscom $
 *
 *
 * rev :
 *      20070304 - franciscom - added new check, if bug id does not exist
 *                              on Bug Tracking System (BTS), we will not
 *                              add on TestLink.
 * 
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('exec.inc.php');
testlinkInitPage($db);

$template_dir='execute/';

$exec_id = isset($_REQUEST['exec_id'])? intval($_REQUEST['exec_id']) : 0;
$bug_id = isset($_REQUEST['bug_id'])? trim($_REQUEST['bug_id']) : null;
$msg = "";

if(!is_null($bug_id) && strlen($bug_id) > 0)
{
	$msg = lang_get("error_wrong_BugID_format");
	if ($g_bugInterface->checkBugID($bug_id))
	{
	  $msg = lang_get("error_bug_does_not_exist_on_bts");
    if ($g_bugInterface->checkBugID_existence($bug_id))
    { 	  
		  write_execution_bug($db,$exec_id, $bug_id);
		  $msg = lang_get("bug_added");
    }
	}
}

$smarty = new TLSmarty();
$smarty->assign('bts_url',$g_bugInterface->getEnterBugURL());
$smarty->assign('exec_id',$exec_id);
$smarty->assign('msg',$msg);
$smarty->display($template_dir . 'bug_add.tpl');
?>
<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: bug_add.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2006/10/05 19:18:21 $ by $Author: schlundus $
 *
 * 
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('exec.inc.php');
testlinkInitPage($db);

$exec_id = isset($_REQUEST['exec_id'])? intval($_REQUEST['exec_id']) : 0;
$bug_id = isset($_REQUEST['bug_id'])? trim($_REQUEST['bug_id']) : null;
$msg = "";

if(!is_null($bug_id) && strlen($bug_id) > 0)
{
	if ($g_bugInterface->checkBugID($bug_id))
	{
		write_execution_bug($db,$exec_id, $bug_id);
		$msg = lang_get("bug_added");
	}
	else
	{
		$msg = lang_get("error_wrong_BugID_format");
	}
}

$smarty = new TLSmarty();
$smarty->assign('bts_url',$g_bugInterface->getEnterBugURL());
$smarty->assign('exec_id',$exec_id);
$smarty->assign('msg',$msg);
$smarty->display('bug_add.tpl');
?>
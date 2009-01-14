<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: bugAdd.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2009/01/14 20:06:24 $ by $Author: schlundus $
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
testlinkInitPage($db);

//@TODO: schlundus, refactor!
$template_dir = 'execute/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$exec_id = isset($_REQUEST['exec_id'])? intval($_REQUEST['exec_id']) : 0;
$bug_id = isset($_REQUEST['bug_id'])? trim($_REQUEST['bug_id']) : null;
$msg = "";

if(!is_null($bug_id) && strlen($bug_id))
{
	$msg = lang_get("error_wrong_BugID_format");
	if ($g_bugInterface->checkBugID($bug_id))
	{
		$msg = lang_get("error_bug_does_not_exist_on_bts");
		if ($g_bugInterface->checkBugID_existence($bug_id))
		{ 	  
			if (write_execution_bug($db,$exec_id, $bug_id))
			{
				$msg = lang_get("bug_added");
				logAuditEvent(TLS("audit_executionbug_added",$bug_id),"CREATE",$exec_id,"executions");
			}
		}
	}
}

$smarty = new TLSmarty();
$smarty->assign('bts_url',$g_bugInterface->getEnterBugURL());
$smarty->assign('exec_id',$exec_id);
$smarty->assign('msg',$msg);
$smarty->display($template_dir . $default_template);
?>
<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: bugDelete.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2009/02/07 19:44:03 $ by $Author: schlundus $
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('exec.inc.php');
testlinkInitPage($db,false,false,"checkRights");

//@TODO: schlundus, refactor!
$template_dir = 'execute/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$exec_id = isset($_REQUEST['exec_id'])? intval($_REQUEST['exec_id']) : 0;
$bug_id = isset($_REQUEST['bug_id'])? trim($_REQUEST['bug_id']) : null;
$msg = "";
if ($exec_id > 0 && !is_null($bug_id) && strlen($bug_id))
{
	if (write_execution_bug($db,$exec_id, $bug_id,true))
	{
		$msg = lang_get('bugdeleting_was_ok');
		logAuditEvent(TLS("audit_executionbug_deleted",$bug_id),"DELETE",$exec_id,"executions");
	}
}

$smarty = new TLSmarty();
$smarty->assign('msg',$msg);
$smarty->display($template_dir . $default_template);

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,"testplan_execute");
}
?>
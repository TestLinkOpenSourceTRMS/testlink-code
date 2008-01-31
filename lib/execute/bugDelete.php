<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: bugDelete.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2008/01/31 22:15:47 $ by $Author: schlundus $
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('exec.inc.php');
testlinkInitPage($db);

$template_dir = 'execute/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$exec_id = isset($_REQUEST['exec_id'])? intval($_REQUEST['exec_id']) : 0;
$bug_id = isset($_REQUEST['bug_id'])? trim($_REQUEST['bug_id']) : null;
$msg = "";
if ($exec_id > 0 && !is_null($bug_id) && strlen($bug_id) > 0)
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
?>
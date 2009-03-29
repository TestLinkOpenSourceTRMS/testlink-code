<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: bugDelete.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2009/03/29 14:10:01 $ by $Author: franciscom $
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('exec.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

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
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function checkRights(&$db,&$user)
{
	return $user->hasRight($db,"testplan_execute");
}
?>
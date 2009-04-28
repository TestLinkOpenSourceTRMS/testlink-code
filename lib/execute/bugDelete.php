<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: bugDelete.php,v $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2009/04/28 19:22:33 $ by $Author: schlundus $
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('exec.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$args = init_args();

$msg = "";
if ($args->exec_id && $args->bug_id != "")
{
	if (write_execution_bug($db,$args->exec_id, $args->bug_id,true))
	{
		$msg = lang_get('bugdeleting_was_ok');
		logAuditEvent(TLS("audit_executionbug_deleted",$args->bug_id),"DELETE",$args->exec_id,"executions");
	}
}

$smarty = new TLSmarty();
$smarty->assign('msg',$msg);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

function init_args()
{
	global $g_bugInterface;
	
	$iParams = array(
		"exec_id" => array("GET",tlInputParameter::INT_N),
		"bug_id" => array("GET",tlInputParameter::STRING_N,0,$g_bugInterface->getBugIDMaxLength()),
	);
	$pParams = I_PARAMS($iParams);
	
	$args = new stdClass();
	$args->bug_id = $pParams["bug_id"];
	$args->exec_id = $pParams["exec_id"];
	
	return $args;
}


function checkRights(&$db,&$user)
{
	return $user->hasRight($db,"testplan_execute");
}
?>
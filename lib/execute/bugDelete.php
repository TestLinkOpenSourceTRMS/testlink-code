<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: bugDelete.php,v $
 *
 * @version $Revision: 1.14 $
 * @modified $Date: 2011/01/10 15:38:55 $ by $Author: asimon83 $
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
if (config_get('interface_bugs') != 'NO')
{
  require_once(TL_ABS_PATH. 'lib' . DIRECTORY_SEPARATOR . 'bugtracking' . 
               DIRECTORY_SEPARATOR . 'int_bugtracking.php');
}
require_once('exec.inc.php');

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$args = init_args();
checkRights($db,$_SESSION['currentUser'],$args);


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

/**
 * 
 * @return object returns the arguments of the page
 */
function init_args()
{
	global $g_bugInterface;

	$iParams = array(
		"exec_id" => array("GET",tlInputParameter::INT_N),
		"bug_id" => array("GET",tlInputParameter::STRING_N,0,$g_bugInterface->getBugIDMaxLength()),
	);
	$args = new stdClass();
	
	$pParams = I_PARAMS($iParams,$args);
	
	// BUGID 4066 - take care of proper escaping when magic_quotes_gpc is enabled
	$_REQUEST=strings_stripSlashes($_REQUEST);

	return $args;
}


/**
 * Checks the user rights for using the page
 * 
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	if( config_get('bugInterfaceOn') )
	{
		$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
		$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
		checkSecurityClearance($db,$userObj,$env,array('testplan_execute'),'and');
	}
	else
	{
	  	redirect($_SESSION['basehref'],"top.location");
		exit();
	}
}
?>
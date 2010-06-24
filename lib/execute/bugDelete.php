<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: bugDelete.php,v $
 *
 * @version $Revision: 1.12 $
 * @modified $Date: 2010/06/24 17:25:57 $ by $Author: asimon83 $
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
if (config_get('interface_bugs') != 'NO')
{
  require_once(TL_ABS_PATH. 'lib' . DIRECTORY_SEPARATOR . 'bugtracking' . 
               DIRECTORY_SEPARATOR . 'int_bugtracking.php');
}
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
	
	return $args;
}


/**
 * Checks the user rights for viewing the page
 * 
 * @param $db resource the database connection handle
 * @param $user tlUser the object of the current user
 *
 * @return boolean return true if the page can be viewed, false if not
 */
function checkRights(&$db,&$user)
{
    $hasRights = false;	
	if( config_get('bugInterfaceOn') )
	{
		$hasRights = $user->hasRight($db,"testplan_execute");
	}
	return $hasRights;

}
?>
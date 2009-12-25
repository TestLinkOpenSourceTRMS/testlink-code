<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: bugAdd.php,v $
 *
 * @version $Revision: 1.9 $
 * @modified $Date: 2009/12/25 18:29:54 $ by $Author: franciscom $
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once('exec.inc.php');

if (config_get('interface_bugs') != 'NO')
{
  require_once(TL_ABS_PATH. 'lib' . DIRECTORY_SEPARATOR . 'bugtracking' . 
               DIRECTORY_SEPARATOR . 'int_bugtracking.php');
}

testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();
$msg = "";

if($args->bug_id != "")
{
	$msg = lang_get("error_wrong_BugID_format");
	if ($g_bugInterface->checkBugID($args->bug_id))
	{
		$msg = lang_get("error_bug_does_not_exist_on_bts");
		if ($g_bugInterface->checkBugID_existence($args->bug_id))
		{ 	  
			if (write_execution_bug($db,$args->exec_id, $args->bug_id))
			{
				$msg = lang_get("bug_added");
				logAuditEvent(TLS("audit_executionbug_added",$args->bug_id),"CREATE",$args->exec_id,"executions");
			}
		}
	}
}

$smarty = new TLSmarty();
$smarty->assign('bugIDMaxLength',$g_bugInterface->getBugIDMaxLength());
$smarty->assign('bts_url', $g_bugInterface->getEnterBugURL());
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
		"bug_id" => array("POST",tlInputParameter::STRING_N,0,$g_bugInterface->getBugIDMaxLength()),
	);
	$args = new stdClass();
	
	$pParams = I_PARAMS($iParams,$args);
	
	if ($args->exec_id)
		$_SESSION['bugAdd_execID'] = $args->exec_id;
	else
		$args->exec_id = isset($_SESSION['bugAdd_execID']) ? $_SESSION['bugAdd_execID'] : 0;
		
	
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
	global $g_bugInterfaceOn;
	
	if (!$g_bugInterfaceOn)
		return false;
		
	return $user->hasRight($db,"testplan_execute");
}
?>
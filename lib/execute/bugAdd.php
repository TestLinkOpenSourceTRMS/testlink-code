<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: bugAdd.php,v $
 *
 * @version $Revision: 1.14 $
 * @modified $Date: 2010/09/17 10:47:54 $ by $Author: amkhullar $
 * 
 * rev:
 *	20100917 - amitkhullar - missing $gui param to smarty
 */
require_once('../../config.inc.php');
require_once('common.php');

$gui = new stdClass();
$gui->interface_bugs = config_get('interface_bugs');

if( $gui->interface_bugs != 'NO' )
{
  require_once(TL_ABS_PATH. 'lib' . DIRECTORY_SEPARATOR . 'bugtracking' . 
               DIRECTORY_SEPARATOR . 'int_bugtracking.php');
}
require_once('exec.inc.php');

testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args($g_bugInterface);
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
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * 
 * @return object returns the arguments of the page
 */
function init_args($bugInterface)
{
	$iParams = array("exec_id" => array("GET",tlInputParameter::INT_N),
		             "bug_id" => array("POST",tlInputParameter::STRING_N,0,$bugInterface->getBugIDMaxLength()));
	$args = new stdClass();
	I_PARAMS($iParams,$args);
	
	if ($args->exec_id)
	{
		$_SESSION['bugAdd_execID'] = $args->exec_id;
	}
	else
	{
		$args->exec_id = isset($_SESSION['bugAdd_execID']) ? $_SESSION['bugAdd_execID'] : 0;
	}	
	
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
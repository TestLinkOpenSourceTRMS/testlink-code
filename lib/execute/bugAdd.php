<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	bugAdd.php
 * @internal revisions
 * @since 1.9.4
 * 20120219 - franciscom - TICKET 4904: integrate with ITS on test project basis
 */
require_once('../../config.inc.php');
require_once('common.php');


require_once('exec.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();


$args = init_args();

$gui = new stdClass();
$gui->bugIDMaxLength = 0;
$gui->createIssueURL = null;
$gui->issueTrackerVerboseID = '';
$gui->issueTrackerVerboseType = '';
$gui->tproject_id = $args->tproject_id;

$its = null;
$tprojectMgr = new testproject($db);
$info = $tprojectMgr->get_by_id($args->tproject_id);
if($info['issue_tracker_enabled'])
{
	$it_mgr = new tlIssueTracker($db);
	$issueT = $it_mgr->getLinkedTo($args->tproject_id);
	if( !is_null($issueT) )
	{
		$its = $it_mgr->getInterfaceObject($args->tproject_id);
		$gui->issueTrackerVerboseType = $issueT['verboseType'];
		$gui->issueTrackerVerboseID = $issueT['issuetracker_name'];
		$gui->bugIDMaxLength = $its->getBugIDMaxLength();
		$gui->createIssueURL = $its->getEnterBugURL();
	}
}	


$msg = "";
if(!is_null($issueT) && $args->bug_id != "")
{
	// 20120317
	$l18n = init_labels(array("error_wrong_BugID_format" => null,"error_bug_does_not_exist_on_bts" => null));
	$msg = $l18n["error_wrong_BugID_format"];
	if ($its->checkBugIDSyntax($args->bug_id))
	{
		$msg = $l18n["error_bug_does_not_exist_on_bts"];
		if ($its->checkBugIDExistence($args->bug_id))
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
$smarty->assign('msg',$msg);
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * 
 * @return object returns the arguments of the page
 */
function init_args()
{
	/*
	$iParams = array("exec_id" => array("GET",tlInputParameter::INT_N),
		             "bug_id" => array("POST",tlInputParameter::STRING_N,0,$bugInterface->getBugIDMaxLength()),
		             "tproject_id" => array("GET",tlInputParameter::INT_N));
	*/	             
	$iParams = array("exec_id" => array("GET",tlInputParameter::INT_N),
		             "bug_id" => array("POST",tlInputParameter::STRING_N),
		             "tproject_id" => array("REQUEST",tlInputParameter::INT_N));
		             
		             
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
	/*
	$hasRights = false;	
	if( config_get('bugInterfaceOn') )
	{
		$hasRights = $user->hasRight($db,"testplan_execute");
	}
	*/
	$hasRights = $user->hasRight($db,"testplan_execute");
	
	return $hasRights;
}
?>
<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  bugAdd.php
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

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$args = init_args($g_bugInterface);
checkRights($db,$_SESSION['currentUser'],$args);

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
	
	// BUGID 4066 - take care of proper escaping when magic_quotes_gpc is enabled
	$_REQUEST=strings_stripSlashes($_REQUEST);

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


function initEnv(&$dbHandler)
{
  $args = init_args();
  $gui = new stdClass();
  $gui->bugIDMaxLength = 0;
  $gui->createIssueURL = null;
  $gui->issueTrackerVerboseID = '';
  $gui->issueTrackerVerboseType = '';
  $gui->tproject_id = $args->tproject_id;

  // get issue tracker config and object to manage TestLink - BTS integration 
  $its = null;
  $tprojectMgr = new testproject($dbHandler);
  list($issueTrackerEnabled,$its) = $tprojectMgr->getIssueTrackerMgr($args->tproject_id);
  if($issueTrackerEnabled)
  {
  	if( !is_null($its) )
	  {
  		$gui->issueTrackerVerboseType = $issueT['verboseType'];
  		$gui->issueTrackerVerboseID = $issueT['issuetracker_name'];
  		$gui->bugIDMaxLength = $its->getBugIDMaxLength();
  		$gui->createIssueURL = $its->getEnterBugURL();
  	}
  }	

  return array($args,$gui);
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
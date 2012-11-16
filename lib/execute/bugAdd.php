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
$gui->automaticMode = false;

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

    if( method_exists($its,'addIssue') ) 
    {
      $gui->automaticMode = addIssue($db,$args,$its);
    }
	}
}	

if(!$gui->automaticMode)
{
  $gui->msg = "";
  if(!is_null($issueT) && $args->bug_id != "")
  {
  	$l18n = init_labels(array("error_wrong_BugID_format" => null,"error_bug_does_not_exist_on_bts" => null));
  	$gui->msg = $l18n["error_wrong_BugID_format"];
  	if ($its->checkBugIDSyntax($args->bug_id))
  	{
  		$gui->msg = $l18n["error_bug_does_not_exist_on_bts"];
  		if ($its->checkBugIDExistence($args->bug_id))
  		{ 	  
  			if (write_execution_bug($db,$args->exec_id, $args->bug_id))
  			{
  				$gui->msg = lang_get("bug_added");
  				logAuditEvent(TLS("audit_executionbug_added",$args->bug_id),"CREATE",$args->exec_id,"executions");
  			}
  		}
  	}
  }
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * 
 * @return object returns the arguments of the page
 */
function init_args()
{
	$iParams = array("exec_id" => array("GET",tlInputParameter::INT_N),
		               "bug_id" => array("POST",tlInputParameter::STRING_N),
		               "tproject_id" => array("REQUEST",tlInputParameter::INT_N),
		               "tcversion_id" => array("REQUEST",tlInputParameter::INT_N));
		             
		             
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

function addIssue($dbHandler,$argsObj,$itsObj)
{
  $automaticMode = false;             

	$resultsCfg = config_get('results');                      
	// $resultsCfg['status_code']['not_run'];  

  $tcaseMgr = new testcase($dbHandler);
  $dummy = $tcaseMgr->tree_manager->get_node_hierarchy_info($argsObj->tcversion_id);
  $auditSign = $tcaseMgr->getAuditSignature((object)array('id' => $dummy['parent_id'])); 
  $exec = current($tcaseMgr->getExecution($argsObj->exec_id,$argsObj->tcversion_id));
  $dummy = $exec['status'];
  if( isset($resultsCfg['code_status'][$exec['status']]) )
  {
    $dummy = $resultsCfg['code_status'][$exec['status']];  
  }                         
  $exec['statusVerbose'] = sprintf(lang_get('issue_exec_result'),$dummy);
  
  unset($tcaseMgr);
  $signature = sprintf(lang_get('issue_generated_description'),
                       $argsObj->exec_id,$exec['tester_login'],$exec['testplan_name']);
  
  if($exec['platform_id'] > 0)
  {
    $signature .= sprintf(lang_get('issue_platform') ,$exec['platform_name']);
  }
  $signature .= sprintf(lang_get('issue_build') . lang_get('execution_ts_iso'),
                        $exec['build_name'],$exec['execution_ts']) . "\n" .
                        $exec['statusVerbose'] . "\n\n" . $exec['execution_notes'];
  
  $rs = $itsObj->addIssue($auditSign . ' - ' . sprintf(lang_get('execution_ts_iso'),$exec['execution_ts']),$signature);  
  
  if($rs['id'] > 0)
  {
    $automaticMode = true;
  	if (write_execution_bug($dbHandler,$argsObj->exec_id, $rs['id']))
  	{
  		$msg = lang_get("bug_added");
  		logAuditEvent(TLS("audit_executionbug_added",$rs['id']),"CREATE",$argsObj->exec_id,"executions");
  	}
  }
  return $automaticMode;
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
	$hasRights = $user->hasRight($db,"testplan_execute");
	return $hasRights;
}
?>
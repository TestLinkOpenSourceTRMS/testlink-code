<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	bugAdd.php
 * @internal revisions
 * @since 1.9.5
 * 
 */
require_once('../../config.inc.php');
require_once('common.php');

require_once('exec.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();
$gui = initializeGui($args);
list($its,$issueT) = itsProcess($db,$args,$gui);

if(!$gui->tlCanCreateIssue || $args->user_action == 'link')
{
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
		               "tcversion_id" => array("REQUEST",tlInputParameter::INT_N),
		               "user_action" => array("REQUEST",tlInputParameter::STRING_N));
		             
		             
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
  $opOK = false;             
  $msg = '';
	$resultsCfg = config_get('results');                      
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
  if($rs['status_ok'])
  {                   
    $msg = $rs['msg'];
    $opOK = true;
  	if (write_execution_bug($dbHandler,$argsObj->exec_id, $rs['id']))
  	{
  		logAuditEvent(TLS("audit_executionbug_added",$rs['id']),"CREATE",$argsObj->exec_id,"executions");
  	}
  }
  else
  {
    $msg = $rs['msg'];
  }
  return array($opOK,$msg);
}

function initializeGui($argsObj)
{
  $gui = new stdClass();
  $gui->bugIDMaxLength = 0;
  $gui->createIssueURL = null;
  $gui->issueTrackerVerboseID = '';
  $gui->issueTrackerVerboseType = '';
  $gui->tproject_id = $argsObj->tproject_id;
  $gui->tlCanCreateIssue = false;
  $gui->user_action = $argsObj->user_action;
  return $gui;
}

function itsProcess(&$dbHandler,$argsObj,&$guiObj)
{
  $its = null;
  $tprojectMgr = new testproject($dbHandler);
  $info = $tprojectMgr->get_by_id($argsObj->tproject_id);
  if($info['issue_tracker_enabled'])
  {
  	$it_mgr = new tlIssueTracker($dbHandler);
  	$issueT = $it_mgr->getLinkedTo($argsObj->tproject_id);
  	if( !is_null($issueT) )
  	{
  		$its = $it_mgr->getInterfaceObject($argsObj->tproject_id);
  		$guiObj->issueTrackerVerboseType = $issueT['verboseType'];
  		$guiObj->issueTrackerVerboseID = $issueT['issuetracker_name'];
  		$guiObj->bugIDMaxLength = $its->getBugIDMaxLength();
  		$guiObj->createIssueURL = $its->getEnterBugURL();
  
      if( method_exists($its,'addIssue') && ($argsObj->user_action == 'create') )
      {
        list($guiObj->tlCanCreateIssue,$guiObj->msg) = addIssue($dbHandler,$argsObj,$its);
      }
  	}
  }	              
  return array($its,$issueT); 
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
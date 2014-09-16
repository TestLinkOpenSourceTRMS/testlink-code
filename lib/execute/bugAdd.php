<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	bugAdd.php
 * @internal revisions
 * @since 1.9.12
 * 
 */
require_once('../../config.inc.php');
require_once('common.php');

require_once('exec.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args($db);
$gui = initializeGui($args);

list($its,$issueT) = itsProcess($db,$args,$gui);

if(!$gui->tlCanCreateIssue || $args->user_action == 'link' || $args->user_action == 'add_note' )
{
  // Well do not think is very elegant to check for $args->bug_id != ""
  // to understand if user has pressed ADD Button
  if(!is_null($issueT) && $args->bug_id != "")
  {
  	$l18n = init_labels(array("error_wrong_BugID_format" => null,"error_bug_does_not_exist_on_bts" => null));

    switch($args->user_action)
    {
      case 'link':
        $gui->msg = $l18n["error_wrong_BugID_format"];
        if ($its->checkBugIDSyntax($args->bug_id))
        {
          
          if ($its->checkBugIDExistence($args->bug_id))
          {     
            if (write_execution_bug($db,$args->exec_id, $args->bug_id))
            {
              $gui->msg = lang_get("bug_added");
              logAuditEvent(TLS("audit_executionbug_added",$args->bug_id),"CREATE",$args->exec_id,"executions");

              // blank notes will not be added :).
              if($gui->tlCanAddIssueNote && (strlen($gui->bug_notes) > 0) )
              {
                // will do call to update issue Notes
                $its->addNote($args->bug_id,$gui->bug_notes);
              }  
            }
          }
          else
          {
            $gui->msg = sprintf($l18n["error_bug_does_not_exist_on_bts"],$gui->bug_id);
          }  
        }
      break;
      
      case 'add_note':
        // blank notes will not be added :).
        $gui->msg = '';
        if($gui->tlCanAddIssueNote && (strlen($gui->bug_notes) > 0) )
        {
          $its->addNote($args->bug_id,$gui->bug_notes);
        }  
      break;
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
function init_args(&$dbHandler)
{
  // sorry for magic numbers
  // link => 4
  // create => 6
  // add_note => 8
	$iParams = array("exec_id" => array("GET",tlInputParameter::INT_N),
		               "bug_id" => array("REQUEST",tlInputParameter::STRING_N),
		               "tproject_id" => array("REQUEST",tlInputParameter::INT_N),
		               "tcversion_id" => array("REQUEST",tlInputParameter::INT_N),
                   "bug_notes" => array("POST",tlInputParameter::STRING_N),
		               "user_action" => array("REQUEST",tlInputParameter::STRING_N,4,8));
		             
		             
	$args = new stdClass();
	I_PARAMS($iParams,$args);
	if ($args->exec_id)
	{
		$_SESSION['bugAdd_execID'] = intval($args->exec_id);
	}
	else
	{
		$args->exec_id = intval(isset($_SESSION['bugAdd_execID']) ? $_SESSION['bugAdd_execID'] : 0);
	}	

  $args->bug_id = trim($args->bug_id);
  if( $args->bug_id == '' && $args->exec_id > 0)
  {
    $map = get_execution($dbHandler,$args->exec_id);
    $args->bug_notes = $map[0]['notes'];    
  }  
  $args->bug_notes = trim($args->bug_notes);

  $args->user = $_SESSION['currentUser'];

  return $args;
}


/**
 *
 */
function initializeGui($argsObj)
{
  $gui = new stdClass();
  $gui->tlCanCreateIssue = false; 
  $gui->tlCanAddIssueNote = true;

  $gui->bugIDMaxLength = 0;
  $gui->createIssueURL = null;
  $gui->issueTrackerVerboseID = '';
  $gui->issueTrackerVerboseType = '';
  $gui->msg = '';

  $gui->tproject_id = $argsObj->tproject_id;
  $gui->user_action = $argsObj->user_action;
  $gui->bug_notes = $argsObj->bug_notes;
  $gui->bug_id = $argsObj->bug_id;

  switch($argsObj->user_action)
  {
    case 'create':
      $gui->pageTitle = lang_get('create_issue');
    break;

    case 'add_note':
      $gui->pageTitle = lang_get('add_issue_note');
    break;

    case 'link':
    default:
      $gui->pageTitle = lang_get('title_bug_add');
    break;
  }

  return $gui;
}

/**
 *
 */
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
      $guiObj->tlCanAddIssueNote = method_exists($its,'addNote');
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
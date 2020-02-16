<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	scriptAdd.php
 * @internal revisions
 * @since 1.9.15
 * 
 */
require_once('../../config.inc.php');
require_once('../functions/common.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
list($args,$gui,$cts,$codeT) = initEnv($db);

$gui->codeTrackerMetaData = array('projects' => null, 'repos' => null, 'files' => null,
                                  'branches' => null, 'commits' => null);
if(!is_null($args->projectKey))
{
  $gui->codeTrackerMetaData['projects'] = $args->projectKey;
}
else
{
  $gui->codeTrackerMetaData['projects'] = $cts->getProjectsForHTMLSelect();
}

if(is_null($gui->project_key))
{
  $gui->project_key = isset($_SESSION['testscript_projectKey']) ? $_SESSION['testscript_projectKey'] : $cts->cfg->projectkey;
}
if(is_null($gui->repository_name) && isset($_SESSION['testscript_repositoryName']) && $args->user_action == 'link')
{
  $gui->repository_name = $_SESSION['testscript_repositoryName'];
}

if(!is_null($args->repositoryName) && $args->user_action != 'projectSelected')
{
  $gui->codeTrackerMetaData['repos'] = $args->repositoryName;
}
else
{
  $gui->codeTrackerMetaData['repos'] = $cts->getReposForHTMLSelect($gui->project_key);
}

if(!is_null($args->branchName) && $args->user_action != 'projectSelected' && $args->user_action != 'repoSelected')
{
  $gui->codeTrackerMetaData['branches'] = $args->branchName;
}
else
{
  $gui->codeTrackerMetaData['branches'] = $cts->getBranchesForHTMLSelect($gui->project_key, $gui->repository_name);
}

if(!is_null($args->commits) && $args->user_action != 'projectSelected' && $args->user_action != 'repoSelected' &&
    $args->user_action != 'branchSelected')
{
  $gui->codeTrackerMetaData['commits'] = $args->commits;
}
else if(($args->user_action == 'branchSelected') ||
    (!is_null($gui->branch_name) && ($args->user_action == 'expand' || $args->user_action == 'collapse')))
{
  $gui->codeTrackerMetaData['commits'] = $cts->getCommitsForHTMLSelect($gui->project_key, $gui->repository_name,
                                         $gui->branch_name);
}

if(!is_null($args->files) && ($args->user_action == 'expand' || $args->user_action == 'collapse'))
{
  $gui->codeTrackerMetaData['files'] = $args->files;
}
else if($args->user_action == 'repoSelected' || $args->user_action == 'branchSelected' ||
$args->user_action == 'expand' || $args->user_action == 'collapse' || ($args->user_action == 'link' && !is_null($gui->repository_name)))
{
  $gui->codeTrackerMetaData['files'] = $cts->getRepoContentForHTMLSelect($gui->project_key, $gui->repository_name, '', $gui->branch_name, $gui->commit_id);
}
if($args->user_action == 'expand')
{
  $expandArray = explode("/", $args->expand_item);
  $tmpFileArray = &$gui->codeTrackerMetaData['files'];
  $tmpPath = "";
  foreach($expandArray as $item)
  {
    $tmpPath .= $item . "/";
    $tmpFileArray[$item][0] = $cts->getRepoContentForHTMLSelect($gui->project_key, $gui->repository_name, $tmpPath, $gui->branch_name, $gui->commit_id);
    $tmpFileArray = &$tmpFileArray[$item][0];
  }
}
else if($args->user_action == 'collapse')
{
  if(substr($args->collapse_item,-1) == "/")
  {
    $args->collapse_item = substr($args->collapse_item,0,-1);
  }
  $collapseArray = explode("/", $args->collapse_item);
  array_pop($collapseArray);

  $tmpFileArray = &$gui->codeTrackerMetaData['files'];
  $tmpPath = "";
  foreach($collapseArray as $item)
  {
    $tmpPath .= $item . "/";
    $tmpFileArray[$item][0] = $cts->getRepoContentForHTMLSelect($gui->project_key, $gui->repository_name, $tmpPath, $gui->branch_name, $gui->commit_id);
    $tmpFileArray = &$tmpFileArray[$item][0];
  }
}

if($args->user_action == 'create')
{
  if(!is_null($codeT) && $args->project_key != "" && $args->repository_name != "" && $args->code_path != "")
  {
    $l18n = init_labels(array("error_code_does_not_exist_on_cts" => null));

    $gui->msg = "";

    $baseURL = $cts->buildViewCodeURL($args->project_key, $args->repository_name, '');
    /* if code_path was entered manually it may contain the complete URL
     * in this case we have to strip the base URL from the string
    */
    if (substr($args->code_path, 0, strlen($baseURL)) === $baseURL)
    {
      $args->code_path = substr($args->code_path, strlen($baseURL));
    }
    // if code_path contains reference to certain branch, delete it
    $refPos = strpos($args->code_path, '?at=');
    if ($refPos !== false)
    {
      // in case no branch was seleted try to extract branch name from reference if possible
      $refStr = substr($args->code_path, $refPos);
      if (strpos($refStr, '?at=refs%2Fheads%2F') !== false)
      {
        $refStr = str_replace("?at=refs%2Fheads%2F", "", $refStr);
        if (is_null($args->branch_name) && (isset($args->branchName[$refStr]) || array_key_exists($refStr, $args->branchName)))
        {
          $args->branch_name = $refStr;
        }
      }
      else //no branch given, but commit_id -> try to extract this
      {
        $refStr = str_replace("?at=", "", $refStr);
        if (is_null($args->commit_id))
        {
          $args->commit_id = $refStr;
        }
      }

      $args->code_path = substr($args->code_path, 0, $refPos);
    }

    $scriptWritten = write_testcase_script($db,$cts,$args->tcversion_id, $args->project_key, $args->repository_name,
                                           $args->code_path, $args->branch_name, $args->commit_id);


    $auditMsg = "audit_testcasescript_added";
    $item_id = $args->tcversion_id;
    $objectType = "testcase_script_links";
    $args->direct_link = $cts->buildViewCodeURL($args->project_key,$args->repository_name,$args->code_path,$args->branch_name);
    if ($scriptWritten)
    {
      $gui->msg = lang_get("script_added");

      //save user selection of project_key and repository_name to current session
      $_SESSION['testscript_projectKey'] = $args->project_key;
      $_SESSION['testscript_repositoryName'] = $args->repository_name;

      //update value of Custom Field "Test Script" if possible
      $tScriptFieldID = null;
      $tScriptFieldValue = null;

      $tcase_mgr = new testcase($db);
      $linked_cfields = $tcase_mgr->cfield_mgr->get_linked_cfields_at_design($args->tproject_id,
                          1,null,'testcase',$args->tcversion_id);
      unset($tcase_mgr);
      foreach($linked_cfields as $cfieldID => $cfieldValue)
      {
        if (is_null($tScriptFieldValue) && strpos(strtolower(str_replace(" ", "", $cfieldValue['name'])), 'testscript') !== false)
        {
          $tScriptFieldID = $cfieldID;
          $tScriptFieldValue = $cfieldValue;
          break;
        }
      }

      if (!is_null($tScriptFieldID))
      {
        $cfieldWritten = write_cfield_testscript($db, $args->user, $tScriptFieldID, $args->tcversion_id, $args->code_path);
      }
      else
      {
        $cfieldWritten = true;
      }

      if (!$cfieldWritten)
      {
        $gui->msg .= " - But Custom Field 'Test Script' could not be updated";
      }

      logAuditEvent(TLS($auditMsg,$args->direct_link),"CREATE",$item_id,$objectType);
    }
    else
    {
      $gui->msg = sprintf($l18n["error_code_does_not_exist_on_cts"],$args->direct_link);
    }  
  }
  else
  {
    $gui->msg = lang_get("error_script_not_added"); 
  }
}
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * 
 * 
 */
function initEnv(&$dbHandler)
{
  $uaWhiteList = array();
  $uaWhiteList['elements'] = array('link','create','projectSelected','repoSelected',
                                   'branchSelected','expand','collapse');
  $uaWhiteList['length'] = array();
  foreach($uaWhiteList['elements'] as $xmen)
  {
    $uaWhiteList['length'][] = strlen($xmen);
  }  
  $user_action['maxLength'] = max($uaWhiteList['length']);
  $user_action['minLength'] = min($uaWhiteList['length']);

  $iParams = array("script_id" => array("REQUEST",tlInputParameter::STRING_N),
	           "tproject_id" => array("REQUEST",tlInputParameter::INT_N),
                   "tplan_id" => array("REQUEST",tlInputParameter::INT_N),
                   "tcversion_id" => array("REQUEST",tlInputParameter::INT_N),
                   "project_key" => array("POST",tlInputParameter::STRING_N),
                   "repository_name" => array("POST",tlInputParameter::STRING_N),
                   "code_path" => array("POST",tlInputParameter::STRING_N),
                   "branch_name" => array("POST",tlInputParameter::STRING_N),
                   "commit_id" => array("POST",tlInputParameter::STRING_N),
                   "projectKey" => array("POST",tlInputParameter::ARRAY_STRING_N),
                   "repositoryName" => array("POST",tlInputParameter::ARRAY_STRING_N),
                   "branchName" => array("POST",tlInputParameter::ARRAY_STRING_N),
                   "commits" => array("POST",tlInputParameter::ARRAY_STRING_N),
                   "files" => array("POST",tlInputParameter::ARRAY_STRING_N),
                   "expand_item" => array("POST",tlInputParameter::STRING_N),
                   "collapse_item" => array("POST",tlInputParameter::STRING_N),
		   "user_action" => array("REQUEST",tlInputParameter::STRING_N,
                   $user_action['minLength'],$user_action['maxLength']));
	
  $args = new stdClass();
  I_PARAMS($iParams,$args);

  $args->user = $_SESSION['currentUser'];

  $gui = new stdClass();
  $gui->pageTitle = lang_get('title_script_add');

  $gui->msg = '';
  $gui->tproject_id = $args->tproject_id;
  $gui->tplan_id = $args->tplan_id;
  $gui->tcversion_id = $args->tcversion_id;
  $gui->user_action = $args->user_action;

  $gui->project_key = $args->project_key;
  $gui->repository_name = $args->repository_name;
  $gui->code_path = $args->code_path;
  $gui->branch_name = $args->branch_name;
  $gui->commit_id = $args->commit_id;

  // -----------------------------------------------------------------------
  // Special processing
  list($ctObj,$ctCfg) = getCodeTracker($dbHandler,$args,$gui);

  $args->script_id = trim($args->script_id);

  return array($args,$gui,$ctObj,$ctCfg);
}


/**
 *
 */
function getCodeTracker(&$dbHandler,$argsObj,&$guiObj)
{
  $cts = null;
  $tprojectMgr = new testproject($dbHandler);
  $info = $tprojectMgr->get_by_id($argsObj->tproject_id);
  unset($tprojectMgr);

  $guiObj->codeTrackerCfg = new stdClass();
  $guiObj->codeTrackerCfg->createCodeURL = null;
  $guiObj->codeTrackerCfg->VerboseID = '';
  $guiObj->codeTrackerCfg->VerboseType = '';

  if($info['code_tracker_enabled'])
  {
    $ct_mgr = new tlCodeTracker($dbHandler);
    $codeTrackerCfg = $ct_mgr->getLinkedTo($argsObj->tproject_id);

    if( !is_null($codeTrackerCfg) )
    {
      $cts = $ct_mgr->getInterfaceObject($argsObj->tproject_id);
      $guiObj->codeTrackerCfg->VerboseType = $codeTrackerCfg['verboseType'];
      $guiObj->codeTrackerCfg->VerboseID = $codeTrackerCfg['codetracker_name'];
      $guiObj->codeTrackerCfg->createCodeURL = $cts->getEnterCodeURL();    
    }
  }
  return array($cts,$codeTrackerCfg); 
}

/**
 *
 */
function write_testcase_script(&$dbHandler, &$cts, $tcversion_id, $project_key,
                               $repo_name, $code_path, $branch_name, $commit_id)
{
  $result = true;

  $repoCont = $cts->getRepoContent($project_key,$repo_name,$code_path,$branch_name,$commit_id);
  if(property_exists($repoCont, 'errors'))
  {
    return false;
  }

  $tbk = array('testcase_script_links');
  $tbl = tlObjectWithDB::getDBTables($tbk);

  //check if entry already exists in DB
  $sql = " SELECT * FROM `{$tbl['testcase_script_links']}` " .
         " WHERE `tcversion_id` = " .intval($tcversion_id) .
         " AND `project_key` = '{$project_key}' " .
         " AND `repository_name` = '{$repo_name}' " .
         " AND `code_path` = '{$code_path}'";

  $rs = $dbHandler->get_recordset($sql);
  if (is_null($rs))
  {
    $fields = "(`tcversion_id`,`project_key`,`repository_name`,`code_path`";
    $values = "(".intval($tcversion_id).",'{$project_key}'," .
              "'{$repo_name}','{$code_path}'";
    if (!is_null($branch_name))
    {
      $fields .= ",`branch_name`";
      $values .= ",'{$branch_name}'";
    }
    if (!is_null($commit_id))
    {
      $fields .= ",`commit_id`";
      $values .= ",'{$commit_id}'";
    }
    $fields .= ")";
    $values .= ")";

    //no entry found, i.e. add new values to DB
    $sql = " INSERT INTO `{$tbl['testcase_script_links']}` {$fields} " .
           " VALUES {$values}";

    $result = $dbHandler->exec_query($sql);
  }

  return $result;
}

function write_cfield_testscript(&$dbHandler, &$user, $field_id, $node_id, $value)
{
  $tbk = array('cfield_design_values', 'tcversions','executions');
  $tbl = tlObjectWithDB::getDBTables($tbk);

  $result = false;
  //check if tcversion can be edited
  $sql = " SELECT id, active, is_open, baseline, reviewer_id " .
         " FROM `{$tbl['tcversions']}` " .
         " WHERE `id` = '{$node_id}'";

  $rs = $dbHandler->get_recordset($sql);

  if(!is_null($rs))
  {
    $rs = $rs[0];
    /* only update custom field value if test case can be
     * modified, i.e. has no baseline, is not status "Accepted"
     * and is active and open */
    if($rs['active'] == 1 && $rs['is_open'] == 1 &&
    is_null($rs['baseline']) && is_null($rs['reviewer_id']))
    {
      $sql = " SELECT id FROM `{$tbl['executions']}` " .
             " WHERE `tcversion_id` = '{$node_id}'";

      $rsExec = $dbHandler->get_recordset($sql);

      /* only update if test case was not executed yet or
       * corresponding modification right is given */
      if(is_null($rsExec) ||
      $user->hasRight($dbHandler,"testproject_edit_executed_testcases"))
      {
        //check if entry already exists in DB
        $sql = " UPDATE `{$tbl['cfield_design_values']}` " .
               " SET `value` = '{$value}' " .
               " WHERE `field_id` = '{$field_id}' " .
               " AND `node_id` = '{$node_id}'";

        $result = $dbHandler->exec_query($sql);
      }
    }
  }

  return $result;

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
  $hasRights = $user->hasRight($db,"mgt_modify_tc");
  return $hasRights;
}

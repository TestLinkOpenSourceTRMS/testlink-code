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

if(!is_null($codeT) && $args->project_key != "" && $args->repository_name != "" && $args->code_path != "")
{
  $l18n = init_labels(array("error_code_does_not_exist_on_cts" => null));

  $gui->msg = "";
  $scriptDeleted = del_testcase_script($db,$args->tcversion_id, $args->project_key,
                                       $args->repository_name, $args->code_path);
  $auditMsg = "audit_testcasescript_deleted";
  $item_id = $args->tcversion_id;
  $objectType = "testcase_script_links";
  $args->direct_link = $cts->buildViewCodeURL($args->project_key,$args->repository_name,$args->code_path,$args->branch_name);
  if ($scriptDeleted)
  {
    $gui->msg = lang_get("scriptdeleting_was_ok");
    logAuditEvent(TLS($auditMsg,$args->script_id),"DELETE",$item_id,$objectType);
  }
  else
  {
    $gui->msg = sprintf($l18n["error_code_does_not_exist_on_cts"],$args->direct_link);
  }  
}
else
{
  $gui->msg = lang_get("error_script_not_deleted"); 
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
  $iParams = array("script_id" => array("REQUEST",tlInputParameter::STRING_N),
                   "tproject_id" => array("REQUEST",tlInputParameter::INT_N),
                   "tcversion_id" => array("REQUEST",tlInputParameter::INT_N),
                   "project_key" => null,
                   "repository_name" => null,
                   "code_path" => null);
	
  $args = new stdClass();
  I_PARAMS($iParams,$args);

  $args->user = $_SESSION['currentUser'];

  $args->script_id = trim($args->script_id);
  
  $scriptArray = explode("&&", $args->script_id);
  if(count($scriptArray) > 0)
  {
    $args->project_key = $scriptArray[0];
    $args->repository_name = $scriptArray[1];
    $args->code_path = $scriptArray[2];
  }

  if (is_null($args->tproject_id))
  {
    $args->tproject_id = $_SESSION['testprojectID'];
  }
  $gui = new stdClass();
  $gui->pageTitle = lang_get('title_delete_script');

  $gui->msg = '';
  $gui->tcversion_id = $args->tcversion_id;

  $gui->project_key = $args->project_key;
  $gui->repository_name = $args->repository_name;
  $gui->code_path = $args->code_path;

  // -----------------------------------------------------------------------
  // Special processing
  list($ctObj,$ctCfg) = getCodeTracker($dbHandler,$args,$gui);

  $args->basehref = $_SESSION['basehref'];

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
function del_testcase_script(&$dbHandler, $tcversion_id, $project_key,
                             $repo_name, $code_path)
{
  $fields = "(`tcversion_id`,`project_key`,`repository_name`,`code_path`";
  $values = "(".intval($tcversion_id).",'{$project_key}'," .
            "'{$repo_name}','{$code_path}'";
  $fields .= ")";
  $values .= ")";

  $tbk = array('testcase_script_links');
  $tbl = tlObjectWithDB::getDBTables($tbk);
  $sql = " DELETE FROM `{$tbl['testcase_script_links']}` " .
         " WHERE `tcversion_id` = " . intval($tcversion_id) .
         " AND `project_key` = '{$project_key}' " .
         " AND `repository_name` = '{$repo_name}' " .
         " AND `code_path` = '{$code_path}' ";

  $result = $dbHandler->exec_query($sql);

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

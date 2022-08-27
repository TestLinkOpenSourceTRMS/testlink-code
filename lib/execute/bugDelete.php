<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  bugDelete.php
 * @internal revisions
 * @since 1.9.16
 *
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('exec.inc.php');

testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();
$msg = "";
if ($args->exec_id && $args->bug_id != "")
{
  if (write_execution_bug($db,$args->exec_id,$args->bug_id,$args->tcstep_id,true))
  {
    // get audit info
    $ainfo = get_execution($db,$args->exec_id,array('output' => 'audit'));
    $ainfo = $ainfo[0];

    $msg = lang_get('bugdeleting_was_ok');
    if( $ainfo['platform_name'] == '' )
    {
      $auditMsg = TLS('audit_executionbug_deleted_no_platform',$args->bug_id,
                      $ainfo['exec_id'],$ainfo['testcase_name'],
                      $ainfo['testproject_name'],$ainfo['testplan_name'],
                      $ainfo['build_name']);
    } 
    else
    {
      $auditMsg = TLS('audit_executionbug_deleted',$args->bug_id,$ainfo['exec_id'],
                      $ainfo['testcase_name'],$ainfo['testproject_name'],
                      $ainfo['testplan_name'],$ainfo['platform_name'],
                      $ainfo['build_name']);
    } 
    logAuditEvent($auditMsg,"DELETE",$args->exec_id,"executions");
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
  $args = new stdClass();
  $iParams = array("exec_id" => array("GET",tlInputParameter::INT_N),
                 "tcstep_id"  => array("GET",tlInputParameter::INT_N),
           "bug_id" => array("GET",tlInputParameter::STRING_N,0,config_get('field_size')->bug_id));
  
  $pParams = I_PARAMS($iParams,$args);
  $args->tproject_id = isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : $_SESSION['testprojectID'];

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
  $hasRights = $user->hasRight($db,"testplan_execute");
  return $hasRights;
}
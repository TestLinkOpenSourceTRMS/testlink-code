<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	editExecution.php
 *
 * Edit an execution notes and custom fields
 * @since 1.9.14
 * 
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once("web_editor.php");

$editorCfg = getWebEditorCfg('edit_execution');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();
$tcase_mgr = new testcase($db);

$args = init_args();

$owebeditor = web_editor('notes',$args->basehref,$editorCfg);
switch ($args->doAction)
{
  case 'edit':
	break;
        
  case 'doUpdate':
    doUpdate($db,$args,$tcase_mgr,$_REQUEST);
  break;  
}
$map = get_execution($db,$args->exec_id);
$owebeditor->Value = $map[0]['notes'];

// order on script is critic 
$gui = initializeGui($args,$tcase_mgr);
$cols = intval(isset($editorCfg['cols']) ? $editorCfg['cols'] : 60);
$rows = intval(isset($editorCfg['rows']) ? $editorCfg['rows'] : 10); 
$gui->notes = $owebeditor->CreateHTML($rows,$cols);
$gui->editorType = $editorCfg['type'];

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 *
 */
function doUpdate(&$db,&$args,&$tcaseMgr,&$request)
{
 	updateExecutionNotes($db,$args->exec_id,$args->notes);
    
 	$cfield_mgr = new cfield_mgr($db);
  $cfield_mgr->execution_values_to_db($request,$args->tcversion_id,$args->exec_id,$args->tplan_id);
}

/**
 *
 */
function init_args()
{
  // Take care of proper escaping when magic_quotes_gpc is enabled
  $_REQUEST=strings_stripSlashes($_REQUEST);

	$iParams = array("exec_id" => array(tlInputParameter::INT_N),
		               "doAction" => array(tlInputParameter::STRING_N,0,100),
   		             "notes" => array(tlInputParameter::STRING_N),
					         "tcversion_id" => array(tlInputParameter::INT_N),
					         "tplan_id" => array(tlInputParameter::INT_N),
					         "tproject_id" => array(tlInputParameter::INT_N));

	$args = new stdClass();
  R_PARAMS($iParams,$args);
    
  $args->basehref = $_SESSION['basehref'];
  $args->user = $_SESSION['currentUser'];

  return $args; 
}

/**
 *
 */
function initializeGui(&$argsObj,&$tcaseMgr)
{
  $guiObj = new stdClass();
  $guiObj->exec_id = $argsObj->exec_id;
  $guiObj->tcversion_id = $argsObj->tcversion_id;
  $guiObj->tplan_id = $argsObj->tplan_id;
  $guiObj->tproject_id = $argsObj->tproject_id;
  $guiObj->edit_enabled = $argsObj->user->hasRight($db,"exec_edit_notes") == 'yes' ? 1 : 0;
  $guiObj->cfields_exec = $tcaseMgr->html_table_of_custom_field_inputs($argsObj->tcversion_id,null,'execution','_cf',
                                                                       $argsObj->exec_id,$argsObj->tplan_id,$argsObj->tproject_id);
  return $guiObj;
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
	return $user->hasRight($db,"testplan_execute") && $user->hasRight($db,"exec_edit_notes");
}
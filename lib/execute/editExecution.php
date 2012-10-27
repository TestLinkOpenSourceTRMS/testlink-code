<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	editExecution.php
 *
 * Edit an execution notes and custom fields
 *
 * @internal revisions
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once("web_editor.php");

$editorCfg = getWebEditorCfg('execution');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$tcase_mgr = new testcase($db);
$args = init_args();
checkRights($db,$_SESSION['currentUser'],$args);


$gui = new stdClass();
$gui->exec_id = $args->exec_id;
$gui->tcversion_id = $args->tcversion_id;
$gui->tplan_id = $args->tplan_id;
$gui->tproject_id = $args->tproject_id;

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
$gui->cfields_exec = $tcase_mgr->html_table_of_custom_field_inputs($args->tcversion_id,null,'execution','_cf',
                                                                   $args->exec_id,$args->tplan_id,$args->tproject_id);

$gui->notes = $owebeditor->CreateHTML();
$gui->editorType = $editorCfg['type'];

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function doUpdate(&$db,&$args,&$tcaseMgr,&$request)
{
 	updateExecutionNotes($db,$args->exec_id,$args->notes);
    
 	$cfield_mgr = new cfield_mgr($db);
    $cfield_mgr->execution_values_to_db($request,$args->tcversion_id,$args->exec_id,$args->tplan_id);
}

function init_args()
{
	$iParams = array("exec_id" => array(tlInputParameter::INT_N),
		             "doAction" => array(tlInputParameter::STRING_N,0,100),
   		             "notes" => array(tlInputParameter::STRING_N),
					"tcversion_id" => array(tlInputParameter::INT_N),
					"tplan_id" => array(tlInputParameter::INT_N),
					"tproject_id" => array(tlInputParameter::INT_N),
			);
	$args = new stdClass();
    R_PARAMS($iParams,$args);
    
    $args->basehref = $_SESSION['basehref'];
    
    // BUGID 4066 - take care of proper escaping when magic_quotes_gpc is enabled
	$_REQUEST=strings_stripSlashes($_REQUEST);

	return $args; 
}


/**
 * Checks the user rights for using the page
 * 
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	if($userObj->hasRight($dbHandler,"exec_edit_notes",$env['tproject_id'],$env['tplan_id']) )
	{
		checkSecurityClearance($db,$userObj,$env,array('testplan_execute'),'and');
	}
	else
	{
    redirect($_SESSION['basehref'],"top.location");
		exit();
	}
}
?>
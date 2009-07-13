<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: editExecution.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2009/07/13 18:36:34 $ by $Author: franciscom $
 *
 * Edit an execution notes and custom fields
 *
 * rev: 20090530 - franciscom - BUGID 
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once("web_editor.php");

$editorCfg = getWebEditorCfg('execution');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db);
$gui = new stdClass();

$templateCfg = templateConfiguration();
$tcase_mgr = new testcase($db);
$args = init_args();
$gui->exec_id = $args->exec_id;
$gui->tcversion_id = $args->tcversion_id;
$gui->tplan_id = $args->tplan_id;
$gui->tproject_id = $args->tproject_id;

$owebeditor = web_editor('notes',$_SESSION['basehref'],$editorCfg);
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

$gui->notes=$owebeditor->CreateHTML();
$gui->editorType=$editorCfg['type'];

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function: 

  args :
  
  returns: 

*/
function doUpdate(&$dbHandler,&$argsObj,&$tcaseMgr,&$request)
{
    $targetTable=tlObjectWithDB::getDBTables('executions');
    $sql = "UPDATE {$targetTable['executions']} " .
           "SET notes='" . $dbHandler->prepare_string($argsObj->notes) . "' " .
           "WHERE id={$argsObj->exec_id}";
    $dbHandler->exec_query($sql);     
    
    $cfield_mgr = new cfield_mgr($dbHandler);
    $cfield_mgr->execution_values_to_db($request,$argsObj->tcversion_id,$argsObj->exec_id,$argsObj->tplan_id);
}


/*
  function: 

  args :
  
  returns: 

*/
function init_args()
{
    $args = new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);
    
    $args->notes=isset($_REQUEST['notes']) ? trim($_REQUEST['notes']) : null;
    $args->doAction=isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : 'show';

    $args->exec_id=$_REQUEST['exec_id'];
    $args->tcversion_id=$_REQUEST['tcversion_id'];
    $args->tplan_id=$_REQUEST['tplan_id'];
    $args->tproject_id=$_REQUEST['tproject_id'];


    return $args; 
}
?>
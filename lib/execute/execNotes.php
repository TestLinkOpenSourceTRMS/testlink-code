<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: execNotes.php,v $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2009/09/04 19:22:37 $ by $Author: schlundus $
 *
 * Edit an execution note
 *
 * @ TODO schlundus. seems to be no longer used
 *
 * rev: 20080827 - franciscom - BUGID 1692
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once("web_editor.php");

$editorCfg = getWebEditorCfg('execution');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db);
$templateCfg = templateConfiguration();

$args = init_args();
$owebeditor = web_editor('notes',$_SESSION['basehref'],$editorCfg);

switch ($args->doAction)
{
    case 'edit':
	    break;
        
    case 'doUpdate':
	    doUpdate($db,$args);
	    break;  
}
$map = get_execution($db,$args->exec_id);
$owebeditor->Value = $map[0]['notes'];

$smarty = new TLSmarty();
$smarty->assign('notes',$owebeditor->CreateHTML());
$smarty->assign('editorType',$editorCfg['type']);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function: 

  args :
  
  returns: 

*/
function doUpdate(&$dbHandler,&$argsObj)
{
    $tables = tlObjectWithDB::getDBTables('executions');
    $sql = "UPDATE {$tables['executions']} " .
           " SET notes='" . $dbHandler->prepare_string($argsObj->notes) . "' " .
           " WHERE id={$argsObj->exec_id} ";
    $dbHandler->exec_query($sql);     
}


/*
  function: 

  args :
  
  returns: 

*/
function init_args()
{
    $iParams = array("exec_id" => array(tlInputParameter::INT_N),
		             "doAction" => array(tlInputParameter::STRING_N,0,100),
   		             "notes" => array(tlInputParameter::STRING_N));
	$args = new stdClass();
    R_PARAMS($iParams,$args);
    return $args; 
}

?>
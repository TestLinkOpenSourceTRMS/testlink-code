<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: execNotes.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2008/09/02 16:39:49 $ by $Author: franciscom $
 *
 * Edit an execution note
 *
 * rev: 20080827 - franciscom - BUGID 1692
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once("web_editor.php");

$editorCfg=getWebEditorCfg('execution');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db);
$templateCfg=templateConfiguration();

$args=init_args();
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
$owebeditor->Value=$map[0]['notes'];

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
    $sql="UPDATE executions " .
         "SET notes='{$argsObj->notes}'" .
         "WHERE id={$argsObj->exec_id}";
    $dbHandler->exec_query($sql);     
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
    $args->exec_id=$_REQUEST['exec_id'];
    $args->doAction=isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : 'show';
    return $args; 
}

?>
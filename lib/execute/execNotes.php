<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: execNotes.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2008/03/22 15:47:46 $ by $Author: franciscom $
 *
 * Edit an execution note
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once("web_editor.php");
require_once('exec.inc.php');
testlinkInitPage($db);

$template_dir = 'execute/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$args=init_args();
$owebeditor = web_editor('notes',$_SESSION['basehref']);

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
$smarty->display($template_dir . $default_template);


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
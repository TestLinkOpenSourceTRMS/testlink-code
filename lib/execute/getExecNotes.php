<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: getExecNotes.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2009/05/09 17:59:19 $ by $Author: schlundus $
 *
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once("web_editor.php");
require_once('exec.inc.php');

testlinkInitPage($db);
$templateCfg = templateConfiguration();

$args = init_args();

$webeditorCfg = getWebEditorCfg('execution');
$map = get_execution($db,$args->exec_id);

$smarty = new TLSmarty();
$smarty->assign('notes',$map[0]['notes']);
$smarty->assign('webeditorType',$webeditorCfg['type']);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

function init_args()
{
    $iParams = array(
		"exec_id" => array(tlInputParameter::INT_N),
	);
	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);
    
    return $args; 
}
?>
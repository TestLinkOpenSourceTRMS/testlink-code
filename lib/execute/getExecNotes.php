<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: getExecNotes.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2008/12/31 15:07:19 $ by $Author: franciscom $
 *
 * rev: 20081231 - franciscom - 
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once("web_editor.php");
require_once('exec.inc.php');

testlinkInitPage($db);
$templateCfg = templateConfiguration();

$webeditorCfg=getWebEditorCfg('execution');
$map = get_execution($db,$_REQUEST['exec_id']);
$smarty = new TLSmarty();
$smarty->assign('notes',$map[0]['notes']);
$smarty->assign('webeditorType',$webeditorCfg['type']);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);
?>
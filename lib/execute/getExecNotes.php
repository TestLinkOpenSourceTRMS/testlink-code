<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: getExecNotes.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2008/01/04 16:20:11 $ by $Author: franciscom $
 *
 * 
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
testlinkInitPage($db);

$template_dir='execute/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$webeditorType=config_get('gui')->webeditor;
$map=get_execution($db,$_REQUEST['exec_id']);
$smarty = new TLSmarty();
$smarty->assign('notes',$map[0]['notes']);
$smarty->assign('webeditorType',$webeditorType);
$smarty->display($template_dir . $default_template);

?>
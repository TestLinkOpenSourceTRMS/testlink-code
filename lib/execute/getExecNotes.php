<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: getExecNotes.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2007/12/31 16:25:19 $ by $Author: franciscom $
 *
 * Deletes a bug
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
testlinkInitPage($db);

$template_dir='execute/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));


$map=get_execution($db,$_REQUEST['exec_id']);
//echo(nl2br($map[0]['notes']));
$smarty = new TLSmarty();
$smarty->assign('notes',$map[0]['notes']);
$smarty->display($template_dir . $default_template);

?>
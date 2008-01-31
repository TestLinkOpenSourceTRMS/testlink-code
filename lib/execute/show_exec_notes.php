<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: show_exec_notes.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2008/01/31 22:15:47 $ by $Author: schlundus $
 *
 * Deletes a bug
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('exec.inc.php');
testlinkInitPage($db);

$template_dir = 'execute/';

$map = get_execution($db,$_REQUEST['exec_id']);

$smarty = new TLSmarty();
$smarty->assign('notes',$map[0]['notes']);
$smarty->display($template_dir . 'show_exec_notes.tpl');
?>
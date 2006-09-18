<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: bug_delete.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2006/09/18 07:14:48 $ by $Author: franciscom $
 *
 * Deletes an attachment
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('exec.inc.php');
testlinkInitPage($db);

define('JUST_DELETE',TRUE);
$exec_id = isset($_REQUEST['exec_id'])? intval($_REQUEST['exec_id']) : 0;
$bug_id = isset($_REQUEST['bug_id'])? trim($_REQUEST['bug_id']) : null;
$msg="";
if ($exec_id > 0 && !is_null($bug_id) && strlen($bug_id) > 0)
{
  write_execution_bug($db,$exec_id, $bug_id,JUST_DELETE);
  $msg=lang_get('deleting_was_ok');
}

$smarty = new TLSmarty();
$smarty->assign('msg',$msg);
$smarty->display('bug_delete.tpl');
?>
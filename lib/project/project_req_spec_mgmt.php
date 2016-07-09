<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	project_req_spec_mgmt.php
 * @author 		Martin Havlat
 *
 * @internal revisions
 * @since 1.9.10
 */
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db,false,false,"checkRights");

$tproject_id   = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'undefined';

$gui = new stdClass();
$gui->main_descr = lang_get('testproject') .  TITLE_SEP . $tproject_name . TITLE_SEP . lang_get('title_req_spec');
$gui->tproject_id = $tproject_id;
$gui->refresh_tree = 'no';

$uo = $_SESSION['currentUser'];
$gui->grants = new stdClass();
$gui->grants->modify = $uo->hasRight($db,'mgt_modify_req');
$gui->grants->ro = $uo->hasRight($db,'mgt_view_req');

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display('requirements/project_req_spec_mgmt.tpl');

/**
 *
 */
function checkRights(&$db,&$user)
{
  return ($user->hasRight($db,'mgt_view_req') || 
		  $user->hasRight($db,'mgt_modify_req'));
}
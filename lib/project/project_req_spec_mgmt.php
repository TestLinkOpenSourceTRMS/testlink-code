<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @version $Id: project_req_spec_mgmt.php,v 1.6 2009/01/07 19:55:35 schlundus Exp $
 * @author Martin Havlat
 *
 * Allows you to show test suites, test cases.
 * Normally launched from tree navigator.
 *
 */
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db,false,false,"checkRights");

$tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'undefined';

$gui = new stdClass();
$gui->main_descr = lang_get('testproject') .  TITLE_SEP . $tproject_name;
$gui->tproject_id = $tproject_id;
$gui->refresh_tree = 'no';

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display('requirements/project_req_spec_mgmt.tpl');

function checkRights(&$db,&$user)
{
	return ($user->hasRight($db,'mgt_view_req') && $user->hasRight($db,'mgt_modify_req'));
}
?>

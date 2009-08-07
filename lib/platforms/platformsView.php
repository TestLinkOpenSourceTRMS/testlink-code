<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: platformsView.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2009/08/07 06:48:12 $ by $Author: franciscom $
 *
 * allows users to manage keywords. 
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("platform.class.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();

$platform_mgr= new tlPlatform($db, $args->testproject_id);
$gui = new stdClass();
$gui->platforms = $platform_mgr->getAll();
$gui->canManage = $_SESSION['currentUser']->hasRight($db,"platform_management");
$gui->canManage = true;
$gui->action = null;
$gui->sqlResult = null;

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

function init_args()
{
	$args = new stdClass();
	$args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

	return $args;
}

function checkRights(&$db,&$user)
{
// TODO: add proper rights
	return true;
#	return $user->hasRight($db,'mgt_view_platform');
}
?>
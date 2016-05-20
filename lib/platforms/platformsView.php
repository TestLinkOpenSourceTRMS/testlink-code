<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: platformsView.php,v $
 *
 * @version $Revision: 1.7 $
 * @modified $Date: 2010/09/12 15:16:09 $ by $Author: franciscom $
 *
 * allows users to manage platforms. 
 * @internal revisions
 */
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();

$platform_mgr = new tlPlatform($db, $args->testproject_id);

$gui = new stdClass();
$gui->platforms = $platform_mgr->getAll(array('include_linked_count' => true));

$gui->canManage = $args->currentUser->hasRight($db,"platform_management");
$gui->user_feedback = null;
$cfg = getWebEditorCfg('platform');
$gui->editorType = $cfg['type'];
	  

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * 
 *
 */
function init_args()
{
	$args = new stdClass();
	$args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->currentUser = $_SESSION['currentUser']; 

	return $args;
}

function checkRights(&$db,&$user)
{
	return ($user->hasRight($db,'platform_management') || $user->hasRight($db,'platform_view'));
}
?>

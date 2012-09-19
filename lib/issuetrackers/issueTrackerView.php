<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	issueTrackerView.php
 * @author	francisco.mancardi@gmail.com
 * @since 1.9.4
 *
 * @internal revisions
 *
**/
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once("common.php");

testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();

$issueTrackerMgr = new tlIssueTracker($db);

$args = init_args();

$gui = new stdClass();
$gui->items = $issueTrackerMgr->getAll(array('output' => 'add_link_count'));
$gui->canManage = $args->currentUser->hasRight($db,"issuetracker_management");
$gui->user_feedback = $args->user_feedback;

new dBug($gui);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



/**
 * @return object returns the arguments for the page
 */
function init_args()
{
	$args = new stdClass();
	$args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
	$args->currentUser = $_SESSION['currentUser']; 
	$args->user_feedback = array('type' => '', 'message' => '');
	return $args;
}


function checkRights(&$db,&$user)
{
	return $user->hasRight($db,"issuetracker_view");
}
?>
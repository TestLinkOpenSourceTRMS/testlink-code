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

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$gui = initializeGui($db,($args = init_args($db)));

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



/**
 * @return object returns the arguments for the page
 */
function init_args(&$dbHandler)
{
	$args = new stdClass();
	$args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
	$args->currentUser = $_SESSION['currentUser']; 
  $args->canManage = $_SESSION['currentUser']->hasRight($dbHandler,"issuetracker_management");
	$args->user_feedback = array('type' => '', 'message' => '');
	return $args;
}

function initializeGui(&$dbHandler,$argsObj)
{
  $issueTrackerMgr = new tlIssueTracker($dbHandler);
  $gui = new stdClass();
  $gui->items = $issueTrackerMgr->getAll(array('output' => 'add_link_count','checkEnv' => true));
  $gui->user_feedback = $argsObj->user_feedback;
  $gui->canManage = $argsObj->canManage;
  $gui->tproject_id = $argsObj->tproject_id;
  $gui->body_onload = '';
  return $gui;
}


function checkRights(&$db,&$user)
{
	return $user->hasRight($db,"issuetracker_view");
}
?>
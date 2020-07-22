<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package		TestLink
 * @copyright	2005-2020, TestLink community 
 * 
 */

require_once(dirname(__FILE__)."/../../config.inc.php");
require_once("common.php");

testlinkInitPage($db, false, false);

$assignment_mgr = new assignment_mgr($db);
$testplan_mgr = new testplan($db);
$build_mgr = new build_mgr($db);
$templateCfg = templateConfiguration();

$args = init_args();
$gui = init_gui($db, $args);

$context = new stdClass();
$context->tproject_id = $args->tproject_id;
checkRights($db,$_SESSION['currentUser'],$context);



$assignment_count = 0;

$build_name = "";
if ($args->build_id) {
	$assignment_count = $assignment_mgr->get_count_of_assignments_for_build_id($args->build_id);
	$build_info = $build_mgr->get_by_id($args->build_id);
	$build_name = $build_info['name'];
}


if ($assignment_count) {
	// there are assignments
	if ($args->confirmed) {
		// their deletion has been confirmed, so delete them
		$assignment_mgr->delete_by_build_id($args->build_id);
		$gui->message = sprintf(lang_get('unassigned_all_tcs_msg'), $build_name);
		$gui->refreshTree = $args->refreshTree ? true : false;
	} else {
		// there are assignments, but their deletion has still to be confirmed
		$gui->draw_tc_unassign_button = true;
		$gui->popup_title = lang_get('unassign_all_tcs_msgbox_title');
		$gui->popup_message = sprintf(lang_get('unassign_all_tcs_warning_msg'), $build_name);
		$gui->message = sprintf(lang_get('number_of_assignments_per_build'), $assignment_count, $build_name);
	}
} else {
	// there are no assignments for this build
	$gui->message = lang_get('no_testers_assigned_to_build');
}

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 *
 */
function init_args() {
	
	$args = new stdClass();
	
	$_REQUEST = strings_stripSlashes($_REQUEST);
	
	$args->build_id = isset($_REQUEST['build_id']) ? 
	                  intval($_REQUEST['build_id']) : 0;
	$args->confirmed = isset($_REQUEST['confirmed']) && $_REQUEST['confirmed'] == 'yes' ? true : false;
	
	$args->user_id = $_SESSION['userID'];
	$args->testproject_id = intval($_SESSION['testprojectID']);
	$args->testproject_name = $_SESSION['testprojectName'];
	
	$args->refreshTree = isset($_SESSION['setting_refresh_tree_on_action']) ?
	                     $_SESSION['setting_refresh_tree_on_action'] : false;
	
	return $args;
}


/**
 *
 */
function init_gui(&$dbHandler, &$argsObj) {
	
	$gui = new stdClass();
	
	$gui->build_id = $argsObj->build_id;
	$gui->draw_tc_unassign_button = false;
	$gui->refreshTree = false;
	
	$gui->title = lang_get('remove_all_tester_assignments_title');
	$gui->message = "";
	
	$gui->popup_title = "";
	$gui->popup_message = "";
	
	return $gui;
}


/**
 *
 */
function checkRights(&$db,&$user,&$context)
{
  $context->rightsOr = [];
  $context->rightsAnd = ["testplan_planning"];
  pageAccessCheck($db, $user, $context);
}


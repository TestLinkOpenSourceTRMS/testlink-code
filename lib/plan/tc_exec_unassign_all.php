<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package		  TestLink
 * @author		  Andreas Simon
 * @copyright	  2005-2019, TestLink community 
 * @filesource	tc_exec_unassign_all.php
 * @link		http://www.testlink.org/ 
 * 
 * 
 */

require_once(dirname(__FILE__)."/../../config.inc.php");
require_once("common.php");

testlinkInitPage($db);

$assignment_mgr = new assignment_mgr($db);
$testplan_mgr = new testplan($db);
$build_mgr = new build($db);
$templateCfg = templateConfiguration();

$args = init_args($db);
$gui = init_gui($db, $args);

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
function init_args(&$dbH) {
	
	list($args,$env) = initContext();
	
	$_REQUEST = strings_stripSlashes($_REQUEST);
	
	$args->build_id = isset($_REQUEST['build_id']) ? $_REQUEST['build_id'] : 0;
	$args->confirmed = isset($_REQUEST['confirmed']) && $_REQUEST['confirmed'] == 'yes' ? true : false;
	
	$args->user_id = $_SESSION['userID'];
	$args->user = $_SESSION['currentUser'];

	$args->testproject_id = $args->tproject_id;
	$args->testproject_name = testproject::getName($dbH,$args->tproject_id);
	
	$args->refreshTree = isset($_SESSION['setting_refresh_tree_on_action']) ?
	                     $_SESSION['setting_refresh_tree_on_action'] : false;


  // ----------------------------------------------------------------
  // Feature Access Check
  // This feature is affected only for right at Test Project Level
  $env = array()
  $env['script'] = basename(__FILE__);
  $env['tproject_id'] = $args->tproject_id;
  $env['tplan_id'] = $args->tplan_id;
  $args->user->checkGUISecurityClearance($dbH,$env,
                    array('exec_assign_testcases'),'and');
  // ----------------------------------------------------------------
	
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
<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Test navigator for Test Plan
 *
 * @filesource	planTCNavigator.php
 * @package 	TestLink
 * @copyright 	2003-2012, TestLink community
 * @link 		http://www.teamst.org/index.php
 *
 * @internal revisions
 * @since 1.9.4	
 * 20110824 - franciscom - TICKET 4721: Left side tree manu - add specific navigator titles
 *
 **/

require('../../config.inc.php');
require_once("common.php");
require_once("users.inc.php");
require_once("treeMenu.inc.php");
require_once('exec.inc.php');

// Time tracking - 
// $chronos[] = microtime(true);$tnow = end($chronos);

testlinkInitPage($db);
$templateCfg = templateConfiguration();

$assignment_mgr = new assignment_mgr($db);
$control = new tlTestCaseFilterControl($db, 'plan_mode');
$gui = initializeGui($db, $control, $assignment_mgr);
$control->build_tree_menu($gui);

//$chronos[] = microtime(true);$tnow = end($chronos); $tprev = prev($chronos);
//$t_elapsed = number_format( $tnow - $tprev, 4);
//echo '<br> ' . __FUNCTION__ . ' Elapsed BEFORE RENDERING (sec) (xxx()):' . $t_elapsed .'<br>';
//reset($chronos);	

$smarty = new TLSmarty();

$smarty->assign('gui', $gui);
$smarty->assign('control', $control);
$smarty->assign('menuUrl', $gui->menuUrl);
$smarty->assign('args', $control->get_argument_string());
$smarty->assign('treeHeader', $gui->title);

$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * @param unknown_type $dbHandler
 * @param unknown_type $control
 * @return stdClass
 * 
 * @internal revisions:
 *   20100721 - asimon - BUGID 3406, added assignmentMgr
 */
function initializeGui(&$dbHandler, &$control, &$assignmentMgr) {

	$gui = new stdClass();
	
	$gui->feature = $control->args->feature;
    $gui->tPlanID = $control->args->testplan_id;
	$gui->title = lang_get('title_test_plan_navigator');
	$gui->src_workframe = '';
	$gui->additional_string = '';
	
	// configure target URLs and clickable buttons
	switch($control->args->feature) 
	{
		case 'planUpdateTC':
			$gui->menuUrl = "lib/plan/planUpdateTC.php";
			$gui->title_navigator = lang_get('navigator_update_linked_tcversions');
			$control->draw_bulk_update_button = true;
		break;
		
		case 'test_urgency':
			$gui->title_navigator = lang_get('navigator_test_urgency');
			$gui->menuUrl = "lib/plan/planUrgency.php";
		break;

		case 'tc_exec_assignment':
			$gui->title_navigator = lang_get('navigator_tc_exec_assignment');
			$gui->menuUrl = "lib/plan/tc_exec_assignment.php";
			// BUGID 3406 - check for assignments before displaying the unassign button
			$build_id = $control->settings['setting_build']['selected'];
			if ($assignmentMgr->get_count_of_assignments_for_build_id($build_id)) {
				$control->draw_tc_unassign_button = true;
			}
		break;
	}
	
	return $gui;
}
?>
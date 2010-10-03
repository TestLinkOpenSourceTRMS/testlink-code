<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Test navigator for Test Plan
 *
 *
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2003-2009, TestLink community
 * @version    	CVS: $Id: planTCNavigator.php,v 1.52 2010/10/03 15:34:15 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 *  20100708 - asimon - BUGID 3406
 *  20100628 - asimon - removal of constants from filter control class
 *  20160625 - asimon - refactoring for new filter features
 *  20100624 - asimon - CVS merge (experimental branch to HEAD)
 *  20100622 - asimon - huge refactorization for new tlTestCaseFilterControl class
 *  20100428 - asimon - BUGID 3301 and related issues - changed name or case
 *                      of some variables used in new common template,
 *                      added filtering by custom fields
 *	20100202 - asimon - BUGID 2455, BUGID 3026
 *  20081223 - franciscom - advanced/simple filter feature
 **/

require('../../config.inc.php');
require_once("common.php");
require_once("users.inc.php");
require_once("treeMenu.inc.php");
require_once('exec.inc.php');

testlinkInitPage($db);

$templateCfg = templateConfiguration();

$assignment_mgr = new assignment_mgr($db); // BUGID 3406
$control = new tlTestCaseFilterControl($db, 'plan_mode');
$gui = initializeGui($db, $control, $assignment_mgr);
$control->build_tree_menu($gui);

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
	switch($control->args->feature) {
		case 'planUpdateTC':
			$gui->menuUrl = "lib/plan/planUpdateTC.php";
			$control->draw_bulk_update_button = true;
		break;
		
		case 'test_urgency':
			$gui->menuUrl = "lib/plan/planUrgency.php";
		break;

		case 'tc_exec_assignment':
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
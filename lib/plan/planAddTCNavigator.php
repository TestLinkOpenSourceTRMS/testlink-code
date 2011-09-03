<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	planAddTCNavigator.php
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2005-2011, TestLink community
 * @link 		http://www.teamst.org/index.php
 *
 * 	Navigator for feature: add Test Cases to a Test Case Suite in Test Plan.
 *	It builds the javascript tree that allow the user select a required part
 *	Test specification. Keywords should be used for filter.
 *
 * @internal revisions
 * @since 2.0
 * 20110824 - franciscom - TICKET 4721: Left side tree manu - add specific navigator titles
 *
 */

require('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");

testlinkInitPage($db);

$templateCfg = templateConfiguration();

$control = new tlTestCaseFilterControl($db, 'plan_add_mode');
$gui = initializeGui($control);
$control->build_tree_menu($gui);

$smarty = new TLSmarty();

$smarty->assign('gui', $gui);
$smarty->assign('control', $control);
$smarty->assign('args', $gui->args);
$smarty->assign('menuUrl', $gui->menuUrl);

// new dBug($templateCfg);

$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * Initialize gui object for use in templates.
 * @param tlTestCaseFilterControl $control
 * @return object $gui
 */
function initializeGui($control) 
{
	$gui = new stdClass();
	$gui->menuUrl = 'lib/plan/planAddTC.php';
	$gui->args = $control->get_argument_string();
	$gui->additional_string = '';

	// Seems useless!!! 
	// $gui->src_workframe = $control->args->basehref . $gui->menuUrl .
	//                       "?edit=testproject&id={$control->args->testproject_id}&" . 
	//                       "tproject_id={$control->args->testproject_id}&" . 
	//                       "tplan_id={$control->args->testplan_id}" . 
	//                       $gui->args;
	
	$gui->title_navigator = lang_get('navigator_add_remove_tcase_to_tplan');
	return $gui;
}
?>
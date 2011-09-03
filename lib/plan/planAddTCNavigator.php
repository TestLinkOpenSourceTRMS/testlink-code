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
 * @since 1.9.4
 * 20110824 - franciscom - TICKET 4721: Left side tree manu - add specific navigator titles
 *
 * @since 1.9.3
 * 20100628 - asimon - removal of constants from filter control class
 * 20100625 - asimon - refactoring for new filter features and BUGID 3516
 * 20100624 - asimon - CVS merge (experimental branch to HEAD)
 * 20100622 - asimon - huge refactorization for new tlTestCaseFilterControl class
 * 20100428 - asimon - BUGID 3301 and related issues - changed name or case
 *                     of some variables used in new common template,
 *                     added custom field filtering logic
 * 20100417 - franciscom - BUGID 2498: Add test case to test plan - Filter Test Cases based on Test Importance
 * 20100410 - franciscom - BUGID 2797 - filter by test case execution type
 * 20100228 - franciscom - BUGID 0001927: filter on keyword - Filter tree when add/remove testcases - KO
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
	$gui->src_workframe = $control->args->basehref . $gui->menuUrl .
	                "?edit=testproject&id={$control->args->testproject_id}" . $gui->args;
	
	$gui->title_navigator = lang_get('navigator_add_remove_tcase_to_tplan');
	return $gui;
}
?>
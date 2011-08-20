<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * link/unlink test cases to a test plan
 *
 * @filesource	execNavigator.php
 * @package 	TestLink
 * @copyright 	2007-2011, TestLink community
 * @link 		http://www.teamst.org/index.php
 *
 * @internal revisions
 * @since 1.9.4
 *
 * @since 1.9.3
 * 20101007 - franciscom - 	BUGID 3270 - Export Test Plan in XML Format
 * 20100628 - asimon - removal of constants from filter control class
 * 20100624 - asimon - CVS merge (experimental branch to HEAD)
 * 20100622 - asimon - huge refactoring for new filter design,
 *                     removed as much logic from here as possible
 * 20100609 - eloff - Prevent selection of invalid platform
 * 20100428 - asimon - BUGID 3301 and related issues - changed name or case
 *                     of some variables used in new common template
 * 20100417 - franciscom - BUGID 3380 execution type filter
 * 20100409 - eloff - BUGID 3050 - remember selected platform and build in session
 * 20100222 - asimon - fixes in initializeGui() for testplan select box when there are no builds
 * 20100217 - asimon - added check for open builds on initBuildInfo()
 * 20100202 - asimon - changed filtering, BUGID 2455, BUGID 3026
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');
require_once("users.inc.php");
require_once('treeMenu.inc.php');
require_once('exec.inc.php');

testlinkInitPage($db);

$templateCfg = templateConfiguration();


$control = new tlTestCaseFilterControl($db, 'execution_mode');
$gui = initializeGui($control);
$control->build_tree_menu($gui);
$smarty = new TLSmarty();

$smarty->assign('gui',$gui);
$smarty->assign('control', $control);
$smarty->assign('menuUrl',$gui->menuUrl);
$smarty->assign('args', $gui->args);

$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * 
 *
 */
function initializeGui(&$control) {
	$gui = new stdClass();
	
	$gui->menuUrl = 'lib/execute/execSetResults.php';
	$gui->args = $control->get_argument_string();
	$gui->src_workframe = $control->args->basehref . $gui->menuUrl .
	                "?edit=testproject&id={$control->args->testproject_id}" . $gui->args;
	
	// BUGID 3270 - Export Test Plan in XML Format
	$control->draw_export_testplan_button = true;
	return $gui;
}
?>
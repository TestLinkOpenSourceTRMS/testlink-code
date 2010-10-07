<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * link/unlink test cases to a test plan
 *
 * @package 	TestLink
 * @copyright 	2007-2009, TestLink community
 * @version    	CVS: $Id: execNavigator.php,v 1.122 2010/10/07 19:53:26 franciscom Exp $
 * @filesource	http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/lib/functions/object.class.php?view=markup
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
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
 * 20090828 - franciscom - added contribution platform feature
 * 20090828 - franciscom - BUGID 2296 - filter by Last Exec Result on Any of previous builds
 * 20081227 - franciscom - BUGID 1913 - filter by same results on ALL previous builds
 * 20081220 - franciscom - advanced/simple filters
 * 20081217 - franciscom - only users that have effective role with right
 *                         that allow test case execution are displayed on
 *                         filter by user combo.
 *
 * 20080517 - franciscom - fixed testcase filter bug
 * 20080428 - franciscom - keyword filter can be done on multiple keywords
 * 20080224 - franciscom - refactoring
 * 20080224 - franciscom - BUGID 1056
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
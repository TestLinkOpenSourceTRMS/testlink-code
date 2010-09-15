<?php
/**
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
*
* 	@version 	$Id: listTestCases.php,v 1.60 2010/09/15 20:55:12 franciscom Exp $
* 	@author 	Martin Havlat
*
* 	Generates tree menu with test specification.
*   It builds the javascript tree that allows the user to choose testsuite or testcase.
*
*	@internal revision
*
*   20100628 - asimon - removal of constants from filter control class
*   20100624 - asimon - CVS merge (experimental branch to HEAD)
*   20100622 - asimon - huge refactorization for new tlTestCaseFilterControl class
*   20100517 - asimon - BUGID 3301 and related - huge refactoring for first implementation
*                       of filter panel class hierarchy to simplify/standardize
*                       filter panel handling for test cases and requirements
*   20100428 - asimon - BUGID 3301 and related issues - changed name or case
*                       of some variables used in new common template,
*                       added custom field filtering logic
*	20091210 - franciscom - test case execution type filter
*   20090308 - franciscom - added option Any in keywords filter
*   20090210 - BUGID 2062 - franciscom -
*/
require_once('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();

// new class for filter controlling/handling
$control = new tlTestCaseFilterControl($db, 'edit_mode');

$gui = initializeGui($db, $control);
$control->build_tree_menu($gui);

$smarty = new TLSmarty();

$smarty->assign('gui', $gui);
$smarty->assign('control', $control);
$smarty->assign('args', $control->get_argument_string());
$smarty->assign('menuUrl', $gui->menuUrl);

$smarty->display($templateCfg->template_dir . 'tcTree.tpl');


/**
 * Initialize object with information for graphical user interface.
 * 
 * @param tlTestCaseFilterControl $control
 * @return stdClass $gui
 */
function initializeGui(&$dbHandler, &$control) 
{
	$gui = new stdClass();
	$gui->feature = $control->args->feature;
	$gui->treeHeader = lang_get('title_navigator'). ' - ' . lang_get('title_test_spec');

	$lblkey = (config_get('testcase_reorder_by') == 'NAME') ? '_alpha' : '_externalid';
	$gui->btn_reorder_testcases = lang_get('btn_reorder_testcases' . $lblkey);

	$feature_path = array('edit_tc' => "lib/testcases/archiveData.php",
	                      'keywordsAssign' => "lib/keywords/keywordsAssign.php",
	                      'assignReqs' => "lib/requirements/reqTcAssign.php");

	$gui->tree_drag_and_drop_enabled = array('edit_tc' => (has_rights($dbHandler, "mgt_modify_tc") == 'yes'),
	                                         'keywordsAssign' => false,
	                                         'assignReqs' => false);

	$gui->menuUrl = $feature_path[$gui->feature];
	return $gui;
}
?>
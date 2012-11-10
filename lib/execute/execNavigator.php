<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Execution Navigator Widget
 *
 * @filesource	execNavigator.php
 * @package 	TestLink
 * @copyright 	2007-2011, TestLink community
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');
require_once("users.inc.php");
require_once('exec.inc.php');

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$control = new tlTestCaseFilterControl($db, 'execution_mode');

$gui = initializeGui($control);
$control->build_tree_menu($gui);
new dBug($control);
$gui->title = sprintf($gui->title,$control->args->testplan_name);

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
function initializeGui(&$control) 
{
	$gui = new stdClass();
	
	$gui->tproject_id = $control->args->testproject_id;
	$gui->tplan_id = $control->args->testplan_id;
	$gui->menuUrl = 'lib/execute/execSetResults.php';
	$gui->args = $control->get_argument_string();
	$gui->src_workframe = $control->args->basehref . $gui->menuUrl . 
						            "?edit=testproject&id={$control->args->testproject_id}" . 
	                	    "&tplan_id={$control->args->testplan_id}" . $gui->args;
	

	$control->draw_export_testplan_button = true;
	
	$sep1 = config_get('gui_title_separator_1');
	$buildSelection = $control->settings['setting_build']['selected'];

	$gui->title = lang_get('test_plan') . $sep1 . '%s ' .
				        config_get('gui_separator_open') . lang_get('build') . $sep1 .
				        $control->settings['setting_build']['items'][$buildSelection] . 
				        config_get('gui_separator_close');
	return $gui;
}
?>
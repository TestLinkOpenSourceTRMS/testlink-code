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
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');
require_once("users.inc.php");
require_once('treeMenu.inc.php');
require_once('exec.inc.php');

testlinkInitPage($db);

$templateCfg = templateConfiguration();

$chronos[] = $tstart = microtime(true);
//echo '<br>' . basename(__FILE__) . '::' . __LINE__ . '::Start!!!' . current($chronos);
//reset($chronos);	

$control = new tlTestCaseFilterControl($db, 'execution_mode');
$gui = initializeGui($control);
$control->build_tree_menu($gui);

//$chronos[] = microtime(true);
//$tnow = end($chronos);
//$tprev = prev($chronos);
//echo '<br>' . basename(__FILE__) . '::' . __LINE__ . '::AFTER build_tree_menu()' . $tnow;
//$t_elapsed = number_format( $tnow - $tprev, 4);
//echo '<br> ' . basename(__FILE__) . ' Elapsed (sec):' . $t_elapsed;
//reset($chronos);	
//$t_elapsed = number_format( $tnow - $tstart, 4);
//echo '<br> ' . basename(__FILE__) . ' FROM START Elapsed (sec):' . $t_elapsed;


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
	
	$gui->menuUrl = 'lib/execute/execSetResults.php';
	$gui->args = $control->get_argument_string();
	$gui->src_workframe = $control->args->basehref . $gui->menuUrl .
	                "?edit=testproject&id={$control->args->testproject_id}" . $gui->args;
	
	$control->draw_export_testplan_button = true;
	
	
	$dummy = config_get('results');
	$gui->not_run = $dummy['status_code']['not_run'];
	
	$dummy = config_get('execution_filter_methods');
	$gui->lastest_exec_method = $dummy['status_code']['latest_execution'];

	//new dBug($gui);
	return $gui;
}
?>
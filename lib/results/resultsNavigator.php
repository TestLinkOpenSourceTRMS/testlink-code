<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * @version $Id: resultsNavigator.php,v 1.56 2010/02/17 21:32:44 franciscom Exp $ 
 * @author	Martin Havlat <havlat@users.sourceforge.net>
 * 
 * Scope: Launcher for Test Results and Metrics.
 *
 * rev :
 *      20071109,11 - havlatm - move data to config + refactorization; removed obsolete build list
 * 							 move functions into class  
 *      20070930 - franciscom - 
 *      20070916 - franciscom - added logic to choose test plan
 *      20070826 - franciscom - disable resultsImport
 * 
 **/
require('../../config.inc.php');
require_once('common.php');
require_once('reports.class.php');
testlinkInitPage($db,true,false,"checkRights");

$templateCfg = templateConfiguration();

$args = init_args($tlCfg);
$gui = new stdClass();
$gui->workframe = $_SESSION['basehref'] . "lib/general/staticPage.php?key=showMetrics";
$gui->do_report = array('status_ok' => 1, 'msg' => '');
$gui->tplan_id = $args->tplan_id;
$btsEnabled = config_get('bugInterfaceOn');

$tplan_mgr = new testplan($db);
$reports_magic = new tlReports($db, $gui->tplan_id);

// -----------------------------------------------------------------------------
// Do some checks to understand if reports make sense

// Check if there are linked test cases to the choosen test plan.
$tc4tp_count = $reports_magic->get_count_testcase4testplan();
tLog('TC in TP count = ' . $tc4tp_count);
if( $tc4tp_count == 0)
{
   // Test plan without test cases
   $gui->do_report['status_ok'] = 0;
   $gui->do_report['msg'] = lang_get('report_tplan_has_no_tcases');       
}

// Build qty
$build_count = $reports_magic->get_count_builds();
tLog('Active Builds count = ' . $build_count);
if( $build_count == 0)
{
   // Test plan without builds can have execution data
   $gui->do_report['status_ok'] = 0;
   $gui->do_report['msg'] = lang_get('report_tplan_has_no_build');       
}

// -----------------------------------------------------------------------------
// get navigation data
$gui->menuItems = array();
$gui->tplans = array();
if($gui->do_report['status_ok'])
{
	// create a list or reports
	$gui->menuItems = $reports_magic->get_list_reports($btsEnabled,$args->optReqs, 
	                                                  $tlCfg->reports_formats[$args->format]);
}

// get All test Plans for combobox
$gui->tplans = $tplan_mgr->getTestPlanNamesById($args->tproject_id,FALSE);


$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->assign('arrReportTypes', localize_array($tlCfg->reports_formats));
$smarty->assign('selectedReportType', $args->format);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function init_args($tlCfg)
{
	$iParams = array(
		"format" => array(tlInputParameter::INT_N),
		"tplan_id" => array(tlInputParameter::INT_N),
	);
	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);
	
	if (is_null($args->format))
		$args->format = sizeof($tlCfg->reports_formats) ? key($tlCfg->reports_formats) : null;
	if (is_null($args->tplan_id))
		$args->tplan_id = $_SESSION['testplanID'];
	
	$_SESSION['resultsNavigator_testplanID'] = $args->tplan_id;
	$_SESSION['resultsNavigator_format'] = $args->format;
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
   	$args->userID = $_SESSION['userID'];
    // $args->optReqs = $_SESSION['testprojectOptReqs'];
    $args->optReqs = $_SESSION['testprojectOptions']->requirementsEnabled;

    return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>

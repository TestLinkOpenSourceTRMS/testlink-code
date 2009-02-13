<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * @version $Id: resultsNavigator.php,v 1.49 2009/02/13 16:10:01 havlat Exp $ 
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
//@TODO, schlundus, delete if not needed
//require_once('builds.inc.php');
require_once('reports.class.php');
testlinkInitPage($db);
tLog('resultsNavigator.php called');

$templateCfg = templateConfiguration();

$gui = new stdClass();
$gui->workframe = $_SESSION['basehref'] . "lib/general/staticPage.php?key=showMetrics";

$gui->do_report = array('status_ok' => 1, 'msg' => '');
$selectedReportType = null;

$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$gui->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];
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
  	if (isset($_GET['format']))
  	{
		    $selectedReportType = intval($_GET['format']);
		}    
  	else
  	{
		    $selectedReportType = sizeof($tlCfg->reports_formats) ? key($tlCfg->reports_formats) : null;
    }
    
  	// create a list or reports
	  $gui->menuItems = $reports_magic->get_list_reports($btsEnabled,$_SESSION['testprojectOptReqs'], 
		                                                   $tlCfg->reports_formats[$selectedReportType]);

}
$tplans = getAccessibleTestPlans($db, $tproject_id, $_SESSION['userID']);
foreach($tplans as $key => $value)
{
  	$gui->tplans[$value['id']] = $value['name'];
}

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->assign('arrReportTypes', localize_array($tlCfg->reports_formats));
$smarty->assign('selectedReportType', $selectedReportType);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);
?>

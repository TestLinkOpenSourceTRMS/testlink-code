<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * @version $Id: resultsNavigator.php,v 1.21 2007/01/15 01:05:56 kevinlevy Exp $ 
 * @author	Martin Havlat <havlat@users.sourceforge.net>
 * 
 * This page list View of Test Results and Metrics.
 *
 * @todo Reload all workarea if build is changed 
 * @todo xls ouput should be general over all builds
 *
 */
require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');
testlinkInitPage($db);

// there is list of available results and metrics view
$arrData = array(
	array('name' => lang_get('link_report_general_tp_metrics'), 'href' => 'resultsGeneral.php?report_type='), 
	array('name' => lang_get('link_report_overall_build'), 'href' => 'resultsAllBuilds.php?report_type='), 
    array('name' => lang_get('link_report_metrics_more_builds'), 'href' => 'resultsMoreBuilds.php?report_type='), 
	array('name' => lang_get('link_report_failed'), 'href' => 'resultsByStatus.php?type=f&report_type='),
	array('name' => lang_get('link_report_blocked_tcs'), 'href' => 'resultsByStatus.php?type=b&report_type='),
	array('name' => lang_get('link_report_test'), 'href' => 'resultsTC.php?report_type=')
);

// $arrReportTypes = array('normal', 'MS Excel', 'HTML email', 'text email', 'PDF');

$arrReportTypes = array('normal', 'MS Excel', 'HTML email');
if ($g_bugInterfaceOn)
	$arrData[] = array('name' => lang_get('link_report_total_bugs'), 'href' => 'resultsBugs.php');


if ($_SESSION['testprojectOptReqs'])
{
	$arrData[] = array('name' => lang_get('link_report_reqs_coverage'), 'href' => 'resultsReqs.php');
}

// this results are related to selected build
$arrDataB = array(
	array('name' => lang_get('link_report_metrics_active_build'), 'href' => 'resultsBuild.php'),
);

$arrBuilds = getBuilds($db,$_SESSION['testPlanId'], " ORDER BY builds.name ");

if (isset($_GET['build']))
	$selectedBuild = intval($_GET['build']);
else
	$selectedBuild = sizeof($arrBuilds) ? key($arrBuilds) : null;

if (isset($_GET['report_type']))
	$selectedReportType = intval($_GET['report_type']);
else
	$selectedReportType = sizeof($arrReportTypes) ? key($arrReportTypes) : null;

/** comment out until further notice
for now, send email to user

if (isset($_POST['email_to']))
	$email_to = intval($_POST['email_to']);
else
	$email_to = $_SESSION['email'];

if (isset($_POST['email_subject']))
	$email_subject = intval($_POST['email_subject']);
else
	$email_subject = "";
	
print "$email_to, $email_subject <BR>";
*/

$smarty = new TLSmarty;
$smarty->assign('title', 'Navigator - Results');
$smarty->assign('arrData', $arrData);
$smarty->assign('arrDataB', $arrDataB);
$smarty->assign('arrBuilds', $arrBuilds);
$smarty->assign('selectedBuild', $selectedBuild);
$smarty->assign('selectedReportType', $selectedReportType);
$smarty->assign('arrReportTypes', $arrReportTypes);
//$smarty->assign('email_to', $email_to);
$smarty->display('resultsNavigator.tpl');
?>
<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * @version $Id: resultsNavigator.php,v 1.13 2006/10/26 06:06:04 kevinlevy Exp $ 
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
		 // KL - 29072006 - comment out non-functional reports. Add links back in as 
		 // functionality in 1.7 becomes available

		    array('name' => lang_get('link_report_general_tp_metrics'), 'href' => 'resultsGeneral.php'), 
		  array('name' => lang_get('link_report_overall_build'), 'href' => 'resultsAllBuilds.php'), 

    array('name' => lang_get('link_report_metrics_more_builds'), 'href' => 'resultsMoreBuilds.php'), 

	array('name' => lang_get('link_report_failed'), 'href' => 'resultsByStatus.php?type=f'),
	array('name' => lang_get('link_report_blocked_tcs'), 'href' => 'resultsByStatus.php?type=b'),
);

if ($g_bugInterfaceOn)
	$arrData[] = array('name' => lang_get('link_report_total_bugs'), 'href' => 'resultsBugs.php');
	
// 'Query by Start and End Build'
/**	array('name' => lang_get('link_report_test'), 'href' => 'resultsTC.php'),
	array('name' => lang_get('link_report_excel'), 'href' => 'resultsTC.php?format=excel'),
	// KL - 20060618 - temporarly location of links to documentation on classes
	/**
	array('name' => 'database.class API', 'href' => '../functions/database.class.test.php'),
	array('name' => 'testcase.class API', 'href' => '../functions/testcase.class.test.php'),
	array('name' => 'testplan.class API', 'href' => '../functions/testplan.class.test.php'),
	array('name' => 'testproject.class API', 'href' => '../functions/testproject.class.test.php'),
	array('name' => 'testsuite.class API', 'href' => '../functions/testsuite.class.test.php'),
	array('name' => 'tree.class API', 'href' => '../functions/tree.class.test.php'),
	*/

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

$smarty = new TLSmarty;
$smarty->assign('title', 'Navigator - Results');
$smarty->assign('arrData', $arrData);
$smarty->assign('arrDataB', $arrDataB);
$smarty->assign('arrBuilds', $arrBuilds);
$smarty->assign('selectedBuild', $selectedBuild);
$smarty->display('resultsNavigator.tpl');
?>
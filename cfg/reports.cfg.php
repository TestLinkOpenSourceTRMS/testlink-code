<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: reports.cfg.php,v $
 * @version $Revision: 1.5 $
 * @modified $Date: 2008/12/27 18:25:50 $ by $Author: franciscom $
 * @author Martin Havlat
 *
 * SCOPE: Definition of report/metrics menu 
 * 
 * Revision:
 *  20081227 - franciscom - added tcases_without_tester
 *  20081213 - franciscom - replace of old $g_ variables
 *  20081109 - franciscom - added uncovered_testcases
 * 	20080813 - havlatm - removed metrics_tp_builds
 *
 * *********************************************************************************** */

/** supported document formats */
$tlCfg->reports_formats = array(
	'HTML', 
	'MS Excel', 
	'MS Word',
//	'PDF',
	'Email'
);


/** 
 * @VAR $tlCfg->reports_list['report_identifier'] 
 * definition of default set of reports
 * title - title string identifier
 * url - http path (without testPlanId and format)
 * enabled - availability
 * 	1. all (everytime),
 * 	2. bts (if bug tracker is connected only), 
 * 	3. req (if project has available requirements only)
 */
$tlCfg->reports_list['test_plan'] = array( 
	'title' => 'test_plan',
	'url' => 'lib/results/printDocOptions.php?type=testplan',
	'enabled' => 'all',
	'format' => 'HTML,MS Word'
);
$tlCfg->reports_list['metrics_tp_general'] = array( 
	'title' => 'link_report_general_tp_metrics',
	'url' => 'lib/results/resultsGeneral.php',
	'enabled' => 'all',
	'format' => 'HTML,MS Excel,Email'
);
$tlCfg->reports_list['results_custom_query'] = array( 
	'title' => 'link_report_metrics_more_builds',
	'url' => 'lib/results/resultsMoreBuilds.php',
	'enabled' => 'all',
	'format' => 'HTML,MS Excel,Email'
);
$tlCfg->reports_list['list_tc_failed'] = array( 
	'title' => 'link_report_failed',
	'url' => 'lib/results/resultsByStatus.php?type=' . $tlCfg->results['status_code']['failed'],
	'enabled' => 'all',
	'format' => 'HTML,MS Excel,Email'
);
$tlCfg->reports_list['list_tc_blocked'] = array( 
	'title' => 'link_report_blocked_tcs',
	'url' => 'lib/results/resultsByStatus.php?type=' . $tlCfg->results['status_code']['blocked'],
	'enabled' => 'all',
	'format' => 'HTML,MS Excel,Email'
);
$tlCfg->reports_list['list_tc_norun'] = array( 
	'title' => 'link_report_not_run',
	'url' => 'lib/results/resultsByStatus.php?type=' . $tlCfg->results['status_code']['not_run'],
	'enabled' => 'all',
	'format' => 'HTML,MS Excel,Email'
);
$tlCfg->reports_list['results_matrix'] = array( 
	'title' => 'link_report_test',
	'url' => 'lib/results/resultsTC.php',
	'enabled' => 'all',
	'format' => 'HTML,MS Excel,Email'
);
$tlCfg->reports_list['charts_basic'] = array( 
	'title' => 'link_charts',
	'url' => 'lib/results/charts.php',
	'enabled' => 'all',
	'format' => 'HTML'
);
$tlCfg->reports_list['results_requirements'] = array( 
	'title' => 'link_report_reqs_coverage',
	'url' => 'lib/results/resultsReqs.php',
	'enabled' => 'req',
	'format' => 'HTML'
);
$tlCfg->reports_list['list_problems'] = array( 
	'title' => 'link_report_total_bugs',
	'url' => 'lib/results/resultsBugs.php',
	'enabled' => 'bts',
	'format' => 'HTML'
);
$tlCfg->reports_list['uncovered_testcases'] = array( 
	'title' => 'link_report_uncovered_testcases',
	'url' => 'lib/results/uncoveredTestCases.php',
	'enabled' => 'req',
	'format' => 'HTML'
);
$tlCfg->reports_list['tcases_without_tester'] = array( 
	'title' => 'link_report_tcases_without_tester',
	'url' => 'lib/results/testCasesWithoutTester.php',
	'enabled' => 'all',
	'format' => 'HTML'
);
// -------------------------------------------------------------------
?>
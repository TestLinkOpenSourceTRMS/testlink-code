<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 *
 */

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
// $tlCfg->reports_list['MY_REPORT'] = array( 
// 	'title' => 'link_MY_REPORT',
// 	'url' => 'lib/results/fileThatImplementsMYREPORTLogics.php',
// 	'enabled' => 'all',
// 	'format' => 'format_xls' >>>> See cfg/reports.cfg.php
// );
?>
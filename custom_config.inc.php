<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: custom_config.inc.php,v $
 *
 * @version $Revision: 1.10 $
 * @modified $Date: 2008/01/24 21:21:03 $ by $Author: franciscom $
 *
 * SCOPE:
 * Constants and configuration parameters used throughout TestLink 
 * DEFINED BY USERS.
 *
 * Use this page to overwrite configuration parameters (variables and defines)
 * presente in:
 *
 *             config.inc.php
 *             cfg\const.inc.php
 *-----------------------------------------------------------------------------
*/
$g_tree_type='JTREE';
$g_tree_show_testcase_id=1;
$g_exec_cfg->enable_tree_testcase_counters=1;
$g_exec_cfg->enable_tree_colouring=1;

// ----------------------------------------------------------------------------
/** [Test Case Status] */

// $g_tc_status
// $g_tc_status_css
// $g_tc_status_verbose_labels
// $g_tc_status_for_ui
//
//
// These are the possible Test Case statuses.
//
// Localisation Note:
// IMPORTANT:
//           Do not do localisation here, i.e do not change "passed"
//           with the corresponding word in you national language.
//           These strings ARE NOT USED at User interface level.
//
//           Labels showed to users will be created using lang_get()
//           function, getting key from:
//                                      $g_tc_status_verbose_labels
//           example:
//                   lang_get($g_tc_status_verbose_labels["passed"]);
//
//           If you add new statuses, please use custom_strings.txt to add your
//           localized strings
//
$g_tc_status = array (
	"failed"        => 'f',
	"blocked"       => 'b',
	"passed"        => 'p',
	"not_run"       => 'n',
	"not_available" => 'x',
	"unknown"       => 'u',
	"all"           => 'all',
	"tcstatus_1"    => 'q',
	"tcstatus_2"    => 'w'
); 

// Please if you add an status you need to add a corresponding CSS Class
// in the CSS files (see the gui directory)
$g_tc_status_css = array_flip($g_tc_status);


// Used to get localized string to show to users
// key: status
// value: id to use with lang_get() to get the string, from strings.txt
//        or custom_strings.txt
//
$g_tc_status_verbose_labels = array(
  "all"      => "test_status_all_status",
	"not_run"  => "test_status_not_run",
	"passed"   => "test_status_passed",
	"failed"   => "test_status_failed",
	"blocked"  => "test_status_blocked",
	"not_available" => "test_status_not_available",
	"unknown"       => "test_status_unknown",
	"tcstatus_1" => "test_status_new_one",
	"tcstatus_2" => "test_status_new_two"
);


// Used to generate radio and buttons at user interface level.
// Order is important, because this will be display order on User Interface
//
// key   => verbose status as defined in $g_tc_status
// value => string id defined in the strings.txt file, 
//          used to localize the strings.
//
// $g_tc_status_for_ui = array(
// 	"not_run" => "test_status_not_run",
// 	"passed"  => "test_status_passed",
// 	"failed"  => "test_status_failed",
// 	"blocked" => "test_status_blocked"
// );

$g_tc_status_for_ui = array(
	"passed"  => "test_status_passed",
	"failed"  => "test_status_failed",
	"blocked" => "test_status_blocked",
	"tcstatus_1" => "test_status_new_one",
	"tcstatus_2" => "test_status_new_two"
);

// radio button selected by default
$g_tc_status_for_ui_default="blocked";
?>
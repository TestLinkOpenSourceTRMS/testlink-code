<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package 	TestLink
 * @author asimon
 * @copyright 	2005-2010, TestLink community 
 * @version    	CVS: $Id: tcCompareVersions.php,v 1.4.6.1 2011/01/07 18:29:13 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * Compares selected testcase versions with each other.
 *
 * @internal Revisions:
 * 20110107 - asimon - added daisydiff (html diff engine which handles tags well)
 */

require_once("../../config.inc.php");
require_once("common.php");
require('../../third_party/diff/diff.php');
require('../../third_party/daisydiff/src/HTMLDiff.php');

$templateCfg = templateConfiguration();
testlinkInitPage($db);
$smarty = new TLSmarty();

$differ = new diff();
$args = init_args();
$gui = new stdClass();

$tcaseMgr = new testcase($db); 
$tcaseSet = $tcaseMgr->get_by_id($args->tcase_id);

$gui->tc_versions = $tcaseSet;
$gui->tc_id = $args->tcase_id;
$gui->compare_selected_versions = $args->compare_selected_versions;
$gui->context = $args->context;
$gui->version_short = lang_get('version_short');

$labels = array();
$labels["num_changes"] = lang_get("num_changes");
$labels["no_changes"] = lang_get("no_changes");

//if already two versions are selected, display diff
//else display template with versions to select
if ($args->compare_selected_versions) {
	$diff_array = array("summary" => array(),
						"preconditions" => array());

	foreach($tcaseSet as $tcase) {		
		if ($tcase['version'] == $args->version_left) {
			$left = $tcase;
		}
		if ($tcase['version'] == $args->version_right) {
			$right = $tcase;
		}
	}

	foreach($diff_array as $key => $val) {
		//attach a line break so we can use that as separation character for explode
		$diff_array[$key]["left"] = $left[$key];
		$diff_array[$key]["right"] = $right[$key];
	}
	
	//now for the new tcsteps feature
	$diff_array["steps"] = array();
	$diff_array["expected_results"] = array();
		
	if (is_array($left['steps'])) {
		$steps = "";
		$results = "";
		foreach ($left['steps'] as $step) {
			$steps .= str_replace("</p>", "</p>\n", $step['actions']);
			$results .=str_replace("</p>", "</p>\n", $step['expected_results']);
			}
		$diff_array["steps"]["left"] = $steps;
		$diff_array["expected_results"]["left"] = $results;
		}

	if (is_array($right['steps'])) {
		$steps = "";
		$results = "";
		foreach ($right['steps'] as $step) {
			$steps .= str_replace("</p>", "</p>\n", $step['actions']);
			$results .=str_replace("</p>", "</p>\n", $step['expected_results']);
		}
		$diff_array["steps"]["right"] = $steps;
		$diff_array["expected_results"]["right"] = $results;
	}
	
	foreach($diff_array as $key => $val) {
		// 20110107 - new diff engine
		$localized_key = lang_get($key);
		$gui->diff[$key]["count"] = 0;
		
		if ($args->use_daisydiff) {
			// using daisydiff as diffing engine
			$diff = new HTMLDiffer();
			list($differences, $diffcount) = $diff->htmlDiff($val['left'], $val['right']);
			$gui->diff[$key]["diff"] = $differences;
			$gui->diff[$key]["count"] = $diffcount;
		} else {
			// insert line endings so diff is better readable and makes sense (not everything in one line)
			// then cast to array with \n as separating character, differ needs that
			$gui->diff[$key]["left"] = explode("\n", str_replace("</p>", "</p>\n", $val['left']));
			$gui->diff[$key]["right"] = explode("\n", str_replace("</p>", "</p>\n", $val['right']));
		
			$gui->diff[$key]["diff"] = $differ->inline($gui->diff[$key]["left"], $gui->leftID, 
			                                            $gui->diff[$key]["right"], $gui->rightID,$args->context);
			$gui->diff[$key]["count"] = count($differ->changes);
		}
		
		$gui->diff[$key]["heading"] = $localized_key;

		//are there any changes? then display! if not, nothing to show here
		if ($gui->diff[$key]["count"] > 0) {
			$gui->diff[$key]["message"] = sprintf($labels["num_changes"], $localized_key, 
											$diff_array[$key]["count"]);
		} else {
			$gui->diff[$key]["message"] = sprintf($labels["no_changes"], $localized_key);
		}
	}	

	$gui->subtitle = sprintf(lang_get('diff_subtitle_tc'), $args->version_left, 
										$args->version_left, $args->version_right, 
										$args->version_right, $tcaseSet[0]['name']);
} 

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function init_args()
{
	$args = new stdClass();
	
	$args->tcase_id = isset($_REQUEST['testcase_id']) ? $_REQUEST['testcase_id'] : 0;
	$args->compare_selected_versions = isset($_REQUEST['compare_selected_versions']) ? 
											$_REQUEST['compare_selected_versions'] : 0;
	$args->version_left = $_REQUEST['version_left'];
	$args->version_right = $_REQUEST['version_right'];
	
	$diffEngineCfg = config_get("diffEngine");
	if (isset($_REQUEST['context_show_all'])) {
		$args->context = null;
	} else {
		$args->context = (isset($_REQUEST['context']) && is_numeric($_REQUEST['context'])) ? 
											$_REQUEST['context'] : $diffEngineCfg->context;	
	}
	
	// 20110107 - new diff engine
	$args->use_daisydiff = isset($_REQUEST['use_html_comp']);
	
	return $args;
}

?>
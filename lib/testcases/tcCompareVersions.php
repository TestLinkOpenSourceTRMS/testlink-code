<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package 	TestLink
 * @author asimon
 * @copyright 	2005-2009, TestLink community 
 * @version    	CVS: $Id: tcCompareVersions.php,v 1.1 2010/01/06 08:36:41 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * Compares selected testcase versions with each other.
 *
 * @internal Revisions:
 */

require_once("../../config.inc.php");
require_once("common.php");
require('../../third_party/diff/diff.php');

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
	$diff_array = array("summary" => array(), "preconditions" => array(), "steps" => array(), 
	                    "expected_results" => array());

	foreach($tcaseSet as $tcase) {		
		// insert line endings so diff is better readable and makes sense (not everything in one line)
		// then cast to array with \n as separating character, differ needs that

		$side = '';
		if ($tcase['version'] == $args->version_left) {
			$side = 'left';
		}
		if ($tcase['version'] == $args->version_right) {
			$side = 'right';
		}
        if( $side != '' )
        {
			foreach($diff_array as $key => $val) {
				$diff_array[$key][$side] = explode("\n", str_replace("</p>", "</p>\n", $tcase[$key]));
			}
		}
	}
	
	foreach($diff_array as $key => $val) {
		$diff_array[$key]["diff"] = $differ->inline($val["left"], $gui->version_short . $args->version_left, 
		                                            $val["right"], $gui->version_short . $args->version_right, 
		                                            $args->context);
		$diff_array[$key]["count"] = count($differ->changes);
		$diff_array[$key]["heading"] = lang_get($key);
		
		//are there any changes? then display! if not, nothing to show here
		if ($diff_array[$key]["count"] > 0) {
			$diff_array[$key]["message"] = sprintf($labels["num_changes"], $key, $diff_array[$key]["count"]);
		} else {
			$diff_array[$key]["message"] = sprintf($labels["no_changes"], $key);
		}
	}	
	$gui->diff_array = $diff_array;
	$gui->subtitle = sprintf(lang_get('diff_subtitle_tc'), $args->version_left, $args->version_left, 
	                         $args->version_right, $args->version_right, $tcaseSet[0]['name']);
} 

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function init_args()
{
	$args = new stdClass();
	
	$args->tcase_id = isset($_REQUEST['testcase_id']) ? $_REQUEST['testcase_id'] : 0;
	$args->compare_selected_versions = isset($_REQUEST['compare_selected_versions']) ? $_REQUEST['compare_selected_versions'] : 0;
	$args->version_left = $_REQUEST['version_left'];
	$args->version_right = $_REQUEST['version_right'];
	
	$diff_cfg = config_get("diff_cfg");
	if (isset($_REQUEST['context_show_all'])) {
		$args->context = null;
	} else {
		$args->context = (isset($_REQUEST['context']) && is_numeric($_REQUEST['context'])) ? $_REQUEST['context'] : $diff_cfg->context;	
	}
	
	return $args;
}

?>
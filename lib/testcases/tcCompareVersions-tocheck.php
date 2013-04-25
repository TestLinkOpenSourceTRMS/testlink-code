<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package 		TestLink
 * @author 			asimon
 * @copyright 	2005-2013, TestLink community 
 * @filesource	tcCompareVersions.php
 * @link 				http://www.teamst.org/index.php
 *
 * Compares selected testcase versions with each other.
 *
 * @internal revisions
 * @since 1.9.6
 *
 */

require_once("../../config.inc.php");
require_once("common.php");
require('../../third_party/diff/diff.php');
require('../../third_party/daisydiff/src/HTMLDiff.php');

$templateCfg = templateConfiguration();
testlinkInitPage($db);
$smarty = new TLSmarty();




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


$diffEngine = $args->use_daisydiff ? new HTMLDiffer() : new diff();

$panel = array('left' => null,'right' => null);
new dBug($args);
if ($args->compare_selected_versions) 
{

	$diff = buildDiff($tcaseSet,$args);
	new dBug($diff);

	/*
	$diff = array("summary" => array(),"preconditions" => array());
	foreach($tcaseSet as $tcase) 
	{		
		foreach($panel as $side => $dummy)
		{
			$tk = 'version_' . $side;
			if ($tcase['version'] == $args->$tk) 
			{
				$panel[$side] = $tcase;
			}
		}	
	}
	foreach($diff as $key => $val) 
	{
		//attach a line break so we can use that as separation character for explode
		$diff[$key]["left"] = $panel['left'][$key];
		$diff[$key]["right"] = $panel['right'][$key];
	}
	*/


	$diff["steps"] = array();
	$diff["expected_results"] = array();

	reset($panel);
	foreach($panel as $side)
  {
		if(is_array($panel[$side]['steps'])) 
		{
			$diff["steps"][$side] = "";
			$diff["expected_results"][$side] = "";
			foreach ($panel[$side]['steps'] as $item) 
			{
				$diff["steps"][$side] .= str_replace("</p>", "</p>\n", $item['actions']);
				$diff["expected_results"][$side] .= str_replace("</p>", "</p>\n", $item['expected_results']);
			}
		}
  }

	
	foreach($diff as $key => $val) 
	{
		$gui->diff[$key]["heading"] = $localized_key = lang_get($key);
		$gui->diff[$key]["count"] = 0;
		
		if ($args->use_daisydiff) 
		{
			list($gui->diff[$key]["diff"], $gui->diff[$key]["count"]) = $diffEngine->htmlDiff($val['left'], $val['right']);
		} 
		else 
		{
			// insert line endings so diff is better readable and makes sense (not everything in one line)
			// then cast to array with \n as separating character, differ needs that
			$gui->diff[$key]["left"] = explode("\n", str_replace("</p>", "</p>\n", $val['left']));
			$gui->diff[$key]["right"] = explode("\n", str_replace("</p>", "</p>\n", $val['right']));
		
			$gui->diff[$key]["diff"] = $diffEngine->inline($gui->diff[$key]["left"], $gui->leftID, 
			                                               $gui->diff[$key]["right"], $gui->rightID,$args->context);
			$gui->diff[$key]["count"] = count($diffEngine->changes);
		}
		

		//are there any changes? then display! if not, nothing to show here
<<<<<<< Updated upstream
		if ($gui->diff[$key]["count"] > 0) {
			$gui->diff[$key]["message"] = sprintf($labels["num_changes"], $localized_key,$gui->diff[$key]["count"]);
		} else {
=======
		if ($gui->diff[$key]["count"] > 0) 
		{
			$gui->diff[$key]["message"] = sprintf($labels["num_changes"], $localized_key,$diff[$key]["count"]);
		} 
		else 
		{
>>>>>>> Stashed changes
			$gui->diff[$key]["message"] = sprintf($labels["no_changes"], $localized_key);
		}
	}	

	$gui->subtitle = sprintf(lang_get('diff_subtitle_tc'), $args->version_left,$args->version_left, $args->version_right,
										       $args->version_right, $tcaseSet[0]['name']);
} 

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function init_args()
{
	$args = new stdClass();

	$args->use_daisydiff = isset($_REQUEST['use_html_comp']);
	
	$key2set = array('compare_selected_versions' => 0,'version_left' => '','version_right' => '');
	foreach($key2set as $tk => $value)
	{
		$args->$tk = isset($_REQUEST[$tk]) ? $_REQUEST[$tk] : $value;
	}	
	$args->tcase_id = isset($_REQUEST['testcase_id']) ? $_REQUEST['testcase_id'] : 0;
	
	//$args->compare_selected_versions = isset($_REQUEST['compare_selected_versions']) ? 
	//																	 $_REQUEST['compare_selected_versions'] : 0;
	//$args->version_left = $_REQUEST['version_left'];
	//$args->version_right = $_REQUEST['version_right'];
	

	$diffEngineCfg = config_get("diffEngine");
	if (isset($_REQUEST['context_show_all'])) {
		$args->context = null;
	} else {
		$args->context = (isset($_REQUEST['context']) && is_numeric($_REQUEST['context'])) ? 
											$_REQUEST['context'] : $diffEngineCfg->context;	
	}
	
	
	return $args;
}


function buildDiff($items,$argsObj)
{

	$attrKeys = array();
	$attrKeys['simple'] = array("summary","preconditions");
	$attrKeys['complex'] = array("steps","expected_results");
	$dummy = array_merge($attrKeys['simple'],$attrKeys['complex']); 
	foreach($dummy as $gx)
	{
		$diff[$gx] = array();
	}	

	// $panel = array('left' => null,'right' => null);
	$panel = array('left','right');
	foreach($items as $tcase) 
	{		
		foreach($panel as $side)
		{
			$tk = 'version_' . $side;
			if ($tcase['version'] == $argsObj->$tk) 
			{
				foreach($attrKeys['simple'] as $attr)
				{
					$diff[$attr][$side] = $tcase[$attr];
				}
			}
		}	
	}

	/*
	$diff = array("summary" => array(),"preconditions" => array());	
	foreach($diff as $key => $val) 
	{
		$diff[$key]["left"] = $panel['left'][$key];
		$diff[$key]["right"] = $panel['right'][$key];
	}




	$panel = array('left' => null,'right' => null);
	foreach($items as $tcase) 
	{		
		foreach($panel as $side => $dummy)
		{
			$tk = 'version_' . $side;
			if ($tcase['version'] == $argsObj->$tk) 
			{
				$panel[$side] = $tcase;
			}
		}	
	}

	$diff = array("summary" => array(),"preconditions" => array());	
	foreach($diff as $key => $val) 
	{
		$diff[$key]["left"] = $panel['left'][$key];
		$diff[$key]["right"] = $panel['right'][$key];
	}


	$diff["steps"] = array();
	$diff["expected_results"] = array();

	reset($panel);
	foreach($panel as $side)
  {
		if(is_array($panel[$side]['steps'])) 
		{
			$diff["steps"][$side] = "";
			$diff["expected_results"][$side] = "";
			foreach ($panel[$side]['steps'] as $item) 
			{
				$diff["steps"][$side] .= str_replace("</p>", "</p>\n", $item['actions']);
				$diff["expected_results"][$side] .= str_replace("</p>", "</p>\n", $item['expected_results']);
			}
		}
  }
*/
	return $diff;
}

?>
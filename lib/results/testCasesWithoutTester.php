<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource	testCasesWithoutTester.php
 * @author 		Francisco Mancardi - francisco.mancardi@gmail.com
 * 
 * For a test plan, list test cases that has no tester assigned
 *
 * @internal Revisions:
 * 
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$tplan_mgr = new testplan($db);
$args = init_args($tplan_mgr);
checkRights($db,$_SESSION['currentUser'],$args);

$gui = new stdClass();
$gui->pageTitle = lang_get('caption_testCasesWithoutTester');
$gui->warning_msg = '';

$gui->tproject_id = $args->tproject_id;
$gui->tproject_name = $args->tproject_name;
$gui->tplan_name = $args->tplan_name;

$labels = init_labels(array('design' => null, 'execution' => null, 'execution_history' => null));
$edit_img = TL_THEME_IMG_DIR . "edit_icon.png";
$history_img = TL_THEME_IMG_DIR . "history_small.png";

$msg_key = 'no_linked_tcversions';
if($tplan_mgr->count_testcases($args->tplan_id) > 0)
{
	$msg_key = 'all_testcases_have_tester';
	
	// BUGID 3723 - filter test cases by exec_status => not run
	$cfg = config_get('results');
	$filters = array('assigned_to' => TL_USER_NOBODY, 'exec_status' => $cfg['status_code']['not_run']);
	$options = array('output' => 'array', 'details' => 'summary');
	$testCaseSet = $tplan_mgr->get_linked_tcversions($args->tplan_id,$filters, $options);
	if(($gui->row_qty = count($testCaseSet)) > 0)
	{
		$msg_key = '';
		$gui->pageTitle .= " - " . lang_get('match_count') . ":" . $gui->row_qty;

		$tproject_mgr = new testproject($db);
		$prefix = $tproject_mgr->getTestCasePrefix($args->tproject_id);
		unset($tproject_mgr);

		// Collect all tc_id:s and get all test suite paths
		$tcase_set = array();
		foreach ($testCaseSet as $item) {
			$tcase_set[] = $item['tc_id'];
		}
		$tree_mgr = new tree($db);
		$path_info = $tree_mgr->get_full_path_verbose($tcase_set);
		unset($tree_mgr);

		$data = array();
		foreach ($testCaseSet as $item)
		{
			$verbosePath = join(" / ", $path_info[$item['tc_id']]);
			$name = buildExternalIdString($prefix,$item['external_id'] . ': ' . $item['name']);

			// create linked icons
			$exec_history_link = "<a href=\"javascript:openExecHistoryWindow({$item['tc_id']});\">" .
			                     "<img title=\"{$labels['execution_history']}\" src=\"{$history_img}\" /></a> ";
		    $edit_link = "<a href=\"javascript:openTCEditWindow({$gui->tproject_id},{$item['tc_id']});\">" .
			             "<img title=\"{$labels['design']}\" src=\"{$edit_img}\" /></a> ";

			$link = "<!-- " . sprintf("%010d", $item['external_id']) . " -->" . $exec_history_link .
			        $edit_link . $name;

			$row = array($verbosePath,$link);
			if ($args->show_platforms)
			{
				$row[] = $item['platform_name'];
			}

			if($_SESSION['testprojectOptions']->testPriorityEnabled)
			{
				$row[] = $tplan_mgr->urgencyImportanceToPriorityLevel($item['priority']);
			}
			
			$row[] = strip_tags($item['summary']);
			
			$data[] = $row;
		}

		$gui->tableSet[] = buildTable($data, $args->tproject_id, $args->show_platforms,
									  $_SESSION['testprojectOptions']->testPriorityEnabled);
	}
}

$gui->warning_msg = lang_get($msg_key);
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * 
 *
 */
function buildTable($data, $tproject_id, $show_platforms, $priorityMgmtEnabled) 
{
	$key2search = array('testsuite','testcase','platform','priority','summary');
	foreach($key2search as $key)
	{
		$labels[$key] = lang_get($key);
	}				
	$columns[] = array('title_key' => 'testsuite', 'width' => 20);
	
	$columns[] = array('title_key' => 'testcase', 'width' => 25);
	
	if ($show_platforms){
		$columns[] = array('title_key' => 'platform', 'width' => 10);
	}
	
	if ($priorityMgmtEnabled) {
		$columns[] = array('title_key' => 'priority', 'type' => 'priority', 'width' => 5);
	}
	
	$columns[] = array('title_key' => 'summary', 'type' => 'text', 'width' => 40);
	
	$matrix = new tlExtTable($columns, $data, 'tl_table_tc_without_tester');
	
	$matrix->setGroupByColumnName($labels['testsuite']);
	$matrix->setSortByColumnName($labels['testcase']);
	$matrix->addCustomBehaviour('text', array('render' => 'columnWrap'));
	
	if($priorityMgmtEnabled) 
	{
		$matrix->addCustomBehaviour('priority', array('render' => 'priorityRenderer', 'filter' => 'Priority'));
		$matrix->setSortByColumnName($labels['priority']);
	}
	return $matrix;
}

/*
  function: 

  args :
  
  returns: 

*/
function init_args(&$tplan_mgr)
{
	$iParams = array("format" => array(tlInputParameter::INT_N),
					 "tproject_id" => array(tlInputParameter::INT_N),
					 "tplan_id" => array(tlInputParameter::INT_N));

	$args = new stdClass();
	R_PARAMS($iParams,$args);
    
    $args->show_platforms = false;
    $args->tproject_name = '';
    if($args->tproject_id > 0)
    {
		$dummy = $tplan_mgr->tree_manager->get_node_hierarchy_info($args->tproject_id);
    	$args->tproject_name = $dummy['name'];
    }

    $args->tplan_name = '';
    if($args->tplan_id > 0)
    {
		$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
		$args->tplan_name = $tplan_info['name'];  
		$args->show_platforms = $tplan_mgr->hasLinkedPlatforms($args->tplan_id);
    }
    
    return $args;
}

/**
 * 
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = $argsObj->tproject_id;
	$env['tplan_id'] = $argsObj->tplan_id;
	checkSecurityClearance($db,$userObj,$env,array('testplan_metrics'),'and');
}
?>

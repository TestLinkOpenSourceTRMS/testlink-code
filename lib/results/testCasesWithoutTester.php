<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: testCasesWithoutTester.php,v $
 * @version $Revision: 1.17 $
 * @modified $Date: 2010/10/19 09:05:06 $ by $Author: mx-julian $
 * @author Francisco Mancardi - francisco.mancardi@gmail.com
 * 
 * For a test plan, list test cases that has no tester assigned
 *
 * @internal Revisions:
 * 20101019 - Julian - show priority column only if priority is enabled for project
 * 20101015 - Julian - used title_key for exttable columns instead of title to be able to use 
 *                     table state independent from localization
 * 20101012 - Julian - added html comment to properly sort by test case column
 * 20101001 - asimon - added linked icon for testcase editing
 * 20100830 - Julian - Added test case summary column
 * 20100830 - franciscom - refactoring
 * 20100830 - Julian - BUGID 3723 - filter shown test cases by not run status
 * 20100825 - eloff - BUGID 3712 - show only platform if available
 * 20100823 - Julian - added unique table id, default sorting and grouping
 * 20100823 - eloff - Improve report with ext table and information on platforms and prio
 * 
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$tplan_mgr = new testplan($db);
$args = init_args($tplan_mgr);

$gui = new stdClass();
$gui->pageTitle = lang_get('caption_testCasesWithoutTester');
$gui->warning_msg = '';
$gui->tproject_name = $args->tproject_name;
$gui->tplan_name = $args->tplan_name;

$labels = init_labels(array('design' => null, 'execution' => null));
$edit_img = TL_THEME_IMG_DIR . "edit_icon.png";

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
		    $edit_link = "<a href=\"javascript:openTCEditWindow({$item['tc_id']});\">" .
						 "<img title=\"{$labels['design']}\" src=\"{$edit_img}\" /></a> ";

		    $link = "<!-- " . sprintf("%010d", $item['external_id']) . " -->" . $edit_link . $name;

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
					 "tplan_id" => array(tlInputParameter::INT_N));

	$args = new stdClass();
	R_PARAMS($iParams,$args);
    
    $args->show_platforms = false;
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
    $args->tplan_name = '';
    if(!$args->tplan_id)
    {
        $args->tplan_id = isset($_SESSION['testplanID']) ? $_SESSION['testplanID'] : 0;
    }
    
    if($args->tplan_id > 0)
    {
		$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
		$args->tplan_name = $tplan_info['name'];  
		$args->show_platforms = $tplan_mgr->hasLinkedPlatforms($args->tplan_id);

    }
    
    return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>

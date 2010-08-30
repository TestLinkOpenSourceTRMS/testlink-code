<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: testCasesWithoutTester.php,v $
 * @version $Revision: 1.8 $
 * @modified $Date: 2010/08/30 14:41:25 $ by $Author: mx-julian $
 * @author Francisco Mancardi - francisco.mancardi@gmail.com
 * 
 * For a test plan, list test cases that has no tester assigned
 *
 * @internal Revisions:
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
$show_platforms = !is_null($tplan_mgr->getPlatforms($args->tplan_id));

$msg_key = 'no_linked_tcversions';
if($tplan_mgr->count_testcases($args->tplan_id) > 0)
{
	$msg_key = 'all_testcases_have_tester';
	//BUGID 3723 - filter test cases by exec_status => 'n'
	$filters = array('assigned_to' => TL_USER_NOBODY, 'exec_status' => 'n');
	$options = array('output' => 'array');
	$testCaseSet = $tplan_mgr->get_linked_tcversions($args->tplan_id,
		$filters, $options);

	if(($gui->row_qty = count($testCaseSet)) > 0)
	{
		$msg_key = '';
		$gui->pageTitle .= " - " . lang_get('match_count') . ":" . $gui->row_qty;
		$tree_mgr = new tree($db);
		$tproject_mgr = new testproject($db);

		// Collect all tc_id:s and get all test suite paths
		$tcase_set = array();
		foreach ($testCaseSet as $item) {
			$tcase_set[] = $item['tc_id'];
		}
		$path_info = $tree_mgr->get_full_path_verbose($tcase_set);

		$prefix = $tproject_mgr->getTestCasePrefix($args->tproject_id);

		$data = array();
		foreach ($testCaseSet as $item)
		{
			$verbosePath = join(" / ", $path_info[$item['tc_id']]);
			$name = buildExternalIdString($prefix,
				$item['external_id'] . ': ' . $item['name']);
			$link = '<a href="lib/testcases/archiveData.php?edit=testcase&id=' .
				$item['tc_id'] . '">' . $name . '</a>';

			$row = array(
				$verbosePath,
				$link,
			);
			if ($show_platforms)
			{
				$row[] = $item['platform_name'];
			}

			if($_SESSION['testprojectOptions']->testPriorityEnabled)
			{
				$row[] = $tplan_mgr->urgencyImportanceToPriorityLevel($item['priority']);
			}
			$data[] = $row;
		}

		$gui->tableSet[] = buildTable($data, $args, $show_platforms);
	}
}

$gui->warning_msg = lang_get($msg_key);
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function buildTable($data, &$args, $show_platforms) {
	$columns = array(
		lang_get('testsuite'),
		lang_get('testcase'),
	);

	if ($show_platforms)
	{
		$columns[] = lang_get('platform');
	}
	$columns[] = array('title' => lang_get('priority'), 'type' => 'priority');
	
	// unique table id for each project
	$table_id = 'tl_'.$args->tproject_id.'_table_tc_without_tester';
	$matrix = new tlExtTable($columns, $data, $table_id);
	
	// group by test suite , sort by test case
	$matrix->setGroupByColumnName(lang_get('testsuite'));
	$matrix->setSortByColumnName(lang_get('testcase'));
	
	if($_SESSION['testprojectOptions']->testPriorityEnabled)
	{
		$matrix->addCustomBehaviour('priority', array('render' => 'priorityRenderer'));
		// if priority is enabled sort by priority
		$matrix->setSortByColumnName(lang_get('priority'));
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
   $iParams = array(
		"format" => array(tlInputParameter::INT_N),
		"tplan_id" => array(tlInputParameter::INT_N),
	);

	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);
    
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
    }
    
    return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>

<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: testCasesWithoutTester.php,v $
 * @version $Revision: 1.5 $
 * @modified $Date: 2010/08/23 16:30:35 $ by $Author: erikeloff $
 * @author Francisco Mancardi - francisco.mancardi@gmail.com
 * 
 * For a test plan, list test cases that has no tester assigned
 *
 * @internal Revisions:
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

$msg_key = 'no_linked_tcversions';
if($tplan_mgr->count_testcases($args->tplan_id) > 0)
{
	$msg_key = 'all_testcases_have_tester';
	$filters = array('assigned_to' => TL_USER_NOBODY);
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
				$item['platform_name'],
			);

			if($_SESSION['testprojectOptions']->testPriorityEnabled)
			{
				$row[] = $tplan_mgr->urgencyImportanceToPriorityLevel($item['priority']);
			}
			$data[] = $row;
		}

		$gui->tableSet[] = buildTable($data, $args);
	}
}

$gui->warning_msg = lang_get($msg_key);
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function buildTable($data, &$args, $options) {
	$columns = array(
		lang_get('testsuite'),
		lang_get('testcase'),
		lang_get('platform'),
		array('title' => lang_get('priority'), 'type' => 'priority'),
	);
	$matrix = new tlExtTable($columns, $data);
	if($_SESSION['testprojectOptions']->testPriorityEnabled)
	{
		$matrix->addCustomBehaviour('priority', array('render' => 'priorityRenderer'));
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

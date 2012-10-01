<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource	resultsBugs.php
 * 
 * @internal revisions
 */


require('../../config.inc.php');
require_once('common.php');
require_once("lang_api.php");
require_once('displayMgr.php');
require_once('exec.inc.php'); // used for bug string lookup
if (config_get('interface_bugs') != 'NO')
{
  require_once(TL_ABS_PATH. 'lib' . DIRECTORY_SEPARATOR . 'bugtracking' .
               DIRECTORY_SEPARATOR . 'int_bugtracking.php');
}
testlinkInitPage($db);
$templateCfg = templateConfiguration();
$args = init_args();
checkRights($db,$_SESSION['currentUser'],$args);


$gui = new stdClass();
$gui->tproject_id = $args->tproject_id;
$gui->tplan_id = $args->tplan_id;

$gui->warning_msg = '';
$gui->tableSet = null;

$history_img = TL_THEME_IMG_DIR . "history_small.png";
$edit_img = TL_THEME_IMG_DIR . "edit_icon.png";

$openBugs = array();
$resolvedBugs = array();
$arrData = array();

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);

$filters = array();
$options = array('output' => 'array', 'only_executed' => true, 'details' => 'full');
$results = $tplan_mgr->get_linked_tcversions($args->tplan_id, $filters, $options);

$testcase_bugs = array();
foreach ($results as $execution) {
	$tc_id = $execution['tc_id'];
	$mine[] = $execution['exec_id'];
	$exec_id = $execution['exec_id'];
	$bug_urls = buildBugString($db, $exec_id, $openBugs, $resolvedBugs);
	if ($bug_urls)
	{
		// First bug found for this tc
		if (!isset($testcase_bugs[$tc_id])) {
			$suiteName = $execution['tsuite_name'];
			$tc_name = buildExternalIdString($tproject_info['prefix'], $execution['external_id']) . ":" . $execution['name'];

			// add linked icons
			$exec_history_link = "<a href=\"javascript:openExecHistoryWindow({$tc_id});\">" .
			                     "<img title=\"".lang_get('execution_history')."\" src=\"{$history_img}\" /></a> ";
			$edit_link = "<a href=\"javascript:openTCEditWindow({$gui->tproject_id},{$tc_id});\">" .
						 "<img title=\"".lang_get('design')."\" src=\"{$edit_img}\" /></a> ";
			$tc_name = "<!-- " . sprintf("%010d", $execution['external_id']) . " -->" . $exec_history_link .
			           $edit_link . $tc_name;

			$testcase_bugs[$tc_id] = array($suiteName, $tc_name, array());
		}
		foreach ($bug_urls as $url)
		{
			if (!in_array($url, $testcase_bugs[$tc_id][2]))
			{
				$testcase_bugs[$tc_id][2][] = $url;
			}
		}
	}
}
foreach ($testcase_bugs as &$row)
{
	$row[2] = implode("<br/>", $row[2]);
}
$arrData = array_values($testcase_bugs);

if(count($arrData) > 0) {
	// Create column headers
	$columns = getColumnsDefinition();

	// Extract the relevant data and build a matrix
	$matrixData = array();
	
	foreach($arrData as $bugs) {
		$rowData = array();
		
		$rowData[] = $bugs[0];
		$rowData[] = $bugs[1];
		$rowData[] = $bugs[2];
		
		$matrixData[] = $rowData;
	}
	
	$table = new tlExtTable($columns, $matrixData, 'tl_table_bugs_per_test_case');
	
	$table->setGroupByColumnName(lang_get('title_test_suite_name'));
	$table->setSortByColumnName(lang_get('title_test_case_title'));
	$table->sortDirection = 'ASC';
	
	$gui->tableSet = array($table);
} 
else 
{
	$gui->warning_msg = lang_get('no_linked_bugs');
}

$totalOpenBugs = count($openBugs);
$totalResolvedBugs = count($resolvedBugs);
$totalBugs = $totalOpenBugs + $totalResolvedBugs;
$totalCasesWithBugs = count($arrData);

$smarty = new TLSmarty;
$gui->user = $args->user;
$gui->printDate = '';
$gui->tproject_name = $tproject_info['name'];
$gui->tplan_name = $tplan_info['name'];
$gui->title = lang_get('link_report_total_bugs');
$gui->totalOpenBugs = $totalOpenBugs;
$gui->totalResolvedBugs = $totalResolvedBugs;
$gui->totalBugs = $totalBugs;
$gui->totalCasesWithBugs = $totalCasesWithBugs;
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * Register the bug for counting.
 */
function registerBug($bugID, $bugInfo, &$openBugsArray, &$resolvedBugsArray)
{
	$linkString = $bugInfo['link_to_bts'];
	$position = strpos($linkString,"<del>");
	$position2 = strpos($linkString,"</del>");
	if ((!$position) && (!$position2))
	{
		if (!in_array($bugID, $openBugsArray))
			$openBugsArray[] = $bugID;
	}
	else if (!in_array($bugID, $resolvedBugsArray))
	{
		$resolvedBugsArray[] = $bugID;
   	} 
}


/**
 * Get links to bugs related to execution.
 * @param $db
 * @param $execID execution id
 * @param $openBugsArray array to count open bugs
 * @param $resolvedBugsArray array to count resolved bugs
 *
 * @return array List of links to related bugs
 */
function buildBugString(&$db,$execID,&$openBugsArray,&$resolvedBugsArray)
{
	$bugUrls = array();
	$bugInterface = config_get('bugInterface');
	if ($bugInterface)
	{
		$bugs = get_bugs_for_exec($db,$bugInterface,$execID);
		if ($bugs)
		{
			foreach($bugs as $bugID => $bugInfo)
			{
				registerBug($bugID, $bugInfo, $openBugsArray, $resolvedBugsArray);
				$bugUrls[] = $bugInfo['link_to_bts'];
			}
		}
	}
	return $bugUrls;
}

/**
 * get Columns definition for table to display
 *
 */
function getColumnsDefinition()
{
	$colDef = array();
	
	$colDef[] = array('title_key' => 'title_test_suite_name', 'width' => 30, 'type' => 'text');
	$colDef[] = array('title_key' => 'title_test_case_title', 'width' => 30, 'type' => 'text');
	$colDef[] = array('title_key' => 'title_test_case_bugs', 'width' => 40, 'type' => 'text');

	return $colDef;
}


/*
  function: init_args()

  args :
  
  returns: 

*/
function init_args()
{
	$iParams = array("format" => array(tlInputParameter::INT_N),
					 "tplan_id" => array(tlInputParameter::INT_N),
					 "tproject_id" => array(tlInputParameter::INT_N));
	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);
	
	$args->tproject_id = intval($args->tproject_id);
	$args->tplan_id = intval($args->tplan_id);
	
	$args->user = $_SESSION['currentUser'];

	return $args;
}

/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('testplan_metrics'),'and');
}
?>

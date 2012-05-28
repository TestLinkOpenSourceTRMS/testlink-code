<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource	freeTestCases.php
 * @author Francisco Mancardi - francisco.mancardi@gmail.com
 * 
 * For a test project, list FREE test cases, i.e. not assigned to a test plan.
 * 
 * @internak revisions
 * @since 1.9.4
 *
 * @since 1.9.4
 * 20101019 - Julian - show importance column only if priority is enabled for project
 * 20101015 - Julian - used title_key for exttable columns instead of title to be able to use 
 *                     table state independent from localization
 * 20101012 - Julian - added html comment to properly sort by test case column
 * 20101005 - asimon - added linked icon for testcase editing
 * 20100920 - Julian - use exttable
 *                   - added importance column
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db,true,false,"checkRights");

$templateCfg = templateConfiguration();
$tcase_cfg = config_get('testcase_cfg');


$args = init_args();
$tproject_mgr = new testproject($db);

$priorityMgmtEnabled = $_SESSION['testprojectOptions']->testPriorityEnabled;

$msg_key = 'all_testcases_has_testplan';

$edit_img = TL_THEME_IMG_DIR . "edit_icon.png";

// Time tracking
//$tstart = microtime(true);
//$chronos[] = $tstart; $tnow = end($chronos);reset($chronos);
// Memory metrics	
//$mem['usage'][] = memory_get_usage(true); $mem['peak'][] = memory_get_peak_usage(true);

$gui = new stdClass();
$gui->path_info = null;
$gui->tableSet = null;
$gui->freeTestCases = $tproject_mgr->getFreeTestCases($args->tproject_id);

// Time tracking
//$chronos[] = microtime(true);$tnow = end($chronos);$tprev = prev($chronos);
//$t_elapsed_abs = number_format( $tnow - $tstart, 4);
//$t_elapsed = number_format( $tnow - $tprev, 4);
//echo '<br>' . __FUNCTION__ . ' Elapsed relative (sec):' . $t_elapsed . ' Elapsed ABSOLUTE (sec):' . $t_elapsed_abs .'<br>';
//reset($chronos);	


if(!is_null($gui->freeTestCases['items']))
{
	
	$l18n = init_labels(array('low_importance' => null,'medium_importance' => null,
							  'high_importance' => null, 'test_suite' => null, 'design' => null));
	$il = config_get('importance_levels');
	$impCols = array();
	$impCols[$il[LOW]] = "<!-- 1 -->" . $l18n['low_importance'];
	$impCols[$il[MEDIUM]] = "<!-- 2 -->" . $l18n['medium_importance'];
	$impCols[$il[HIGH]] = "<!-- 3 -->" . $l18n['high_importance'];
	
    if($gui->freeTestCases['allfree'])
    { 
        // has no sense display all test cases => display just message.
        $msg_key = 'all_testcases_are_free';
  	}   
  	else
  	{
        $msg_key = '';    
        $tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tproject_id) . $tcase_cfg->glue_character;
        $tcaseSet = array_keys($gui->freeTestCases['items']);
        $tsuites = $tproject_mgr->tree_manager->get_full_path_verbose($tcaseSet,
        															  array('output_format' => 'path_as_string'));
  	    unset($tcaseSet);

		// Time tracking
		//$chronos[] = microtime(true);$tnow = end($chronos);$tprev = prev($chronos);
		//$t_elapsed_abs = number_format( $tnow - $tstart, 4);
		//$t_elapsed = number_format( $tnow - $tprev, 4);
		//echo '<br>' . __FUNCTION__ . ' Elapsed relative (sec):' . $t_elapsed . ' Elapsed ABSOLUTE AFTER get_full_path_verbose(sec):' . $t_elapsed_abs .'<br>';
		//reset($chronos);	
  	    
		$columns = getColumnsDefinition($priorityMgmtEnabled);
	
		// Extract the relevant data and build a matrix
		$matrixData = array();
		foreach($gui->freeTestCases['items'] as &$tcases) 
		{
			$rowData = array();
			$rowData[] = strip_tags($tsuites[$tcases['id']]);
			$rowData[] = "<!-- " . sprintf("%010d", $tcases['tc_external_id']) . " -->" . 
		    		  	 "<a href=\"javascript:openTCEditWindow({$tcases['id']});\">" .
					  	 "<img title=\"{$l18n['design']}\" src=\"{$edit_img}\" /></a> " .
					  	 $tcasePrefix . $tcases['tc_external_id'] . ':' . strip_tags($tcases['name']);
			
			// only add importance column if 
			if($priorityMgmtEnabled)
			{
				$rowData[] = $impCols[$tcases['importance']];
			}
			
			$matrixData[] = $rowData;
		}
		// Time tracking
		//$chronos[] = microtime(true);$tnow = end($chronos);$tprev = prev($chronos);
		//$t_elapsed_abs = number_format( $tnow - $tstart, 4);
		//$t_elapsed = number_format( $tnow - $tprev, 4);
		//echo '<br>' . __FUNCTION__ . ' Elapsed relative (sec):' . $t_elapsed . ' Elapsed ABSOLUTE (sec):' . $t_elapsed_abs .'<br>';
		//reset($chronos);	
		
		$table = new tlExtTable($columns, $matrixData, 'tl_table_test_cases_not_assigned_to_any_test_plan');
		$table->setGroupByColumnName($l18n['test_suite']);
		$table->setSortByColumnName(lang_get(($priorityMgmtEnabled) ? 'importance' : 'test_case'));
		$table->sortDirection = 'DESC';
		$table->showToolbar = true;
		$table->toolbarExpandCollapseGroupsButton = true;
		$table->toolbarShowAllColumnsButton = true;
		
		$gui->tableSet = array($table);
  	}
}


$gui->tproject_name = $args->tproject_name;
$gui->pageTitle = lang_get('report_free_testcases_on_testproject');
$gui->warning_msg = lang_get($msg_key);



$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * get Columns definition for table to display
 *
 */
function getColumnsDefinition($priorityMgmtEnabled)
{
	$colDef = array();
	
	$colDef[] = array('title_key' => 'test_suite', 'type' => 'text');
	$colDef[] = array('title_key' => 'test_case', 'type' => 'text');
	if ($priorityMgmtEnabled) {
		$urgencies_for_filter = array(lang_get('urgency_low'),lang_get('urgency_medium'),lang_get('urgency_high'));
		$colDef[] = array('title_key' => 'importance', 'width' => 20, 'filter' => 'ListSimpleMatch', 'filterOptions' => $urgencies_for_filter);
	}

	return $colDef;
}

/**
 * init_args
 *
 * Collect all inputs (arguments) to page, that can be arrived via $_REQUEST,$_SESSION
 * and creates an stdClass() object where each property is result of mapping page inputs.
 * We have created some sort of 'namespace', thi way we can easy understand which variables
 * has been created for local use, and which have arrived on call.
 *
 */
function init_args()
{
    $iParams = array(
		"tplan_id" => array(tlInputParameter::INT_N),
		"format" => array(tlInputParameter::INT_N),
	);

	$args = new stdClass();
	$pParams = G_PARAMS($iParams,$args);

	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

    return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>



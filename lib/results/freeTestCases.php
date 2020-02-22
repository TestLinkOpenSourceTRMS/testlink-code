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
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db,true,false,"checkRights");

$templateCfg = templateConfiguration();
$tcase_cfg = config_get('testcase_cfg');


$tprojMgr = new testproject($db);
list($args,$gui) = initScript($db,$tprojMgr);

$msg_key = 'all_testcases_has_testplan';
$edit_img = TL_THEME_IMG_DIR . "edit_icon.png";

if( !is_null($gui->freeTestCases['items'])) {
	
	$l10n = init_labels(array('low_importance' => null,
                            'medium_importance' => null,
							              'high_importance' => null, 
                            'test_suite' => null, 'design' => null));
	$il = config_get('importance_levels');
	$impCols = array();
	$impCols[$il[LOW]] = "<!-- 1 -->" . $l10n['low_importance'];
	$impCols[$il[MEDIUM]] = "<!-- 2 -->" . $l10n['medium_importance'];
	$impCols[$il[HIGH]] = "<!-- 3 -->" . $l10n['high_importance'];
	
  if ($gui->freeTestCases['allfree']) { 
    // has no sense display all test cases => display just message.
    $msg_key = 'all_testcases_are_free';
  } else {
    $msg_key = '';    
    $tcasePrefix = $tprojMgr->getTestCasePrefix($args->tproject_id) . $tcase_cfg->glue_character;
    $tcaseSet = array_keys($gui->freeTestCases['items']);
    $tsuites = $tprojMgr->tree_manager->get_full_path_verbose($tcaseSet,
        															  array('output_format' => 'path_as_string'));
  	unset($tcaseSet);

		$columns = getColumnsDefinition($gui->tprojOpt->testPriorityEnabled);
	
		// Extract the relevant data and build a matrix
		$matrixData = array();
		foreach ($gui->freeTestCases['items'] as &$tcases)  {
			$rowData = array();
			$rowData[] = strip_tags($tsuites[$tcases['id']]);
			$rowData[] = "<!-- " . sprintf("%010d", $tcases['tc_external_id']) . 
         " -->" . 
		     "<a href=\"javascript:openTCEditWindow({$tcases['id']});\">" .
				 "<img title=\"{$l10n['design']}\" src=\"{$edit_img}\" /></a> " .
				 $tcasePrefix . $tcases['tc_external_id'] . ':' . 
         strip_tags($tcases['name']);
			
			// only add importance column if 
			if($gui->tprojOpt->testPriorityEnabled) {
				$rowData[] = $impCols[$tcases['importance']];
			}
			$matrixData[] = $rowData;
		}
		
		$table = new tlExtTable($columns, $matrixData, 'tl_table_test_cases_not_assigned_to_any_test_plan');
		$table->setGroupByColumnName($l10n['test_suite']);
		$table->setSortByColumnName(lang_get(($priorityMgmtEnabled) ? 'importance' : 'test_case'));
		$table->sortDirection = 'DESC';
		$table->showToolbar = true;
		$table->toolbarExpandCollapseGroupsButton = true;
		$table->toolbarShowAllColumnsButton = true;
		
		$gui->tableSet = array($table);
  }
}


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
function init_args(&$dbH)
{
  $iParams = array(
    "tproject_id" => array(tlInputParameter::INT_N),
		"tplan_id" => array(tlInputParameter::INT_N),
		"format" => array(tlInputParameter::INT_N));

	$args = new stdClass();
	$pParams = G_PARAMS($iParams,$args);

  if ($args->tproject_id == 0 && $args->tplan_id >0) {
    $tplan = new testplan($dbH);
    $nn = $tplan->get_by_id($args->tplan_id);
    $args->tproject_id = $nn['testproject_id'];
  }

  if ($args->tproject_id == 0) {
    throw new Exception("BAD Test Project ID", 1);
  } 
  $args->tproject_name = testproject::getName($dbH,$args->tproject_id);

  return $args;
}

/**
 *
 */
function initScript(&$dbH,&$tprojMgr) {

  $args = init_args($dbH);
  list($add2args,$gui) = initUserEnv($dbH,$args);
  
  $gui->path_info = null;
  $gui->tableSet = null;
  $gui->freeTestCases = $tprojMgr->getFreeTestCases($args->tproject_id);
  $gui->tproject_name = $args->tproject_name;
  $gui->pageTitle = lang_get('report_free_testcases_on_testproject');

  return array($args,$gui);
}



function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
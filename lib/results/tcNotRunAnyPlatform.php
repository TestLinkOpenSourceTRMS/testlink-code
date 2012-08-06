<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
*
* @author	Andreas Simon
*
* This report shows Test Cases which have not been executed for any Platform.
*
* @internal revisions
* @since 1.9.4
*
*/

require('../../config.inc.php');
require_once('common.php');
require_once('results.class.php');
require_once('displayMgr.php');
require_once('exttable.class.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();

$gui = new stdClass();
$gui->title = lang_get('title_test_report_not_run_on_any_platform');
$gui->printDate = '';
$gui->matrixData = array();

$labels = init_labels(array('design' => null, 'execution' => null, 'execution_history' => null));
$edit_img = TL_THEME_IMG_DIR . "edit_icon.png";
$history_img = TL_THEME_IMG_DIR . "history_small.png";

$buildIDSet = null;
$buildQty = 0;
$tplan_mgr = new testPlanUrgency($db);
$tproject_mgr = new testproject($db);
$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);
$gui->tplan_name = $tplan_info['name'];
$gui->tproject_name = $tproject_info['name'];
$testCaseCfg = config_get('testcase_cfg');
$testCasePrefix = $tproject_info['prefix'] . $testCaseCfg->glue_character;
$mailCfg = buildMailCfg($gui);

$getOpt = array('outputFormat' => 'map');
$gui->platforms = $tplan_mgr->getPlatforms($args->tplan_id,$getOpt);
$platforms_active = !is_null($gui->platforms);

// $re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,ALL_TEST_SUITES,ALL_BUILDS,ALL_PLATFORMS);

$gui->buildInfoSet = $tplan_mgr->get_builds($args->tplan_id, 1); // only active builds
if ($gui->buildInfoSet)
{
	$buildIDSet = array_keys($gui->buildInfoSet);
	$buildQty = sizeOf($buildIDSet);
}

// Get Results on map with access key = test case's parent test suite id
// $executionsMap = $re->getSuiteList();

// lastResultMap provides list of all test cases in plan - data set includes title and suite names
$lastResultMap = $re->getMapOfLastResult();

$resultsCfg = config_get('results');
$urgencyCfg = config_get('urgency');

$gui->number_of_testcases = 0;
$gui->number_of_not_run_testcases = 0;
$gui->warning_msg = '';
$gui->status_msg = '';

$gui->matrix = array();
$gui->tableSet = array();

$cols = array_flip(array('tsuite', 'link', 'priority'));

if ($lastResultMap != null && $platforms_active) 
{
	$versionTag = lang_get('tcversion_indicator');
	foreach ($lastResultMap as $suiteId => $tsuite)  
	{
		foreach ($tsuite as $testCaseId => $platform) 
		{

			$any_result_found = false;
			$rowArray = null;
			$gui->number_of_testcases ++;

			foreach($platform as $platformId => $tcase) 
			{

				if (!$any_result_found) 
				{
					$suiteName = $tcase['suiteName'];
					$name = $tcase['name'];
					$linkedTCVersion = $tcase['version'];
					$external_id = $testCasePrefix . $tcase['external_id'];
					$tc_name = htmlspecialchars("{$external_id}:{$name}",ENT_QUOTES);

					// create linked icons
					$exec_history_link = "<a href=\"javascript:openExecHistoryWindow({$testCaseId});\">" .
										 "<img title=\"{$labels['execution_history']}\" src=\"{$history_img}\" /></a> ";
					$edit_link = "<a href=\"javascript:openTCEditWindow({$testCaseId});\">" .
								 "<img title=\"{$labels['design']}\" src=\"{$edit_img}\" /></a> ";

					$dl = str_replace(" ", "%20", $args->basehref) . 'linkto.php?tprojectPrefix=' . urlencode($tproject_info['prefix']) .
						  '&item=testcase&id=' . urlencode($external_id);
					$mail_link = "<a href='{$dl}'>{$tc_name}</a> ";

					$tcLink = "<!-- " . sprintf("%010d", $tcase['external_id']) . " -->" .
							  $exec_history_link .$edit_link . $tc_name;

					$rowArray[$cols['tsuite']] = $suiteName;
					$rowArray[$cols['link']] = $args->format != FORMAT_HTML ? $mail_link : $tcLink;

					if($_SESSION['testprojectOptions']->testPriorityEnabled)
					{
						$dummy = $tplan_mgr->getPriority($args->tplan_id, array('tcversion_id' => $tcase['tcversion_id']));
						$rowArray[$cols['priority']] = $dummy[$tcase['tcversion_id']]['priority_level'];
					}

					$suiteExecutions = $executionsMap[$suiteId];

					foreach ($buildIDSet as $idx => $buildId) {
						$resultsForBuild = null;
						$lastStatus = $resultsCfg['status_code']['not_run'];

						// iterate over executions for this suite, look for
						// entries that match current:
						// test case id,build id ,platform id
						$qta_suites=sizeOf($suiteExecutions);

						foreach ($suiteExecutions as $jdx => $execution_array) {
							if (($execution_array['testcaseID'] == $testCaseId) &&
								($execution_array['build_id'] == $buildId) &&
								($execution_array['platform_id'] == $platformId) &&
								isset($execution_array['status']))
							{
								$any_result_found = true;
							}
						}
					}
				}
        	} // end of inner foreach()

			if (!$any_result_found) {
				$gui->matrix[] = $rowArray;
				$gui->number_of_not_run_testcases++;
			}
        }	
    }
}

// create and show the table only if we have data to display
if ($gui->number_of_not_run_testcases) 
{
	$gui->tableSet[] = buildMatrix($gui->matrix, $args->format);
}

if ($platforms_active) 
{
	$gui->status_message = sprintf(lang_get('not_run_any_platform_status_msg'),
                                                    $gui->number_of_testcases,
                                                    $gui->number_of_not_run_testcases);
} 
else 
{
	$gui->warning_msg = lang_get('not_run_any_platform_no_platforms');
}

$smarty = new TLSmarty;
$smarty->assign('gui',$gui);
displayReport($templateCfg->template_dir . $templateCfg->default_template, $smarty, $args->format, $mailCfg);


/**
 * 
 *
 */
function init_args()
{
	$iParams = array("format" => array(tlInputParameter::INT_N),
		             "tplan_id" => array(tlInputParameter::INT_N));

	$args = new stdClass();
	R_PARAMS($iParams,$args);
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->basehref = $_SESSION['basehref'];
	
    return $args;
}

/**
 * 
 *
 */
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}

/**
 * Builds ext-js rich table or static HTML table to display matrix results
 *
 * @param $dataSet
 * @param $format
 * @return tlExtTable|tlHTMLTable
 *
 *
 */
function buildMatrix($dataSet, $format)
{
	$columns = array(array('title_key' => 'title_test_suite_name', 'width' => 100),
	                 array('title_key' => 'title_test_case_title', 'width' => 150));

	if($_SESSION['testprojectOptions']->testPriorityEnabled)
	{
		$columns[] = array('title_key' => 'priority', 'type' => 'priority', 'width' => 40);
	}

	if ($format == FORMAT_HTML) 
	{
		
		$matrix = new tlExtTable($columns, $dataSet, 'tl_table_results_tc');
		$matrix->setGroupByColumnName(lang_get('title_test_suite_name'));
		$matrix->sortDirection = 'DESC';

		if($_SESSION['testprojectOptions']->testPriorityEnabled)
		{
			$matrix->addCustomBehaviour('priority', array('render' => 'priorityRenderer', 'filter' => 'Priority'));
			//sort by priority
			$matrix->setSortByColumnName(lang_get('priority'));
		} else {
			//sort by test case
			$matrix->setSortByColumnName(lang_get('title_test_case_title'));
		}
		
		//define table toolbar
		$matrix->showToolbar = true;
		$matrix->toolbarExpandCollapseGroupsButton = true;
		$matrix->toolbarShowAllColumnsButton = true;

	} 
	else 
	{
		$matrix = new tlHTMLTable($columns, $dataSet, 'tl_table_results_tc');
	}
	return $matrix;
}


/**
 * 
 *
 */
function buildMailCfg(&$guiObj)
{
	$labels = array('testplan' => lang_get('testplan'), 'testproject' => lang_get('testproject'));
	$cfg = new stdClass();
	$cfg->cc = ''; 
	$cfg->subject = $guiObj->title . ' : ' . $labels['testproject'] . ' : ' . $guiObj->tproject_name . 
	                ' : ' . $labels['testplan'] . ' : ' . $guiObj->tplan_name;
	                 
	return $cfg;
}
?>

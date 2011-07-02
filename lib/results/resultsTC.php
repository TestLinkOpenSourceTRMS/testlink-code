<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsTC.php,v 1.79 2010/11/01 17:14:48 franciscom Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author 	Chad Rosen
* 
* Show Test Report by individual test case.
*
* @author
* 20110512 - Julian - BUGID 4451 - remove version tag from not run test cases as the shown version
*                                  is only taken from previous build and might not be right
* 20110329 - Julian - BUGID 4341 - added "Last Execution" column
* 20101013 - asimon - use linkto.php for emailed links
* 20101012 - Julian - added html comment to properly sort by test case column
* 20101007 - asimon - BUGID 3857: Replace linked icons in reports if reports get sent by e-mail
* 20100930 - asimon - added icons for testcase editing and execution
* 20100923 - eloff - refactored to use improved table interface
* 20100828 - eloff - adapt to rendering of status column
* 20100823 - Julian - table now uses a unique table id per test project and test plan
* 20100816 - Julian - changed default column width
                    - added default sorting
* 20100723 - asimon - BUGID 3590: crash when clicking testcase link because of missing build id
* 20100719 - eloff - Update due to changes in tlExtTable
* 20100716 - eloff - group by platform column
* 20100715 - eloff - use grouping on first column
*                    Show only one table, group by platform is still possible
* 20100503 - franciscom - BUGID 3419: In "Test result matrix", tests statuses or not colorized
* 20100502 - Julian - BUGID 3418
* 20100424 - franciscom - BUGID 3356	 
* 20091223 - eloff - added HTML tables for reports where JS is unavailable
* 20091221 - eloff - fixed bug when iterating over results
*                    changed link to executed testcase to be an absolute url
* 20091016 - franciscom - fix bug on URL to test case execution
* 20090909 - franciscom - refactored to manage multiple tables when more that one
*                         platform exists.
*
* 20090804 - franciscom - added Eloff contribution
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
$gui->map_status_css = null;
$gui->title = lang_get('title_test_report_all_builds');
$gui->printDate = '';
$gui->matrixCfg  = config_get('resultMatrixReport');
$gui->matrixData = array();

$labels = init_labels(array('design' => null, 'execution' => null));
$exec_img = TL_THEME_IMG_DIR . "exec_icon.png";
$edit_img = TL_THEME_IMG_DIR . "edit_icon.png";

$buildIDSet = null;
$buildQty = 0;
$tplan_mgr = new testPlanUrgency($db);

$tproject_mgr = new testproject($db);
$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);
$gui->tplan_name = $tplan_info['name'];
$gui->tproject_name = $tproject_info['name'];

$testCaseCfg = config_get('testcase_cfg');
$testCasePrefix = $tproject_info['prefix'] . $testCaseCfg->glue_character;;

$mailCfg = buildMailCfg($gui);


$getOpt = array('outputFormat' => 'map');
$gui->platforms = $tplan_mgr->getPlatforms($args->tplan_id,$getOpt);

$show_platforms = !is_null($gui->platforms);
$re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,ALL_TEST_SUITES,ALL_BUILDS,ALL_PLATFORMS);

$gui->buildInfoSet = $tplan_mgr->get_builds($args->tplan_id, 1); //MHT: active builds only
if ($gui->buildInfoSet)
{
	$buildIDSet = array_keys($gui->buildInfoSet);
	$buildQty = sizeOf($buildIDSet);
}

// BUGID 3590
$last_build = end($buildIDSet);

// Get Results on map with access key = test case's parent test suite id
$executionsMap = $re->getSuiteList();

// lastResultMap provides list of all test cases in plan - data set includes title and suite names
$lastResultMap = $re->getMapOfLastResult();

$indexOfArrData = 0;
$resultsCfg = config_get('results');
$urgencyCfg = config_get('urgency');
$map_tc_status_verbose_code = $resultsCfg['code_status'];
$map_tc_status_verbose_label = $resultsCfg['status_label'];

foreach($map_tc_status_verbose_code as $code => $verbose)
{
  if( isset($map_tc_status_verbose_label[$verbose]))
  {
    $label = $map_tc_status_verbose_label[$verbose];
    $map_tc_status_code_langet[$code] = lang_get($label);
    $gui->map_status_css[$code] = $resultsCfg['code_status'][$code] . '_text';
  }
}
$not_run_label=lang_get($resultsCfg['status_label']['not_run']);

// Will add a column
if ($gui->matrixCfg->buildColumns['showStatusLastExecuted'])
{
	$gui->buildInfoSet[] = array('name' => lang_get('result_on_last_build'));
}

if ($gui->matrixCfg->buildColumns['latestBuildOnLeft'])
{
	$gui->buildInfoSet = array_reverse($gui->buildInfoSet);
}

// Every Test suite a row on matrix to display will be created
// One matrix will be created for every platform that has testcases
if ($show_platforms)
{
	$cols = array_flip(array('tsuite', 'link', 'platform', 'priority'));
}
else
{
	$cols = array_flip(array('tsuite', 'link', 'priority'));
}

$gui->matrix = array();
if ($lastResultMap != null) 
{
	$versionTag = lang_get('tcversion_indicator');
	$priorityCache  = null;
	foreach ($lastResultMap as $suiteId => $tsuite) 
	{
		foreach ($tsuite as $testCaseId => $platform) 
		{
			foreach($platform as $platformId => $tcase) 
			{
    			$suiteName = $tcase['suiteName'];
				$name = $tcase['name'];
				$linkedTCVersion = $tcase['version'];
				$external_id = $testCasePrefix . $tcase['external_id'];
				
				// BUGID 3590: crash when clicking testcase link
				$buildId = $tcase['buildIdLastExecuted'] ? $tcase['buildIdLastExecuted'] : $last_build;

			    $tc_name = htmlspecialchars("{$external_id}:{$name}",ENT_QUOTES);

				// create linked icons
				$edit_link = "<a href=\"javascript:openTCEditWindow({$testCaseId});\">" .
							 "<img title=\"{$labels['design']}\" src=\"{$edit_img}\" /></a> ";
			    // 20101007 - asimon - BUGID 3857

				// 20101013 - asimon - use linkto.php for emailed links
				$dl = str_replace(" ", "%20", $args->basehref) . 'linkto.php?tprojectPrefix=' . urlencode($tproject_info['prefix']) .
					  '&item=testcase&id=' . urlencode($external_id);
				$mail_link = "<a href='{$dl}'>{$tc_name}</a> ";

			    $tcLink = "<!-- " . sprintf("%010d", $tcase['external_id']) . " -->" . $edit_link . $tc_name;

				$rowArray = null;
				$rowArray[$cols['tsuite']] = $suiteName;
			    // 20101007 - asimon - BUGID 3857
				$rowArray[$cols['link']] = $args->format != FORMAT_HTML ? $mail_link : $tcLink;
				if ($show_platforms)
				{
					$rowArray[$cols['platform']] = $gui->platforms[$platformId];
				}
				// $rowArray[$cols['tcversion']] = $testCaseVersion;

			
				if($_SESSION['testprojectOptions']->testPriorityEnabled) 
				{
					if( !isset($priorityCache[$tcase['tcversion_id']]) )
					{
						$dummy = $tplan_mgr->getPriority($args->tplan_id, array('tcversion_id' => $tcase['tcversion_id']));
						$priorityCache[$tcase['tcversion_id']] = $dummy[$tcase['tcversion_id']];
					}
					// is better to use code to do reorder instead of localized string ???
					$rowArray[$cols['priority']] = $priorityCache[$tcase['tcversion_id']]['priority_level'];
				}

				$suiteExecutions = $executionsMap[$suiteId];
				
				// BUGID 4341 - Remember the status of the latest execution based on highest execution_id
				$latestExecution = array();
				// reset exec_id for each test case
				$latestExecution['exec_id'] = 0;
				$latestExecution['status'] = null;
			    
			    // Remember the status of the last build that was executed
				// Use array format for status as specified in tlTable::$data
				$lastBuildRun = null;

				// iterate over all builds and lookup results for current test case			
				// Keeps a list of status for every build
				$buildExecStatus = array();
				for ($idx = 0 ; $idx < $buildQty; $idx++) 
				{
					$buildId = $buildIDSet[$idx];
					$resultsForBuild = null;
					$lastStatus = $resultsCfg['status_code']['not_run'];
					
					// BUGID 3419
					$cssClass = $gui->map_status_css[$lastStatus]; 
					
					// iterate over executions for this suite, look for 
					// entries that match current:
					// test case id,build id ,platform id
					$qta_suites=sizeOf($suiteExecutions);

					// build icon for execution link
					$exec_link = "";
					if ($args->format == FORMAT_HTML) {
						$exec_link = "<a href=\"javascript:openExecutionWindow(" .
						             "{$testCaseId}, {$tcase['tcversion_id']}, {$buildId}, " .
						             "{$args->tplan_id}, {$platformId});\">" .
						             "<img title=\"{$labels['execution']}\" src=\"{$exec_img}\" /></a> ";
					}

					for ($jdx = 0; $jdx < $qta_suites; $jdx++) 
					{
						$execution_array = $suiteExecutions[$jdx];
						if (($execution_array['testcaseID'] == $testCaseId) && 
						    ($execution_array['build_id'] == $buildId) &&
						    ($execution_array['platform_id'] == $platformId))
						{
							$status = $execution_array['status'];
							$resultsForBuildText = $map_tc_status_code_langet[$status];
							$resultsForBuildText .= sprintf($versionTag,$execution_array['version']);

							$resultsForBuild = array(
								"value" => $status,
								"text" => $exec_link . $resultsForBuildText,
								"cssClass" => $gui->map_status_css[$status]);

							$lastStatus = $execution_array['status'];
							
							// BUGID 4341 - If execution_id for this test cases within this build
							// has a higher value remember as latest execution
							if ($execution_array['executions_id'] > $latestExecution['exec_id']) {
								$latestExecution['exec_id'] = $execution_array['executions_id'];
								$latestExecution['status'] = array(
									"value" => $status,
									"text" => $exec_link . $resultsForBuildText,
									"cssClass" => $gui->map_status_css[$status]);
							}
						}
					}
					// If no execution was found => not run
					if( $resultsForBuild === null )
					{
						$cssClass = $gui->map_status_css[$resultsCfg['status_code']['not_run']]; 
						$resultsForBuildText = $not_run_label;
						// BUGID 4451 - remove version tag from not run test cases as the
						//              shown version is only taken from previous build and
						//              might not be right
						// $resultsForBuildText .= sprintf($versionTag,$linkedTCVersion);

						$resultsForBuild = array(
							"value" => $resultsCfg['status_code']['not_run'],
							"text" => $exec_link . $resultsForBuildText,
							"cssClass" => $cssClass);
						
						// BUGID 4341 - if status has not been set for prior builds set it to not_run
						if (!isset($latestExecution['status'])) {
							$latestExecution['status'] = array(
								"value" => $resultsCfg['status_code']['not_run'],
								"text" => $exec_link . $resultsForBuildText,
								"cssClass" => $cssClass);
						}
					}
					
					$buildExecStatus[$idx] = $resultsForBuild;
					// keep track of last executed status
					if ($lastBuildRun == null || $lastStatus != $resultsCfg['status_code']['not_run'])
					{
						$lastBuildRun = $resultsForBuild;
					}
				} // end build for loop

			    if ($gui->matrixCfg->buildColumns['showStatusLastExecuted'])
			    {
			    	// Add additional column
			    	$buildExecStatus[] = $lastBuildRun;
                }
                
			    if ($gui->matrixCfg->buildColumns['latestBuildOnLeft']) 
			    {
			    	$buildExecStatus = array_reverse($buildExecStatus);
			    }
			    $rowArray = array_merge($rowArray, $buildExecStatus);
			    
			    // BUGID 4341
				$rowArray[] = $latestExecution['status'];
				
			    $gui->matrix[] = $rowArray;
  			    $indexOfArrData++;
        	}
        }	
    }
} // end if

$gui->tableSet[] =  buildMatrix($gui->buildInfoSet, $gui->matrix, $args->format, $show_platforms, $args);
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
 * Builds ext-js rich table to display matrix results
 *
 * @param map buildSet: info about all Builds analized
 * @param map dataSet: data to be displayed on matrix
 *
 * return tlExtTable
 *
 */
function buildMatrix($buildSet, $dataSet, $format, $show_platforms, &$args)
{
	$columns = array(array('title_key' => 'title_test_suite_name', 'width' => 100),
	                 array('title_key' => 'title_test_case_title', 'width' => 150));
	if ($show_platforms)
	{
		$columns[] = array('title_key' => 'platform', 'width' => 60);
	}
	
	// BUGID 3418: check if test priority is enabled
	if($_SESSION['testprojectOptions']->testPriorityEnabled) 
	{
		$columns[] = array('title_key' => 'priority', 'type' => 'priority', 'width' => 40);
	}
	
	foreach ($buildSet as $build) 
	{
		$columns[] = array('title' => $build['name'], 'type' => 'status', 'width' => 100);
	}
	
	// BUGID 4341 - add new column for last result
	$columns[] = array('title_key' => 'last_execution', 'type' => 'status', 'width' => 100);
	
	if ($format == FORMAT_HTML) 
	{
		
		$matrix = new tlExtTable($columns, $dataSet, 'tl_table_results_tc');
		
		//if platforms feature is enabled group by platform otherwise group by test suite
		$group_name = ($show_platforms) ? lang_get('platform') : lang_get('title_test_suite_name');
		$matrix->setGroupByColumnName($group_name);
		
		$matrix->sortDirection = 'DESC';

		// BUGID 3418: check if test priority is enabled
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

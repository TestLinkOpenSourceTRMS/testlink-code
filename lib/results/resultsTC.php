<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsTC.php,v 1.44 2009/08/05 07:27:26 franciscom Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author 	Chad Rosen
* 
* Show Test Report by individual test case.
*
* @author 
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
$gui->map_label_css = null;
$gui->title = lang_get('title_test_report_all_builds');
$gui->printDate = '';
$gui->matrixCfg  = config_get('resultMatrixReport');
$gui->matrixData = array();

$buildIDSet = null;

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);

$gui->tplan_name = $tplan_info['name'];
$gui->tproject_name = $tproject_info['name'];

$testCaseCfg = config_get('testcase_cfg');
$testCasePrefix = $tproject_info['prefix'] . $testCaseCfg->glue_character;;

$re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,
                  ALL_TEST_SUITES,ALL_BUILDS);

$gui->buildInfoSet = $tplan_mgr->get_builds($args->tplan_id, 1); //MHT: active builds only
if ($gui->buildInfoSet)
{
	$buildIDSet = array_keys($gui->buildInfoSet);
}
$executionsMap = $re->getSuiteList();

// lastResultMap provides list of all test cases in plan - data set includes title and suite names
$lastResultMap = $re->getMapOfLastResult();
$indexOfArrData = 0;

// -----------------------------------------------------------------------------------
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
    $gui->map_label_css[$map_tc_status_code_langet[$code]] = $resultsCfg['code_status'][$code];
  }
}

$not_run_label=lang_get($resultsCfg['status_label']['not_run']);
// -----------------------------------------------------------------------------------

// Will add a column
if ($gui->matrixCfg->buildColumns['showStatusLastExecuted'])
{
	$gui->buildInfoSet[] = array('name' => lang_get('result_on_last_build'));
}

if ($gui->matrixCfg->buildColumns['latestBuildOnLeft'])
{
	$gui->buildInfoSet = array_reverse($gui->buildInfoSet);
}

if ($lastResultMap != null) 
{
	while($suiteId = key($lastResultMap)) 
	{
		$currentSuiteInfo = $lastResultMap[$suiteId];
		
		while ($testCaseId = key($currentSuiteInfo))
		{
			
			$currentTestCaseInfo = $currentSuiteInfo[$testCaseId];

			$suiteName = $currentTestCaseInfo['suiteName'];
			$name = $currentTestCaseInfo['name'];		
			$testCaseVersion = $currentTestCaseInfo['version'];
			$external_id = $testCasePrefix . $currentTestCaseInfo['external_id'];
			
			$rowArray = array($suiteName, $external_id . ":" . $name, $testCaseVersion);

			if ($_SESSION['testprojectOptPriority']) {
				$prio = $re->getPriority($args->tplan_id, $currentTestCaseInfo['tcversion_id']);
				$rowArray[] = $prio;
				// $rowArray[] = lang_get($urgencyCfg["code_label"][$prio]);
			}

			$suiteExecutions = $executionsMap[$suiteId];		

			// Keeps a list of status for every build
			$buildsArray = array();

			// Remember the status of the last build that was executed
			$lastBuildRun = array($resultsCfg['status_code']['not_run'], $not_run_label);
			
			// iterate over all builds and lookup results for current test case			
		 	$qta_loops=sizeOf($buildIDSet);
			for ($idx = 0 ; $idx < $qta_loops; $idx++) 
			{
				$buildId = $buildIDSet[$idx];
				$resultsForBuild =$not_run_label;
				$lastStatus = $resultsCfg['status_code']['not_run'];
				
				// iterate over executions for this suite, look for 
				// entries that match current test case id and build id 
				$qta_suites=sizeOf($suiteExecutions);
				for ($jdx = 0; $jdx < $qta_suites; $jdx++) 
				{
					$execution_array = $suiteExecutions[$jdx];
					if (($execution_array['testcaseID'] == $testCaseId) && 
					    ($execution_array['build_id'] == $buildId)) 
					{
						$resultsForBuild = $map_tc_status_code_langet[$execution_array['status']];	
						$lastStatus = $execution_array['status'];
					}
				}
				$buildsArray[] = array($lastStatus,$resultsForBuild);
				if ($lastStatus != $resultsCfg['status_code']['not_run'])
				{
					$lastBuildRun = array($lastStatus, $resultsForBuild);
				}
				//next($gui->buildInfoSet);
			} // end for loop


			if ($gui->matrixCfg->buildColumns['showStatusLastExecuted'])
			{
				$buildsArray[] = $lastBuildRun;
            }
			if ($gui->matrixCfg->buildColumns['latestBuildOnLeft']) 
			{
				$buildsArray = array_reverse($buildsArray);
			}
			$rowArray = array_merge($rowArray, $buildsArray);
			
			$gui->matrixData[$indexOfArrData] = $rowArray;
  			$indexOfArrData++;

			
			next($currentSuiteInfo);		
		} // end test case loop
		next($lastResultMap);
	} // end suite loop
} // end if

$gui->table = buildMatrix($gui->buildInfoSet, $gui->matrixData);

$smarty = new TLSmarty;
$smarty->assign('gui',$gui);
displayReport($templateCfg->template_dir . $templateCfg->default_template, $smarty, $args->format);



function init_args()
{
	$iParams = array("format" => array(tlInputParameter::INT_N),
		             "tplan_id" => array(tlInputParameter::INT_N));

	$args = new stdClass();
	R_PARAMS($iParams,$args);
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    return $args;
}

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
function buildMatrix($buildSet, $dataSet)
{
	$columns = array(array('title' => lang_get('title_test_suite_name'), 'width' => 100),
		             array('title' => lang_get('title_test_case_title'), 'width' => 350),
		             array('title' => lang_get('version'), 'width' => 50),
		             array('title' => lang_get('priority'), 'type' => 'priority'));
	
	foreach ($buildSet as $build) 
	{
		$columns[] = array('title' => $build['name'], 'type' => 'status', 'width' => 100);
	}
	$matrix = new tlExtTable($columns, $dataSet);
	return $matrix;
}

?>
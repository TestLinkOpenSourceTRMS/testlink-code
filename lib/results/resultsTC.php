<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsTC.php,v 1.62 2010/08/16 13:59:27 mx-julian Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author 	Chad Rosen
* 
* Show Test Report by individual test case.
*
* @author 
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

// Those defines are simply refering to the column number
define('TABLE_GROUP_BY_TESTSUITE', 0);
define('TABLE_GROUP_BY_PLATFORM', 2);

$templateCfg = templateConfiguration();
$args = init_args();

$gui = new stdClass();
$gui->map_status_css = null;
$gui->title = lang_get('title_test_report_all_builds');
$gui->printDate = '';
$gui->matrixCfg  = config_get('resultMatrixReport');
$gui->matrixData = array();

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
$re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,ALL_TEST_SUITES,ALL_BUILDS);

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
				
				$link = '<a href="' . $_SESSION['basehref'] . 'lib/execute/execSetResults.php?' .
				        'level=testcase' .
				        '&build_id=' . $buildId .
				        '&id=' . $testCaseId .
				        '&version_id=' . $tcase['tcversion_id'] .
				        '&tplan_id=' . $args->tplan_id .
				        '&platform_id=' . $platformId .'">' .
				        htmlspecialchars("{$external_id}:{$name}",ENT_QUOTES) . '</a>';

				$rowArray = null;
				$rowArray[$cols['tsuite']] = $suiteName;
				$rowArray[$cols['link']] = $link;
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
			    
			    // Remember the status of the last build that was executed
			    // $lastBuildRun = array($resultsCfg['status_code']['not_run'], $not_run_label);
				$lastBuildRun = array($not_run_label);

				// iterate over all builds and lookup results for current test case			
				// Keeps a list of status for every build
				$buildExecStatus = array();
				for ($idx = 0 ; $idx < $buildQty; $idx++) 
				{
					$buildId = $buildIDSet[$idx];
					$resultsForBuild = $not_run_label;
					$lastStatus = $resultsCfg['status_code']['not_run'];
					
					// BUGID 3419
					$cssClass = $gui->map_status_css[$lastStatus]; 
					
					// iterate over executions for this suite, look for 
					// entries that match current:
					// test case id,build id ,platform id
					$qta_suites=sizeOf($suiteExecutions);
					for ($jdx = 0; $jdx < $qta_suites; $jdx++) 
					{
						$execution_array = $suiteExecutions[$jdx];
						if (($execution_array['testcaseID'] == $testCaseId) && 
						    ($execution_array['build_id'] == $buildId) &&
						    ($execution_array['platform_id'] == $platformId))
						{
							$cssClass = $gui->map_status_css[$execution_array['status']]; 
							$resultsForBuild = $map_tc_status_code_langet[$execution_array['status']];	
							$resultsForBuild .= sprintf($versionTag,$execution_array['version']);
							$resultsForBuild = '<span class="' . $cssClass . '">' . $resultsForBuild . '</span>';

							$lastStatus = $execution_array['status'];
						}
					}
					if( $resultsForBuild == $not_run_label )
					{
						$cssClass = $gui->map_status_css[$resultsCfg['status_code']['not_run']]; 
						$resultsForBuild .= sprintf($versionTag,$linkedTCVersion);
						$resultsForBuild = '<span class="' . $cssClass . '">' . $resultsForBuild . '</span>';
					}
					
					// CRITIC - $buildExecStatus
					// methods on classes: 
					//                    exttable.class.php
					//                    tlHTMLTable.class.php
					// Depends on structure of elements present on $buildExecStatus.
					// Right now element[0] is used a value to be displayed.
					// If you plan to change this, give a careful look to these classes
					//
					$buildExecStatus[$idx] = array($resultsForBuild,$cssClass);
					if ($lastStatus != $resultsCfg['status_code']['not_run'])
					{
						$lastBuildRun = array($resultsForBuild,$cssClass);
					}
					//next($gui->buildInfoSet);
				} // end for loop

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
			    $gui->matrix[] = $rowArray;
  			    $indexOfArrData++;
        	}
        }	
    }
} // end if

$gui->tableSet[] =  buildMatrix($gui->buildInfoSet, $gui->matrix, $args->format, $show_platforms);

new dBug($gui);
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
function buildMatrix($buildSet, $dataSet, $format, $show_platforms)
{
	$columns = array(array('title' => lang_get('title_test_suite_name'), 'width' => 100),
		             array('title' => lang_get('title_test_case_title'), 'width' => 150));
	if ($show_platforms)
	{
		$columns[] = array('title' => lang_get('platform'), 'width' => 60);
	}
	
	// BUGID 3418: check if test priority is enabled
	if($_SESSION['testprojectOptions']->testPriorityEnabled) 
	{
		$columns[] = array('title' => lang_get('priority'), 'type' => 'priority', 'width' => 40);
	}
	
	foreach ($buildSet as $build) 
	{
		$columns[] = array('title' => $build['name'], 'type' => 'status', 'width' => 100);
	}
	
	if ($format == FORMAT_HTML) 
	{
		$matrix = new tlExtTable($columns, $dataSet, 'tl_table_results_tc');
		if ($show_platforms)
		{
			$matrix->groupByColumn = TABLE_GROUP_BY_PLATFORM;
		}
		else
		{
			$matrix->groupByColumn = TABLE_GROUP_BY_TESTSUITE;
		}
		
		$matrix->sortDirection = 'DESC';

		// BUGID 3418: check if test priority is enabled
		if($_SESSION['testprojectOptions']->testPriorityEnabled) 
		{
			$matrix->addCustomBehaviour('priority', array('render' => 'priorityRenderer'));
			//sort by priority
			$matrix->sortByColumn = ($show_platforms) ? 3:2;
		} else {
			//sort by test case
			$matrix->sortByColumn = 1;
		}

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

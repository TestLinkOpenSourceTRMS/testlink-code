<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsTC.php,v 1.42 2009/05/26 19:06:04 schlundus Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author 	Chad Rosen
* 
* Show Test Report by individual test case.
*
* @author 20050919 - fm - refactoring
* 
*/
require('../../config.inc.php');
require_once('common.php');
require_once('results.class.php');
require_once('displayMgr.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();

$arrData = array();

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);

$tplan_name = $tplan_info['name'];
$tproject_name = $tproject_info['name'];

$testCaseCfg = config_get('testcase_cfg');
$testCasePrefix = $tproject_info['prefix'] . $testCaseCfg->glue_character;;

$re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,
                  ALL_TEST_SUITES,ALL_BUILDS);

$arrBuilds = $tplan_mgr->get_builds($args->tplan_id, 1); //MHT: active builds only

$arrBuildIds = null;
if ($arrBuilds)
{
	$arrBuildIds = array_keys($arrBuilds);
}
$executionsMap = $re->getSuiteList();

// lastResultMap provides list of all test cases in plan - data set includes title and suite names
$lastResultMap = $re->getMapOfLastResult();
$indexOfArrData = 0;

// -----------------------------------------------------------------------------------
$resultsCfg = config_get('results');
$map_tc_status_verbose_code = $resultsCfg['code_status'];
$map_tc_status_verbose_label = $resultsCfg['status_label'];

foreach($map_tc_status_verbose_code as $code => $verbose)
{
  if( isset($map_tc_status_verbose_label[$verbose]))
  {
    $label = $map_tc_status_verbose_label[$verbose];
    $map_tc_status_code_langet[$code] = lang_get($label);
    $map_label_css[$map_tc_status_code_langet[$code]] = $resultsCfg['code_status'][$code];
  }
}

$not_run_label=lang_get('test_status_not_run');
// -----------------------------------------------------------------------------------

if ($lastResultMap != null) 
{
	while($suiteId = key($lastResultMap)) {
		$currentSuiteInfo = $lastResultMap[$suiteId];
		
		while ($testCaseId = key($currentSuiteInfo))
		{
			
			$currentTestCaseInfo = $currentSuiteInfo[$testCaseId];

			$suiteName = $currentTestCaseInfo['suiteName'];
			$name = $currentTestCaseInfo['name'];		
			$testCaseVersion = $currentTestCaseInfo['version'];
			$external_id = $testCasePrefix . $currentTestCaseInfo['external_id'];
			
		  $rowArray = array($suiteName, $external_id . ":" . $name, $testCaseVersion);
		
			$suiteExecutions = $executionsMap[$suiteId];		
			
			// iterate over all builds and lookup results for current test case			
		 	$qta_loops=sizeOf($arrBuildIds);
			for ($i = 0 ; $i < $qta_loops; $i++) {
				$buildId = $arrBuildIds[$i];
				$resultsForBuild =$not_run_label;
				$lastStatus = 'n';
				
				// iterate over executions for this suite, look for 
				// entries that match current test case id and build id 
				$qta_suites=sizeOf($suiteExecutions);
				for ($j = 0; $j < $qta_suites; $j++) {
					$execution_array = $suiteExecutions[$j];
					if (($execution_array['testcaseID'] == $testCaseId) && ($execution_array['build_id'] == $buildId)) {
						$resultsForBuild = $map_tc_status_code_langet[$execution_array['status']];	
						$lastStatus = $execution_array['status'];
					}
				}
				array_push($rowArray, array($lastStatus,$resultsForBuild));
				//next($arrBuilds);
			} // end for loop
			
			$arrData[$indexOfArrData] = $rowArray;
  			$indexOfArrData++;

			
			next($currentSuiteInfo);		
		} // end while
		next($lastResultMap);
	} // end while
} // end if

$smarty = new TLSmarty;
$smarty->assign('map_css',$map_label_css);
$smarty->assign('title', lang_get('title_test_report_all_builds'));
$smarty->assign('arrData', $arrData);
$smarty->assign('arrBuilds', $arrBuilds);
$smarty->assign('printDate','');
$smarty->assign('tproject_name', $tproject_name);
$smarty->assign('tplan_name', $tplan_name);

displayReport($templateCfg->template_dir . $templateCfg->default_template, $smarty, $format);

function init_args()
{
	$iParams = array(
		"format" => array(tlInputParameter::INT_N),
		"tplan_id" => array(tlInputParameter::INT_N),
	);

	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);

    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

    return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>
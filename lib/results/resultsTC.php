<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsTC.php,v 1.29 2007/08/27 06:37:44 franciscom Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author 	Chad Rosen
* 
* Show Test Report by individual test case.
*
* @author 20050919 - fm - refactoring
* 
* 20070127 - franciscom
* code to change display of test case status from code to label
*
*/
// There may be an issue with test case results which have 
// multiple executions associated with the same build<BR>";
require('../../config.inc.php');
require_once('common.php');
require_once('results.class.php');
require_once('displayMgr.php');
testlinkInitPage($db);
$tp = new testplan($db);
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0 ;
$arrBuilds = $tp->get_builds($tpID); 

$arrBuildIds = array_keys($arrBuilds);
$arrData = array();
$re = new results($db, $tp, ALL_TEST_SUITES, ALL_BUILDS);
$executionsMap = $re->getSuiteList();

// lastResultMap provides list of all test cases in plan - data set includes title and suite names
$lastResultMap = $re->getMapOfLastResult();
$indexOfArrData = 0;

// -----------------------------------------------------------------------------------
$map_tc_status_verbose_code=array_flip(config_get('tc_status'));
$map_tc_status_verbose_label=config_get('tc_status_for_ui');
foreach($map_tc_status_verbose_code as $code => $verbose )
{
  if( isset($map_tc_status_verbose_label[$verbose]) )
  {
    $label=$map_tc_status_verbose_label[$verbose];
    $map_tc_status_code_langet[$code]=lang_get($label);  
  }
}

$not_run_label=lang_get('test_status_not_run');
// -----------------------------------------------------------------------------------

if ($lastResultMap != null) {
	while($suiteId = key($lastResultMap)) {
		$currentSuiteInfo = $lastResultMap[$suiteId];
		
		while ($testCaseId = key($currentSuiteInfo)){
			
			$currentTestCaseInfo = $currentSuiteInfo[$testCaseId];
			$suiteName = $currentTestCaseInfo['suiteName'];
			$name = $currentTestCaseInfo['name'];		
			
			
			$rowArray = array($suiteName, $testCaseId . ":" . $name);
			$suiteExecutions = $executionsMap[$suiteId];		
			
			// iterate over all builds and lookup results for current test case			
		  $qta_loops=sizeOf($arrBuildIds);
			for ($i = 0 ; $i < $qta_loops; $i++) {
				$buildId = $arrBuildIds[$i];
				$resultsForBuild =$not_run_label;
				
				// iterate over executions for this suite, look for 
				// entries that match current test case id and build id 
				$qta_suites=sizeOf($suiteExecutions);
				for ($j = 0; $j < $qta_suites; $j++) {
					$execution_array = $suiteExecutions[$j];
					if (($execution_array['testcaseID'] == $testCaseId) && ($execution_array['build_id'] == $buildId)) {
						$resultsForBuild = $map_tc_status_code_langet[$execution_array['status']];	
					}
				}	
				array_push($rowArray, $resultsForBuild);
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
// $smarty->assign('title', $_SESSION['testPlanName'] .  " " . lang_get('title_test_report_all_builds'));
$smarty->assign('title', lang_get('title_test_report_all_builds'));
$smarty->assign('arrData', $arrData);
$smarty->assign('arrBuilds', $arrBuilds);

$smarty->assign('tproject_name', $_SESSION['testprojectName'] );
$smarty->assign('tplan_name', $_SESSION['testPlanName'] );

$report_type = isset($_GET['report_type']) ? intval($_GET['report_type']) : null;
if (!isset($_GET['report_type']))
{
	tlog('$_GET["report_type"] is not defined');
	exit();
}
displayReport('resultsTC', $smarty, $report_type);
?>
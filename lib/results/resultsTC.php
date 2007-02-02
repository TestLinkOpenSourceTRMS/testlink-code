<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsTC.php,v 1.25 2007/02/02 06:19:01 kevinlevy Exp $ 
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

// print "KL - 20061127 - should be functional. T
// here may be an issue with test case results which have 
// multiple executions associated with the same build<BR>";
require('../../config.inc.php');
require_once('common.php');
require_once('../functions/results.class.php');
require_once('displayMgr.php');

testlinkInitPage($db);
$tp = new testplan($db);
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0 ;
$arrBuilds = $tp->get_builds($tpID); 
$arrData = array();


$suitesSelected = 'all';
// get results for all builds
$buildsToQuery = 'a';
$re = new results($db, $tp, $suitesSelected, $buildsToQuery);
$executionsMap = $re->getSuiteList();

// lastResultMap provides list of all test cases in plan - data set includes title and suite names
$lastResultMap = $re->getMapOfLastResult();
$indexOfArrData = 0;


// -----------------------------------------------------------------------------------
// 20070127 - franciscom
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
		while ($key = key($arrBuilds)) {
			$buildId = $arrBuilds[$key]['id'];
			$resultsForBuild = "?";
		
			// iterate over executions for this suite, look for 
			// entries that match current test case id and build id 
			$qta_suites=sizeOf($suiteExecutions);
			for ($j = 0; $j < $qta_suites; $j++) {
				$execution_array = $suiteExecutions[$j];
				if (($execution_array['testcaseID'] == $testCaseId) && ($execution_array['build_id'] == $buildId)) {

          // 20070127 - franciscom
					$resultsForBuild = $map_tc_status_code_langet[$execution_array['status']];					
				}
			}	
			array_push($rowArray, $resultsForBuild);
			next($arrBuilds);
		}

		$arrData[$indexOfArrData] = $rowArray;
		$indexOfArrData++;
		next($currentSuiteInfo);		
	}
	next($lastResultMap);
} // end while
} // end if

$smarty = new TLSmarty;
$smarty->assign('title', lang_get('title_test_report_all_builds'));
$smarty->assign('arrData', $arrData);
$smarty->assign('arrBuilds', $arrBuilds);
/**
if ($xls) {
	$smarty->assign('printDate', strftime($g_date_format, time()) );
	$smarty->assign('user', $_SESSION['user']);
}
*/
$report_type = isset($_GET['report_type']) ? intval($_GET['report_type']) : null;
if (!isset($_GET['report_type']))
{
	tlog('$_GET["report_type"] is not defined');
	exit();
}

displayReport('resultsTC', $smarty, $report_type);
?>



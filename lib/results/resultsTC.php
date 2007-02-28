<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsTC.php,v 1.27 2007/02/28 07:08:47 kevinlevy Exp $ 
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
require_once('../functions/results.class.php');
require_once('displayMgr.php');
testlinkInitPage($db);
$tp = new testplan($db);
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0 ;
$arrBuilds = $tp->get_builds($tpID); 

//print "arrBuilds = <BR>";
//print_r($arrBuilds);
//print "<BR>";
$arrBuildIds = array_keys($arrBuilds);
//print "arrBuildIds = <BR>";
//print_r($arrBuildIds);
//print "<BR>";

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
			for ($i = 0 ; $i < sizeOf($arrBuildIds); $i++) {
				$buildId = $arrBuildIds[$i];
				// initialize result
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
$smarty->assign('title', $_SESSION['testPlanName'] .  " " . lang_get('title_test_report_all_builds'));
$smarty->assign('arrData', $arrData);
$smarty->assign('arrBuilds', $arrBuilds);
$report_type = isset($_GET['report_type']) ? intval($_GET['report_type']) : null;
if (!isset($_GET['report_type']))
{
	tlog('$_GET["report_type"] is not defined');
	exit();
}
displayReport('resultsTC', $smarty, $report_type);
?>
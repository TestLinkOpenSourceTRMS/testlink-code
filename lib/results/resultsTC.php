<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsTC.php,v 1.20 2007/01/13 23:43:42 kevinlevy Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author 	Chad Rosen
* 
* Show Test Report by individual test case.
*
* @author 20050919 - fm - refactoring
* 
* 20051022 - scs - correct wrong index
* 20061127 - kl - upgrading to 1.7
*/

//print "KL - 20061127 - should be functional. There may be an issue with test case results which have multiple executions associated with the same build<BR>";
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
		for($i = 0; $i < sizeOf($arrBuilds); $i++) {
			$buildId = $arrBuilds[$i]['id'];
			$resultsForBuild = "?";
		
			// iterate over executions for this suite, look for 
			// entries that match current test case id and build id 
			for ($j = 0; $j < sizeOf($suiteExecutions); $j++) {
				$execution_array = $suiteExecutions[$j];
				if (($execution_array['testcaseID'] == $testCaseId) && ($execution_array['build_id'] == $buildId)) {
					$resultsForBuild = $execution_array['status'];					
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


// is output is excel?
$xls = FALSE;
if (isset($_GET['format']) && $_GET['format'] =='excel'){
	$xls = TRUE;
}

// for excel send header
if ($xls) {
	$re->sendXlsHeader();
}

$smarty = new TLSmarty;
$smarty->assign('title', lang_get('title_test_report_all_builds'));
$smarty->assign('arrData', $arrData);
$smarty->assign('arrBuilds', $arrBuilds);
if ($xls) {
	$smarty->assign('printDate', strftime($g_date_format, time()) );
	$smarty->assign('user', $_SESSION['user']);
}


$report_type = isset($_GET['report_type']) ? intval($_GET['report_type']) : null;
if (!isset($_GET['report_type']))
{
	tlog('$_GET["report_type"] is not defined');
	exit();
}

displayReport('resultsTC.tpl', $smarty, $report_type);
//$smarty->display('resultsTC.tpl');
?>



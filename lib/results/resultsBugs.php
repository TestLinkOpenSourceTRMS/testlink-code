<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* @author kevinlevy
* 20061127 - kl - upgrading to 1.7
*/

require('../../config.inc.php');
require_once('common.php');
require_once('../functions/results.class.php');
require_once("../../lib/functions/lang_api.php");
// exec.inc.php required by buildBugString()
require_once('exec.inc.php');
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
while($suiteId = key($lastResultMap)) {
	$currentSuiteInfo = $lastResultMap[$suiteId];
	$timestampInfo = null;
	$bugInfo = null;

	while ($testCaseId = key($currentSuiteInfo)){
		$currentTestCaseInfo = $currentSuiteInfo[$testCaseId];
		$suiteName = $currentTestCaseInfo['suiteName'];
		$name = $currentTestCaseInfo['name'];		
		
		$suiteExecutions = $executionsMap[$suiteId];
		
		$rowArray = array($suiteName, $testCaseId . ":" . $name);
		for ($i = 0; $i < sizeOf($suiteExecutions); $i++) {
			$currentExecution = $suiteExecutions[$i];
			$currentTimeStamp = $currentExecution['execution_ts'];
			$executions_id = $currentExecution['executions_id'];
			$bugString = '';
			if ($executions_id) {
				$bugString = buildBugString($db, $executions_id);
			}
			if ($bugString) {
				// there is always only 1 timestamp
				// but there can be multiple bugs
				// print "bugString = $bugString <BR>";
				$timestampInfo = $timestampInfo .  $currentTimeStamp . "<BR>";
				$bugInfo = $bugInfo . $bugString ;			
			}		
		}		
		//array_push($rowArray, $timestampInfo);
		array_push($rowArray, $bugInfo);
		$arrData[$indexOfArrData] = $rowArray;
		$indexOfArrData++;	
		next($currentSuiteInfo);		
	}
	next($lastResultMap);
}


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
$smarty->display('resultsBugs.tpl');


function buildBugString(&$db,$execID)
{
	$bugString = null;
	$bugs = get_bugs_for_exec($db,config_get('bugInterface'),$execID);
	if ($bugs)
	{
		foreach($bugs as $bugID => $bugInfo)
		{
			$bugString .= $bugInfo['link_to_bts']."<br />";
		}
	}
	return $bugString;
}
?>



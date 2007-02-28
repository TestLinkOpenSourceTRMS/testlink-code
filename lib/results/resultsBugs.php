<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* @author kevinlevy
* 20061127 - kl - upgrading to 1.7
*/

require('../../config.inc.php');
require_once('../functions/results.class.php');
require_once("../../lib/functions/lang_api.php");
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

// be sure to check if last result map is null or not before accessing
if ($lastResultMap) {
	while($suiteId = key($lastResultMap)) {
		$currentSuiteInfo = $lastResultMap[$suiteId];
		$timestampInfo = null;
		$bugInfo = null;
		while ($testCaseId = key($currentSuiteInfo)){
			// initialize bugInfo
			// $allTimeStamps = array();
			// initialize list of bugs associated with this testCaseId
			$allBugLinks = array();
			$currentTestCaseInfo = $currentSuiteInfo[$testCaseId];
			$suiteName = $currentTestCaseInfo['suiteName'];
			$name = $currentTestCaseInfo['name'];		
			$suiteExecutions = $executionsMap[$suiteId];
			$rowArray = array($suiteName, $testCaseId . ":" . $name);
			for ($i = 0; $i < sizeOf($suiteExecutions); $i++) {
				$currentExecution = $suiteExecutions[$i];
				if ($currentExecution['testcaseID'] == $testCaseId) {
					$executions_id = $currentExecution['executions_id'];
					// initialize bug associated with an execution
					$bugLink = null;
					if ($executions_id) {
						$bugLink = buildBugString($db, $executions_id);
					}
					if ($bugLink) {
						// there is always only 1 timestamp
						// but there can be multiple bugs
						// print "bugString = $bugString <BR>";
						// $timestampInfo = $timestampInfo .  $currentTimeStamp . "<BR>";
						if (!in_array($bugLink, $allBugLinks)) {
							array_push($allBugLinks, $bugLink);
							//array_push($allTimeStamps, $currentTimeStamp);
						}
					}
				}
			}		
			//array_push($rowArray, $timestampInfo);
			$allBugLinksString = implode("", $allBugLinks);
			//$allTimeStampsString = implode("<BR>", $allTimeStamps);
			array_push($rowArray, $allBugLinksString);
			$arrData[$indexOfArrData] = $rowArray;
			$indexOfArrData++;	
			next($currentSuiteInfo);		
		}  // end while
		next($lastResultMap);
	} // end while
} // end if

$smarty = new TLSmarty;
$smarty->assign('title', $_SESSION['testPlanName'] . " " . lang_get('link_report_total_bugs'));
$smarty->assign('arrData', $arrData);
$smarty->assign('arrBuilds', $arrBuilds);
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
<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* @author kevinlevy
* 20061127 - kl - upgrading to 1.7
* 20070610 - kl - added logic to calculate total bugs
*
*/

require('../../config.inc.php');
require_once('results.class.php');
require_once("lang_api.php");
require_once('displayMgr.php');

$openBugs = array();
$resolvedBugs = array();

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
						$bugLink = buildBugString($db, $executions_id, $openBugs, $resolvedBugs);
					}
					if ($bugLink) {
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
			
			// KL - 20070610
			$onlyShowTCsWithBugs = true;
			if (($allBugLinksString) && ($onlyShowTCsWithBugs)) {
				$arrData[$indexOfArrData] = $rowArray;
				$indexOfArrData++;
			}
				
			next($currentSuiteInfo);		
		}  // end while
		next($lastResultMap);
	} // end while
} // end if

$totalOpenBugs = count($openBugs);
$totalResolvedBugs = count($resolvedBugs);
$totalBugs = $totalOpenBugs + $totalResolvedBugs;
$totalCasesWithBugs = count($arrData);

/**
print "total open bugs = $totalOpenBugs <BR>";
print "total resolved bugs = $totalResolvedBugs <BR>";
print "total bugs = $totalBugs <BR>";
print "total test cases with bugs = $totalCasesWithBugs <BR>";
*/

$smarty = new TLSmarty;
$smarty->assign('tproject_name', $_SESSION['testprojectName'] );
$smarty->assign('tplan_name', $_SESSION['testPlanName'] );
$smarty->assign('title', lang_get('link_report_total_bugs'));
$smarty->assign('arrData', $arrData);
$smarty->assign('arrBuilds', $arrBuilds);

$smarty->assign('totalOpenBugs', $totalOpenBugs);
$smarty->assign('totalResolvedBugs', $totalResolvedBugs);
$smarty->assign('totalBugs', $totalBugs);
$smarty->assign('totalCasesWithBugs', $totalCasesWithBugs);

$smarty->display('resultsBugs.tpl');
?>


<?php
function registerBug($bugID, $bugInfo, &$openBugsArray, &$resolvedBugsArray){
   $linkString = $bugInfo[link_to_bts];
   $position = strpos($linkString,"<del>");
   $position2 = strpos($linkString,"</del>");
   if ((!$position)&&(!$position2)) {
	tallyOpenBug($bugID, $openBugsArray);
   }
   else {
	tallyResolvedBug($bugID, $resolvedBugsArray);
   } 
}

function tallyOpenBug($bugID, &$array) {
	if (!in_array($bugID, $array)) {
		array_push($array, $bugID);
	}
}

function tallyResolvedBug($bugID, &$array) {
	if (!in_array($bugID, $array)) {
		array_push($array, $bugID);
	}
}

function buildBugString(&$db,$execID,&$openBugsArray,&$resolvedBugsArray)
{
	$bugString = null;
	$bugs = get_bugs_for_exec($db,config_get('bugInterface'),$execID);
	if ($bugs)
	{
		foreach($bugs as $bugID => $bugInfo)
		{
		    registerBug($bugID, $bugInfo, $openBugsArray, $resolvedBugsArray);
			$bugString .= $bugInfo['link_to_bts']."<br />";
		}
	}
	return $bugString;
}
?>
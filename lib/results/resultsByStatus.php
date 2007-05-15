<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsByStatus.php,v 1.41 2007/05/15 13:56:59 franciscom Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author 	Chad Rosen
* @author     KL
* 
* This page show Test Results over all Builds.
*
* @author 20050919 - fm - refactoring cat/comp name
* 20050901 - scs - added fix for Mantis 81
* 20061126 - KL - upgrade to 1.7
*/
require('../../config.inc.php');
require_once('common.php');
require_once("results.class.php");
require_once('displayMgr.php');
require_once('users.inc.php');

testlinkInitPage($db);

$tpID = isset($_SESSION['testPlanId']) ?  $_SESSION['testPlanId'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'n';
$report_type = isset($_GET['report_type']) ? intval($_GET['report_type']) : null;

$tp = new testplan($db);

if($type == $g_tc_status['failed'])
	$title = lang_get('list_of_failed');
else if($type == $g_tc_status['blocked'])
	$title = lang_get('list_of_blocked');
else if($type == $g_tc_status['not_run'])
	$title = lang_get('list_of_not_run');

else
{
	tlog('wrong value of GET type');
	exit();
}


$SUITES_SELECTED = "all";

// TO-DO : KL define constants and verify localization is not necessary
$builds = 'a';

$tp = new testplan($db);
$arrBuilds = $tp->get_builds($tpID); 
$results = new results($db, $tp, $SUITES_SELECTED, $builds, $type);
$mapOfLastResult = $results->getMapOfLastResult();
$arrOwners = get_users_for_html_options($db, ALL_USERS_FILTER, !ADD_BLANK_OPTION);
$arrDataIndex = 0;
$arrData = null;
if (is_array($mapOfLastResult)) {
  while ($suiteId = key($mapOfLastResult)){
   while($tcId = key($mapOfLastResult[$suiteId])){
		$lastBuildIdExecuted = null;
		$lastBuildIdExecuted = $mapOfLastResult[$suiteId][$tcId]['buildIdLastExecuted'];
		$buildName = null;
		$currentBuildInfo = null;
		if ($lastBuildIdExecuted) {
			$currentBuildInfo = $arrBuilds[$lastBuildIdExecuted];
		}
		$buildName = $currentBuildInfo['name'];
		$notes = $mapOfLastResult[$suiteId][$tcId]['notes'];
		$execution_ts = $mapOfLastResult[$suiteId][$tcId]['execution_ts'];
		$suiteName = $mapOfLastResult[$suiteId][$tcId]['suiteName'];
		$name = $mapOfLastResult[$suiteId][$tcId]['name'];		
		$tester_id = $mapOfLastResult[$suiteId][$tcId]['tester_id'];
		$executions_id = $mapOfLastResult[$suiteId][$tcId]['executions_id'];
		$tcversion_id = $mapOfLastResult[$suiteId][$tcId]['tcversion_id'];		
		$localizedTS = '';
		if ($execution_ts != null) {
		   $localizedTS = localize_dateOrTimeStamp(null,$dummy,'timestamp_format',$execution_ts);
		}
		$bugString = buildBugString($db, $executions_id);
        $bCanExecute = has_rights($db,"tp_execute");
		$testTitle = getTCLink($bCanExecute,$tcId,$tcversion_id,$name,$lastBuildIdExecuted);
		// $tcId . ":" . htmlspecialchars($name)
        $testerName = '';
		if (array_key_exists($tester_id, $arrOwners)) {
		   $testerName = $arrOwners[$tester_id];
		}
		$arrData[$arrDataIndex] = array($suiteName,$testTitle,htmlspecialchars($buildName),
		                                htmlspecialchars($testerName),
		                                htmlspecialchars($execution_ts),
		                                htmlspecialchars($notes),$bugString);
		$arrDataIndex++;
		next($mapOfLastResult[$suiteId]);
	}
	next($mapOfLastResult);
  } // end while
} // end if


$smarty = new TLSmarty;
$smarty->assign('title', $_SESSION['testPlanName'] . " " . $title);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('arrData', $arrData);
$smarty->assign('type', $type);
displayReport('resultsByStatus', $smarty, $report_type);
?>

<?php
/**
* builds bug information for execution id
* written by Andreas, being implemented again by KL
*/

function buildBugString(&$db,$execID)
{
    if (!$execID) {
	  return null;
	}
	
	$bugString = null;
	$bugsOn = config_get('bugInterfaceOn');
	if ($bugsOn == null) {
		return $bugString;
	}
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


/**
* Function returns number of Test Cases in the Test Plan
* @return string Link of Test ID + Title 
*/
function getTCLink($rights, $tcID,$tcversionID, $title, $buildID)
{
	$title = htmlspecialchars($title);
	$suffix = $tcID . ":&nbsp;<b>" . $title. "</b></a>";
	
	$testTitle = '<a href="lib/execute/execSetResults.php?level=testcase&build_id='
				 . $buildID . '&id=' . $tcID.'&version_id='.$tcversionID.'">';
	$testTitle .= $suffix;
		
	return $testTitle;
}
?>

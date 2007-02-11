<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsByStatus.php,v 1.37 2007/02/11 01:55:14 kevinlevy Exp $ 
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
require_once('../functions/common.php');
require_once('../functions/exec.inc.php');
require_once("../../lib/functions/results.class.php");
require_once('displayMgr.php');
require_once('../functions/users.inc.php');

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
		$lastBuildIdExecuted = $mapOfLastResult[$suiteId][$tcId]['buildIdLastExecuted'];
		$buildName = null;
		while ($key = key($arrBuilds)) {
			$currentBuildInfo = $arrBuilds[$key];
			if ($currentBuildInfo['id'] == $lastBuildIdExecuted) {
				$buildName = $currentBuildInfo['name'];
			}
		    next($arrBuilds);	
		}
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
		$arrData[$arrDataIndex] = array($suiteName,$testTitle,htmlspecialchars($buildName),htmlspecialchars($testerName),htmlspecialchars($execution_ts),htmlspecialchars($notes),$bugString);
		$arrDataIndex++;
		next($mapOfLastResult[$suiteId]);
	}
	next($mapOfLastResult);
  } // end while
} // end if

/**  ****************************************************************************
KL - 20061029 - I will review this code and use some logic and thoughts that Andreas has added herer
but for now I will use the same code I am using for the other reports to consolidate development
$tp = new testplan($db);

$arrData = array();
$dummy = null;

$tcs = $tp->get_linked_tcversions($tpID,null,0,1);
$maxBuildID = $tp->get_max_build_id($tpID);

if ($tcs && $maxBuildID)
{
	foreach($tcs as $tcID => $tcInfo)
	{
		$tcMgr = new testcase($db); 
		$exec = $tcMgr->get_last_execution($tcID,$tcInfo['tcversion_id'],$tpID,$maxBuildID,0);
		if (!$exec)
			$exec = $tcMgr->get_last_execution($tcID,$tcInfo['tcversion_id'],$tpID,null,0);
		
		if ($exec)
		{
			$e = current($exec);
			if ($e['status'] != $type)
				continue;
			
			$localizedTS = localize_dateOrTimeStamp(null,$dummy,'timestamp_format',$e['execution_ts']);
			$ts = new testsuite($db);
			$tsData = $ts->get_by_id($e['tsuite_id']);
			$testTitle = getTCLink($bCanExecute,$tcID,$tcInfo['tcversion_id'],$e['name'],$e['build_id']);
			$arrData[] = 	array(
									htmlspecialchars($tsData['name']),
									$testTitle, 
									htmlspecialchars($e['build_name']),
									htmlspecialchars(format_username(array('first' => $e['tester_first_name'],
														  'last' => $e['tester_last_name'], 
														  'login' => $e['tester_login']))), 
									htmlspecialchars($localizedTS), 
									htmlspecialchars($e['execution_notes']),
									buildBugString($db,$e['execution_id']),
								);	
		}
	}
}
*/

/**
* builds bug information for execution id
* written by Andreas, being implemented again by KL
*/
function buildBugString(&$db,$execID)
{
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

$smarty = new TLSmarty;
$smarty->assign('title', $title);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('arrData', $arrData);

displayReport('resultsByStatus', $smarty, $report_type);
?>

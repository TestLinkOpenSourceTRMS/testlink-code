<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsByStatus.php,v 1.13 2006/10/30 00:50:48 kevinlevy Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author 	Chad Rosen
* 
* This page show Test Results over all Builds.
*
* @author 20050919 - fm - refactoring cat/comp name
* 20050901 - scs - added fix for Mantis 81
*/
require('../../config.inc.php');
require_once('common.php');
//require_once('builds.inc.php');	
//require_once('results.inc.php');
require_once('exec.inc.php');
require_once("../../lib/functions/lang_api.php");
require_once("../../lib/functions/results.class.php");
testlinkInitPage($db);
$tp = new testplan($db);
$type = isset($_GET['type']) ? $_GET['type'] : 'n';
//print "type = $type <BR>";

if($type == $g_tc_status['failed'])
	$title = lang_get('list_of_failed');
else if($type == $g_tc_status['blocked'])
	$title = lang_get('list_of_blocked');
else
{
	tlog('wrong value of GET type');
	exit();
}

$tpID = isset($_SESSION['testPlanId']) ?  $_SESSION['testPlanId'] : 0;
$bCanExecute = has_rights($db,"tp_execute");
$SUITES_SELECTED = "all";

// TO-DO : KL define constants and verify localization is not necessary
$builds = 'a';

$tp = new testplan($db);
$results = new results($db, $tp, $SUITES_SELECTED, $builds, $type);
$mapOfLastResult = $results->getMapOfLastResult();
//print "map of last results = <BR>";
//print_r($mapOfLastResult);
//print "<BR>";

$arrDataIndex = 0;
while ($suiteId = key($mapOfLastResult)){
//	print "suiteId = $suiteId <BR>";
	while($tcId = key($mapOfLastResult[$suiteId])){
//		print "tcId = $tcId <BR>";
		$lastBuildIdExecuted = $mapOfLastResult[$suiteId][$tcId]['buildIdLastExecuted'];
//		print "lastBuildIdExecuted = $lastBuildIdExecuted <BR>";
		$arrData[$arrDataIndex] = array("suiteId=". $suiteId,"tcID=" . $tcId,"buildId=" . $lastBuildIdExecuted,'run by','date','notes','bugs');
		$arrDataIndex++;
		next($mapOfLastResult[$suiteId]);
	}
	next($mapOfLastResult);
}

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

*/

$smarty = new TLSmarty;
$smarty->assign('title', $title);
$smarty->assign('arrData', $arrData);
$smarty->display('resultsByStatus.tpl');
?>
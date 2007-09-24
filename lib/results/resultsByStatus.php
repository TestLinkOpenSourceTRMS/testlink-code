<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsByStatus.php,v 1.50 2007/09/24 08:43:28 franciscom Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author Chad Rosen
* @author KL
* 
*
* rev : 
*       20070908 - franciscom - change column qty on arrData is status not_run
*                               to have nice html table
*       20070623 - franciscom - BUGID 911
*/
require('../../config.inc.php');
require_once('common.php');
require_once("results.class.php");
require_once('displayMgr.php');
require_once('users.inc.php');

testlinkInitPage($db);
$dummy=null;

$type = isset($_GET['type']) ? $_GET['type'] : 'n';
$report_type = isset($_GET['report_type']) ? intval($_GET['report_type']) : null;

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);
$tcase_mgr = new testcase($db);


$tplan_id=$_REQUEST['tplan_id'];
$tproject_id=$_SESSION['testprojectID'];

$tplan_info = $tplan_mgr->get_by_id($tplan_id);
$tproject_info = $tproject_mgr->get_by_id($tproject_id);


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

$arrBuilds = $tplan_mgr->get_builds($tplan_id); 
$lastBuildID = $tplan_mgr->get_max_build_id($tplan_id,1,1);
$results = new results($db, $tplan_mgr, $tproject_info, $tplan_info, ALL_TEST_SUITES, ALL_BUILDS);

$mapOfLastResult = $results->getMapOfLastResult();
$arrOwners = get_users_for_html_options($db, ALL_USERS_FILTER, !ADD_BLANK_OPTION);
$arrDataIndex = 0;
$arrData = null;
if (is_array($mapOfLastResult)) {
  while ($suiteId = key($mapOfLastResult)){
   while($tcId = key($mapOfLastResult[$suiteId])){
		$lastBuildIdExecuted = $mapOfLastResult[$suiteId][$tcId]['buildIdLastExecuted'];
		$result = $mapOfLastResult[$suiteId][$tcId]['result'];
		if ($result == $type)
		{
			$currentBuildInfo = null;
			if ($lastBuildIdExecuted) {
				$currentBuildInfo = $arrBuilds[$lastBuildIdExecuted];
			}
			else if ($type == $g_tc_status['not_run'])
			{
				$lastBuildIdExecuted = $lastBuildID;
			}
			
			$buildName = $currentBuildInfo['name'];
			
			$notes = $mapOfLastResult[$suiteId][$tcId]['notes'];
			$suiteName = $mapOfLastResult[$suiteId][$tcId]['suiteName'];
			$name = $mapOfLastResult[$suiteId][$tcId]['name'];		
			$tester_id = $mapOfLastResult[$suiteId][$tcId]['tester_id'];
			$executions_id = $mapOfLastResult[$suiteId][$tcId]['executions_id'];
			$tcversion_id = $mapOfLastResult[$suiteId][$tcId]['tcversion_id'];		
			
			// ------------------------------------------------------------------------------------
			// 20070623 - BUGID 911 - no need to localize, is already localized
			$execution_ts = $mapOfLastResult[$suiteId][$tcId]['execution_ts'];
			$localizedTS = '';
			if ($execution_ts != null) {
			   $localizedTS = $execution_ts;
			}
			// ------------------------------------------------------------------------------------
			
			$bugString = buildBugString($db, $executions_id);
	        $bCanExecute = has_rights($db,"tp_execute");
			$testTitle = getTCLink($bCanExecute,$tcId,$tcversion_id,$name,$lastBuildIdExecuted);
			// $tcId . ":" . htmlspecialchars($name)
	        $testerName = '';
			if (array_key_exists($tester_id, $arrOwners)) {
			   $testerName = $arrOwners[$tester_id];
			}
			
			// 20070917 - franciscom -
			$tcInfo = $tcase_mgr->get_by_id($tcId,$tcversion_id);
   		$testVersion = $tcInfo[0]['version'];

			// 20070908 - franciscom - to avoid bad presentation on smarty
			if($type == $g_tc_status['not_run'])
			{
      	// $arrData[$arrDataIndex] = array($suiteName,$testTitle,$testVersion);
      	$arrData[$arrDataIndex] = array($suiteName,$testTitle,$testVersion);
      	
      }
      else
      {
      	$arrData[$arrDataIndex] = array($suiteName,$testTitle,$testVersion,
      	                                htmlspecialchars($buildName),
			                                  htmlspecialchars($testerName),
			                                  htmlspecialchars($localizedTS),
 						  					                htmlspecialchars($notes),
 						  					                $bugString);
      }
      
            // KL - 20070610 - only increment this var if we added to arrData
		    $arrDataIndex++;
		}
		next($mapOfLastResult[$suiteId]);
	}
	next($mapOfLastResult);
  } // end while
} // end if



$smarty = new TLSmarty;
$smarty->assign('tproject_name', $_SESSION['testprojectName'] );
$smarty->assign('tplan_name', $_SESSION['testPlanName'] );
$smarty->assign('title', $title);
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

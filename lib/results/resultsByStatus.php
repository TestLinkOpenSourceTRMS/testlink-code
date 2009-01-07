<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/
* $Id: resultsByStatus.php,v 1.61 2009/01/07 22:19:46 franciscom Exp $
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author Chad Rosen
* @author KL
*
*
* rev :
*       20081213 - franciscom - refactoring to remove old $g_ config variables
*       20080602 - franciscom - changes due to BUGID 1504
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

$templateCfg = templateConfiguration();

$resultsCfg = config_get('results');
$statusCode = $resultsCfg['status_code'];

$type = isset($_GET['type']) ? $_GET['type'] : 'n';
$report_type = isset($_GET['format']) ? intval($_GET['format']) : null;

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);
$tcase_mgr = new testcase($db);

$tplan_id = $_REQUEST['tplan_id'];
$tproject_id = $_SESSION['testprojectID'];

$tplan_info = $tplan_mgr->get_by_id($tplan_id);
$tproject_info = $tproject_mgr->get_by_id($tproject_id);

$tplan_name = $tplan_info['name'];
$tproject_name = $tproject_info['name'];


$testCaseCfg=config_get('testcase_cfg');
$testCasePrefix = $tproject_info['prefix'] . $testCaseCfg->glue_character;;

$title = null;
foreach( array('failed','blocked','not_run') as $verbose_status )
{
    if( $type == $statusCode[$verbose_status] )
    {
        $title = lang_get('list_of_' . $verbose_status);
        break;
    }  
}
if( is_null($title) )
{
	tlog('wrong value of GET type');
	exit();
}

/*
if($type == $statusCode['failed'])
	$title = lang_get('list_of_failed');
else if($type == $statusCode['blocked'])
	$title = lang_get('list_of_blocked');
else if($type == $statusCode['not_run'])
	$title = lang_get('list_of_not_run');
else
{
}
*/

$arrBuilds = $tplan_mgr->get_builds($tplan_id);
$lastBuildID = $tplan_mgr->get_max_build_id($tplan_id,1,1);
$results = new results($db, $tplan_mgr, $tproject_info, $tplan_info, ALL_TEST_SUITES, ALL_BUILDS);

$mapOfLastResult = $results->getMapOfLastResult();
$arrOwners = getUsersForHtmlOptions($db);
$arrDataIndex = 0;
$arrData = null;

$canExecute = has_rights($db,"tp_execute");
if (is_array($mapOfLastResult)) 
{
    foreach($mapOfLastResult as $suiteId => $suiteContents)
    {
      foreach($suiteContents as $tcId => $tcaseContent)
      {
	  	    $lastBuildIdExecuted = $tcaseContent['buildIdLastExecuted'];
	  	    if ($tcaseContent['result'] == $type)
	  	    {
	  	    	$currentBuildInfo = null;
	  	    	if ($lastBuildIdExecuted) {
	  	    		$currentBuildInfo = $arrBuilds[$lastBuildIdExecuted];
	  	    	}
	  	    	else if ($type == $statusCode['not_run'])
	  	    	{
	  	    		$lastBuildIdExecuted = $lastBuildID;
	  	    	}
          
	  	    	$buildName = $currentBuildInfo['name'];

	  	    	$notes = $tcaseContent['notes'];
	  	    	$suiteName = $tcaseContent['suiteName'];
	  	    	$name = $tcaseContent['name'];
	  	    	$tester_id = $tcaseContent['tester_id'];
	  	    	$executions_id = $tcaseContent['executions_id'];
	  	    	$tcversion_id = $tcaseContent['tcversion_id'];
            
	            // 20080602 - franciscom
	            $testVersion = $tcaseContent['version'];
	               
	  	    	// ------------------------------------------------------------------------------------
	  	    	// 20070623 - BUGID 911 - no need to localize, is already localized
	  	    	$execution_ts = $tcaseContent['execution_ts'];
	  	    	$localizedTS = '';
	  	    	if ($execution_ts != null) {
	  	    	   $localizedTS = $execution_ts;
	  	    	}
	  	    	// ------------------------------------------------------------------------------------
          
	  	    	$bugString = $results->buildBugString($db, $executions_id);
	  	    	$testTitle = getTCLink($canExecute,$tcId,$tcversion_id,$name,$lastBuildIdExecuted,
	  	    	                       $testCasePrefix . $tcaseContent['external_id']);
            $testerName = '';
	  	    	if (array_key_exists($tester_id, $arrOwners))
	  	    	   $testerName = $arrOwners[$tester_id];
          
          
	            // 20080602 - francisco.mancardi@gruppotesi.com
	            // To get executed Version, we can not do anymore this 
	      		// $tcInfo = $tcase_mgr->get_by_id($tcId,$tcversion_id);
	            // $testVersion = $tcInfo[0]['version'];
	          
	  	    	$suiteName = htmlspecialchars($suiteName);
	  	    	if($type == $statusCode['not_run'])
	  	    	{
	  	    		$arrData[] = array($suiteName,$testTitle,$testVersion);
	  	    	}
	  	    	else
				{
	  	    		$arrData[] = array($suiteName,$testTitle,$testVersion,
                                 htmlspecialchars($buildName),
                                 htmlspecialchars($testerName),
                                 htmlspecialchars($localizedTS),
        		                     strip_tags($notes),$bugString);
      			}
	  	    }
	  	
	    }
    } //foreach
} // end if

$smarty = new TLSmarty();
$smarty->assign('tproject_name', $tproject_name );
$smarty->assign('tplan_name', $tplan_name );
$smarty->assign('title', $title);
$smarty->assign('arrBuilds', $arrBuilds);
$smarty->assign('arrData', $arrData);
$smarty->assign('type', $type);
displayReport($templateCfg->template_dir . $templateCfg->default_template, $smarty, $report_type);

/**
* builds bug information for execution id
* written by Andreas, being implemented again by KL
*/

// function buildBugString(&$db,$execID)
// {
//     if (!$execID)
// 	  return null;
// 
// 	$bugString = null;
// 	$bugsOn = config_get('bugInterfaceOn');
// 	if ($bugsOn == null)
// 		return $bugString;
// 
// 	$bugs = get_bugs_for_exec($db,config_get('bugInterface'),$execID);
// 	if ($bugs)
// 	{
// 		foreach($bugs as $bugID => $bugInfo)
// 		{
// 			$bugString .= $bugInfo['link_to_bts']."<br />";
// 		}
// 	}
// 	return $bugString;
// }


/**
* Function returns number of Test Cases in the Test Plan
* @return string Link of Test ID + Title
*/
function getTCLink($rights, $tcID,$tcversionID, $title, $buildID,$testCaseExternalId)
{
	$title = htmlspecialchars($title);
	$suffix = htmlspecialchars($testCaseExternalId) . ":&nbsp;<b>" . $title. "</b></a>";

	$testTitle = '<a href="lib/execute/execSetResults.php?level=testcase&build_id='
				 . $buildID . '&id=' . $tcID.'&version_id='.$tcversionID.'">';
	$testTitle .= $suffix;

	return $testTitle;
}
?>

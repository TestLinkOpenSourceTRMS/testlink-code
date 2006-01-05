<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsByStatus.php,v 1.8 2006/01/05 07:30:34 franciscom Exp $ 
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
require_once('builds.inc.php');	
require_once('results.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage($db);

$type = $_GET['type'];
if($type == $g_tc_status['failed'])
	$title = lang_get('list_of_failed');
else if($type == $g_tc_status['blocked'])
	$title = lang_get('list_of_blocked');
else
{
	tlog('wrong value of GET type');
	exit();
}
$arrBuilds = getBuilds($db,$_SESSION['testPlanId'], " ORDER BY build.name ");

//SQL to select the most current status of all the current test cases

// 20050919 - fm - refactoring
$sql = " SELECT tcid,status,build_id,runby,daterun,title,results.notes," .
       " MGTCOMP.name  AS comp_name, MGTCAT.name AS cat_name, COMP.id, CAT.id,mgttcid  " .
  		 " FROM results, testplans TP, component COMP, category CAT, " .
  		 " mgtcomponent MGTCOMP, mgtcategory MGTCAT, testcase TC " .
		   " WHERE TP.id = COMP.projid " .
		   " AND COMP.id = CAT.compid " .
		   " AND CAT.id = TC.catid " .
		   " AND TC.id = results.tcid " .
		   " AND MGTCOMP.id = COMP.mgtcompid " .
		   " AND MGTCAT.id = CAT.mgtcatid " .
		   " AND TP.id = " . $_SESSION['testPlanId'] . 
		   " ORDER BY tcid,build_id DESC";

$totalResult = do_sql_query($sql,$db);

reset($arrBuilds);
$maxBuild = each($arrBuilds);
$maxBuild = $maxBuild[0];
$testCaseNumArray = null;
//Looping through all of the test cases that we found
$arrData = null;
$tcIDArray = null;
while($myrow = $GLOBALS['db']->fetch_array($totalResult))
{
	$tcID = $myrow[0];
	$status = $myrow[1];
	if ($status == 'n' || isset($tcIDArray[$tcID]))
		continue;
	$tcIDArray[$tcID] = $myrow;
}
if (sizeof($tcIDArray))
{
	foreach($tcIDArray as $tcID => $myrow)
	{
		$status = $myrow[1];
		if ($status != $type)
			continue;
		$build = $myrow[2];
		///This statement builds an array with the most current status
		//the component and category 
		$testSuite = $myrow[7] . "/" . $myrow[8];	
	
		//Display the test case with a hyper link to the execution pages
		$testTitle = getTCLink(has_rights("tp_execute"), $tcID, $myrow[11], $myrow[5], $build); // TC title
		$tester = $myrow[3]; //Run By
		$testDate = $myrow[4]; //Date run
		$notes = $myrow[6]; //notes
		
		//Grab all of the bugs for the test case in the build
		$sqlBugs = " SELECT bug FROM bugs WHERE tcid=" . $tcID . 
				       " AND build_id=" . $build;
		$resultBugs = do_sql_query($sqlBugs,$db);
		$bugString = null;
		while ($myrowBug = $GLOBALS['db']->fetch_array($resultBugs))
		{
			if (!is_null($bugString))
				$bugString .= ","; 
			$bugID = $myrowBug[0];
			if($g_bugInterfaceOn)
				$bugString .= $g_bugInterface->buildViewBugLink($bugID);
			else
				$bugString .= $bugID;
		}
		$arrData[] = 	array(
								htmlspecialchars($testSuite), 
								$testTitle, 
								//20050815 - scs - added escaping of build identifiers
								htmlspecialchars($arrBuilds[$build]),
								htmlspecialchars($tester), 
								htmlspecialchars($testDate), 
								htmlspecialchars($notes),	
								$bugString,
							);
	}
}

$smarty = new TLSmarty;
$smarty->assign('title', $title);
$smarty->assign('arrData', $arrData);
$smarty->display('resultsByStatus.tpl');
?>
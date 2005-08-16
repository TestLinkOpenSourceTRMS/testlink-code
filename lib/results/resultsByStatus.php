<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsByStatus.php,v 1.2 2005/08/16 18:00:58 franciscom Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author 	Chad Rosen
* 
* This page show Test Results over all Builds.
*
* @author 20050807 - fm
* refactoring:  
* removed deprecated: $_SESSION['project']
* 
* 
*/
require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');	
require_once('results.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

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
$arrBuilds = getBuilds($_SESSION['testPlanId']);
//SQL to select the most current status of all the current test cases

// 20050807 - fm - $_SESSION['testPlanId']
$sql = "select tcid,status,build,runby,daterun,title,results.notes,component.name, category.name, component.id, category.id,mgttcid  " .
		"from results,project,component,category,testcase ".
		"where project.id = " . $_SESSION['testPlanId'] . 
		" and project.id = component.projid and component.id = category.compid and " .
		"category.id = testcase.catid and testcase.id = results.tcid and status = '{$type}' " .
		"order by tcid,build";
$totalResult = do_mysql_query($sql,$db);

$testCaseNumArray = null;
//Looping through all of the test cases that we found
$arrData = null;
while($myrow = mysql_fetch_row($totalResult))
{
	///This statement builds an array with the most current status
	$testCaseStatus = $myrow[0];
	//the component and category 
	$testSuite = $myrow[7] . "/" . $myrow[8];	

	//Display the test case with a hyper link to the execution pages
	$build = $myrow[2]; //Build
	$testTitle = getTCLink(has_rights("tp_execute"), $testCaseStatus, $myrow[11], $myrow[5], $build); // TC title
	$tester = $myrow[3]; //Run By
	$testDate = $myrow[4]; //Date run
	$notes = $myrow[6]; //notes
	
	//Grab all of the bugs for the test case in the build
	$sqlBugs = "SELECT bug FROM bugs WHERE tcid='" . $testCaseStatus . 
			"' AND build='" . $build . "'";
	$resultBugs = do_mysql_query($sqlBugs,$db);
	$bugString = null;
	while ($myrowBug = mysql_fetch_row($resultBugs))
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

$smarty = new TLSmarty;
$smarty->assign('title', $title);
$smarty->assign('arrData', $arrData);
$smarty->display('resultsByStatus.tpl');
?>
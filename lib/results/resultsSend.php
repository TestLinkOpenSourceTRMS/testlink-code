<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsSend.php,v 1.13 2007/01/09 06:56:06 kevinlevy Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author	Chad Rosen
* 
* Shows and processes the form for sending a Test Report.
*
* 
* @author Francisco Mancardi - 20050906 - reduce global coupling
*
*/
require('../../config.inc.php');
require_once('common.php');
// require_once('results.inc.php');
require_once('builds.inc.php');
require_once('info.inc.php');
require_once("../../lib/functions/lang_api.php");
require_once('../functions/results.class.php');
testlinkInitPage($db);

$tp = new testplan($db);
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0 ;
$builds = $tp->get_builds($tpID); 
$builds_two = array();
for ($i = 0; $i < sizeOf($builds); $i++ ) {
	$array = $builds[$i];
	$builds_two[$array['id']] = $array['name'];
}

$tp = new testplan($db);
$builds_to_query = -1;
$suitesSelected = 'all';
$re = new results($db, $tp, $suitesSelected, $builds_to_query);
$topLevelSuites= $re->getTopLevelSuites();
$topLevelSuites_two = array();
if (is_array($topLevelSuites)) {
while ($i = key($topLevelSuites)){
	$array = $topLevelSuites[$i];
	$topLevelSuites_two[$array['id']] = $array['name'];
	next($topLevelSuites);	
}
} // end if 
$message = null;
// process input data
if(isset($_POST['submit']))
{
	if($_POST['to'] == "") //check to see if the to field was blank
		$message = lang_get("send_to_empty_email_warning");
	else
	{

		// create message body
		$msgBody = "<html><body>";
		$msgBody .= (isset($_POST['body']) ? $_POST['body'] : null) . "\n\n";
		$status = isset($_POST['status']) ? $_POST['status'] : null;
		//$builds = getBuilds($db,$_SESSION['testPlanId']," ORDER BY builds.name ");

		if($status == 'projAll')
		{
				// query on all builds
				$builds_to_query = 'a';
				$re2 = new results($db, $tp, $suitesSelected, $builds_to_query);
				
				/** 
				* COMPONENTS REPORT 
				*/
				
				$topLevelSuites = $re2->getTopLevelSuites();
				$mapOfAggregate = $re2->getAggregateMap();
				$arrDataSuite = null;
				$arrDataSuiteIndex = 0;
				$msgBody .= "<h2>Results by Suite</h2>";
				$msgBody .= "<table border=2>";
                $msgBody .= "<tr><th>suite name</th><th>total cases</th><th>passed</th><th>failed</th><th>blocked</th><th>not run</th><th>% completed</th></tr>";
				while ($i = key($topLevelSuites)) {
					$pairArray = $topLevelSuites[$i];
					$currentSuiteId = $pairArray['id'];
					$currentSuiteName = $pairArray['name'];
					$resultArray = $mapOfAggregate[$currentSuiteId];	
					$total = $resultArray['total'];
					$notRun = $resultArray['notRun'];
					if ($total > 0) {
					   $percentCompleted = (($total - $notRun) / $total) * 100;
					}
					else {
					   $percentCompleted = 0;
					}
					$percentCompleted = number_format($percentCompleted,2);
					$arrDataSuite[$arrDataSuiteIndex] = array($currentSuiteName,$total,$resultArray['pass'],$resultArray['fail'],$resultArray['blocked'],$notRun,$percentCompleted);
					$appendThis = "<tr><td>" . $currentSuiteName . "</td><td>" . $total . "</td><td>" . $resultArray['pass'] . "</td><td>" . $resultArray['fail'] . "</td><td>" . $resultArray['blocked'] . "</td><td>" . $notRun . "</td><td>" . $percentCompleted . "</td></tr>";
					$msgBody .= $appendThis;
					$arrDataSuiteIndex++;
					next($topLevelSuites);
				}  // end while
				$msgBody .= "</table>";
		} 
		else if($status == 'comAll')
		{ 
		  // user has chosen to send a specific component status across all builds
		  
		  // 20051106 - fm - missed argument
			$msgBody .= "reportSuiteStatus"; 
				//reportSuiteStatus($db,$_SESSION['testPlanId'],$_POST['comSelectAll']);
		}	
		else if($status == 'projBuild') 
		{ 
			// 20051106 - fm - missed argument
		  //user has chosen to send the status of a particular build
			$msgBody .= "reportBuildStatus"; 
				// reportBuildStatus($db,$_SESSION['testPlanId'],
			                         //     $_POST['buildProj'],$builds[$_POST['buildProj']]);
		}	
		else
		{ 
			// 20051106 - fm - missed argument
		  //user has chosen to send the status of a particular component for a build
			$msgBody .= "reportSuiteBuildStatus"; 
				//reportSuiteBuildStatus($db,$_SESSION['testPlanId'],$_POST['comSelectBuild'], 
			                       //            $_POST['buildCom'],$builds[$_POST['buildCom']]);
		}
		
			
		// Send mail
		$headers = null;
		$send_cc_to_myself=false;
		if (isset($_POST['cc']))
		{
			// 20051106 - fm
			// $headers = "Cc: " . $_SESSION['email'] . "\r\n";
			$send_cc_to_myself=true;
    }
    
    // 20050906 - fm
		$msgBody .= "</body></html>";
		$message = sendMail($_SESSION['email'],$_POST['to'], $_POST['subject'],
		                    $msgBody,$send_cc_to_myself);
	}
}

/**
* 20061127 - KL - temporarily comment out
*/
//Gather all of the current TP components for the dropdown box
//$suites = listTPComponent($db,$_SESSION['testPlanId']);
// Gather info for the build dropdown box
//$builds = getBuilds($db,$_SESSION['testPlanId']," ORDER BY builds.name ");
// warning if no build or component

if(count($topLevelSuites_two) == 0 || count($builds_two) == 0) {
	displayInfo($_SESSION['testPlanName'], lang_get("warning_create_build_first"));
}

$smarty = new TLSmarty;
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->assign('message', $message);
$smarty->assign('suites', $topLevelSuites_two);
$smarty->assign('builds', $builds_two);
$smarty->display('resultsSend.tpl');
?>
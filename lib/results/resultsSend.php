<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsSend.php,v 1.7 2006/01/05 07:30:34 franciscom Exp $ 
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
require_once('results.inc.php');
require_once('builds.inc.php');
require_once('info.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage($db);

$message = null;
// process input data
if(isset($_POST['submit']))
{
	if($_POST['to'] == "") //check to see if the to field was blank
		$message = lang_get("send_to_empty_email_warning");
	else
	{
		// create message body
		$msgBody = (isset($_POST['body']) ? $_POST['body'] : null) . "\n\n";
		$status = isset($_POST['status']) ? $_POST['status'] : null;
		$builds = getBuilds($db,$_SESSION['testPlanId']," ORDER BY build.name ");

		if($status == 'projAll')
		{
			 //if the user has chosen to sent the entire testplan priority info
			//grab all of the priority info and stuff it into the message body
			$msgBody .= reportGeneralStatus($db,$_SESSION['testPlanId']);
		} 
		else if($status == 'comAll')
		{ 
		  // user has chosen to send a specific component status across all builds
		  
		  // 20051106 - fm - missed argument
			$msgBody .= reportSuiteStatus($db,$_SESSION['testPlanId'],$_POST['comSelectAll']);
		}	
		else if($status == 'projBuild') 
		{ 
			// 20051106 - fm - missed argument
		  //user has chosen to send the status of a particular build
			$msgBody .= reportBuildStatus($db,$_SESSION['testPlanId'],
			                              $_POST['buildProj'],$builds[$_POST['buildProj']]);
		}	
		else
		{ 
			// 20051106 - fm - missed argument
		  //user has chosen to send the status of a particular component for a build
			$msgBody .= reportSuiteBuildStatus($db,$_SESSION['testPlanId'],$_POST['comSelectBuild'], 
			                                   $_POST['buildCom'],$builds[$_POST['buildCom']]);
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
		$message = sendMail($_SESSION['email'],$_POST['to'], $_POST['subject'],
		                    $msgBody,$send_cc_to_myself);
	}
}

//Gather all of the current TP components for the dropdown box
$suites = listTPComponent($db,$_SESSION['testPlanId']);

// Gather info for the build dropdown box
$builds = getBuilds($db,$_SESSION['testPlanId']," ORDER BY build.name ");
// warning if no build or component
if(count($suites) == 0 || count($builds) == 0)
	displayInfo($_SESSION['testPlanName'], lang_get("warning_create_build_first"));
	
$smarty = new TLSmarty;
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->assign('message', $message);
$smarty->assign('suites', $suites);
$smarty->assign('builds', $builds);
$smarty->display('resultsSend.tpl');
?>
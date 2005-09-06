<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsSend.php,v 1.3 2005/09/06 06:42:04 franciscom Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author	Chad Rosen
* 
* Shows and processes the form for sending a Test Report.
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
testlinkInitPage();

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
		$builds = getBuilds($_SESSION['testPlanId']);
		
		if($status == 'projAll')
		{
			 //if the user has chosen to sent the entire project priority info
			//grab all of the priority info and stuff it into the message body
			$msgBody .= reportGeneralStatus($_SESSION['testPlanId']);
		} 
		else if($status == 'comAll') //user has chosen to send a specific component status across all builds
			$msgBody .= reportSuiteStatus($_POST['comSelectAll']);
		else if($status == 'projBuild') //user has chosen to send the status of a particular build
			$msgBody .= reportBuildStatus($_POST['buildProj'],$builds[$_POST['buildProj']]);
		else //user has chosen to send the status of a particular component for a build
			$msgBody .= reportSuiteBuildStatus($_POST['comSelectBuild'], $_POST['buildCom'],$builds[$_POST['buildCom']]);
		// Send mail
		$headers = null;
		if (isset($_POST['cc']))
		{
			$headers = "Cc: " . $_SESSION['email'] . "\r\n";
    }
    
    // 20050906 - fm 
		$message = sendMail($_SESSION['email'],$_POST['to'], $_POST['subject'], $msgBody, $headers);
	}
}

//Gather all of the current TP components for the dropdown box
$suites = listTPComponent();

// Gather info for the build dropdown box
$builds = getBuilds($_SESSION['testPlanId']);

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
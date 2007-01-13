<?php
/*
 * Created on Jan 13, 2007 by Kevin Levy
 *
 */
 // has the sendMail() method
require_once('info.inc.php');

function displayReport($template_file, &$smarty, $report_type, $buildName = null)
{
    //print "displayReport $template_file, $report_type <BR>";
    if ($report_type == '0') {
		$smarty->display($template_file);
	}
	else if ($report_type == '1'){
		print "MS Excel report for $template_file is not yet implemented - KL - 20070109 <BR>";
	}
	else if ($report_type == '2'){
		//print "HTML email report for resultsBuild.php is not yet implemented - KL - 20070109 <BR>";	
		$html_report = $smarty->fetch($template_file);
		// $message = sendMail($_SESSION['email'],$_POST['to'], $_POST['subject'], $msgBody,$send_cc_to_myself);
		$htmlReportType = true;
		$send_cc_to_myself = false;
		$subjectOfMail = $_SESSION['testPlanName'] . ": " . $template_file . " " . $buildName;
		
		$emailFrom = $_SESSION['email'];
		$emailTo = $_SESSION['email'];
		if (!$emailTo) {
			print "email for this user is not specified, please edit email credentials in \"Personal\" tab. <BR>";
		}
		//print "emailTo = $emailTo <BR>";
		// function sendMail($from,$to, $title, $message, $send_cc_to_myself = false, $isHtmlFormat = false)
		$message = sendMail($emailFrom, $emailTo, $subjectOfMail, $html_report, $send_cc_to_myself, $htmlReportType);
		
		$smarty = new TLSmarty;
		$smarty->assign('message', $message);
		$smarty->display('emailSent.tpl');
	
	}
	else if ($report_type == '3'){
		print "text email report for $template_file is not yet implemented - KL - 20070109 <BR>";
	}

} //end function

?>

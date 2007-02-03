<?php
/*
 * Created on Jan 13, 2007 by Kevin Levy
 *
 */
 // has the sendMail() method
require_once('info.inc.php');

function displayReport($template_file, &$smarty, $report_type, $buildName = null)
{
	// default report
    if ($report_type == '0')
	{
		
	}
	// excel report
	else if ($report_type == '1')
	{
		sendXlsHeader();
	}
	// html email report
	else if ($report_type == '2')
	{
		$template_file = $template_file . ".tpl";
		$html_report = $smarty->fetch($template_file);
		$emailIsHtml = true;
		$send_cc_to_myself = false;
		$subjectOfMail = $_SESSION['testPlanName'] . ": " . $template_file . " " . $buildName;
		
		$emailFrom = $_SESSION['email'];
		$emailTo = $_SESSION['email'];
		if (!$emailTo)
		{
			//Email for this user is not specified, please edit email credentials in \"Personal\" tab.
			$message = lang_get("error_sendreport_no_email_credentials");
		}
		else
		{
			$message = sendMail($emailFrom, $emailTo, $subjectOfMail, $html_report, $send_cc_to_myself, $emailIsHtml);
		}
		$smarty = new TLSmarty();
		$smarty->assign('message', $message);
		$template_file = "emailSent";
	}
	// text email report
	else if ($report_type == '3'){
		/**
		$template_file = $template_file . "_text.tpl";
		$text_report = $smarty->fetch($template_file);
		$emailIsHtml = false;
		$send_cc_to_myself = false;
		$subjectOfMail = $_SESSION['testPlanName'] . ": " . $template_file . " " . $buildName;
		
		$emailFrom = $_SESSION['email'];
		$emailTo = $_SESSION['email'];
		if (!$emailTo) {
			print "email for this user is not specified, please edit email credentials in \"Personal\" tab. <BR>";
		}
		$message = sendMail($emailFrom, $emailTo, $subjectOfMail, $text_report, $send_cc_to_myself, $emailIsHtml);
		*/
		$message = "text email messages not implemented <BR>";
		$smarty = new TLSmarty();
		$smarty->assign('message', $message);
		
		$template_file = "emailSent";
	}
	// PDF report
	else if ($report_type == '4')
	{
		sendPdfHeader();
	}
	$template_file = $template_file . ".tpl";
	$smarty->display($template_file);
} //end function


function sendXlsHeader()
{
        header("Content-Disposition: inline; filename=testReport.xls");
        header("Content-Description: PHP Generated Data");
        header("Content-type: application/vnd.ms-excel; name='My_Excel'");
        flush();
}


function sendPdfHeader()
{
	// We'll be outputting a PDF
	header('Content-type: application/pdf');

	// It will be called downloaded.pdf
	header('Content-Disposition: attachment; filename="testReport.pdf"');
	
}

?>

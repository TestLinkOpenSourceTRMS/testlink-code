<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: displayMgr.php,v 1.9 2007/12/09 02:15:19 havlat Exp $ 
*
* @author	Kevin Levy
* 
* Revision:
* 2007/12/07 - havlatm - added MSWord, magic numbers -> use global const.
*/
 // has the sendMail() method
require_once('info.inc.php');

function displayReport($template_file, &$smarty, $report_type, $buildName = null)
{
	global $g_reports_cfg;	

	// default report is HTML
	// if ($report_type == key($g_reports_cfg->formats['HTML'])) do nothing

	// excel report
	if ($g_reports_cfg->formats[$report_type] == 'MS Excel') //1
	{
		sendXlsHeader();
	}
	
	// msword report
	if ($g_reports_cfg->formats[$report_type] == 'MS Word')
	{
		sendMsWordHeader();
	}

	// html email report
	if ($g_reports_cfg->formats[$report_type] == 'Email')
//	else if ($report_type == '2')
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

	// PDF report
	if ($g_reports_cfg->formats[$report_type] == 'PDF')
	{
		sendPdfHeader();
	}

	$template_file = $template_file . ".tpl";
	$smarty->display($template_file);

} //end function


/*
  function: 

*/
function sendXlsHeader()
{
		$timeStamp = date('Y-m-d'); // . "-" . time();
		$filename = "testReport-" . $timeStamp . ".xls"; 
        header("Content-Disposition: attachment; filename=$filename");
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

// add MS Word header 
function sendMsWordHeader()
{
	header("Content-Disposition: inline; filename=testReport.pdf");
	header("Content-Description: PHP Generated Data");
	header("Content-type: application/vnd.ms-word; name='My_Word'");
	flush();
}

?>

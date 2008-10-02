<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: displayMgr.php,v 1.13 2008/10/02 19:18:44 schlundus Exp $ 
*
* @author	Kevin Levy
* 
* Revision:
* 20080928 - franciscom - minor refactoring
* 20071207 - havlatm - added MSWord, magic numbers -> use global const.
*/
require_once('info.inc.php'); // has the sendMail() method
require_once('../../cfg/reports.cfg.php');

function displayReport($template_file, &$smarty, $report_type, $buildName = null)
{
	$reports_cfg = config_get('reportsCfg');
	$reports_formats = config_get('reports_formats');

	// excel report
	switch($reports_formats[$report_type])
	{
		case 'MS Excel':
	  		sendXlsHeader();
    		break;  

	    case 'MS Word':
      		sendMsWordHeader();
      		break;
      
	    case 'Email':
			// $template_file = $template_file . ".tpl";
			$html_report = $smarty->fetch($template_file);
			$emailIsHtml = true;
		 	$send_cc_to_myself = false;
			$subjectOfMail = $_SESSION['testPlanName'] . ": " . $template_file . " " . $buildName;
		  
			$emailFrom = $_SESSION['currentUser']->emailAddress;
			$emailTo = $emailFrom;
		  	if (!strlen($emailTo))
		  	{
				//Email for this user is not specified, please edit email credentials in \"Personal\" tab.
				$message = lang_get("error_sendreport_no_email_credentials");
		  	}
		  	else
		  		$message = sendMail($emailFrom, $emailTo, $subjectOfMail, $html_report, $send_cc_to_myself, $emailIsHtml);
		  
			$smarty = new TLSmarty();
			$smarty->assign('message', $message);
		  	$template_file = "emailSent.tpl";
      		break;
      
      case 'PDF':
  			sendPdfHeader();
      break;


	} 
	$smarty->display($template_file);
}

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

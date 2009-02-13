<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * @filesource $RCSfile: displayMgr.php,v $
 * @version $Revision: 1.15 $
 * @modified $Date: 2009/02/13 16:10:01 $ by $Author: havlat $
 * @author	Kevin Levy
 * 
 * Revision:
 * 	20090213 - havlatm - added flushHttpHeader function instead of particular headers
 * 						support for OpenOffice
 * 	20080928 - franciscom - minor refactoring
 * 	20071207 - havlatm - added MSWord, magic numbers -> use global const.
 */

require_once('info.inc.php'); // has the sendMail() method
require_once('../../cfg/reports.cfg.php');


function displayReport($template_file, &$smarty, $report_type, $buildName = null)
{
	$reports_cfg = config_get('reportsCfg');
	$reports_formats = config_get('reports_formats');

	switch($reports_formats[$report_type])
	{
		case 'format_odt':
		case 'format_ods':
		case 'format_xls':
		case 'format_msword':
		case 'format_pdf':
	  		flushHttpHeader($reports_formats[$report_type], $doc_kind=0);
    		break;  

	    case 'format_mail_html':
			$html_report = $smarty->fetch($template_file);
			$emailIsHtml = true;
		 	$send_cc_to_myself = false;
		 	$tpName = $smarty->get_template_vars("tplan_name");
			$subjectOfMail =  $tpName . ": " . $template_file . " " . $buildName;
		  
			$emailFrom = $_SESSION['currentUser']->emailAddress;
			$emailTo = $emailFrom;
			if (!strlen($emailTo))
		  		$message = lang_get("error_sendreport_no_email_credentials");
		  	else
		  		$message = sendMail($emailFrom, $emailTo, $subjectOfMail, $html_report, $send_cc_to_myself, $emailIsHtml);
		  		
			$smarty = new TLSmarty();
			$smarty->assign('message', $message);
			$smarty->assign('tpName', $tpName);
		  	$template_file = "emailSent.tpl";
      		break;
	} 

	$smarty->display($template_file);
}




/**
 * Generate HTML header and send it to browser
 * @param string $format identifier of document format; value must be in $tlCfg->reports_formats
 * @param integer $doc_kind Magic number of document kind; see consts.inc.php for list 
 * 		(for example: DOC_TEST_PLAN)
 * @author havlatm
 */
function flushHttpHeader($format, $doc_kind=0)
{
	$file_extensions = config_get('reports_file_extension');
	$reports_applications = config_get('reports_applications');

	switch($doc_kind)
	{
		case DOC_TEST_SPEC: $kind_acronym = '_test_spec'; break;
		case DOC_TEST_PLAN: $kind_acronym = '_test_plan'; break;
		case DOC_TEST_REPORT: $kind_acronym = '_test_report'; break;
		case DOC_REQ_SPEC: $kind_acronym = '_req_spec'; break;
		default: $kind_acronym = '';
	}
	
	if(($format == 'format_mail_html') || ($format == ''))
		tLog('flushHttpHeader> Invalid format: '.$format, 'ERROR');

	$filename = $_SESSION['testprojectPrefix'] . $kind_acronym . '-' . date('Y-m-d') . '.' . $file_extensions[$format];
	tLog('Flush HTTP header for '.$format); 


    header("Content-Description: TestLink - Generated Document");
	header("Content-Disposition: attachment; filename=$filename");
   	header("Content-type: {$reports_applications[$format]}; name='Testlink_$format'");
	flush();
}

/*
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
	header('Content-type: application/pdf');
	header('Content-Disposition: attachment; filename="testReport.pdf"');
}

function sendMsWordHeader()
{
	header("Content-Disposition: inline; filename=testReport.pdf");
	header("Content-Description: PHP Generated Data");
	header("Content-type: application/vnd.ms-word; name='My_Word'");
	flush();
}
*/
?>

<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Functions for GUI support
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2005-2009, TestLink community 
 * @version    	CVS: $Id: info.inc.php,v 1.8 2009/06/30 10:59:53 havlat Exp $
 * @link 		http://www.teamst.org/index.php
 * @uses 		common.php
 *
 * @internal Revisions:
 * 
 * 20070109 - KL - altered to allow for html reports if caller so chooses
 */

/** @uses email_api.php */
require_once("../functions/email_api.php");


/**
 * Display simple info and exit
 *
 * @param string $title
 * @param string $message
 */
function displayInfo($title, $message)
{
	$smarty = new TLSmarty;
	$smarty->assign('title', $title);
	$smarty->assign('content', $message);
	$smarty->display('workAreaSimple.tpl');

	exit();
}


/**
 * Display simple info and exit
 *
 * @param string $from
 * @param string $to
 * @param string $title
 * @param string $message
 * @param string $cc (optional) yes = send a copy myself
 *
 * @return string Ok message.
 *
 * @internal Revisions:
 * 20051106 - fm - use of email_send()
 * 20050906 - fm - added from
 * @todo use email_send() directly - remove
 */
function sendMail($from,$to, $title, $message, $send_cc_to_myself = false, $isHtmlFormat = false)
{
	$cc = '';
	if ($send_cc_to_myself)
	{
		$cc = $from;
	}
	
	$email_op = @email_send($from, $to, $title, $message, $cc, false, $isHtmlFormat);
	
	if ($email_op->status_ok)
	{
		return lang_get('email_sent_message');
	}	
	else
	{
		die("Error sending email" . $email_op->msg);
	}	
}
?>
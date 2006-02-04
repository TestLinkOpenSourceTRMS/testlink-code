<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: info.inc.php,v 1.5 2006/02/04 20:13:14 schlundus Exp $
* 
* @author Martin Havlat
*
* Functions for GUI Communication
*/
require_once('../../config.inc.php');
require_once("../functions/common.php");
require_once("../functions/lang_api.php");

// 20051106 - fm 
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
* 20051106 - fm - use of email_send()
* 20050906 - fm - added from
*/
function sendMail($from,$to, $title, $message, $send_cc_to_myself = false)
{
	
	// 20051106 - fm
	// Create headers 
	/*$headers  = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/plain; charset=utf-8\r\n"; // Content-type: text/html
	$headers .= "From: " . $from . "\r\n";
	

	if ($cc == 'yes')
	{
		$headers = "Cc: " . $from . "\r\n";
	}
	*/
	$cc = '';
	if ($send_cc_to_myself)
	{
		$cc = $from;
	}
	
	
	// $email_op = @email_send($to, $title, $message, $headers);
	$email_op = @email_send($from, $to, $title, $message, $cc);
	
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
<?
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: info.inc.php,v 1.3 2005/09/06 06:44:07 franciscom Exp $
* 
* @author Martin Havlat
*
* Functions for GUI Communication
*/
require_once('../../config.inc.php');
require_once("../functions/common.php");
require_once("../../lib/functions/lang_api.php");

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
* 20050906 - fm - added from
*/
function sendMail($from,$to, $title, $message, $cc = 'no')
{
	// Create headers 
	$headers  = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/plain; charset=utf-8\r\n"; // Content-type: text/html
	$headers .= "From: " . $from . "\r\n";
	if ($cc == 'yes')
	{
		$headers = "Cc: " . $from . "\r\n";
	}
	$sendResult = mail($to, $title, $message, $headers);
	if ($sendResult)
		return lang_get('email_sent_message');
	else
		die("Error sending email");
}
?>
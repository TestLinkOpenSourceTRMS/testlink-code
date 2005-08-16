<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: login.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2005/08/16 17:57:41 $
 *
 * @author Martin Havlat
 *
 * 
**/
require('config.inc.php');
require_once('lib/functions/lang_api.php');

$note = isset($_GET['note']) ? $_GET['note'] : null;

$message = lang_get('please_login');
// assign a comment for login
switch($note)
{
	case 'expired':
		$message = lang_get('session_expired');
		break;
	case 'wrong':
		$message = lang_get('bad_user_passwd');
		break;
	case 'first':
		$message = lang_get('your_first_login');
		break;
	case 'lost':
		$message = lang_get('passwd_lost');
		break;
}
	
$smarty = new TLSmarty;
$smarty->assign('note',$message);
$smarty->assign('css', TL_BASE_HREF . 'gui/css/tl_login.css');
$smarty->display('login.tpl');
?>
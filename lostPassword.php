<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: lostPassword.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2005/08/16 17:57:41 $
 *
 * @author Chad Rosen
 *
 * 
**/
require_once('config.inc.php');
require_once('common.php');
include('users.inc.php');
require_once('lib/functions/lang_api.php');

$login = isset($_POST['login']) ? strings_stripSlashes($_POST['login']): null;

$smarty = new TLSmarty;
$op = doDBConnect();
if ($op['status'] == 0)
{
	$smarty->assign('title', lang_get('fatal_page_title'));
	$smarty->assign('msg', $op['dbms_msg']);
	$smarty->display('fatal_error.tpl');
	exit();
}

$message = lang_get('your_info_for_passwd');
if (strlen($login))
{
	if(!existLogin($login,$userInfo))
		$message = lang_get('bad_user');
	else
	{
		$emailAddress = $userInfo['email'];
		$userID = $userInfo['id'];
		
		if (strlen($emailAddress))
		{
			// because pwds are now hashed we cannot simply resend 
			// the password instead we must generate a new one
			$newPassword = md5(uniqid(rand(),1));
			
			//Setup the message body
			$msgBody = lang_get('your_password_is') . $newPassword .  lang_get('contact_admin');  
			
			if (!@mail($emailAddress, lang_get('mail_passwd_subject'), $msgBody))
				$message = lang_get('mail_problems');
			else
			{
				if (setUserPassword($userID,$newPassword))
				{
					redirect(TL_BASE_HREF ."login.php?note=lost");
					exit();
				}
			}
		}
		else
			$message = lang_get('mail_empty_address');
	}
}

$smarty->assign('css', TL_BASE_HREF . 'gui/css/tl_login.css');
$smarty->assign('note',$message);
$smarty->assign('page_title',lang_get('page_title_lost_passwd'));
$smarty->display('loginLost.tpl');
?>

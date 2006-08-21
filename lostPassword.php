<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: lostPassword.php,v $
 *
 * @version $Revision: 1.15 $
 * @modified $Date: 2006/08/21 13:21:59 $ $Author: franciscom $
 *
 * 20060103 - scs - ADOdb changes
 * 20060819 - franciscom - logo added
**/
require_once('config.inc.php');
require_once('common.php');
require_once('users.inc.php');
require_once('email_api.php');

$_POST = strings_stripSlashes($_POST);
$login = isset($_POST['login']) ? $_POST['login']: null;

$op = doDBConnect($db);
if ($op['status'] == 0)
{
	$smarty = new TLSmarty();
	$smarty->assign('title', lang_get('fatal_page_title'));
	$smarty->assign('msg', $op['dbms_msg']);
	$smarty->display('fatal_error.tpl');
	exit();
}

$message = lang_get('your_info_for_passwd');
if (strlen($login))
{
	if(!existLogin($db,$login,$userInfo))
	{
		$message = lang_get('bad_user');
	}	
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

      		// 20051209 - fm - BUGID 289
      		$mail_op = email_send(config_get('from_email'), $emailAddress,  
                                lang_get('mail_passwd_subject'), $msgBody);
			
			if ($mail_op->status_ok)
			{
				if (setUserPassword($db,$userID,$newPassword))
				{
					redirect(TL_BASE_HREF ."login.php?note=lost");
					exit();
				}
			}	
			else
				$message = lang_get('mail_problems') . " - " . $mail_op->msg;
		}
		else
			$message = lang_get('mail_empty_address');
	}
}

$smarty = new TLSmarty();
$smarty->assign('login_logo', LOGO_LOGIN_PAGE);
$smarty->assign('css', TL_BASE_HREF . TL_LOGIN_CSS);
$smarty->assign('note',$message);
$smarty->assign('page_title',lang_get('page_title_lost_passwd'));
$smarty->display('loginLost.tpl');
?>

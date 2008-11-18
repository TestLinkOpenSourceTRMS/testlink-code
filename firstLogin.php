<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: firstLogin.php,v $
 *
 * @version $Revision: 1.29 $
 * @modified $Date: 2008/11/18 20:54:42 $ $Author: schlundus $
 *
 */
require_once('config.inc.php');
require_once('common.php');
require_once('users.inc.php');

if (!config_get('user_self_signup'))
{
	$smarty = new TLSmarty();
	$smarty->assign('title', lang_get('fatal_page_title'));
	$smarty->assign('content', lang_get('error_self_signup_disabled'));
	$smarty->assign('link_to_op', "login.php");
	$smarty->assign('hint_text', lang_get('link_back_to_login'));
	$smarty->display('workAreaSimple.tpl');
	exit();
}

$_POST = strings_stripSlashes($_POST);
$bEditUser = isset($_POST['editUser']) ? $_POST['editUser'] : null;
$login = isset($_POST['loginName']) ? $_POST['loginName'] : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;
$password2 = isset($_POST['password2']) ? $_POST['password2'] : null;
$first = isset($_POST['first']) ? $_POST['first'] : null;
$last = isset($_POST['last']) ? $_POST['last'] : null;
$email = isset($_POST['email']) ? $_POST['email'] : null;

doDBConnect($db);

$message = lang_get('your_info_please');
if($bEditUser)
{
	if(strcmp($password,$password2))
		$message = lang_get('passwd_dont_match');
	else
	{
		$user = new tlUser();	
		$result = $user->setPassword($password);
		if ($result >= tl::OK)
		{
			$user->login = $login;
			$user->emailAddress = $email;
			$user->firstName = $first;
			$user->lastName = $last;
			$result = $user->writeToDB($db);
		}
		if ($result >= tl::OK)
		{
			logAuditEvent(TLS("audit_users_self_signup",$login),"CREATE",$user->dbID,"users");
			redirect(TL_BASE_HREF . "login.php?note=first");
			exit();
		}
		else 
			$message = getUserErrorMessage($result);
	}
}

$smarty = new TLSmarty();
$smarty->assign('external_password_mgmt',tlUser::isPasswordMgtExternal());
$smarty->assign('login', $login);
$smarty->assign('firstName', $first);
$smarty->assign('lastName', $last);
$smarty->assign('email', $email);
$smarty->assign('message',$message);
$smarty->display('loginFirst.tpl');
?>

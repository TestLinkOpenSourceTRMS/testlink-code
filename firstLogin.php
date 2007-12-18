<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: firstLogin.php,v $
 *
 * @version $Revision: 1.18 $
 * @modified $Date: 2007/12/18 20:47:19 $ $Author: schlundus $
 *
 * @author Asiel Brumfield
 * @author Martin Havlat 
 *
 * rev :
 *       20070626 - franciscom - added missing LDAP code
**/
require_once('config.inc.php');
require_once('common.php');
require_once('users.inc.php');

$_POST = strings_stripSlashes($_POST);
$bEditUser = isset($_POST['editUser']) ? $_POST['editUser'] : null;
$login = isset($_POST['loginName']) ? $_POST['loginName'] : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;
$password2 = isset($_POST['password2']) ? $_POST['password2'] : null;
$first = isset($_POST['first']) ? $_POST['first'] : null;
$last = isset($_POST['last']) ? $_POST['last'] : null;
$email = isset($_POST['email']) ? $_POST['email'] : null;

$message = lang_get('your_info_please');

// 20070626 - franciscom
$login_method = config_get('login_method');
$external_password_mgmt = ('LDAP' == $login_method )? 1 : 0;

$op = doDBConnect($db);
if (!config_get('user_self_signup'))
{
	$msg = lang_get('error_self_signup_disabled');

	$smarty = new TLSmarty();
	$smarty->assign('title', lang_get('fatal_page_title'));
	$smarty->assign('content', $msg);
	$smarty->assign('link_to_op', "login.php");
	$smarty->assign('hint_text', lang_get('link_back_to_login'));
	$smarty->display('workAreaSimple.tpl');
	exit();
}

if($bEditUser)
{
	if(strcmp($password,$password2))
		$message = lang_get('passwd_dont_match');
	else
	{
		$user = new tlUser();	
		$sqlResult = $user->setPassword($password);
		if ($sqlResult == OK)
		{
			$user->m_login = $login;
			$user->m_emailAddress = $email;
			$user->m_firstName = $first;
			$user->m_lastName = $last;
			$sqlResult = $user->writeToDB($db);
		}
		if ($sqlResult == OK)
		{
			redirect(TL_BASE_HREF . "login.php?note=first");
			exit();
		}
		else 
			$message = getUserErrorMessage($sqlResult);
	}
}

$smarty = new TLSmarty();
$smarty->assign('external_password_mgmt',$external_password_mgmt);
$smarty->assign('login', $login);
$smarty->assign('firstName', $first);
$smarty->assign('lastName', $last);
$smarty->assign('email', $email);
$smarty->assign('login_logo', LOGO_LOGIN_PAGE);
$smarty->assign('css', TL_BASE_HREF . TL_LOGIN_CSS);
$smarty->assign('message',$message);
$smarty->display('loginFirst.tpl');
?>

<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: firstLogin.php,v $
 *
 * @version $Revision: 1.15 $
 * @modified $Date: 2007/03/04 00:03:19 $ $Author: schlundus $
 *
 * @author Asiel Brumfield
 * @author Martin Havlat 
 *
 * Anybody can have Guest rights to browse TL
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
	// Fields that can't be empty
	$fields_not_empty = array ('first' => lang_get('empty_first_name'),
	                           'last'  => lang_get('empty_last_name'),
	                           'email' => lang_get('empty_email_address'),
	                           'password' => lang_get('warning_empty_pwd'),
							   );

	$empty_fm = control_empty_fields( $_POST, $fields_not_empty );
	$passwordCompare = strcmp($password,$password2);
	$user_ok = user_is_name_valid($login);

	if($user_ok === FALSE)
	{
		$message = lang_get('invalid_user_name') . "<br />" . 
		           lang_get('valid_user_name_format');
	}	
	else if (count($empty_fm))
	{
		$message = lang_get('user_cant_be_created_because');
		foreach ($empty_fm as $key_f => $value_m)
			$message .= "<br />" . $value_m;
	}
	else if($passwordCompare)
		$message = lang_get('passwd_dont_match');
	else
	{
		$userData = '';
		if(existLogin($db,$login,$userData))
			$message = lang_get('user_name_exists');
		else
		{
			$result = userInsert($db,$login, $password, $first, $last, $email);
			if ($result)
			{
				redirect(TL_BASE_HREF . "login.php?note=first");
				exit();
			}
			else
				$message = lang_get('cant_create_user');
		}
	}
}

$smarty = new TLSmarty();
$smarty->assign('login', $login);
$smarty->assign('firstName', $first);
$smarty->assign('lastName', $last);
$smarty->assign('email', $email);
$smarty->assign('login_logo', LOGO_LOGIN_PAGE);
$smarty->assign('css', TL_BASE_HREF . TL_LOGIN_CSS);
$smarty->assign('message',$message);
$smarty->display('loginFirst.tpl');
?>

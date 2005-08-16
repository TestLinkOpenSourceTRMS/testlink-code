<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: firstLogin.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2005/08/16 17:57:41 $
 *
 * @author Asiel Brumfield
 * @author Martin Havlat 
 *
 * Anybody can have Guest rights to browse TL
**/
require_once('config.inc.php');
require_once('common.php');
require_once('users.inc.php');
require_once('lib/functions/lang_api.php');

$message = lang_get('your_info_please');
$op = doDBConnect();
if (!$op['status'])
{
	$smarty = new TLSmarty;
	$smarty->assign('title', lang_get('fatal_page_title'));
	$smarty->assign('msg', $op['dbms_msg']);
	$smarty->display('fatal_error.tpl');
	exit();
}

$bEditUser = isset($_POST['editUser']) ? $_POST['editUser'] : null;
if($bEditUser)
{
	$login = isset($_POST['loginName']) ? strings_stripSlashes($_POST['loginName']) : null;
	$password = isset($_POST['password']) ? strings_stripSlashes($_POST['password']) : null;
	$password2 = isset($_POST['password2']) ? strings_stripSlashes($_POST['password2']) : null;
	$first = isset($_POST['first']) ? strings_stripSlashes($_POST['first']) : null;
	$last = isset($_POST['last']) ? strings_stripSlashes($_POST['last']) : null;
	$email = isset($_POST['email']) ? strings_stripSlashes($_POST['email']) : null;
	
	// Fields that can't be empty
	$fields_not_empty = array ('first' => lang_get('empty_first_name'),
	                           'last'  => lang_get('empty_last_name'),
	                           'email' => lang_get('empty_email_address'));

	$empty_fm = control_empty_fields( $_POST, $fields_not_empty );
	$passwordCompare = strcmp($password,$password2);
	$user_ok = user_is_name_valid($login);

	if($user_ok === FALSE)
	{
		$message = lang_get('invalid_user_name') . "<br>" . 
		           lang_get('valid_user_name_format');
	}	
	else if (count($empty_fm))
	{
		$message = lang_get('user_cant_be_created_because');
		foreach ( $empty_fm as $key_f => $value_m )
			$message .= "<br>" . $value_m;
	}
	else if($passwordCompare)
		$message = lang_get('passwd_dont_match');
	else
	{
		$userData = '';
		if(existLogin($login,$userData))
			$message = lang_get('user_name_exists');
		else
		{
			$result = userInsert($login, $password, $first, $last, $email);
			if ($result)
			{
				redirect($_SESSION['basehref'] . "login.php?note=first");
				exit();
			}
			else
				$message = lang_get('cant_create_user');
		}
	}
}

$smarty = new TLSmarty;
$smarty->assign('css', TL_BASE_HREF . 'gui/css/tl_login.css');
$smarty->assign('message',$message);
$smarty->display('loginFirst.tpl');
?>
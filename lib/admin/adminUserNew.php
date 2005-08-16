<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: adminUserNew.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:53 $
 *
 * @author Martin Havlat
 *
 * Page for creation of a new users.
 * 
 * @author Andreas Morsing - added user_is_name_valid whenever a new user will be created
**/
include('../../config.inc.php');
require_once('users.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

$newUser = isset($_POST['newUser']) ? $_POST['newUser'] : null;
$login = isset($_POST['login']) ? strings_stripSlashes($_POST['login']) : null;

$sqlResult = null;
if($newUser)
{
	if (strlen($login))
	{
		$password = isset($_POST['password']) ? strings_stripSlashes($_POST['password']) : null;
		$rightsid = isset($_POST['rights']) ? intval($_POST['rights']) : 5;
		$first = isset($_POST['first']) ? strings_stripSlashes($_POST['first']) : null;
		$last = isset($_POST['last']) ? strings_stripSlashes($_POST['last']) : null;
		$email = isset($_POST['email']) ? strings_stripSlashes($_POST['email']) : null;
		$locale = isset($_POST['locale']) ? strings_stripSlashes($_POST['locale']) : null;
	
		$userInfo = null;
		if (user_is_name_valid($login))
		{
			if (!existLogin($login,$userInfo))
			{
				if(!userInsert($login, $password, $first, $last, $email, $rightsid, $locale))
					$sqlResult = lang_get('user_not_added');
				else 
					$sqlResult = 'ok';
			}
			else
				$sqlResult = lang_get('duplicate_login');
		}
		else
			$sqlResult = $message = lang_get('invalid_user_name') . "\n" . lang_get('valid_user_name_format');
	}
	else
		$sqlResult = lang_get('empty_login_no'); 
}
$smarty = new TLSmarty;
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('name', $login);
$smarty->assign('roles', getListOfRights());
$smarty->assign('defaultLocale', TL_DEFAULT_LOCALE);
$smarty->display('adminUserNew.tpl');
?>

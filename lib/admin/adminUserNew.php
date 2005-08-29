<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: adminUserNew.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2005/08/29 11:13:46 $
 *
 * @author Martin Havlat
 *
 * Page for creation of a new users.
 * 
 * @author Andreas Morsing - added user_is_name_valid whenever a new user will be created
 * 20050829 - scs - moved POST params to the top of the script
 * 20050829 - scs - added locale while inserting new users
 * 
**/
include('../../config.inc.php');
require_once('users.inc.php');
testlinkInitPage();

$bNewUser = isset($_POST['newUser']) ? $_POST['newUser'] : null;
$login = isset($_POST['login']) ? strings_stripSlashes($_POST['login']) : null;
$password = isset($_POST['password']) ? strings_stripSlashes($_POST['password']) : null;
$rightsid = isset($_POST['rights']) ? intval($_POST['rights']) : 5;
$first = isset($_POST['first']) ? strings_stripSlashes($_POST['first']) : null;
$last = isset($_POST['last']) ? strings_stripSlashes($_POST['last']) : null;
$email = isset($_POST['email']) ? strings_stripSlashes($_POST['email']) : null;
$locale = isset($_POST['locale']) ? strings_stripSlashes($_POST['locale']) : null;

$sqlResult = null;
if($bNewUser)
{
	if (strlen($login))
	{
		if (user_is_name_valid($login))
		{
			$userInfo = null;
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

$smarty = new TLSmarty();
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('name', $login);
$smarty->assign('roles', getListOfRights());
$smarty->assign('defaultLocale', TL_DEFAULT_LOCALE);
$smarty->display('adminUserNew.tpl');
?>

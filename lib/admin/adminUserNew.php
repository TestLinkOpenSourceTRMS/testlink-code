<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: adminUserNew.php,v $
 *
 * @version $Revision: 1.7 $
 * @modified $Date: 2005/12/31 14:38:10 $
 *
 * @author Martin Havlat
 *
 * Page for creation of a new users.
 * 
 * ???????? - scw - added user_is_name_valid whenever a new user will be created
 * 20050829 - scs - moved POST params to the top of the script
 * 20050829 - scs - added locale while inserting new users
 * 20051112 - scs - added trim of login, to avoid usernames with only spaces
 * 20051231 - fm - changed due to active state of users
 * 
**/
include('../../config.inc.php');
require_once('users.inc.php');
testlinkInitPage();

$_POST = strings_stripSlashes($_POST);
$bNewUser = isset($_POST['newUser']) ? $_POST['newUser'] : null;
$login = isset($_POST['login']) ? trim($_POST['login']) : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;
$rightsid = isset($_POST['rights']) ? intval($_POST['rights']) : 5;
$first = isset($_POST['first']) ? $_POST['first'] : null;
$last = isset($_POST['last']) ? $_POST['last'] : null;
$email = isset($_POST['email']) ? $_POST['email'] : null;
$locale = isset($_POST['locale']) ? $_POST['locale'] : null;
$user_is_active = isset($_POST['user_is_active']) ? 1 : 0;

$sqlResult = null;
if($bNewUser)
{
	$sqlResult = lang_get("login_must_not_be_empty");
	if (strlen($login))
	{
		if (user_is_name_valid($login))
		{
			$userInfo = null;
			if (!existLogin($login,$userInfo))
			{
				if(!userInsert($login, $password, $first, $last, $email, $rightsid, $locale, $user_is_active))
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
}

$smarty = new TLSmarty();
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('name', $login);
$smarty->assign('roles', getListOfRights($db));
$smarty->assign('defaultLocale', TL_DEFAULT_LOCALE);
$smarty->display('adminUserNew.tpl');
?>

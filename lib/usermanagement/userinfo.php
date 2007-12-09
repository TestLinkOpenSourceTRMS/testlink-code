<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: userinfo.php,v $
*
* @version $Revision: 1.7 $
* @modified $Date: 2007/12/09 12:12:04 $
* 
* Displays the users' information and allows users to change 
* their passwords and user info.
*/
require_once('../../config.inc.php');
require_once('users.inc.php');
testlinkInitPage($db);

$template_dir = 'usermanagement/';

$_POST = strings_stripSlashes($_POST);
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$first = isset($_POST['first']) ? $_POST['first'] : null;
$last = isset($_POST['last']) ? $_POST['last'] : null;
$email = isset($_POST['email']) ? $_POST['email'] : null;
$locale = isset($_POST['locale']) ? $_POST['locale'] : null;
$old = isset($_POST['old']) ? $_POST['old'] : null;
$new = isset($_POST['new1']) ? $_POST['new1'] : null;
$bEdit = isset($_POST['editUser']) ? 1 : 0;
$bChangePwd = isset($_POST['changePasswd']) ? 1 : 0;
$userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0; 

$login_method = config_get('login_method');
$external_password_mgmt = ('LDAP' == $login_method )? 1 : 0;

$user = new tlUser($userID);
$user->readFromDB($db);

$updateResult = OK;
if ($bEdit)
{
	$user->m_firstName = $first;
	$user->m_lastName = $last;
	$user->m_emailAddress = $email;
	$user->m_locale = $locale;
}
else if ($bChangePwd)
{
	$updateResult = $user->comparePassword($old);
	if ($updateResult == OK)
		$updateResult = $user->setPassword($new);
}
if (($bEdit || $bChangePwd) && $updateResult == OK)
{
	$updateResult = $user->writeToDB($db);
	if ($updateResult == OK)
		setUserSession($db,$user->m_login, $userID, $user->m_globalRole, $user->m_emailAddress, $user->m_locale);
}
$msg = getUserErrorMessage($updateResult);
$user->readFromDB($db);

$smarty = new TLSmarty();
$smarty->assign('external_password_mgmt', $external_password_mgmt);
$smarty->assign('user',$user);
$smarty->assign('msg', $msg);
$smarty->assign('update_title_bar', $bEdit);
$smarty->display($template_dir . 'userInfo.tpl');
?>
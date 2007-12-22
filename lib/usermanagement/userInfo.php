<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: userInfo.php,v $
*
* @version $Revision: 1.3 $
* @modified $Date: 2007/12/22 12:26:46 $
* 
* Displays the users' information and allows users to change 
* their passwords and user info.
*/
require_once('../../config.inc.php');
require_once('users.inc.php');
testlinkInitPage($db);

$template_dir = 'usermanagement/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$_REQUEST = strings_stripSlashes($_REQUEST);
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$first = isset($_REQUEST['first']) ? $_REQUEST['first'] : null;
$last = isset($_REQUEST['last']) ? $_REQUEST['last'] : null;
$email = isset($_REQUEST['email']) ? $_REQUEST['email'] : null;
$locale = isset($_REQUEST['locale']) ? $_REQUEST['locale'] : null;
$old = isset($_REQUEST['old']) ? $_REQUEST['old'] : null;
$new = isset($_REQUEST['new1']) ? $_REQUEST['new1'] : null;
$bEdit = isset($_REQUEST['editUser']) ? 1 : 0;
$bChangePwd = isset($_REQUEST['changePasswd']) ? 1 : 0;
$userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0; 

$user = new tlUser($userID);
$user->readFromDB($db);

$updateResult = tl::OK;
if ($bEdit)
{
	$user->firstName = $first;
	$user->lastName = $last;
	$user->emailAddress = $email;
	$user->locale = $locale;
}
else if ($bChangePwd)
{
	$updateResult = $user->comparePassword($old);
	if ($updateResult >= tl::OK)
		$updateResult = $user->setPassword($new);
}
if (($bEdit || $bChangePwd) && $updateResult >= tl::OK)
{
	$updateResult = $user->writeToDB($db);
	if ($updateResult >= tl::OK)
		setUserSession($db,$user->login, $userID, $user->globalRoleID, $user->emailAddress, $user->locale);
}
$msg = getUserErrorMessage($updateResult);
$user->readFromDB($db);

$smarty = new TLSmarty();
$smarty->assign('external_password_mgmt',tlUser::isPasswordMgtExternal());
$smarty->assign('user',$user);
$smarty->assign('msg', $msg);
$smarty->assign('update_title_bar', $bEdit);
$smarty->display($template_dir . $default_template);
?>

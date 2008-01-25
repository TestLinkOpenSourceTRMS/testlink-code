<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: userInfo.php,v $
*
* @version $Revision: 1.12 $
* @modified $Date: 2008/01/25 11:31:37 $
* 
* Displays the users' information and allows users to change 
* their passwords and user info.
*/
require_once('../../config.inc.php');
require_once('users.inc.php');
require_once('../../lib/api/APIKey.php');
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
$bGenApi = isset($_REQUEST['genApi']) ? 1 : 0;
$bChangePwd = isset($_REQUEST['changePasswd']) ? 1 : 0;
$userID = isset($_SESSION['currentUser']) ? $_SESSION['currentUser']->dbID : 0; 

$user = new tlUser($userID);
$user->readFromDB($db);
$APIKey = new APIKey();

$auditMsg = null;
$updateResult = null;
$user_feedback = null;

if ($bEdit)
{
	$user->firstName = $first;
	$user->lastName = $last;
	$user->emailAddress = $email;
	$user->locale = $locale;
	$updateResult = tl::OK;
	$auditMsg = "audit_user_saved";
	$user_feedback = lang_get('result_user_changed');
}
else if ($bChangePwd)
{
	$updateResult = $user->comparePassword($old);
	if ($updateResult >= tl::OK)
	{
		$updateResult = $user->setPassword($new);
		$user_feedback = lang_get('result_password_changed');
	}
	$auditMsg = "audit_user_pwd_saved";
}
else if ($bGenApi)
{
		$api_key = $APIKey->addKeyForUser($userID);
		if (strlen($api_key)>0)
		{
			logAuditEvent(TLS("audit_user_apikey_set"),"CREATE",$userID,"users");
			$auditMsg = "audit_user_apikey_set";
			$user_feedback = lang_get('result_apikey_create_ok');
		}
}		

if (($bEdit || $bChangePwd) && $updateResult >= tl::OK)
{
	$updateResult = $user->writeToDB($db);
	if ($updateResult >= tl::OK)
	{
		logAuditEvent(TLS($auditMsg,$user->login),"SAVE",$user->dbID,"users");
		$_SESSION['currentUser'] = $user;
		setUserSession($db,$user->login, $userID, $user->globalRoleID, $user->emailAddress, $user->locale);
	}
}
$failedLogins = $g_tlLogger->getAuditEventsFor($userID,"users","LOGIN_FAILED",10);
$successfulLogins = $g_tlLogger->getAuditEventsFor($userID,"users","LOGIN",10);

$msg = null;
if ($updateResult)
	$msg = getUserErrorMessage($updateResult);
$user->readFromDB($db);

// set a string if not generated key yet
if (null == $user->userApiKey)
	$user->userApiKey = TLS('none');

$smarty = new TLSmarty();
$smarty->assign('external_password_mgmt',tlUser::isPasswordMgtExternal());
$smarty->assign('user',$user);
$smarty->assign('msg', $msg);
$smarty->assign('failedLogins', $failedLogins);
$smarty->assign('successfulLogins', $successfulLogins);
$smarty->assign('update_title_bar', $bEdit);
$smarty->assign('api_ui_show', $g_api_ui_show);
$smarty->assign('user_feedback', $user_feedback);
$smarty->display($template_dir . $default_template);
?>

<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package 	TestLink
 * @copyright 	2004-2009, TestLink community 
 * @version    	CVS: $Id: firstLogin.php,v 1.35 2010/10/02 14:01:38 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 * 
 *	20101002 - franciscom - BUGID 3828: TL19RC1 - User self signup - Too many warnings in event log.
 *	20090927 - franciscom - added feature: 
 *                          send mail notification to users with administrator role, 
 *                          when user creates her/his acccount.
 * 
 */
require_once('config.inc.php');
require_once('common.php');
require_once('users.inc.php');
require_once('email_api.php');

$templateCfg = templateConfiguration();
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
$args = init_args();
doDBConnect($db);

$message = lang_get('your_info_please');
if($args->doEditUser)
{
	if(strcmp($args->password,$args->password2))
	{
		$message = lang_get('passwd_dont_match');
	}
	else
	{
		$user = new tlUser();	
		$result = $user->setPassword($args->password);
		if ($result >= tl::OK)
		{
			$user->login = $args->login;
			$user->emailAddress = $args->email;
			$user->firstName = $args->firstName;
			$user->lastName = $args->lastName;
			$result = $user->writeToDB($db);
		}
		if ($result >= tl::OK)
		{
			mail2admins($db,$user);
			logAuditEvent(TLS("audit_users_self_signup",$args->login),"CREATE",$user->dbID,"users");
			redirect(TL_BASE_HREF . "login.php?note=first");
			exit();
		}
		else 
		{
			$message = getUserErrorMessage($result);
		}	
	}
}

$smarty = new TLSmarty();
$gui = $args;
$gui->external_password_mgmt = tlUser::isPasswordMgtExternal();
$gui->message = $message;
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->default_template);


/**
 * get input from user and return it in some sort of namespace
 *
 */
function init_args()
{
	$iParams = array("doEditUser" => array(tlInputParameter::STRING_N,0,1),
		             "login" => array(tlInputParameter::STRING_N,0,30),
		             "password" => array(tlInputParameter::STRING_N,0,32),
		             "password2" => array(tlInputParameter::STRING_N,0,32),
		             "firstName" => array(tlInputParameter::STRING_N,0,30),
		             "lastName" => array(tlInputParameter::STRING_N,0,30),
		             "email" => array(tlInputParameter::STRING_N,0,100));
	$args = new stdClass();
	P_PARAMS($iParams,$args);
	return $args;
}

/**
 * send mail to administrators to warn about new user created
 *
 */
function mail2admins(&$dbHandler,&$userObj)
{
	// Get email addresses for all users that have default role = administrator
	$roleMgr = new tlRole(TL_ROLES_ADMIN);
	$userSet = $roleMgr->getAllUsersWithRole($dbHandler);
	$mail['subject'] = lang_get('new_account');
	$key2loop = array_keys($userSet);
	foreach($key2loop as $userID)
	{
		$mail['to'][$userID] = $userSet[$userID]->emailAddress;	
    }
    // email_api uses ',' as list separator
    $mail['to'] = implode(',',$mail['to']);
    
    $mail['body'] = lang_get('new_account') . "\n";
    $mail['body'] .= " user:$userObj->login\n"; 
    $mail['body'] .= " first name:$userObj->firstName surname:$userObj->lastName\n";
    $mail['body'] .= " email:{$userObj->emailAddress}\n";
    
    // silence errors
	@email_send(config_get('from_email'), $mail['to'], $mail['subject'], $mail['body']);
}
?>
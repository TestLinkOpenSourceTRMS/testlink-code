<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: firstLogin.php,v $
 *
 * @version $Revision: 1.32 $
 * @modified $Date: 2009/06/04 19:53:27 $ $Author: schlundus $
 *
 */
require_once('config.inc.php');
require_once('common.php');
require_once('users.inc.php');

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
if($args->bEditUser)
{
	if(strcmp($args->password,$args->password2))
		$message = lang_get('passwd_dont_match');
	else
	{
		$user = new tlUser();	
		$result = $user->setPassword($args->password);
		if ($result >= tl::OK)
		{
			$user->login = $args->login;
			$user->emailAddress = $args->email;
			$user->firstName = $args->first;
			$user->lastName = $args->last;
			$result = $user->writeToDB($db);
		}
		if ($result >= tl::OK)
		{
			logAuditEvent(TLS("audit_users_self_signup",$args->login),"CREATE",$user->dbID,"users");
			redirect(TL_BASE_HREF . "login.php?note=first");
			exit();
		}
		else 
			$message = getUserErrorMessage($result);
	}
}

$smarty = new TLSmarty();
$smarty->assign('external_password_mgmt',tlUser::isPasswordMgtExternal());
$smarty->assign('login', $args->login);
$smarty->assign('firstName', $args->first);
$smarty->assign('lastName', $args->last);
$smarty->assign('email', $args->email);
$smarty->assign('message',$message);
$smarty->display('loginFirst.tpl');

function init_args()
{
	$iParams = array(
		"bEditUser" => array(tlInputParameter::STRING_N,0,1),
		"login" => array(tlInputParameter::STRING_N,0,30),
		"password" => array(tlInputParameter::STRING_N,0,32),
		"password2" => array(tlInputParameter::STRING_N,0,32),
		"first" => array(tlInputParameter::STRING_N,0,30),
		"last" => array(tlInputParameter::STRING_N,0,30),
		"email" => array(tlInputParameter::STRING_N,0,100),
	);
	$args = new stdClass();
	$pParams = P_PARAMS($iParams,$args);
	
	return $args;
}
?>
